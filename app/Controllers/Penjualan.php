<?php

namespace App\Controllers;

use App\Models\PenjualanModel;
use App\Models\DetailPenjualanModel;
use App\Models\BarangModel;
use App\Models\CustomerModel;

class Penjualan extends BaseController
{
    protected $penjualanModel;
    protected $detailModel;
    protected $barangModel;
    protected $customerModel;

    public function __construct()
    {
        $this->penjualanModel = new PenjualanModel();
        $this->detailModel = new DetailPenjualanModel();
        $this->barangModel = new BarangModel();
        $this->customerModel = new CustomerModel();
    }

    // ==========================================
    // HALAMAN TRANSAKSI
    // ==========================================
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Transaksi Penjualan',

            'customer' => $this->customerModel
                ->orderBy('nama_customer', 'ASC')
                ->findAll(),

            'barang' => $this->barangModel
                ->where('stok >', 0)
                ->orderBy('nama_barang', 'ASC')
                ->findAll()
        ];

        return view('penjualan/index', $data);
    }

    // ==========================================
    // SIMPAN TRANSAKSI
    // ==========================================
    public function simpan()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $customer = $this->request->getPost('id_customer');
        $barang = $this->request->getPost('id_barang');
        $qty = $this->request->getPost('qty');
        $bayar = $this->request->getPost('bayar');

        // Validasi dasar
        if (empty($barang)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Barang belum dipilih.');
        }

        if (empty($qty)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Jumlah barang belum diisi.');
        }

        if ($bayar === null || $bayar === '') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Nominal pembayaran belum diisi.');
        }

        // Pastikan array
        if (!is_array($barang)) {
            $barang = [$barang];
        }

        if (!is_array($qty)) {
            $qty = [$qty];
        }

        // ==========================================
        // HITUNG TOTAL DAN VALIDASI STOK
        // ==========================================
        $total = 0;
        $items = [];

        foreach ($barang as $key => $id_barang) {

            $jumlah = (int) ($qty[$key] ?? 0);

            if ($jumlah <= 0) {
                continue;
            }

            $dataBarang = $this->barangModel->find($id_barang);

            if (!$dataBarang) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Barang tidak ditemukan.');
            }

            // Cek stok
            if ($jumlah > $dataBarang['stok']) {
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Stok ' . $dataBarang['nama_barang'] .
                        ' tidak mencukupi. Stok tersedia: ' .
                        $dataBarang['stok']
                    );
            }

            $harga = (float) $dataBarang['harga_jual'];
            $subtotal = $harga * $jumlah;

            $total += $subtotal;

            $items[] = [
                'id_barang' => $id_barang,
                'qty' => $jumlah,
                'harga' => $harga,
                'subtotal' => $subtotal
            ];
        }

        if (empty($items)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Minimal harus ada satu barang.');
        }

        // ==========================================
        // VALIDASI PEMBAYARAN
        // ==========================================
        $bayar = (float) $bayar;

        if ($bayar < $total) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Uang pembayaran kurang. Total transaksi Rp ' .
                    number_format($total, 0, ',', '.')
                );
        }

        $kembalian = $bayar - $total;

        // ==========================================
        // NOMOR TRANSAKSI
        // ==========================================
        $noTransaksi = 'TRX-' . date('YmdHis');

        // ==========================================
        // DATABASE TRANSACTION
        // ==========================================
        $db = \Config\Database::connect();

        $db->transBegin();

        try {

            // -------------------------------
            // SIMPAN PENJUALAN
            // -------------------------------
            $this->penjualanModel->insert([
                'no_transaksi' => $noTransaksi,
                'tanggal' => date('Y-m-d H:i:s'),
                'id_customer' => !empty($customer) ? $customer : null,
                'id_user' => session()->get('id_user'),
                'total' => $total,
                'bayar' => $bayar,
                'kembalian' => $kembalian
            ]);

            $idPenjualan = $this->penjualanModel->getInsertID();

            // -------------------------------
            // SIMPAN DETAIL + KURANGI STOK
            // -------------------------------
            foreach ($items as $item) {

                $this->detailModel->insert([
                    'id_penjualan' => $idPenjualan,
                    'id_barang' => $item['id_barang'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal']
                ]);

                // Kurangi stok
                $this->barangModel
                    ->set(
                        'stok',
                        'stok - ' . $item['qty'],
                        false
                    )
                    ->where('id_barang', $item['id_barang'])
                    ->update();
            }

            // ==========================================
            // CEK TRANSACTION
            // ==========================================
            if ($db->transStatus() === false) {
                $db->transRollback();

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Transaksi gagal disimpan.');
            }

            $db->transCommit();

            return redirect()->to(
                '/penjualan/sukses/' . $idPenjualan
            )->with(
                'success',
                'Transaksi berhasil disimpan.'
            );

        } catch (\Throwable $e) {

            $db->transRollback();

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Terjadi kesalahan saat menyimpan transaksi.'
                );
        }
    }

    // ==========================================
    // HALAMAN SUKSES
    // ==========================================
    public function sukses($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $penjualan = $this->penjualanModel
            ->select('penjualan.*, customer.nama_customer, users.nama_lengkap')
            ->join(
                'customer',
                'customer.id_customer = penjualan.id_customer',
                'left'
            )
            ->join(
                'users',
                'users.id_user = penjualan.id_user'
            )
            ->where('penjualan.id_penjualan', $id)
            ->first();

        if (!$penjualan) {
            return redirect()->to('/penjualan')
                ->with('error', 'Transaksi tidak ditemukan.');
        }

        $detail = $this->detailModel
            ->select(
                'detail_penjualan.*, barang.kode_barang, barang.nama_barang, barang.satuan'
            )
            ->join(
                'barang',
                'barang.id_barang = detail_penjualan.id_barang'
            )
            ->where(
                'detail_penjualan.id_penjualan',
                $id
            )
            ->findAll();

        $data = [
            'title' => 'Transaksi Berhasil',
            'penjualan' => $penjualan,
            'detail' => $detail
        ];

        return view('penjualan/sukses', $data);
    }

    // ==========================================
