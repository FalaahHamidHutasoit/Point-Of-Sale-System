<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;

class Barang extends BaseController
{
    protected $barangModel;
    protected $kategoriModel;

    public function __construct()
    {
        $this->barangModel = new BarangModel();
        $this->kategoriModel = new KategoriModel();
    }

    // =========================
    // MENAMPILKAN DATA BARANG
    // =========================
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $keyword = $this->request->getGet('keyword');

        $builder = $this->barangModel
            ->select('barang.*, kategori.nama_kategori')
            ->join(
                'kategori',
                'kategori.id_kategori = barang.id_kategori'
            );

        if ($keyword) {
            $builder->groupStart()
                ->like('barang.kode_barang', $keyword)
                ->orLike('barang.nama_barang', $keyword)
                ->orLike('kategori.nama_kategori', $keyword)
                ->groupEnd();
        }

        $data = [
            'title' => 'Data Barang',
            'barang' => $builder->orderBy('barang.id_barang', 'DESC')->findAll(),
            'keyword' => $keyword
        ];

        return view('barang/index', $data);
    }

    // =========================
    // FORM TAMBAH
    // =========================
    public function tambah()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Tambah Barang',
            'kategori' => $this->kategoriModel->findAll()
        ];

        return view('barang/tambah', $data);
    }

    // =========================
    // SIMPAN BARANG
    // =========================
    public function simpan()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $kode = trim($this->request->getPost('kode_barang'));
        $nama = trim($this->request->getPost('nama_barang'));
        $kategori = $this->request->getPost('id_kategori');
        $hargaBeli = $this->request->getPost('harga_beli');
        $hargaJual = $this->request->getPost('harga_jual');
        $stok = $this->request->getPost('stok');
        $satuan = trim($this->request->getPost('satuan'));

        // Validasi
        if (
            empty($kode) ||
            empty($nama) ||
            empty($kategori) ||
            $hargaBeli === '' ||
            $hargaJual === '' ||
            $stok === '' ||
            empty($satuan)
        ) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Semua field wajib diisi.');
        }

        if ($hargaBeli < 0 || $hargaJual < 0 || $stok < 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Harga dan stok tidak boleh bernilai negatif.');
        }

        // Cek kode barang
        $cekKode = $this->barangModel
            ->where('kode_barang', $kode)
            ->first();

        if ($cekKode) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Kode barang sudah digunakan.');
        }

        $this->barangModel->insert([
            'id_kategori' => $kategori,
            'kode_barang' => $kode,
            'nama_barang' => $nama,
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaJual,
            'stok' => $stok,
            'satuan' => $satuan
        ]);

        return redirect()->to('/barang')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    // =========================
    // FORM EDIT
    // =========================
    public function edit($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $barang = $this->barangModel->find($id);

        if (!$barang) {
            return redirect()->to('/barang')
                ->with('error', 'Data barang tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Barang',
            'barang' => $barang,
            'kategori' => $this->kategoriModel->findAll()
        ];

        return view('barang/edit', $data);
    }

    // =========================
    // UPDATE BARANG
    // =========================
    public function update($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $barang = $this->barangModel->find($id);

        if (!$barang) {
            return redirect()->to('/barang')
                ->with('error', 'Data barang tidak ditemukan.');
        }

        $kode = trim($this->request->getPost('kode_barang'));
        $nama = trim($this->request->getPost('nama_barang'));
        $kategori = $this->request->getPost('id_kategori');
        $hargaBeli = $this->request->getPost('harga_beli');
        $hargaJual = $this->request->getPost('harga_jual');
        $stok = $this->request->getPost('stok');
        $satuan = trim($this->request->getPost('satuan'));

        if (
            empty($kode) ||
            empty($nama) ||
            empty($kategori) ||
            $hargaBeli === '' ||
            $hargaJual === '' ||
            $stok === '' ||
            empty($satuan)
        ) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Semua field wajib diisi.');
        }

        if ($hargaBeli < 0 || $hargaJual < 0 || $stok < 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Harga dan stok tidak boleh bernilai negatif.');
        }

        // Cek kode barang agar tidak sama dengan barang lain
        $cekKode = $this->barangModel
            ->where('kode_barang', $kode)
            ->where('id_barang !=', $id)
            ->first();

        if ($cekKode) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Kode barang sudah digunakan oleh barang lain.');
        }

        $this->barangModel->update($id, [
            'id_kategori' => $kategori,
            'kode_barang' => $kode,
            'nama_barang' => $nama,
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaJual,
            'stok' => $stok,
            'satuan' => $satuan
        ]);

        return redirect()->to('/barang')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    // =========================
    // HAPUS BARANG
    // =========================
    public function hapus($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $barang = $this->barangModel->find($id);

        if (!$barang) {
            return redirect()->to('/barang')
                ->with('error', 'Data barang tidak ditemukan.');
        }

        try {

            $this->barangModel->delete($id);

            return redirect()->to('/barang')
                ->with('success', 'Barang berhasil dihapus.');

        } catch (\Exception $e) {

            return redirect()->to('/barang')
                ->with('error', 'Barang tidak dapat dihapus karena sudah digunakan dalam transaksi.');
        }
    }

    public function laporanBarang()
{
    $keyword = $this->request->getGet('keyword');

    $builder = $this->barangModel
        ->select('barang.*, kategori.nama_kategori')
        ->join('kategori', 'kategori.id_kategori = barang.id_kategori');

    if ($keyword) {
        $builder->groupStart()
            ->like('barang.kode_barang', $keyword)
            ->orLike('barang.nama_barang', $keyword)
            ->orLike('kategori.nama_kategori', $keyword)
            ->groupEnd();
    }

    $data = [
        'title'   => 'Laporan Barang',
        'barang'  => $builder->orderBy('barang.nama_barang', 'ASC')->findAll(),
        'keyword' => $keyword
    ];

    return view('laporan/barang', $data);
}
}