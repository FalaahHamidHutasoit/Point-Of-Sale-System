<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;
use App\Models\MutasiStokModel;

class Barang extends BaseController
{
    protected $barangModel;
    protected $kategoriModel;
    protected $mutasiModel;

    public function __construct()
    {
        $this->barangModel = new BarangModel();
        $this->kategoriModel = new KategoriModel();
        $this->mutasiModel = new MutasiStokModel();
    }

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));

        $builder = $this->barangModel
            ->select('barang.*, kategori.nama_kategori')
            ->join('kategori', 'kategori.id_kategori = barang.id_kategori');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('barang.kode_barang', $keyword)
                ->orLike('barang.nama_barang', $keyword)
                ->orLike('kategori.nama_kategori', $keyword)
                ->groupEnd();
        }

        return view('barang/index', [
            'title' => 'Data Barang',
            'barang' => $builder->orderBy('barang.nama_barang', 'ASC')->findAll(),
            'keyword' => $keyword,
        ]);
    }

    public function tambah()
    {
        return view('barang/tambah', [
            'title' => 'Tambah Barang',
            'kategori' => $this->kategoriModel->orderBy('nama_kategori', 'ASC')->findAll(),
        ]);
    }

    public function simpan()
    {
        $kode = trim((string) $this->request->getPost('kode_barang'));
        $nama = trim((string) $this->request->getPost('nama_barang'));
        $kategori = (int) $this->request->getPost('id_kategori');
        $hargaBeli = (float) $this->request->getPost('harga_beli');
        $hargaJual = (float) $this->request->getPost('harga_jual');
        $stokAwal = (int) $this->request->getPost('stok');
        $satuan = trim((string) $this->request->getPost('satuan'));

        if ($kode === '' || $nama === '' || !$kategori || $satuan === '') {
            return redirect()->back()->withInput()->with('error', 'Kode, nama, kategori, dan satuan wajib diisi.');
        }
        if ($hargaBeli < 0 || $hargaJual < 0 || $stokAwal < 0) {
            return redirect()->back()->withInput()->with('error', 'Harga dan stok tidak boleh negatif.');
        }
        if ($this->barangModel->where('kode_barang', $kode)->first()) {
            return redirect()->back()->withInput()->with('error', 'Kode barang sudah digunakan.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $this->barangModel->insert([
                'id_kategori' => $kategori,
                'kode_barang' => $kode,
                'nama_barang' => $nama,
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'stok' => $stokAwal,
                'satuan' => $satuan,
            ]);
            $idBarang = $this->barangModel->getInsertID();

            if ($stokAwal > 0) {
                $this->mutasiModel->insert([
                    'id_barang' => $idBarang,
                    'id_user' => (int) session()->get('id_user'),
                    'tipe' => 'PENYESUAIAN',
                    'qty' => $stokAwal,
                    'stok_sebelum' => 0,
                    'stok_sesudah' => $stokAwal,
                    'referensi_tipe' => 'STOK_AWAL',
                    'referensi_id' => $idBarang,
                    'keterangan' => 'Stok awal saat barang dibuat',
                ]);
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal menyimpan barang');
            }
            $db->transCommit();
            return redirect()->to('/barang')->with('success', 'Barang berhasil ditambahkan. Restock berikutnya lakukan melalui menu Pembelian.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Barang gagal disimpan.');
        }
    }

    public function edit($id)
    {
        $barang = $this->barangModel->find($id);
        if (!$barang) {
            return redirect()->to('/barang')->with('error', 'Data barang tidak ditemukan.');
        }

        return view('barang/edit', [
            'title' => 'Edit Barang',
            'barang' => $barang,
            'kategori' => $this->kategoriModel->orderBy('nama_kategori', 'ASC')->findAll(),
        ]);
    }

    public function update($id)
    {
        $barang = $this->barangModel->find($id);
        if (!$barang) {
            return redirect()->to('/barang')->with('error', 'Data barang tidak ditemukan.');
        }

        $kode = trim((string) $this->request->getPost('kode_barang'));
        $nama = trim((string) $this->request->getPost('nama_barang'));
        $kategori = (int) $this->request->getPost('id_kategori');
        $hargaBeli = (float) $this->request->getPost('harga_beli');
        $hargaJual = (float) $this->request->getPost('harga_jual');
        $satuan = trim((string) $this->request->getPost('satuan'));

        if ($kode === '' || $nama === '' || !$kategori || $satuan === '') {
            return redirect()->back()->withInput()->with('error', 'Semua data barang wajib diisi.');
        }
        if ($hargaBeli < 0 || $hargaJual < 0) {
            return redirect()->back()->withInput()->with('error', 'Harga tidak boleh negatif.');
        }

        $duplikat = $this->barangModel->where('kode_barang', $kode)->where('id_barang !=', $id)->first();
        if ($duplikat) {
            return redirect()->back()->withInput()->with('error', 'Kode barang sudah digunakan barang lain.');
        }

        // Stok tidak diubah dari form edit. Semua perubahan stok harus punya jejak mutasi.
        $this->barangModel->update($id, [
            'id_kategori' => $kategori,
            'kode_barang' => $kode,
            'nama_barang' => $nama,
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaJual,
            'satuan' => $satuan,
        ]);

        return redirect()->to('/barang')->with('success', 'Data barang diperbarui. Stok tidak berubah.');
    }

    public function hapus($id)
    {
        $barang = $this->barangModel->find($id);
        if (!$barang) {
            return redirect()->to('/barang')->with('error', 'Data barang tidak ditemukan.');
        }

        try {
            $this->barangModel->delete($id);
            return redirect()->to('/barang')->with('success', 'Barang berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/barang')->with('error', 'Barang tidak dapat dihapus karena sudah memiliki histori transaksi.');
        }
    }

    public function laporanBarang()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $builder = $this->barangModel
            ->select('barang.*, kategori.nama_kategori')
            ->join('kategori', 'kategori.id_kategori = barang.id_kategori');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('barang.kode_barang', $keyword)
                ->orLike('barang.nama_barang', $keyword)
                ->orLike('kategori.nama_kategori', $keyword)
                ->groupEnd();
        }

        return view('laporan/barang', [
            'title' => 'Laporan Persediaan',
            'barang' => $builder->orderBy('barang.nama_barang', 'ASC')->findAll(),
            'keyword' => $keyword,
        ]);
    }
}
