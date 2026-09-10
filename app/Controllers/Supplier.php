<?php

namespace App\Controllers;

use App\Models\SupplierModel;

class Supplier extends BaseController
{
    protected $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new SupplierModel();
    }

    // =========================
    // INDEX
    // =========================
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $keyword = $this->request->getGet('keyword');

        if ($keyword) {
            $dataSupplier = $this->supplierModel
                ->groupStart()
                ->like('nama_supplier', $keyword)
                ->orLike('no_telp', $keyword)
                ->orLike('alamat', $keyword)
                ->groupEnd()
                ->orderBy('id_supplier', 'DESC')
                ->findAll();
        } else {
            $dataSupplier = $this->supplierModel
                ->orderBy('id_supplier', 'DESC')
                ->findAll();
        }

        $data = [
            'title' => 'Data Supplier',
            'supplier' => $dataSupplier,
            'keyword' => $keyword
        ];

        return view('supplier/index', $data);
    }

    // =========================
    // TAMBAH
    // =========================
    public function tambah()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        return view('supplier/tambah', [
            'title' => 'Tambah Supplier'
        ]);
    }

    // =========================
    // SIMPAN
    // =========================
    public function simpan()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $rules = [
            'nama_supplier' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Nama supplier wajib diisi.',
                    'min_length' => 'Nama supplier minimal 3 karakter.'
                ]
            ],
            'no_telp' => [
                'rules' => 'permit_empty|max_length[20]',
                'errors' => [
                    'max_length' => 'Nomor telepon terlalu panjang.'
                ]
            ],
            'alamat' => [
                'rules' => 'permit_empty'
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->supplierModel->insert([
            'nama_supplier' => $this->request->getPost('nama_supplier'),
            'no_telp' => $this->request->getPost('no_telp'),
            'alamat' => $this->request->getPost('alamat')
        ]);

        return redirect()->to('/supplier')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    // =========================
    // EDIT
    // =========================
    public function edit($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $supplier = $this->supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/supplier')
                ->with('error', 'Data supplier tidak ditemukan.');
        }

        return view('supplier/edit', [
            'title' => 'Edit Supplier',
            'supplier' => $supplier
        ]);
    }

    // =========================
    // UPDATE
    // =========================
    public function update($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $supplier = $this->supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/supplier')
                ->with('error', 'Data supplier tidak ditemukan.');
        }

        $rules = [
            'nama_supplier' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Nama supplier wajib diisi.',
                    'min_length' => 'Nama supplier minimal 3 karakter.'
                ]
            ],
            'no_telp' => [
                'rules' => 'permit_empty|max_length[20]',
                'errors' => [
                    'max_length' => 'Nomor telepon terlalu panjang.'
                ]
            ],
            'alamat' => [
                'rules' => 'permit_empty'
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->supplierModel->update($id, [
            'nama_supplier' => $this->request->getPost('nama_supplier'),
            'no_telp' => $this->request->getPost('no_telp'),
            'alamat' => $this->request->getPost('alamat')
        ]);

        return redirect()->to('/supplier')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    // =========================
    // HAPUS
    // =========================
    public function hapus($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $supplier = $this->supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/supplier')
                ->with('error', 'Data supplier tidak ditemukan.');
        }

        try {
            $this->supplierModel->delete($id);

            return redirect()->to('/supplier')
                ->with('success', 'Supplier berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/supplier')
                ->with('error', 'Supplier tidak dapat dihapus.');
        }
    }
}