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

    // Menampilkan data kategori
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Data Kategori',
            'kategori' => $this->kategoriModel->findAll()
        ];

        return view('kategori/index', $data);
    }

    // Menampilkan form tambah
    public function tambah()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        return view('kategori/tambah');
    }

    // Menyimpan kategori
    public function simpan()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $nama = $this->request->getPost('nama_kategori');

        if (empty($nama)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Nama kategori wajib diisi.');
        }

        $this->kategoriModel->insert([
            'nama_kategori' => $nama
        ]);

        return redirect()->to('/kategori')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $kategori = $this->kategoriModel->find($id);

        if (!$kategori) {
            return redirect()->to('/kategori')
                ->with('error', 'Data kategori tidak ditemukan.');
        }

        return view('kategori/edit', [
            'title' => 'Edit Kategori',
            'kategori' => $kategori
        ]);
    }

    // Update kategori
    public function update($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $nama = $this->request->getPost('nama_kategori');

        if (empty($nama)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Nama kategori wajib diisi.');
        }

        $this->kategoriModel->update($id, [
            'nama_kategori' => $nama
        ]);

        return redirect()->to('/kategori')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    // Hapus kategori
    public function hapus($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        try {
            $this->kategoriModel->delete($id);

            return redirect()->to('/kategori')
                ->with('success', 'Kategori berhasil dihapus.');

        } catch (\Exception $e) {

            return redirect()->to('/kategori')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh barang.');
        }
    }
}