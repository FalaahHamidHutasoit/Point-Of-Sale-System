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
        $customer = $this->request->getPost('id_customer');
        $barangIds = $this->request->getPost('id_barang');
        $qtys = $this->request->getPost('qty');
        $bayar = (float) $this->request->getPost('bayar');
        $metode = trim((string) $this->request->getPost('metode_pembayaran')) ?: 'Tunai';
        if (!in_array($metode, ['Tunai', 'QRIS', 'Transfer'], true)) {
            $metode = 'Tunai';
        }

        if (!is_array($barangIds) || !is_array($qtys)) {
            return redirect()->back()->withInput()->with('error', 'Keranjang masih kosong.');
        }

        // Gabungkan barang yang sama agar validasi stok menggunakan total qty per barang.
        $requested = [];
        foreach ($barangIds as $i => $idBarang) {
            $idBarang = (int) $idBarang;
            $qty = (int) ($qtys[$i] ?? 0);
            if ($idBarang > 0 && $qty > 0) {
                $requested[$idBarang] = ($requested[$idBarang] ?? 0) + $qty;
            }
        }

        if (!$requested) {
            return redirect()->back()->withInput()->with('error', 'Minimal harus ada satu barang.');
        }

        $items = [];
        $total = 0;
        foreach ($requested as $idBarang => $qty) {
            $barang = $this->barangModel->find($idBarang);
            if (!$barang) {
                return redirect()->back()->withInput()->with('error', 'Ada barang yang tidak ditemukan.');
            }
            if ($qty > (int) $barang['stok']) {
                return redirect()->back()->withInput()->with('error', 'Stok ' . $barang['nama_barang'] . ' tidak cukup. Tersedia: ' . $barang['stok'] . '.');
            }

            $harga = (float) $barang['harga_jual'];
            $hargaModal = (float) $barang['harga_beli'];
            $subtotal = $harga * $qty;
            $total += $subtotal;
            $items[] = compact('idBarang', 'qty', 'harga', 'hargaModal', 'subtotal', 'barang');
        }

        if ($metode !== 'Tunai') {
            // Pembayaran non-tunai dianggap lunas sesuai total transaksi.
            $bayar = $total;
        } elseif ($bayar < $total) {
            return redirect()->back()->withInput()->with('error', 'Pembayaran kurang. Total Rp ' . number_format($total, 0, ',', '.') . '.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $noTransaksi = 'TRX-' . date('YmdHis') . '-' . random_int(10, 99);
            $this->penjualanModel->insert([
                'no_transaksi' => $noTransaksi,
                'tanggal' => date('Y-m-d H:i:s'),
                'id_customer' => !empty($customer) ? (int) $customer : null,
                'id_user' => (int) session()->get('id_user'),
                'total' => $total,
                'metode_pembayaran' => $metode,
                'bayar' => $bayar,
                'kembalian' => $bayar - $total,
            ]);
            $idPenjualan = $this->penjualanModel->getInsertID();

            foreach ($items as $item) {
                // Baca stok ulang di dalam transaksi sebelum update.
                $current = $this->barangModel->find($item['idBarang']);
                $stokSebelum = (int) $current['stok'];
                if ($item['qty'] > $stokSebelum) {
                    throw new \RuntimeException('Stok berubah saat transaksi diproses.');
                }
                $stokSesudah = $stokSebelum - $item['qty'];

                $this->detailModel->insert([
                    'id_penjualan' => $idPenjualan,
                    'id_barang' => $item['idBarang'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'harga_modal' => $item['hargaModal'],
                    'subtotal' => $item['subtotal'],
                ]);

                $this->barangModel->update($item['idBarang'], ['stok' => $stokSesudah]);
                $this->mutasiModel->insert([
                    'id_barang' => $item['idBarang'],
                    'id_user' => (int) session()->get('id_user'),
                    'tipe' => 'KELUAR',
                    'qty' => $item['qty'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi_tipe' => 'PENJUALAN',
                    'referensi_id' => $idPenjualan,
                    'keterangan' => 'Penjualan ' . $noTransaksi,
                ]);
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database transaction failed');
            }
            $db->transCommit();
            return redirect()->to('/penjualan/sukses/' . $idPenjualan)->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menyimpan penjualan: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Transaksi gagal disimpan. Silakan cek stok dan coba lagi.');
        }
    }

    public function sukses($id)
    {
        $penjualan = $this->penjualanModel
            ->select('penjualan.*, customer.nama_customer, users.nama_lengkap')
            ->join('customer', 'customer.id_customer = penjualan.id_customer', 'left')
            ->join('users', 'users.id_user = penjualan.id_user')
            ->where('penjualan.id_penjualan', $id)
            ->first();

        if (!$penjualan) {
            return redirect()->to('/penjualan/riwayat')->with('error', 'Transaksi tidak ditemukan.');
        }

        $detail = $this->detailModel
            ->select('detail_penjualan.*, barang.kode_barang, barang.nama_barang, barang.satuan')
            ->join('barang', 'barang.id_barang = detail_penjualan.id_barang')
            ->where('detail_penjualan.id_penjualan', $id)
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
}
