<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class Customer extends BaseController
{
    protected $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }

    // =========================
    // DATA CUSTOMER
    // =========================
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $keyword = $this->request->getGet('keyword');

        $builder = $this->customerModel;

        if ($keyword) {
            $builder->groupStart()
                ->like('nama_customer', $keyword)
                ->orLike('no_telp', $keyword)
                ->orLike('alamat', $keyword)
                ->groupEnd();
        }

        $data = [
            'title' => 'Data Customer',
            'customer' => $builder
                ->orderBy('id_customer', 'DESC')
                ->findAll(),
            'keyword' => $keyword
        ];

        return view('customer/index', $data);
    }

    // =========================
    // FORM TAMBAH
    // =========================
    public function tambah()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        return view('customer/tambah', [
            'title' => 'Tambah Customer'
        ]);
    }

    // =========================
    // SIMPAN CUSTOMER
    // =========================
    public function simpan()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $nama = trim($this->request->getPost('nama_customer'));
        $noTelp = trim($this->request->getPost('no_telp'));
        $alamat = trim($this->request->getPost('alamat'));

        if (empty($nama)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Nama customer wajib diisi.');
        }

        $this->customerModel->insert([
            'nama_customer' => $nama,
            'no_telp' => $noTelp,
            'alamat' => $alamat
        ]);

        return redirect()->to('/customer')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    // =========================
    // FORM EDIT
    // =========================
    public function edit($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return redirect()->to('/customer')
                ->with('error', 'Data customer tidak ditemukan.');
        }

        return view('customer/edit', [
            'title' => 'Edit Customer',
            'customer' => $customer
        ]);
    }

    // =========================
    // UPDATE CUSTOMER
    // =========================
    public function update($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return redirect()->to('/customer')
                ->with('error', 'Data customer tidak ditemukan.');
        }

        $nama = trim($this->request->getPost('nama_customer'));
        $noTelp = trim($this->request->getPost('no_telp'));
        $alamat = trim($this->request->getPost('alamat'));

        if (empty($nama)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Nama customer wajib diisi.');
        }

        $this->customerModel->update($id, [
            'nama_customer' => $nama,
            'no_telp' => $noTelp,
            'alamat' => $alamat
        ]);

        return redirect()->to('/customer')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    // =========================
    // HAPUS CUSTOMER
    // =========================
    public function hapus($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return redirect()->to('/customer')
                ->with('error', 'Data customer tidak ditemukan.');
        }

        try {

            $this->customerModel->delete($id);

            return redirect()->to('/customer')
                ->with('success', 'Customer berhasil dihapus.');

        } catch (\Exception $e) {

            return redirect()->to('/customer')
                ->with('error', 'Customer tidak dapat dihapus karena sudah digunakan dalam transaksi.');

        }
    }
}