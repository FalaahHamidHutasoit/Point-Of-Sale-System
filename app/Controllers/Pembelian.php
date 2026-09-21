<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\DetailPembelianModel;
use App\Models\MutasiStokModel;
use App\Models\PembelianModel;
use App\Models\SupplierModel;

class Pembelian extends BaseController
{
    protected $pembelianModel;
    protected $detailModel;
    protected $barangModel;
    protected $supplierModel;
    protected $mutasiModel;

    public function __construct()
    {
        $this->pembelianModel = new PembelianModel();
        $this->detailModel = new DetailPembelianModel();
        $this->barangModel = new BarangModel();
        $this->supplierModel = new SupplierModel();
        $this->mutasiModel = new MutasiStokModel();
    }

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));

        $builder = $this->pembelianModel
            ->select('pembelian.*, supplier.nama_supplier, users.nama_lengkap')
            ->join('supplier', 'supplier.id_supplier = pembelian.id_supplier')
            ->join('users', 'users.id_user = pembelian.id_user');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('pembelian.no_pembelian', $keyword)
                ->orLike('supplier.nama_supplier', $keyword)
                ->orLike('users.nama_lengkap', $keyword)
                ->groupEnd();
        }

        return view('pembelian/index', [
            'title' => 'Pembelian & Restock',
            'pembelian' => $builder->orderBy('pembelian.tanggal', 'DESC')->findAll(),
            'keyword' => $keyword,
        ]);
    }

    public function tambah()
    {
        return view('pembelian/tambah', [
            'title' => 'Tambah Pembelian',
            'supplier' => $this->supplierModel->orderBy('nama_supplier', 'ASC')->findAll(),
            'barang' => $this->barangModel->orderBy('nama_barang', 'ASC')->findAll(),
        ]);
    }

    public function simpan()
    {
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        $supplierId = (int) $this->request->getPost('id_supplier');
        $barangIds = $this->request->getPost('id_barang');
        $qtys = $this->request->getPost('qty');
        $hargaBelis = $this->request->getPost('harga_beli');
        $catatan = trim((string) $this->request->getPost('catatan'));

        if ($supplierId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Supplier wajib dipilih.');
        }
        if (mb_strlen($catatan) > 255) {
            return redirect()->back()->withInput()->with('error', 'Catatan maksimal 255 karakter.');
        }
        if (!is_array($barangIds) || !is_array($qtys) || !is_array($hargaBelis)) {
            return redirect()->back()->withInput()->with('error', 'Minimal satu barang harus ditambahkan.');
        }

        $requested = [];
        foreach ($barangIds as $i => $idBarang) {
            $idBarang = (int) $idBarang;
            $qty = (int) ($qtys[$i] ?? 0);
            $hargaBeli = (float) ($hargaBelis[$i] ?? -1);

            if ($idBarang <= 0 || $qty <= 0 || $hargaBeli < 0) {
                continue;
            }
            if ($qty > 100000) {
                return redirect()->back()->withInput()->with('error', 'Jumlah barang tidak wajar.');
            }
            if (isset($requested[$idBarang])) {
                return redirect()->back()->withInput()->with('error', 'Barang yang sama tidak boleh dimasukkan dua kali.');
            }

            $requested[$idBarang] = [
                'qty' => $qty,
                'hargaBeli' => $hargaBeli,
            ];
        }

        if (!$requested) {
            return redirect()->back()->withInput()->with('error', 'Isi barang, jumlah, dan harga beli dengan benar.');
        }

        // Penjualan dan pembelian sama-sama lock barang berdasarkan ID terurut.
        ksort($requested, SORT_NUMERIC);

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Supplier dikunci agar tidak dapat dihapus di tengah transaksi pembelian.
            $supplier = $db->query(
                'SELECT id_supplier FROM supplier WHERE id_supplier = ? FOR UPDATE',
                [$supplierId]
            )->getRowArray();

            if (!$supplier) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Supplier tidak ditemukan atau sudah dihapus.');
            }

            $items = [];
            $total = 0.0;

            foreach ($requested as $idBarang => $input) {
                $barang = $db->query(
                    'SELECT id_barang, nama_barang, stok FROM barang WHERE id_barang = ? FOR UPDATE',
                    [$idBarang]
                )->getRowArray();

                if (!$barang) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Ada barang yang tidak ditemukan.');
                }

                $subtotal = $input['qty'] * $input['hargaBeli'];
                $total += $subtotal;
                $items[] = [
                    'idBarang' => (int) $idBarang,
                    'qty' => (int) $input['qty'],
                    'hargaBeli' => (float) $input['hargaBeli'],
                    'subtotal' => $subtotal,
                    'stokSebelum' => (int) $barang['stok'],
                ];
            }

            $noPembelian = $this->buatNomorPembelian();
            $insertHeader = $this->pembelianModel->insert([
                'no_pembelian' => $noPembelian,
                'tanggal' => date('Y-m-d H:i:s'),
                'id_supplier' => $supplierId,
                'id_user' => (int) session()->get('id_user'),
                'total' => $total,
                'catatan' => $catatan ?: null,
            ]);
            if ($insertHeader === false) {
                throw new \RuntimeException('Header pembelian gagal disimpan.');
            }

            $idPembelian = (int) $this->pembelianModel->getInsertID();

            foreach ($items as $item) {
                $stokSesudah = $item['stokSebelum'] + $item['qty'];

                if ($this->detailModel->insert([
                    'id_pembelian' => $idPembelian,
                    'id_barang' => $item['idBarang'],
                    'qty' => $item['qty'],
                    'harga_beli' => $item['hargaBeli'],
                    'subtotal' => $item['subtotal'],
                ]) === false) {
                    throw new \RuntimeException('Detail pembelian gagal disimpan.');
                }

                if (!$this->barangModel->update($item['idBarang'], [
                    'stok' => $stokSesudah,
                    'harga_beli' => $item['hargaBeli'],
                ])) {
                    throw new \RuntimeException('Stok barang gagal diperbarui.');
                }

                if ($this->mutasiModel->insert([
                    'id_barang' => $item['idBarang'],
                    'id_user' => (int) session()->get('id_user'),
                    'tipe' => 'MASUK',
                    'qty' => $item['qty'],
                    'stok_sebelum' => $item['stokSebelum'],
                    'stok_sesudah' => $stokSesudah,
                    'referensi_tipe' => 'PEMBELIAN',
                    'referensi_id' => $idPembelian,
                    'keterangan' => 'Restock dari pembelian ' . $noPembelian,
                ]) === false) {
                    throw new \RuntimeException('Mutasi stok pembelian gagal disimpan.');
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database transaction failed.');
            }

            $db->transCommit();

            $this->auditEvent(
                'CREATE',
                'PEMBELIAN',
                $idPembelian,
                $noPembelian,
                'Pembelian/restock berhasil disimpan.',
                null,
                [
                    'no_pembelian' => $noPembelian,
                    'id_supplier' => $supplierId,
                    'total' => $total,
                    'catatan' => $catatan ?: null,
                    'jumlah_baris_barang' => count($items),
                    'items' => array_map(
                        static fn(array $item): array => [
                            'id_barang' => $item['idBarang'],
                            'qty' => $item['qty'],
                            'harga_beli' => $item['hargaBeli'],
                            'subtotal' => $item['subtotal'],
                        ],
                        $items
                    ),
                ]
            );

            return redirect()->to('/pembelian/' . $idPembelian)
                ->with('success', 'Pembelian berhasil disimpan dan stok telah diperbarui.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menyimpan pembelian: {message}', ['message' => $e->getMessage()]);
            $this->auditEvent(
                'CREATE',
                'PEMBELIAN',
                null,
                null,
                'Pembelian gagal dan seluruh perubahan di-rollback.',
                null,
                [
                    'id_supplier' => $supplierId,
                    'jumlah_barang_diminta' => count($requested ?? []),
                ],
                'FAILED'
            );
            return redirect()->back()->withInput()->with(
                'error',
                'Pembelian gagal disimpan. Tidak ada perubahan stok yang diterapkan.'
            );
        }
    }

    public function detail($id)
    {
        $header = $this->pembelianModel
            ->select('pembelian.*, supplier.nama_supplier, supplier.no_telp, users.nama_lengkap')
            ->join('supplier', 'supplier.id_supplier = pembelian.id_supplier')
            ->join('users', 'users.id_user = pembelian.id_user')
            ->where('pembelian.id_pembelian', (int) $id)
            ->first();

        if (!$header) {
            return redirect()->to('/pembelian')->with('error', 'Pembelian tidak ditemukan.');
        }

        $detail = $this->detailModel
            ->select('detail_pembelian.*, barang.kode_barang, barang.nama_barang, barang.satuan')
            ->join('barang', 'barang.id_barang = detail_pembelian.id_barang')
            ->where('detail_pembelian.id_pembelian', (int) $id)
            ->findAll();

        return view('pembelian/detail', [
            'title' => 'Detail Pembelian',
            'pembelian' => $header,
            'detail' => $detail,
        ]);
    }

    private function buatNomorPembelian(): string
    {
        $now = new \DateTimeImmutable();
        return 'PO-' . $now->format('YmdHisu') . '-' . random_int(1000, 9999);
    }
}
