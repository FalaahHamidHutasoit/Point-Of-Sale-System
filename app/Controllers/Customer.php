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

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $builder = $this->customerModel;

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('nama_customer', $keyword)
                ->orLike('no_telp', $keyword)
                ->orLike('alamat', $keyword)
                ->groupEnd();
        }

        return view('customer/index', [
            'title' => 'Data Customer',
            'customer' => $builder->orderBy('id_customer', 'DESC')->paginate(15, 'customer'),
            'pager' => $this->customerModel->pager,
            'keyword' => $keyword,
        ]);
    }

    public function tambah()
    {
        return view('customer/tambah', ['title' => 'Tambah Customer']);
    }

    public function simpan()
    {
        if ($guard = $this->guardSensitivePost(['admin', 'kasir'])) {
            return $guard;
        }

        $nama = trim((string) $this->request->getPost('nama_customer'));
        $noTelp = trim((string) $this->request->getPost('no_telp'));
        $alamat = trim((string) $this->request->getPost('alamat'));

        if ($error = $this->validasiCustomer($nama, $noTelp, $alamat)) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        if (!$this->customerModel->insert([
            'nama_customer' => $nama,
            'no_telp' => $noTelp ?: null,
            'alamat' => $alamat ?: null,
        ])) {
            return redirect()->back()->withInput()->with('error', 'Customer gagal disimpan.');
        }

        $idCustomer = (int) $this->customerModel->getInsertID();
        $after = [
            'id_customer' => $idCustomer,
            'nama_customer' => $nama,
            'no_telp' => $noTelp ?: null,
            'alamat' => $alamat ?: null,
        ];
        $this->auditEvent('CREATE', 'CUSTOMER', $idCustomer, $nama, 'Customer baru ditambahkan.', null, $after);

        return redirect()->to('/customer')->with('success', 'Customer berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $customer = $this->customerModel->find((int) $id);
        if (!$customer) {
            return redirect()->to('/customer')->with('error', 'Data customer tidak ditemukan.');
        }

        return view('customer/edit', [
            'title' => 'Edit Customer',
            'customer' => $customer,
        ]);
    }

    public function update($id)
    {
        if ($guard = $this->guardSensitivePost(['admin', 'kasir'])) {
            return $guard;
        }

        $id = (int) $id;
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return redirect()->to('/customer')->with('error', 'Data customer tidak ditemukan.');
        }

        $nama = trim((string) $this->request->getPost('nama_customer'));
        $noTelp = trim((string) $this->request->getPost('no_telp'));
        $alamat = trim((string) $this->request->getPost('alamat'));

        if ($error = $this->validasiCustomer($nama, $noTelp, $alamat)) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        $after = [
            'id_customer' => $id,
            'nama_customer' => $nama,
            'no_telp' => $noTelp ?: null,
            'alamat' => $alamat ?: null,
        ];

        if (!$this->customerModel->update($id, [
            'nama_customer' => $nama,
            'no_telp' => $noTelp ?: null,
            'alamat' => $alamat ?: null,
        ])) {
            return redirect()->back()->withInput()->with('error', 'Customer gagal diperbarui.');
        }

        $this->auditEvent('UPDATE', 'CUSTOMER', $id, $nama, 'Data customer diperbarui.', $customer, $after);

        return redirect()->to('/customer')->with('success', 'Customer berhasil diperbarui.');
    }

    public function hapus($id)
    {
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to('/customer')->with('error', 'Customer tidak valid.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $customer = $db->query(
                'SELECT id_customer, nama_customer FROM customer WHERE id_customer = ? FOR UPDATE',
                [$id]
            )->getRowArray();

            if (!$customer) {
                $db->transRollback();
                return redirect()->to('/customer')->with('error', 'Data customer tidak ditemukan.');
            }

            $jumlahTransaksi = (int) $db->table('penjualan')
                ->where('id_customer', $id)
                ->countAllResults();

            // FK lama memakai ON DELETE SET NULL. Secara teknis delete bisa lolos,
            // tetapi KAMELA sengaja memblokirnya agar identitas customer pada histori tidak hilang.
            if ($jumlahTransaksi > 0) {
                $db->transRollback();
                $this->auditEvent(
                    'DELETE',
                    'CUSTOMER',
                    $id,
                    $customer['nama_customer'],
                    'Penghapusan customer diblokir karena sudah memiliki histori penjualan.',
                    $customer,
                    ['jumlah_transaksi' => $jumlahTransaksi],
                    'BLOCKED'
                );
                return redirect()->to('/customer')->with(
                    'error',
                    'Customer tidak dapat dihapus karena sudah tercatat pada ' . $jumlahTransaksi . ' transaksi penjualan.'
                );
            }

            $db->table('customer')->where('id_customer', $id)->delete();
            if ($db->affectedRows() !== 1 || $db->transStatus() === false) {
                throw new \RuntimeException('Penghapusan customer gagal.');
            }

            $db->transCommit();
            $this->auditEvent(
                'DELETE',
                'CUSTOMER',
                $id,
                $customer['nama_customer'],
                'Customer yang belum memiliki histori berhasil dihapus.',
                $customer,
                null
            );
            return redirect()->to('/customer')->with('success', 'Customer yang belum memiliki histori berhasil dihapus.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menghapus customer: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/customer')->with('error', 'Customer tidak dapat dihapus.');
        }
    }

    private function validasiCustomer(string $nama, string $noTelp, string $alamat): ?string
    {
        if ($nama === '') {
            return 'Nama customer wajib diisi.';
        }
        if (mb_strlen($nama) > 100) {
            return 'Nama customer maksimal 100 karakter.';
        }
        if (mb_strlen($noTelp) > 20) {
            return 'Nomor telepon maksimal 20 karakter.';
        }
        if (mb_strlen($alamat) > 1000) {
            return 'Alamat terlalu panjang.';
        }

        return null;
    }
}
