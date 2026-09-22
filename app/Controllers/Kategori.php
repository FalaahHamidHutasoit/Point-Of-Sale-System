<?php

namespace App\Controllers;

use App\Models\KategoriModel;

class Kategori extends BaseController
{
    protected $kategoriModel;

    public function __construct()
    {
        $this->kategoriModel = new KategoriModel();
    }

    public function index()
    {
        return view('kategori/index', [
            'title' => 'Data Kategori',
            'kategori' => $this->kategoriModel->orderBy('nama_kategori', 'ASC')->findAll(),
        ]);
    }

    public function tambah()
    {
        return view('kategori/tambah', [
            'title' => 'Tambah Kategori',
        ]);
    }

    public function simpan()
    {
        if ($guard = $this->guardSensitivePost(['admin', 'gudang'])) {
            return $guard;
        }

        $nama = trim((string) $this->request->getPost('nama_kategori'));

        if ($nama === '') {
            return redirect()->back()->withInput()->with('error', 'Nama kategori wajib diisi.');
        }
        if (mb_strlen($nama) > 100) {
            return redirect()->back()->withInput()->with('error', 'Nama kategori maksimal 100 karakter.');
        }
        if ($this->kategoriDuplikat($nama)) {
            return redirect()->back()->withInput()->with('error', 'Kategori dengan nama tersebut sudah ada.');
        }

        if (!$this->kategoriModel->insert(['nama_kategori' => $nama])) {
            return redirect()->back()->withInput()->with('error', 'Kategori gagal disimpan.');
        }

        $idKategori = (int) $this->kategoriModel->getInsertID();
        $this->auditEvent(
            'CREATE',
            'KATEGORI',
            $idKategori,
            $nama,
            'Kategori baru ditambahkan.',
            null,
            ['id_kategori' => $idKategori, 'nama_kategori' => $nama]
        );

        return redirect()->to('/kategori')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kategori = $this->kategoriModel->find((int) $id);
        if (!$kategori) {
            return redirect()->to('/kategori')->with('error', 'Data kategori tidak ditemukan.');
        }

        return view('kategori/edit', [
            'title' => 'Edit Kategori',
            'kategori' => $kategori,
        ]);
    }

    public function update($id)
    {
        if ($guard = $this->guardSensitivePost(['admin', 'gudang'])) {
            return $guard;
        }

        $id = (int) $id;
        $kategori = $this->kategoriModel->find($id);
        if (!$kategori) {
            return redirect()->to('/kategori')->with('error', 'Data kategori tidak ditemukan.');
        }

        $nama = trim((string) $this->request->getPost('nama_kategori'));
        if ($nama === '') {
            return redirect()->back()->withInput()->with('error', 'Nama kategori wajib diisi.');
        }
        if (mb_strlen($nama) > 100) {
            return redirect()->back()->withInput()->with('error', 'Nama kategori maksimal 100 karakter.');
        }
        if ($this->kategoriDuplikat($nama, $id)) {
            return redirect()->back()->withInput()->with('error', 'Kategori dengan nama tersebut sudah ada.');
        }

        if (!$this->kategoriModel->update($id, ['nama_kategori' => $nama])) {
            return redirect()->back()->withInput()->with('error', 'Kategori gagal diperbarui.');
        }

        $this->auditEvent(
            'UPDATE',
            'KATEGORI',
            $id,
            $nama,
            'Kategori diperbarui.',
            $kategori,
            ['id_kategori' => $id, 'nama_kategori' => $nama]
        );

        return redirect()->to('/kategori')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function hapus($id)
    {
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to('/kategori')->with('error', 'Kategori tidak valid.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Kunci parent row supaya tidak berubah/hilang saat pengecekan dependency.
            $kategori = $db->query(
                'SELECT id_kategori, nama_kategori FROM kategori WHERE id_kategori = ? FOR UPDATE',
                [$id]
            )->getRowArray();

            if (!$kategori) {
                $db->transRollback();
                return redirect()->to('/kategori')->with('error', 'Data kategori tidak ditemukan.');
            }

            $dipakaiBarang = (int) $db->table('barang')
                ->where('id_kategori', $id)
                ->countAllResults();

            if ($dipakaiBarang > 0) {
                $db->transRollback();
                $this->auditEvent(
                    'DELETE',
                    'KATEGORI',
                    $id,
                    $kategori['nama_kategori'],
                    'Penghapusan kategori diblokir karena masih digunakan barang.',
                    $kategori,
                    ['jumlah_barang' => $dipakaiBarang],
                    'BLOCKED'
                );
                return redirect()->to('/kategori')->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih digunakan oleh ' . $dipakaiBarang . ' barang.'
                );
            }

            $db->table('kategori')->where('id_kategori', $id)->delete();
            if ($db->affectedRows() !== 1 || $db->transStatus() === false) {
                throw new \RuntimeException('Penghapusan kategori gagal.');
            }

            $db->transCommit();
            $this->auditEvent(
                'DELETE',
                'KATEGORI',
                $id,
                $kategori['nama_kategori'],
                'Kategori yang belum digunakan berhasil dihapus.',
                $kategori,
                null
            );
            return redirect()->to('/kategori')->with('success', 'Kategori berhasil dihapus.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menghapus kategori: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/kategori')->with('error', 'Kategori tidak dapat dihapus.');
        }
    }

    private function kategoriDuplikat(string $nama, ?int $kecualiId = null): bool
    {
        $db = \Config\Database::connect();
        $sql = 'SELECT id_kategori FROM kategori WHERE LOWER(TRIM(nama_kategori)) = LOWER(TRIM(?))';
        $params = [$nama];

        if ($kecualiId !== null) {
            $sql .= ' AND id_kategori <> ?';
            $params[] = $kecualiId;
        }

        $sql .= ' LIMIT 1';
        return $db->query($sql, $params)->getRowArray() !== null;
    }
}
