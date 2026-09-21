<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\CustomerModel;
use App\Models\PembelianModel;
use App\Models\PenjualanModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $barangModel = new BarangModel();
        $customerModel = new CustomerModel();
        $penjualanModel = new PenjualanModel();
        $pembelianModel = new PembelianModel();

        $hariIni = date('Y-m-d');
        $awalBulan = date('Y-m-01');
        $akhirBulan = date('Y-m-t');

        $penjualanHariIni = $penjualanModel
            ->select('COUNT(*) AS jumlah, COALESCE(SUM(total), 0) AS omzet')
            ->where('DATE(tanggal)', $hariIni)
            ->first();

        $penjualanBulanIni = $penjualanModel
            ->select('COUNT(*) AS jumlah, COALESCE(SUM(total), 0) AS omzet')
            ->where('DATE(tanggal) >=', $awalBulan)
            ->where('DATE(tanggal) <=', $akhirBulan)
            ->first();

        $pembelianBulanIni = $pembelianModel
            ->select('COALESCE(SUM(total), 0) AS total')
            ->where('DATE(tanggal) >=', $awalBulan)
            ->where('DATE(tanggal) <=', $akhirBulan)
            ->first();

        $stokMenipis = $barangModel
            ->select('barang.*, kategori.nama_kategori')
            ->join('kategori', 'kategori.id_kategori = barang.id_kategori', 'left')
            ->where('barang.stok <=', 5)
            ->orderBy('barang.stok', 'ASC')
            ->findAll(6);

        $produkTerlaris = $db->table('detail_penjualan dp')
            ->select('b.nama_barang, b.satuan, SUM(dp.qty) AS qty_terjual, SUM(dp.subtotal) AS omzet')
            ->join('barang b', 'b.id_barang = dp.id_barang')
            ->join('penjualan p', 'p.id_penjualan = dp.id_penjualan')
            ->where('DATE(p.tanggal) >=', $awalBulan)
            ->where('DATE(p.tanggal) <=', $akhirBulan)
            ->groupBy(['dp.id_barang', 'b.nama_barang', 'b.satuan'])
            ->orderBy('qty_terjual', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $transaksiTerbaru = $penjualanModel
            ->select('penjualan.*, customer.nama_customer, users.nama_lengkap')
            ->join('customer', 'customer.id_customer = penjualan.id_customer', 'left')
            ->join('users', 'users.id_user = penjualan.id_user')
            ->orderBy('penjualan.tanggal', 'DESC')
            ->findAll(5);

        $grafikRows = $db->table('penjualan')
            ->select('DATE(tanggal) AS tanggal, SUM(total) AS omzet')
            ->where('DATE(tanggal) >=', date('Y-m-d', strtotime('-6 days')))
            ->groupBy('DATE(tanggal)')
            ->orderBy('tanggal', 'ASC')
            ->get()->getResultArray();

        $byDate = [];
        foreach ($grafikRows as $row) {
            $byDate[$row['tanggal']] = (float) $row['omzet'];
        }
        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = date('d M', strtotime($date));
            $chartValues[] = $byDate[$date] ?? 0;
        }

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'totalBarang' => $barangModel->countAll(),
            'totalCustomer' => $customerModel->countAll(),
            'transaksiHariIni' => (int) ($penjualanHariIni['jumlah'] ?? 0),
            'omzetHariIni' => (float) ($penjualanHariIni['omzet'] ?? 0),
            'transaksiBulanIni' => (int) ($penjualanBulanIni['jumlah'] ?? 0),
            'omzetBulanIni' => (float) ($penjualanBulanIni['omzet'] ?? 0),
            'pembelianBulanIni' => (float) ($pembelianBulanIni['total'] ?? 0),
            'stokMenipis' => $stokMenipis,
            'produkTerlaris' => $produkTerlaris,
            'transaksiTerbaru' => $transaksiTerbaru,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
        ]);
    }
}
