<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\DetailPembelianModel;
use App\Models\MutasiStokModel;
use App\Models\PembelianModel;
use App\Models\SupplierModel;

class Pembelian extends BaseController
{
    protected $pembelianModel;
    protected $detailModel;
    protected $barangModel;
    protected $supplierModel;
    protected $mutasiModel;

    public function __construct()
    {
        $this->pembelianModel = new PembelianModel();
        $this->detailModel = new DetailPembelianModel();
        $this->barangModel = new BarangModel();
        $this->supplierModel = new SupplierModel();
        $this->mutasiModel = new MutasiStokModel();
    }

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));

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

        return view('pembelian/index', [
            'title' => 'Pembelian & Restock',
            'pembelian' => $builder->orderBy('pembelian.tanggal', 'DESC')->findAll(),
            'keyword' => $keyword,
        ]);
    }

    public function tambah()
    {
        return view('pembelian/tambah', [
            'title' => 'Tambah Pembelian',
            'supplier' => $this->supplierModel->orderBy('nama_supplier', 'ASC')->findAll(),
            'barang' => $this->barangModel->orderBy('nama_barang', 'ASC')->findAll(),
        ]);
    }

    public function simpan()
    {
        $supplierId = (int) $this->request->getPost('id_supplier');
        $barangIds = $this->request->getPost('id_barang');
        $qtys = $this->request->getPost('qty');
        $hargaBelis = $this->request->getPost('harga_beli');
        $catatan = trim((string) $this->request->getPost('catatan'));

        if (!$supplierId || !$this->supplierModel->find($supplierId)) {
            return redirect()->back()->withInput()->with('error', 'Supplier wajib dipilih.');
        }

        if (!is_array($barangIds) || !is_array($qtys) || !is_array($hargaBelis)) {
            return redirect()->back()->withInput()->with('error', 'Minimal satu barang harus ditambahkan.');
        }

        $items = [];
        $total = 0;
        $seen = [];

        foreach ($barangIds as $i => $idBarang) {
            $idBarang = (int) $idBarang;
            $qty = (int) ($qtys[$i] ?? 0);
            $hargaBeli = (float) ($hargaBelis[$i] ?? 0);

            if (!$idBarang || $qty <= 0 || $hargaBeli < 0) {
                continue;
            }

            if (isset($seen[$idBarang])) {
                return redirect()->back()->withInput()->with('error', 'Barang yang sama tidak boleh dimasukkan dua kali.');
            }
            $seen[$idBarang] = true;

            $barang = $this->barangModel->find($idBarang);
            if (!$barang) {
                return redirect()->back()->withInput()->with('error', 'Barang tidak ditemukan.');
            }

            $subtotal = $qty * $hargaBeli;
            $total += $subtotal;
            $items[] = compact('idBarang', 'qty', 'hargaBeli', 'subtotal', 'barang');
        }

        if (!$items) {
            return redirect()->back()->withInput()->with('error', 'Isi barang, jumlah, dan harga beli dengan benar.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $noPembelian = 'PO-' . date('YmdHis') . '-' . random_int(10, 99);
            $this->pembelianModel->insert([
                'no_pembelian' => $noPembelian,
                'tanggal' => date('Y-m-d H:i:s'),
                'id_supplier' => $supplierId,
                'id_user' => (int) session()->get('id_user'),
                'total' => $total,
                'catatan' => $catatan ?: null,
            ]);
            $idPembelian = $this->pembelianModel->getInsertID();

            foreach ($items as $item) {
                $stokSebelum = (int) $item['barang']['stok'];
                $stokSesudah = $stokSebelum + $item['qty'];

                $this->detailModel->insert([
                    'id_pembelian' => $idPembelian,
                    'id_barang' => $item['idBarang'],
                    'qty' => $item['qty'],
                    'harga_beli' => $item['hargaBeli'],
                    'subtotal' => $item['subtotal'],
                ]);

                $this->barangModel->update($item['idBarang'], [
                    'stok' => $stokSesudah,
                    'harga_beli' => $item['hargaBeli'],
                ]);

                $this->mutasiModel->insert([
                    'id_barang' => $item['idBarang'],
                    'id_user' => (int) session()->get('id_user'),
                    'tipe' => 'MASUK',
                    'qty' => $item['qty'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi_tipe' => 'PEMBELIAN',
                    'referensi_id' => $idPembelian,
                    'keterangan' => 'Restock dari pembelian ' . $noPembelian,
                ]);
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database transaction failed');
            }

            $db->transCommit();
            return redirect()->to('/pembelian/' . $idPembelian)
                ->with('success', 'Pembelian berhasil disimpan dan stok telah diperbarui.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menyimpan pembelian: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Pembelian gagal disimpan.');
        }
    }

    public function detail($id)
    {
        $header = $this->pembelianModel
            ->select('pembelian.*, supplier.nama_supplier, supplier.no_telp, users.nama_lengkap')
            ->join('supplier', 'supplier.id_supplier = pembelian.id_supplier')
            ->join('users', 'users.id_user = pembelian.id_user')
            ->where('pembelian.id_pembelian', $id)
            ->first();

        if (!$header) {
            return redirect()->to('/pembelian')->with('error', 'Pembelian tidak ditemukan.');
        }

        $detail = $this->detailModel
            ->select('detail_pembelian.*, barang.kode_barang, barang.nama_barang, barang.satuan')
            ->join('barang', 'barang.id_barang = detail_pembelian.id_barang')
            ->where('detail_pembelian.id_pembelian', $id)
            ->findAll();

        return view('pembelian/detail', [
            'title' => 'Detail Pembelian',
            'pembelian' => $header,
            'detail' => $detail,
        ]);
    }
}
