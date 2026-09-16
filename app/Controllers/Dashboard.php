<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\CustomerModel;
use App\Models\PenjualanModel;

class Dashboard extends BaseController
{
    protected $barangModel;
    protected $customerModel;
    protected $penjualanModel;

    public function __construct()
    {
        $this->barangModel = new BarangModel();
        $this->customerModel = new CustomerModel();
        $this->penjualanModel = new PenjualanModel();
    }

    public function index()
    {
        // Cek login
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        // Statistik
        $totalBarang = $this->barangModel->countAll();

        $totalCustomer = $this->customerModel->countAll();

        $totalTransaksi = $this->penjualanModel->countAll();

        $stokMenipis = $this->barangModel
        ->select('barang.*, kategori.nama_kategori')
        ->join('kategori', 'kategori.id_kategori = barang.id_kategori')
        ->where('barang.stok <=', 5)
        ->orderBy('barang.stok', 'ASC')
        ->findAll();

        // Total penjualan
        $totalPenjualan = $this->penjualanModel
            ->selectSum('total')
            ->first();

        $data = [
            'title'          => 'Dashboard',
            'totalBarang'    => $totalBarang,
            'totalCustomer'  => $totalCustomer,
            'totalTransaksi' => $totalTransaksi,
            'stokMenipis'    => $stokMenipis,
            'totalPenjualan' => $totalPenjualan['total'] ?? 0
        ];

        return view('dashboard/index', $data);
    }
}