<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\CustomerModel;
use App\Models\DetailPenjualanModel;
use App\Models\MutasiStokModel;
use App\Models\PenjualanModel;

class Penjualan extends BaseController
{
    protected $penjualanModel;
    protected $detailModel;
    protected $barangModel;
    protected $customerModel;
    protected $mutasiModel;

    public function __construct()
    {
        $this->penjualanModel = new PenjualanModel();
        $this->detailModel = new DetailPenjualanModel();
        $this->barangModel = new BarangModel();
        $this->customerModel = new CustomerModel();
        $this->mutasiModel = new MutasiStokModel();
    }

    public function index()
    {
        return view('penjualan/index', [
            'title' => 'Transaksi Penjualan',
            'customer' => $this->customerModel->orderBy('nama_customer', 'ASC')->findAll(),
            'barang' => $this->barangModel
                ->select('barang.*, kategori.nama_kategori')
                ->join('kategori', 'kategori.id_kategori = barang.id_kategori', 'left')
                ->where('barang.stok >', 0)
                ->orderBy('barang.nama_barang', 'ASC')
                ->findAll(),
        ]);
    }

    public function simpan()
    {
        if ($guard = $this->guardSensitivePost(['admin', 'kasir'])) {
            return $guard;
        }

        $customerRaw = $this->request->getPost('id_customer');
        $customerId = ($customerRaw === null || $customerRaw === '') ? null : (int) $customerRaw;
        $barangIds = $this->request->getPost('id_barang');
        $qtys = $this->request->getPost('qty');
        $bayar = (float) $this->request->getPost('bayar');
        $metode = trim((string) $this->request->getPost('metode_pembayaran')) ?: 'Tunai';

        if (!in_array($metode, ['Tunai', 'QRIS', 'Transfer'], true)) {
            return redirect()->back()->withInput()->with('error', 'Metode pembayaran tidak valid.');
        }

        if (!is_array($barangIds) || !is_array($qtys)) {
            return redirect()->back()->withInput()->with('error', 'Keranjang masih kosong.');
        }

        // Gabungkan ID barang yang sama agar satu barang hanya divalidasi satu kali.
        $requested = [];
        foreach ($barangIds as $i => $idBarang) {
            $idBarang = (int) $idBarang;
            $qty = (int) ($qtys[$i] ?? 0);

            if ($idBarang <= 0 || $qty <= 0) {
                continue;
            }
            if ($qty > 100000) {
                return redirect()->back()->withInput()->with('error', 'Jumlah barang tidak wajar.');
            }

            $requested[$idBarang] = ($requested[$idBarang] ?? 0) + $qty;
        }

        if (!$requested) {
            return redirect()->back()->withInput()->with('error', 'Minimal harus ada satu barang.');
        }

        // Urutan lock yang konsisten mengurangi peluang deadlock pada transaksi bersamaan.
        ksort($requested, SORT_NUMERIC);

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if ($customerId !== null) {
                if ($customerId <= 0) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Customer tidak valid.');
                }

                // Lock customer agar tidak dapat dihapus di tengah proses penjualan.
                $customer = $db->query(
                    'SELECT id_customer FROM customer WHERE id_customer = ? FOR UPDATE',
                    [$customerId]
                )->getRowArray();

                if (!$customer) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Customer tidak ditemukan atau sudah dihapus.');
                }
            }

            $items = [];
            $total = 0.0;

            foreach ($requested as $idBarang => $qty) {
                // Row-level lock: penjualan/pembelian lain untuk barang yang sama harus menunggu.
                $barang = $db->query(
                    'SELECT id_barang, nama_barang, harga_jual, harga_beli, stok, satuan '
                    . 'FROM barang WHERE id_barang = ? FOR UPDATE',
                    [$idBarang]
                )->getRowArray();

                if (!$barang) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Ada barang yang tidak ditemukan.');
                }

                $stokSebelum = (int) $barang['stok'];
                if ($qty > $stokSebelum) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with(
                        'error',
                        'Stok ' . $barang['nama_barang'] . ' tidak cukup. Tersedia: ' . $stokSebelum . '.'
                    );
                }

                $harga = (float) $barang['harga_jual'];
                $hargaModal = (float) $barang['harga_beli'];
                $subtotal = $harga * $qty;
                $total += $subtotal;

                $items[] = [
                    'idBarang' => (int) $idBarang,
                    'qty' => (int) $qty,
                    'harga' => $harga,
                    'hargaModal' => $hargaModal,
                    'subtotal' => $subtotal,
                    'stokSebelum' => $stokSebelum,
                ];
            }

            if ($metode !== 'Tunai') {
                $bayar = $total;
                $kembalian = 0.0;
            } else {
                if ($bayar < $total) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with(
                        'error',
                        'Pembayaran kurang. Total Rp ' . number_format($total, 0, ',', '.') . '.'
                    );
                }
                $kembalian = $bayar - $total;
            }

            $noTransaksi = $this->buatNomorTransaksi();
            $insertHeader = $this->penjualanModel->insert([
                'no_transaksi' => $noTransaksi,
                'tanggal' => date('Y-m-d H:i:s'),
                'id_customer' => $customerId,
                'id_user' => (int) session()->get('id_user'),
                'total' => $total,
                'metode_pembayaran' => $metode,
                'bayar' => $bayar,
                'kembalian' => $kembalian,
            ]);
            if ($insertHeader === false) {
                throw new \RuntimeException('Header penjualan gagal disimpan.');
            }

            $idPenjualan = (int) $this->penjualanModel->getInsertID();

            foreach ($items as $item) {
                $stokSesudah = $item['stokSebelum'] - $item['qty'];

                if ($this->detailModel->insert([
                    'id_penjualan' => $idPenjualan,
                    'id_barang' => $item['idBarang'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'harga_modal' => $item['hargaModal'],
                    'subtotal' => $item['subtotal'],
                ]) === false) {
                    throw new \RuntimeException('Detail penjualan gagal disimpan.');
                }

                if (!$this->barangModel->update($item['idBarang'], ['stok' => $stokSesudah])) {
                    throw new \RuntimeException('Stok barang gagal diperbarui.');
                }

                if ($this->mutasiModel->insert([
                    'id_barang' => $item['idBarang'],
                    'id_user' => (int) session()->get('id_user'),
                    'tipe' => 'KELUAR',
                    'qty' => $item['qty'],
                    'stok_sebelum' => $item['stokSebelum'],
                    'stok_sesudah' => $stokSesudah,
                    'referensi_tipe' => 'PENJUALAN',
                    'referensi_id' => $idPenjualan,
                    'keterangan' => 'Penjualan ' . $noTransaksi,
                ]) === false) {
                    throw new \RuntimeException('Mutasi stok penjualan gagal disimpan.');
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database transaction failed.');
            }

            $db->transCommit();

            $this->auditEvent(
                'CREATE',
                'PENJUALAN',
                $idPenjualan,
                $noTransaksi,
                'Transaksi penjualan berhasil disimpan.',
                null,
                [
                    'no_transaksi' => $noTransaksi,
                    'id_customer' => $customerId,
                    'total' => $total,
                    'metode_pembayaran' => $metode,
                    'bayar' => $bayar,
                    'kembalian' => $kembalian,
                    'jumlah_baris_barang' => count($items),
                    'items' => array_map(
                        static fn(array $item): array => [
                            'id_barang' => $item['idBarang'],
                            'qty' => $item['qty'],
                            'harga' => $item['harga'],
                            'subtotal' => $item['subtotal'],
                        ],
                        $items
                    ),
                ]
            );

            return redirect()->to('/penjualan/sukses/' . $idPenjualan)
                ->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menyimpan penjualan: {message}', ['message' => $e->getMessage()]);
            $this->auditEvent(
                'CREATE',
                'PENJUALAN',
                null,
                null,
                'Transaksi penjualan gagal dan seluruh perubahan di-rollback.',
                null,
                [
                    'metode_pembayaran' => $metode,
                    'jumlah_barang_diminta' => count($requested ?? []),
                ],
                'FAILED'
            );
            return redirect()->back()->withInput()->with(
                'error',
                'Transaksi gagal disimpan. Tidak ada perubahan stok yang diterapkan.'
            );
        }
    }

    public function sukses($id)
    {
        $penjualan = $this->penjualanModel
            ->select('penjualan.*, customer.nama_customer, users.nama_lengkap')
            ->join('customer', 'customer.id_customer = penjualan.id_customer', 'left')
            ->join('users', 'users.id_user = penjualan.id_user')
            ->where('penjualan.id_penjualan', (int) $id)
            ->first();

        if (!$penjualan) {
            return redirect()->to('/penjualan/riwayat')->with('error', 'Transaksi tidak ditemukan.');
        }

        $detail = $this->detailModel
            ->select('detail_penjualan.*, barang.kode_barang, barang.nama_barang, barang.satuan')
            ->join('barang', 'barang.id_barang = detail_penjualan.id_barang')
            ->where('detail_penjualan.id_penjualan', (int) $id)
            ->findAll();

        return view('penjualan/sukses', [
            'title' => 'Detail Transaksi',
            'penjualan' => $penjualan,
            'detail' => $detail,
        ]);
    }

    public function riwayat()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $builder = $this->penjualanModel
            ->select('penjualan.*, customer.nama_customer, users.nama_lengkap')
            ->join('customer', 'customer.id_customer = penjualan.id_customer', 'left')
            ->join('users', 'users.id_user = penjualan.id_user', 'left');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('penjualan.no_transaksi', $keyword)
                ->orLike('customer.nama_customer', $keyword)
                ->orLike('users.nama_lengkap', $keyword)
                ->groupEnd();
        }

        return view('penjualan/riwayat', [
            'title' => 'Riwayat Penjualan',
            'penjualan' => $builder->orderBy('penjualan.id_penjualan', 'DESC')->findAll(),
            'keyword' => $keyword,
        ]);
    }

    public function laporan()
    {
        $tanggalMulai = $this->request->getGet('tanggal_mulai');
        $tanggalAkhir = $this->request->getGet('tanggal_akhir');
        $builder = $this->penjualanModel
            ->select('penjualan.*, customer.nama_customer, users.nama_lengkap')
            ->join('customer', 'customer.id_customer = penjualan.id_customer', 'left')
            ->join('users', 'users.id_user = penjualan.id_user', 'left');

        if ($tanggalMulai) {
            $builder->where('DATE(penjualan.tanggal) >=', $tanggalMulai);
        }
        if ($tanggalAkhir) {
            $builder->where('DATE(penjualan.tanggal) <=', $tanggalAkhir);
        }

        $rows = $builder->orderBy('penjualan.tanggal', 'DESC')->findAll();
        $total = array_sum(array_map(static fn($row) => (float) $row['total'], $rows));

        return view('laporan/penjualan', [
            'title' => 'Laporan Penjualan',
            'penjualan' => $rows,
            'tanggalMulai' => $tanggalMulai,
            'tanggalAkhir' => $tanggalAkhir,
            'totalTransaksi' => count($rows),
            'totalPenjualan' => $total,
        ]);
    }

    private function buatNomorTransaksi(): string
    {
        // Timestamp sampai mikrodetik + random membuat collision jauh lebih kecil.
        $now = new \DateTimeImmutable();
        return 'TRX-' . $now->format('YmdHisu') . '-' . random_int(1000, 9999);
    }
}