// RIWAYAT TRANSAKSI
// ==========================================
public function riwayat()
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $keyword = $this->request->getGet('keyword');

    $builder = $this->penjualanModel
        ->select('
            penjualan.*,
            customer.nama_customer,
            users.nama_lengkap
        ')
        ->join(
            'customer',
            'customer.id_customer = penjualan.id_customer',
            'left'
        )
        ->join(
            'users',
            'users.id_user = penjualan.id_user',
            'left'
        );

    if ($keyword) {
        $builder->groupStart()
            ->like('penjualan.no_transaksi', $keyword)
            ->orLike('customer.nama_customer', $keyword)
            ->orLike('users.nama_lengkap', $keyword)
            ->groupEnd();
    }

    $data = [
        'title' => 'Riwayat Transaksi',
        'penjualan' => $builder
            ->orderBy('penjualan.id_penjualan', 'DESC')
            ->findAll(),
        'keyword' => $keyword
    ];

    return view('penjualan/riwayat', $data);
  }

  // ==========================================
// LAPORAN PENJUALAN
// ==========================================
public function laporan()
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $tanggalMulai = $this->request->getGet('tanggal_mulai');
    $tanggalAkhir = $this->request->getGet('tanggal_akhir');

    $builder = $this->penjualanModel
        ->select('
            penjualan.*,
            customer.nama_customer,
            users.nama_lengkap
        ')
        ->join(
            'customer',
            'customer.id_customer = penjualan.id_customer',
            'left'
        )
        ->join(
            'users',
            'users.id_user = penjualan.id_user',
            'left'
        );

    // Filter tanggal
    if (!empty($tanggalMulai)) {
        $builder->where(
            'DATE(penjualan.tanggal) >=',
            $tanggalMulai
        );
    }

    if (!empty($tanggalAkhir)) {
        $builder->where(
            'DATE(penjualan.tanggal) <=',
            $tanggalAkhir
        );
    }

    $dataPenjualan = $builder
        ->orderBy('penjualan.tanggal', 'DESC')
        ->findAll();

    // Hitung total
    $totalPenjualan = 0;

    foreach ($dataPenjualan as $row) {
        $totalPenjualan += (float) $row['total'];
    }

    $data = [
        'title' => 'Laporan Penjualan',
        'penjualan' => $dataPenjualan,
        'tanggalMulai' => $tanggalMulai,
        'tanggalAkhir' => $tanggalAkhir,
        'totalTransaksi' => count($dataPenjualan),
        'totalPenjualan' => $totalPenjualan
    ];

    return view('laporan/penjualan', $data);
    }
}