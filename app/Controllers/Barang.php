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
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        $kode = trim((string) $this->request->getPost('kode_barang'));
        $nama = trim((string) $this->request->getPost('nama_barang'));
        $kategori = (int) $this->request->getPost('id_kategori');
        $hargaBeli = (float) $this->request->getPost('harga_beli');
        $hargaJual = (float) $this->request->getPost('harga_jual');
        $stokAwal = (int) $this->request->getPost('stok');
        $satuan = trim((string) $this->request->getPost('satuan'));

        if ($kode === '' || $nama === '' || $kategori <= 0 || $satuan === '') {
            return redirect()->back()->withInput()->with('error', 'Kode, nama, kategori, dan satuan wajib diisi.');
        }
        if (mb_strlen($kode) > 50 || mb_strlen($nama) > 150 || mb_strlen($satuan) > 30) {
            return redirect()->back()->withInput()->with('error', 'Panjang kode, nama, atau satuan melebihi batas.');
        }
        if ($hargaBeli < 0 || $hargaJual < 0 || $stokAwal < 0) {
            return redirect()->back()->withInput()->with('error', 'Harga dan stok tidak boleh negatif.');
        }
        if (!$this->kategoriModel->find($kategori)) {
            return redirect()->back()->withInput()->with('error', 'Kategori tidak valid atau sudah dihapus.');
        }
        if ($this->barangModel->where('kode_barang', $kode)->first()) {
            return redirect()->back()->withInput()->with('error', 'Kode barang sudah digunakan.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Kunci kategori selama insert agar relasi parent-child tetap konsisten.
            $kategoriRow = $db->query(
                'SELECT id_kategori FROM kategori WHERE id_kategori = ? FOR UPDATE',
                [$kategori]
            )->getRowArray();
            if (!$kategoriRow) {
                throw new \RuntimeException('Kategori tidak tersedia.');
            }

            $inserted = $this->barangModel->insert([
                'id_kategori' => $kategori,
                'kode_barang' => $kode,
                'nama_barang' => $nama,
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'stok' => $stokAwal,
                'satuan' => $satuan,
            ]);
            if (!$inserted) {
                throw new \RuntimeException('Insert barang gagal.');
            }

            $idBarang = (int) $this->barangModel->getInsertID();

            if ($stokAwal > 0) {
                if (!$this->mutasiModel->insert([
                    'id_barang' => $idBarang,
                    'id_user' => (int) session()->get('id_user'),
                    'tipe' => 'PENYESUAIAN',
                    'qty' => $stokAwal,
                    'stok_sebelum' => 0,
                    'stok_sesudah' => $stokAwal,
                    'referensi_tipe' => 'STOK_AWAL',
                    'referensi_id' => $idBarang,
                    'keterangan' => 'Stok awal saat barang dibuat',
                ])) {
                    throw new \RuntimeException('Mutasi stok awal gagal.');
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database transaction failed.');
            }

            $db->transCommit();
            $this->auditEvent(
                'CREATE',
                'BARANG',
                $idBarang,
                $nama,
                'Barang baru ditambahkan.',
                null,
                [
                    'id_barang' => $idBarang,
                    'kode_barang' => $kode,
                    'nama_barang' => $nama,
                    'id_kategori' => $kategori,
                    'harga_beli' => $hargaBeli,
                    'harga_jual' => $hargaJual,
                    'stok' => $stokAwal,
                    'satuan' => $satuan,
                ]
            );
            return redirect()->to('/barang')
                ->with('success', 'Barang berhasil ditambahkan. Restock berikutnya lakukan melalui menu Pembelian.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menyimpan barang: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Barang gagal disimpan. Periksa kembali kode dan kategori.');
        }
    }

    public function edit($id)
    {
        $barang = $this->barangModel->find((int) $id);
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
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        $id = (int) $id;
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

        if ($kode === '' || $nama === '' || $kategori <= 0 || $satuan === '') {
            return redirect()->back()->withInput()->with('error', 'Semua data barang wajib diisi.');
        }
        if (mb_strlen($kode) > 50 || mb_strlen($nama) > 150 || mb_strlen($satuan) > 30) {
            return redirect()->back()->withInput()->with('error', 'Panjang kode, nama, atau satuan melebihi batas.');
        }
        if ($hargaBeli < 0 || $hargaJual < 0) {
            return redirect()->back()->withInput()->with('error', 'Harga tidak boleh negatif.');
        }
        if (!$this->kategoriModel->find($kategori)) {
            return redirect()->back()->withInput()->with('error', 'Kategori tidak valid atau sudah dihapus.');
        }

        $duplikat = $this->barangModel
            ->where('kode_barang', $kode)
            ->where('id_barang !=', $id)
            ->first();
        if ($duplikat) {
            return redirect()->back()->withInput()->with('error', 'Kode barang sudah digunakan barang lain.');
        }

        // Stok tidak pernah diubah dari edit master. Semua perubahan stok harus punya jejak mutasi.
        $after = [
            'id_barang' => $id,
            'id_kategori' => $kategori,
            'kode_barang' => $kode,
            'nama_barang' => $nama,
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaJual,
            'stok' => (int) $barang['stok'],
            'satuan' => $satuan,
        ];

        if (!$this->barangModel->update($id, [
            'id_kategori' => $kategori,
            'kode_barang' => $kode,
            'nama_barang' => $nama,
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaJual,
            'satuan' => $satuan,
        ])) {
            return redirect()->back()->withInput()->with('error', 'Data barang gagal diperbarui.');
        }

        $this->auditEvent('UPDATE', 'BARANG', $id, $nama, 'Data master barang diperbarui tanpa mengubah stok.', $barang, $after);

        return redirect()->to('/barang')->with('success', 'Data barang diperbarui. Stok tidak berubah.');
    }

    public function hapus($id)
    {
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to('/barang')->with('error', 'Barang tidak valid.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $barang = $db->query(
                'SELECT id_barang, kode_barang, nama_barang, stok FROM barang WHERE id_barang = ? FOR UPDATE',
                [$id]
            )->getRowArray();

            if (!$barang) {
                $db->transRollback();
                return redirect()->to('/barang')->with('error', 'Data barang tidak ditemukan.');
            }

            if ((int) $barang['stok'] !== 0) {
                $db->transRollback();
                $this->auditEvent(
                    'DELETE',
                    'BARANG',
                    $id,
                    $barang['nama_barang'],
                    'Penghapusan barang diblokir karena stok belum nol.',
                    $barang,
                    ['stok_tersisa' => (int) $barang['stok']],
                    'BLOCKED'
                );
                return redirect()->to('/barang')->with(
                    'error',
                    'Barang tidak dapat dihapus karena stok masih ' . (int) $barang['stok'] . '. Habiskan atau sesuaikan stok melalui proses yang tercatat.'
                );
            }

            $jumlahPenjualan = (int) $db->table('detail_penjualan')->where('id_barang', $id)->countAllResults();
            $jumlahPembelian = (int) $db->table('detail_pembelian')->where('id_barang', $id)->countAllResults();
            $jumlahMutasi = (int) $db->table('mutasi_stok')->where('id_barang', $id)->countAllResults();
            $jumlahOpname = $db->tableExists('stock_opname_detail')
                ? (int) $db->table('stock_opname_detail')->where('id_barang', $id)->countAllResults()
                : 0;

            if ($jumlahPenjualan > 0 || $jumlahPembelian > 0 || $jumlahMutasi > 0 || $jumlahOpname > 0) {
                $db->transRollback();
                $this->auditEvent(
                    'DELETE',
                    'BARANG',
                    $id,
                    $barang['nama_barang'],
                    'Penghapusan barang diblokir karena memiliki histori.',
                    $barang,
                    [
                        'penjualan' => $jumlahPenjualan,
                        'pembelian' => $jumlahPembelian,
                        'mutasi' => $jumlahMutasi,
                        'stock_opname' => $jumlahOpname,
                    ],
                    'BLOCKED'
                );
                return redirect()->to('/barang')->with(
                    'error',
                    'Barang tidak dapat dihapus karena sudah memiliki histori penjualan, pembelian, mutasi, atau stock opname.'
                );
            }

            $db->table('barang')->where('id_barang', $id)->delete();
            if ($db->affectedRows() !== 1 || $db->transStatus() === false) {
                throw new \RuntimeException('Penghapusan barang gagal.');
            }

            $db->transCommit();
            $this->auditEvent(
                'DELETE',
                'BARANG',
                $id,
                $barang['nama_barang'],
                'Barang yang belum memiliki stok/histori berhasil dihapus.',
                $barang,
                null
            );
            return redirect()->to('/barang')->with('success', 'Barang yang belum memiliki histori berhasil dihapus.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menghapus barang: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/barang')->with('error', 'Barang tidak dapat dihapus karena integritas data harus dipertahankan.');
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
