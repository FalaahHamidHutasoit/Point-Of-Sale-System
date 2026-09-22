<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\DetailPembelianModel;
use App\Models\DetailPenerimaanBarangModel;
use App\Models\MutasiStokModel;
use App\Models\PembelianModel;
use App\Models\PenerimaanBarangModel;

class PenerimaanBarang extends BaseController
{
    protected PenerimaanBarangModel $penerimaanModel;
    protected DetailPenerimaanBarangModel $detailPenerimaanModel;
    protected PembelianModel $pembelianModel;
    protected DetailPembelianModel $detailPembelianModel;
    protected BarangModel $barangModel;
    protected MutasiStokModel $mutasiModel;

    public function __construct()
    {
        $this->penerimaanModel = new PenerimaanBarangModel();
        $this->detailPenerimaanModel = new DetailPenerimaanBarangModel();
        $this->pembelianModel = new PembelianModel();
        $this->detailPembelianModel = new DetailPembelianModel();
        $this->barangModel = new BarangModel();
        $this->mutasiModel = new MutasiStokModel();
    }

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $builder = $this->penerimaanModel
            ->select('penerimaan_barang.*, pembelian.no_pembelian, supplier.nama_supplier, users.nama_lengkap')
            ->join('pembelian', 'pembelian.id_pembelian = penerimaan_barang.id_pembelian')
            ->join('supplier', 'supplier.id_supplier = pembelian.id_supplier')
            ->join('users', 'users.id_user = penerimaan_barang.id_user');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('penerimaan_barang.no_penerimaan', $keyword)
                ->orLike('pembelian.no_pembelian', $keyword)
                ->orLike('supplier.nama_supplier', $keyword)
                ->orLike('penerimaan_barang.no_surat_jalan', $keyword)
                ->groupEnd();
        }

        return view('penerimaan/index', [
            'title' => 'Penerimaan Barang',
            'rows' => $builder->orderBy('penerimaan_barang.tanggal', 'DESC')->findAll(),
            'keyword' => $keyword,
        ]);
    }

    public function tambah(int $idPembelian)
    {
        $po = $this->loadPo($idPembelian);
        if (!$po) {
            return redirect()->to('/pembelian')->with('error', 'Purchase order tidak ditemukan.');
        }
        if (!in_array($po['status'], ['DIORDER', 'SEBAGIAN'], true)) {
            return redirect()->to('/pembelian/' . $idPembelian)->with('error', 'PO ini tidak dapat menerima barang pada status sekarang.');
        }

        $details = $this->loadPoDetails($idPembelian);
        $hasRemaining = false;
        foreach ($details as $row) {
            if (((int) $row['qty'] - (int) $row['qty_diterima']) > 0) {
                $hasRemaining = true;
                break;
            }
        }
        if (!$hasRemaining) {
            return redirect()->to('/pembelian/' . $idPembelian)->with('error', 'Seluruh barang pada PO sudah diterima.');
        }

        return view('penerimaan/tambah', [
            'title' => 'Terima Barang',
            'pembelian' => $po,
            'detail' => $details,
        ]);
    }

    public function simpan(int $idPembelian)
    {
        if ($guard = $this->guardSensitivePost(['admin', 'gudang'])) {
            return $guard;
        }

        $qtyInputs = $this->request->getPost('qty_terima');
        $noSuratJalan = trim((string) $this->request->getPost('no_surat_jalan'));
        $catatan = trim((string) $this->request->getPost('catatan'));

        if (!is_array($qtyInputs)) {
            return redirect()->back()->withInput()->with('error', 'Jumlah penerimaan tidak valid.');
        }
        if (mb_strlen($noSuratJalan) > 60 || mb_strlen($catatan) > 255) {
            return redirect()->back()->withInput()->with('error', 'Nomor surat jalan atau catatan terlalu panjang.');
        }

        $requested = [];
        foreach ($qtyInputs as $detailId => $qty) {
            $detailId = (int) $detailId;
            $qty = (int) $qty;
            if ($detailId > 0 && $qty > 0) {
                $requested[$detailId] = $qty;
            }
        }
        if (!$requested) {
            return redirect()->back()->withInput()->with('error', 'Isi minimal satu jumlah barang yang diterima.');
        }
        ksort($requested, SORT_NUMERIC);

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $po = $db->query(
                'SELECT p.*, s.nama_supplier FROM pembelian p JOIN supplier s ON s.id_supplier=p.id_supplier WHERE p.id_pembelian=? FOR UPDATE',
                [$idPembelian]
            )->getRowArray();
            if (!$po) {
                throw new \RuntimeException('PO tidak ditemukan.');
            }
            if (!in_array($po['status'], ['DIORDER', 'SEBAGIAN'], true)) {
                $db->transRollback();
                return redirect()->to('/pembelian/' . $idPembelian)->with('error', 'PO tidak lagi dapat menerima barang.');
            }

            $items = [];
            foreach ($requested as $detailId => $qtyTerima) {
                $row = $db->query(
                    'SELECT dp.*, b.nama_barang, b.stok FROM detail_pembelian dp JOIN barang b ON b.id_barang=dp.id_barang WHERE dp.id_detail_pembelian=? AND dp.id_pembelian=? FOR UPDATE',
                    [$detailId, $idPembelian]
                )->getRowArray();
                if (!$row) {
                    throw new \RuntimeException('Detail PO tidak valid.');
                }
                $remaining = (int) $row['qty'] - (int) $row['qty_diterima'];
                if ($qtyTerima > $remaining) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Qty diterima untuk ' . $row['nama_barang'] . ' melebihi sisa PO (' . $remaining . ').');
                }
                $items[] = [
                    'detailId' => $detailId,
                    'idBarang' => (int) $row['id_barang'],
                    'qty' => $qtyTerima,
                    'hargaBeli' => (float) $row['harga_beli'],
                    'stokSebelum' => (int) $row['stok'],
                    'qtyDiterimaSebelum' => (int) $row['qty_diterima'],
                ];
            }

            $noPenerimaan = $this->buatNomorPenerimaan();
            if ($this->penerimaanModel->insert([
                'no_penerimaan' => $noPenerimaan,
                'id_pembelian' => $idPembelian,
                'id_user' => (int) session()->get('id_user'),
                'tanggal' => date('Y-m-d H:i:s'),
                'no_surat_jalan' => $noSuratJalan ?: null,
                'catatan' => $catatan ?: null,
            ]) === false) {
                throw new \RuntimeException('Header penerimaan gagal disimpan.');
            }
            $idPenerimaan = (int) $this->penerimaanModel->getInsertID();

            foreach ($items as $item) {
                $stokSesudah = $item['stokSebelum'] + $item['qty'];
                $newReceived = $item['qtyDiterimaSebelum'] + $item['qty'];
                $subtotal = $item['qty'] * $item['hargaBeli'];

                if ($this->detailPenerimaanModel->insert([
                    'id_penerimaan' => $idPenerimaan,
                    'id_detail_pembelian' => $item['detailId'],
                    'id_barang' => $item['idBarang'],
                    'qty_diterima' => $item['qty'],
                    'harga_beli' => $item['hargaBeli'],
                    'subtotal' => $subtotal,
                ]) === false) {
                    throw new \RuntimeException('Detail penerimaan gagal disimpan.');
                }
                if (!$this->detailPembelianModel->update($item['detailId'], ['qty_diterima' => $newReceived])) {
                    throw new \RuntimeException('Progress PO gagal diperbarui.');
                }
                if (!$this->barangModel->update($item['idBarang'], ['stok' => $stokSesudah, 'harga_beli' => $item['hargaBeli']])) {
                    throw new \RuntimeException('Stok barang gagal diperbarui.');
                }
                if ($this->mutasiModel->insert([
                    'id_barang' => $item['idBarang'],
                    'id_user' => (int) session()->get('id_user'),
                    'tipe' => 'MASUK',
                    'qty' => $item['qty'],
                    'stok_sebelum' => $item['stokSebelum'],
                    'stok_sesudah' => $stokSesudah,
                    'referensi_tipe' => 'PENERIMAAN',
                    'referensi_id' => $idPenerimaan,
                    'keterangan' => 'Penerimaan ' . $noPenerimaan . ' untuk ' . $po['no_pembelian'],
                ]) === false) {
                    throw new \RuntimeException('Mutasi stok gagal disimpan.');
                }
            }

            $progress = $db->query(
                'SELECT COALESCE(SUM(qty),0) AS ordered_qty, COALESCE(SUM(qty_diterima),0) AS received_qty FROM detail_pembelian WHERE id_pembelian=?',
                [$idPembelian]
            )->getRowArray();
            $newStatus = (int) $progress['received_qty'] >= (int) $progress['ordered_qty'] ? 'DITERIMA' : 'SEBAGIAN';
            $this->pembelianModel->update($idPembelian, ['status' => $newStatus]);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database transaction failed.');
            }
            $db->transCommit();

            $this->auditEvent(
                'GOODS_RECEIVED',
                'GOODS_RECEIPT',
                $idPenerimaan,
                $noPenerimaan,
                'Gudang menerima barang terhadap purchase order.',
                null,
                [
                    'id_pembelian' => $idPembelian,
                    'no_pembelian' => $po['no_pembelian'],
                    'status_po' => $newStatus,
                    'no_surat_jalan' => $noSuratJalan ?: null,
                    'jumlah_baris_barang' => count($items),
                    'qty_total_diterima' => array_sum(array_column($items, 'qty')),
                ]
            );

            return redirect()->to('/penerimaan/' . $idPenerimaan)->with('success', 'Penerimaan barang berhasil. Stok dan mutasi stok sudah diperbarui.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Penerimaan barang gagal: {message}', ['message' => $e->getMessage()]);
            $this->auditEvent('GOODS_RECEIPT_FAILED', 'GOODS_RECEIPT', null, null, 'Penerimaan barang gagal dan perubahan di-rollback.', null, ['id_pembelian' => $idPembelian], 'FAILED');
            return redirect()->back()->withInput()->with('error', 'Penerimaan gagal. Tidak ada perubahan stok yang diterapkan.');
        }
    }

    public function detail(int $id)
    {
        $header = $this->penerimaanModel
            ->select('penerimaan_barang.*, pembelian.no_pembelian, pembelian.id_supplier, supplier.nama_supplier, users.nama_lengkap')
            ->join('pembelian', 'pembelian.id_pembelian = penerimaan_barang.id_pembelian')
            ->join('supplier', 'supplier.id_supplier = pembelian.id_supplier')
            ->join('users', 'users.id_user = penerimaan_barang.id_user')
            ->where('penerimaan_barang.id_penerimaan', $id)
            ->first();
        if (!$header) {
            return redirect()->to('/penerimaan')->with('error', 'Penerimaan tidak ditemukan.');
        }

        $detail = $this->detailPenerimaanModel
            ->select('detail_penerimaan_barang.*, barang.kode_barang, barang.nama_barang, barang.satuan')
            ->join('barang', 'barang.id_barang = detail_penerimaan_barang.id_barang')
            ->where('detail_penerimaan_barang.id_penerimaan', $id)
            ->findAll();

        return view('penerimaan/detail', [
            'title' => 'Detail Penerimaan Barang',
            'penerimaan' => $header,
            'detail' => $detail,
        ]);
    }

    /** @return array<string,mixed>|null */
    private function loadPo(int $id): ?array
    {
        return $this->pembelianModel
            ->select('pembelian.*, supplier.nama_supplier, users.nama_lengkap')
            ->join('supplier', 'supplier.id_supplier = pembelian.id_supplier')
            ->join('users', 'users.id_user = pembelian.id_user')
            ->where('pembelian.id_pembelian', $id)
            ->first();
    }

    /** @return array<int,array<string,mixed>> */
    private function loadPoDetails(int $id): array
    {
        return $this->detailPembelianModel
            ->select('detail_pembelian.*, barang.kode_barang, barang.nama_barang, barang.satuan')
            ->join('barang', 'barang.id_barang = detail_pembelian.id_barang')
            ->where('detail_pembelian.id_pembelian', $id)
            ->orderBy('detail_pembelian.id_detail_pembelian', 'ASC')
            ->findAll();
    }

    private function buatNomorPenerimaan(): string
    {
        return 'GR-' . date('Ymd-His') . '-' . random_int(1000, 9999);
    }
}
