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

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $builder = $this->supplierModel;

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('nama_supplier', $keyword)
                ->orLike('no_telp', $keyword)
                ->orLike('alamat', $keyword)
                ->groupEnd();
        }

        return view('supplier/index', [
            'title' => 'Data Supplier',
            'supplier' => $builder->orderBy('id_supplier', 'DESC')->findAll(),
            'keyword' => $keyword,
        ]);
    }

    public function tambah()
    {
        return view('supplier/tambah', ['title' => 'Tambah Supplier']);
    }

    public function simpan()
    {
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        $nama = trim((string) $this->request->getPost('nama_supplier'));
        $noTelp = trim((string) $this->request->getPost('no_telp'));
        $alamat = trim((string) $this->request->getPost('alamat'));

        if ($error = $this->validasiSupplier($nama, $noTelp, $alamat)) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        if (!$this->supplierModel->insert([
            'nama_supplier' => $nama,
            'no_telp' => $noTelp ?: null,
            'alamat' => $alamat ?: null,
        ])) {
            return redirect()->back()->withInput()->with('error', 'Supplier gagal disimpan.');
        }

        $idSupplier = (int) $this->supplierModel->getInsertID();
        $after = [
            'id_supplier' => $idSupplier,
            'nama_supplier' => $nama,
            'no_telp' => $noTelp ?: null,
            'alamat' => $alamat ?: null,
        ];
        $this->auditEvent('CREATE', 'SUPPLIER', $idSupplier, $nama, 'Supplier baru ditambahkan.', null, $after);

        return redirect()->to('/supplier')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $supplier = $this->supplierModel->find((int) $id);
        if (!$supplier) {
            return redirect()->to('/supplier')->with('error', 'Data supplier tidak ditemukan.');
        }

        return view('supplier/edit', [
            'title' => 'Edit Supplier',
            'supplier' => $supplier,
        ]);
    }

    public function update($id)
    {
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        $id = (int) $id;
        $supplier = $this->supplierModel->find($id);
        if (!$supplier) {
            return redirect()->to('/supplier')->with('error', 'Data supplier tidak ditemukan.');
        }

        $nama = trim((string) $this->request->getPost('nama_supplier'));
        $noTelp = trim((string) $this->request->getPost('no_telp'));
        $alamat = trim((string) $this->request->getPost('alamat'));

        if ($error = $this->validasiSupplier($nama, $noTelp, $alamat)) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        $after = [
            'id_supplier' => $id,
            'nama_supplier' => $nama,
            'no_telp' => $noTelp ?: null,
            'alamat' => $alamat ?: null,
        ];

        if (!$this->supplierModel->update($id, [
            'nama_supplier' => $nama,
            'no_telp' => $noTelp ?: null,
            'alamat' => $alamat ?: null,
        ])) {
            return redirect()->back()->withInput()->with('error', 'Supplier gagal diperbarui.');
        }

        $this->auditEvent('UPDATE', 'SUPPLIER', $id, $nama, 'Data supplier diperbarui.', $supplier, $after);

        return redirect()->to('/supplier')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function hapus($id)
    {
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to('/supplier')->with('error', 'Supplier tidak valid.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $supplier = $db->query(
                'SELECT id_supplier, nama_supplier FROM supplier WHERE id_supplier = ? FOR UPDATE',
                [$id]
            )->getRowArray();

            if (!$supplier) {
                $db->transRollback();
                return redirect()->to('/supplier')->with('error', 'Data supplier tidak ditemukan.');
            }

            $jumlahPembelian = (int) $db->table('pembelian')
                ->where('id_supplier', $id)
                ->countAllResults();

            if ($jumlahPembelian > 0) {
                $db->transRollback();
                $this->auditEvent(
                    'DELETE',
                    'SUPPLIER',
                    $id,
                    $supplier['nama_supplier'],
                    'Penghapusan supplier diblokir karena sudah memiliki histori pembelian.',
                    $supplier,
                    ['jumlah_pembelian' => $jumlahPembelian],
                    'BLOCKED'
                );
                return redirect()->to('/supplier')->with(
                    'error',
                    'Supplier tidak dapat dihapus karena sudah tercatat pada ' . $jumlahPembelian . ' transaksi pembelian.'
                );
            }

            $db->table('supplier')->where('id_supplier', $id)->delete();
            if ($db->affectedRows() !== 1 || $db->transStatus() === false) {
                throw new \RuntimeException('Penghapusan supplier gagal.');
            }

            $db->transCommit();
            $this->auditEvent(
                'DELETE',
                'SUPPLIER',
                $id,
                $supplier['nama_supplier'],
                'Supplier yang belum memiliki histori berhasil dihapus.',
                $supplier,
                null
            );
            return redirect()->to('/supplier')->with('success', 'Supplier yang belum memiliki histori berhasil dihapus.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menghapus supplier: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/supplier')->with('error', 'Supplier tidak dapat dihapus.');
        }
    }

    private function validasiSupplier(string $nama, string $noTelp, string $alamat): ?string
    {
        if (mb_strlen($nama) < 3) {
            return 'Nama supplier minimal 3 karakter.';
        }
        if (mb_strlen($nama) > 100) {
            return 'Nama supplier maksimal 100 karakter.';
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
