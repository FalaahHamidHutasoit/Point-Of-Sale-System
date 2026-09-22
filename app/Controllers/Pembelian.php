<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\DetailPembelianModel;
use App\Models\PembelianModel;
use App\Models\PenerimaanBarangModel;
use App\Models\SupplierModel;

class Pembelian extends BaseController
{
    protected PembelianModel $pembelianModel;
    protected DetailPembelianModel $detailModel;
    protected BarangModel $barangModel;
    protected SupplierModel $supplierModel;
    protected PenerimaanBarangModel $penerimaanModel;

    public function __construct()
    {
        $this->pembelianModel = new PembelianModel();
        $this->detailModel = new DetailPembelianModel();
        $this->barangModel = new BarangModel();
        $this->supplierModel = new SupplierModel();
        $this->penerimaanModel = new PenerimaanBarangModel();
    }

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $status = strtoupper(trim((string) $this->request->getGet('status')));
        $allowedStatuses = ['DRAFT', 'DIORDER', 'SEBAGIAN', 'DITERIMA', 'DIBATALKAN'];

        $builder = $this->pembelianModel
            ->select('pembelian.*, supplier.nama_supplier, users.nama_lengkap')
            ->join('supplier', 'supplier.id_supplier = pembelian.id_supplier')
            ->join('users', 'users.id_user = pembelian.id_user');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('pembelian.no_pembelian', $keyword)
                ->orLike('supplier.nama_supplier', $keyword)
                ->orLike('users.nama_lengkap', $keyword)
                ->groupEnd();
        }
        if (in_array($status, $allowedStatuses, true)) {
            $builder->where('pembelian.status', $status);
        } else {
            $status = '';
        }

        $rows = $builder->orderBy('pembelian.tanggal', 'DESC')->findAll();

        return view('pembelian/index', [
            'title' => 'Purchase Order',
            'pembelian' => $rows,
            'keyword' => $keyword,
            'statusFilter' => $status,
        ]);
    }

    public function tambah()
    {
        return view('pembelian/tambah', [
            'title' => 'Buat Purchase Order',
            'supplier' => $this->supplierModel->orderBy('nama_supplier', 'ASC')->findAll(),
            'barang' => $this->barangModel->orderBy('nama_barang', 'ASC')->findAll(),
        ]);
    }

    public function simpan()
    {
        if ($guard = $this->guardSensitivePost(['admin', 'purchasing'])) {
            return $guard;
        }

        $supplierId = (int) $this->request->getPost('id_supplier');
        $barangIds = $this->request->getPost('id_barang');
        $qtys = $this->request->getPost('qty');
        $hargaBelis = $this->request->getPost('harga_beli');
        $catatan = trim((string) $this->request->getPost('catatan'));
        $tanggalTarget = trim((string) $this->request->getPost('tanggal_target'));
        $action = (string) $this->request->getPost('submit_action');
        $status = $action === 'draft' ? 'DRAFT' : 'DIORDER';

        if ($supplierId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Supplier wajib dipilih.');
        }
        if ($tanggalTarget !== '') {
            $target = \DateTime::createFromFormat('Y-m-d', $tanggalTarget);
            if (!$target || $target->format('Y-m-d') !== $tanggalTarget) {
                return redirect()->back()->withInput()->with('error', 'Tanggal target penerimaan tidak valid.');
            }
            if ($tanggalTarget < date('Y-m-d')) {
                return redirect()->back()->withInput()->with('error', 'Tanggal target penerimaan tidak boleh sebelum hari ini.');
            }
        }
        if (mb_strlen($catatan) > 255) {
            return redirect()->back()->withInput()->with('error', 'Catatan maksimal 255 karakter.');
        }
        if (!is_array($barangIds) || !is_array($qtys) || !is_array($hargaBelis)) {
            return redirect()->back()->withInput()->with('error', 'Minimal satu barang harus ditambahkan.');
        }

        $requested = [];
        foreach ($barangIds as $i => $idBarang) {
            $idBarang = (int) $idBarang;
            $qty = (int) ($qtys[$i] ?? 0);
            $hargaBeli = (float) ($hargaBelis[$i] ?? -1);

            if ($idBarang <= 0 || $qty <= 0 || $hargaBeli < 0) {
                continue;
            }
            if ($qty > 100000) {
                return redirect()->back()->withInput()->with('error', 'Jumlah barang tidak wajar.');
            }
            if (isset($requested[$idBarang])) {
                return redirect()->back()->withInput()->with('error', 'Barang yang sama tidak boleh dimasukkan dua kali.');
            }
            $requested[$idBarang] = ['qty' => $qty, 'hargaBeli' => $hargaBeli];
        }

        if (!$requested) {
            return redirect()->back()->withInput()->with('error', 'Isi barang, jumlah, dan harga beli dengan benar.');
        }

        ksort($requested, SORT_NUMERIC);
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $supplier = $db->query(
                'SELECT id_supplier FROM supplier WHERE id_supplier = ? FOR UPDATE',
                [$supplierId]
            )->getRowArray();
            if (!$supplier) {
                throw new \RuntimeException('Supplier tidak ditemukan.');
            }

            $items = [];
            $total = 0.0;
            foreach ($requested as $idBarang => $input) {
                $barang = $db->query(
                    'SELECT id_barang, nama_barang FROM barang WHERE id_barang = ?',
                    [$idBarang]
                )->getRowArray();
                if (!$barang) {
                    throw new \RuntimeException('Ada barang yang tidak ditemukan.');
                }
                $subtotal = $input['qty'] * $input['hargaBeli'];
                $total += $subtotal;
                $items[] = [
                    'idBarang' => (int) $idBarang,
                    'qty' => (int) $input['qty'],
                    'hargaBeli' => (float) $input['hargaBeli'],
                    'subtotal' => $subtotal,
                ];
            }

            $noPembelian = $this->buatNomorPembelian();
            $now = date('Y-m-d H:i:s');
            $insertHeader = $this->pembelianModel->insert([
                'no_pembelian' => $noPembelian,
                'tanggal' => $now,
                'tanggal_target' => $tanggalTarget ?: null,
                'id_supplier' => $supplierId,
                'id_user' => (int) session()->get('id_user'),
                'total' => $total,
                'catatan' => $catatan ?: null,
                'status' => $status,
                'ordered_at' => $status === 'DIORDER' ? $now : null,
            ]);
            if ($insertHeader === false) {
                throw new \RuntimeException('Purchase order gagal disimpan.');
            }

            $idPembelian = (int) $this->pembelianModel->getInsertID();
            foreach ($items as $item) {
                if ($this->detailModel->insert([
                    'id_pembelian' => $idPembelian,
                    'id_barang' => $item['idBarang'],
                    'qty' => $item['qty'],
                    'qty_diterima' => 0,
                    'harga_beli' => $item['hargaBeli'],
                    'subtotal' => $item['subtotal'],
                ]) === false) {
                    throw new \RuntimeException('Detail purchase order gagal disimpan.');
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database transaction failed.');
            }
            $db->transCommit();

            $this->auditEvent(
                $status === 'DRAFT' ? 'PO_DRAFT_CREATED' : 'PO_ORDERED',
                'PURCHASE_ORDER',
                $idPembelian,
                $noPembelian,
                $status === 'DRAFT' ? 'Purchase order disimpan sebagai draft.' : 'Purchase order dibuat dan dikirim ke supplier.',
                null,
                [
                    'status' => $status,
                    'id_supplier' => $supplierId,
                    'total' => $total,
                    'tanggal_target' => $tanggalTarget ?: null,
                    'jumlah_baris_barang' => count($items),
                ]
            );

            $message = $status === 'DRAFT'
                ? 'Purchase order tersimpan sebagai draft. Stok belum berubah.'
                : 'Purchase order berhasil dibuat. Stok baru bertambah saat Gudang menerima barang.';

            return redirect()->to('/pembelian/' . $idPembelian)->with('success', $message);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menyimpan PO: {message}', ['message' => $e->getMessage()]);
            $this->auditEvent('PO_CREATE_FAILED', 'PURCHASE_ORDER', null, null, 'Pembuatan purchase order gagal.', null, ['id_supplier' => $supplierId], 'FAILED');
            return redirect()->back()->withInput()->with('error', 'Purchase order gagal disimpan. Tidak ada stok yang berubah.');
        }
    }

    public function detail($id)
    {
        $id = (int) $id;
        $header = $this->pembelianModel
            ->select('pembelian.*, supplier.nama_supplier, supplier.no_telp, users.nama_lengkap')
            ->join('supplier', 'supplier.id_supplier = pembelian.id_supplier')
            ->join('users', 'users.id_user = pembelian.id_user')
            ->where('pembelian.id_pembelian', $id)
            ->first();

        if (!$header) {
            return redirect()->to('/pembelian')->with('error', 'Purchase order tidak ditemukan.');
        }

        $detail = $this->detailModel
            ->select('detail_pembelian.*, barang.kode_barang, barang.nama_barang, barang.satuan')
            ->join('barang', 'barang.id_barang = detail_pembelian.id_barang')
            ->where('detail_pembelian.id_pembelian', $id)
            ->findAll();

        $penerimaan = $this->penerimaanModel
            ->select('penerimaan_barang.*, users.nama_lengkap')
            ->join('users', 'users.id_user = penerimaan_barang.id_user')
            ->where('penerimaan_barang.id_pembelian', $id)
            ->orderBy('penerimaan_barang.tanggal', 'DESC')
            ->findAll();

        $orderedQty = 0;
        $receivedQty = 0;
        foreach ($detail as $row) {
            $orderedQty += (int) $row['qty'];
            $receivedQty += (int) ($row['qty_diterima'] ?? 0);
        }

        return view('pembelian/detail', [
            'title' => 'Detail Purchase Order',
            'pembelian' => $header,
            'detail' => $detail,
            'penerimaan' => $penerimaan,
            'orderedQty' => $orderedQty,
            'receivedQty' => $receivedQty,
        ]);
    }

    public function kirim(int $id)
    {
        if ($guard = $this->guardSensitivePost(['admin', 'purchasing'])) {
            return $guard;
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $po = $db->query('SELECT * FROM pembelian WHERE id_pembelian = ? FOR UPDATE', [$id])->getRowArray();
            if (!$po) {
                throw new \RuntimeException('PO tidak ditemukan.');
            }
            if ($po['status'] !== 'DRAFT') {
                $db->transRollback();
                return redirect()->to('/pembelian/' . $id)->with('error', 'Hanya PO draft yang dapat dikirim.');
            }

            $now = date('Y-m-d H:i:s');
            $this->pembelianModel->update($id, ['status' => 'DIORDER', 'ordered_at' => $now]);
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Update status gagal.');
            }
            $db->transCommit();

            $this->auditEvent('PO_ORDERED', 'PURCHASE_ORDER', $id, (string) $po['no_pembelian'], 'Draft PO dikirim ke supplier.', ['status' => 'DRAFT'], ['status' => 'DIORDER', 'ordered_at' => $now]);
            return redirect()->to('/pembelian/' . $id)->with('success', 'Purchase order ditandai sudah dikirim ke supplier.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Kirim PO gagal: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/pembelian/' . $id)->with('error', 'Purchase order gagal dikirim.');
        }
    }

    public function batalkan(int $id)
    {
        if ($guard = $this->guardSensitivePost(['admin', 'purchasing'])) {
            return $guard;
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $po = $db->query('SELECT * FROM pembelian WHERE id_pembelian = ? FOR UPDATE', [$id])->getRowArray();
            if (!$po) {
                throw new \RuntimeException('PO tidak ditemukan.');
            }
            if (!in_array($po['status'], ['DRAFT', 'DIORDER'], true)) {
                $db->transRollback();
                return redirect()->to('/pembelian/' . $id)->with('error', 'PO pada status ini tidak dapat dibatalkan.');
            }

            $received = (int) ($db->query('SELECT COALESCE(SUM(qty_diterima),0) AS total FROM detail_pembelian WHERE id_pembelian = ?', [$id])->getRowArray()['total'] ?? 0);
            if ($received > 0) {
                $db->transRollback();
                return redirect()->to('/pembelian/' . $id)->with('error', 'PO yang sudah memiliki penerimaan barang tidak dapat dibatalkan.');
            }

            $now = date('Y-m-d H:i:s');
            $this->pembelianModel->update($id, ['status' => 'DIBATALKAN', 'cancelled_at' => $now]);
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Pembatalan gagal.');
            }
            $db->transCommit();

            $this->auditEvent('PO_CANCELLED', 'PURCHASE_ORDER', $id, (string) $po['no_pembelian'], 'Purchase order dibatalkan sebelum penerimaan barang.', ['status' => $po['status']], ['status' => 'DIBATALKAN', 'cancelled_at' => $now]);
            return redirect()->to('/pembelian/' . $id)->with('success', 'Purchase order berhasil dibatalkan.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Batal PO gagal: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/pembelian/' . $id)->with('error', 'Purchase order gagal dibatalkan.');
        }
    }

    private function buatNomorPembelian(): string
    {
        return 'PO-' . date('Ymd-His') . '-' . random_int(1000, 9999);
    }
}
