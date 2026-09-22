<?php

namespace App\Controllers;

use App\Models\StockOpnameDetailModel;
use App\Models\StockOpnameModel;
use RuntimeException;

class StockOpname extends BaseController
{
    private StockOpnameModel $opnameModel;
    private StockOpnameDetailModel $detailModel;

    public function __construct()
    {
        $this->opnameModel = new StockOpnameModel();
        $this->detailModel = new StockOpnameDetailModel();
    }

    public function index()
    {
        $rows = $this->opnameModel
            ->select('stock_opname.*, users.nama_lengkap AS pembuat, fu.nama_lengkap AS finalizer, cu.nama_lengkap AS canceller')
            ->join('users', 'users.id_user = stock_opname.id_user', 'left')
            ->join('users fu', 'fu.id_user = stock_opname.finalized_by', 'left')
            ->join('users cu', 'cu.id_user = stock_opname.cancelled_by', 'left')
            ->orderBy('stock_opname.id_opname', 'DESC')
            ->paginate(10, 'opname');

        return view('stok_opname/index', [
            'title' => 'Stock Opname',
            'opname' => $rows,
            'pager' => $this->opnameModel->pager,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardSensitivePost(['admin', 'gudang'])) {
            return $guard;
        }

        $catatan = trim((string) $this->request->getPost('catatan'));
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $existing = $db->query("SELECT id_opname, no_opname FROM stock_opname WHERE active_guard = 1 LIMIT 1 FOR UPDATE")
                ->getRowArray();
            if ($existing) {
                $db->transRollback();
                return redirect()->to('/stok/opname/' . (int) $existing['id_opname'])
                    ->with('error', 'Masih ada sesi stock opname DRAFT. Selesaikan atau batalkan sesi tersebut terlebih dahulu.');
            }

            $barang = $db->query('SELECT id_barang, stok FROM barang ORDER BY id_barang ASC FOR UPDATE')->getResultArray();
            if (!$barang) {
                throw new RuntimeException('Tidak ada barang yang dapat diopname.');
            }

            $noOpname = 'SO-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $db->table('stock_opname')->insert([
                'no_opname' => $noOpname,
                'tanggal' => date('Y-m-d H:i:s'),
                'id_user' => (int) session()->get('id_user'),
                'status' => 'DRAFT',
                'catatan' => $catatan ?: null,
                'active_guard' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $idOpname = (int) $db->insertID();

            $batch = [];
            foreach ($barang as $row) {
                $batch[] = [
                    'id_opname' => $idOpname,
                    'id_barang' => (int) $row['id_barang'],
                    'stok_sistem' => (int) $row['stok'],
                    'stok_fisik' => null,
                    'selisih' => null,
                ];
            }
            if (!$db->table('stock_opname_detail')->insertBatch($batch)) {
                throw new RuntimeException('Detail stock opname gagal dibuat.');
            }

            if ($db->transStatus() === false) {
                throw new RuntimeException('Transaction gagal.');
            }
            $db->transCommit();

            $this->auditEvent(
                'STOCK_OPNAME_CREATE',
                'STOCK_OPNAME',
                $idOpname,
                $noOpname,
                'Sesi stock opname dibuat dengan snapshot stok sistem.',
                null,
                ['jumlah_barang' => count($batch), 'catatan' => $catatan ?: null]
            );

            return redirect()->to('/stok/opname/' . $idOpname)->with('success', 'Sesi stock opname dibuat. Masukkan hasil hitung fisik sebelum finalisasi.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal membuat stock opname: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/stok/opname')->with('error', 'Stock opname gagal dibuat.');
        }
    }

    public function detail(int $id)
    {
        $header = $this->opnameModel
            ->select('stock_opname.*, users.nama_lengkap AS pembuat, fu.nama_lengkap AS finalizer, cu.nama_lengkap AS canceller')
            ->join('users', 'users.id_user = stock_opname.id_user', 'left')
            ->join('users fu', 'fu.id_user = stock_opname.finalized_by', 'left')
            ->join('users cu', 'cu.id_user = stock_opname.cancelled_by', 'left')
            ->where('stock_opname.id_opname', $id)
            ->first();

        if (!$header) {
            return redirect()->to('/stok/opname')->with('error', 'Stock opname tidak ditemukan.');
        }

        $details = $this->detailModel
            ->select('stock_opname_detail.*, barang.kode_barang, barang.nama_barang, barang.satuan, barang.stok AS stok_sekarang, kategori.nama_kategori')
            ->join('barang', 'barang.id_barang = stock_opname_detail.id_barang')
            ->join('kategori', 'kategori.id_kategori = barang.id_kategori', 'left')
            ->where('stock_opname_detail.id_opname', $id)
            ->orderBy('barang.nama_barang', 'ASC')
            ->findAll();

        return view('stok_opname/detail', [
            'title' => 'Detail Stock Opname',
            'header' => $header,
            'details' => $details,
        ]);
    }

    public function saveCounts(int $id)
    {
        if ($guard = $this->guardSensitivePost(['admin', 'gudang'])) {
            return $guard;
        }

        $counts = $this->request->getPost('stok_fisik');
        if (!is_array($counts)) {
            return redirect()->to('/stok/opname/' . $id)->with('error', 'Data hitung fisik tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $header = $db->query('SELECT * FROM stock_opname WHERE id_opname = ? FOR UPDATE', [$id])->getRowArray();
            if (!$header || $header['status'] !== 'DRAFT') {
                $db->transRollback();
                return redirect()->to('/stok/opname/' . $id)->with('error', 'Hanya stock opname berstatus DRAFT yang dapat diedit.');
            }

            $details = $db->table('stock_opname_detail')->where('id_opname', $id)->get()->getResultArray();
            $updated = 0;

            foreach ($details as $detail) {
                $detailId = (int) $detail['id_detail_opname'];
                $raw = $counts[$detailId] ?? null;

                if ($raw === '' || $raw === null) {
                    $db->table('stock_opname_detail')->where('id_detail_opname', $detailId)->update([
                        'stok_fisik' => null,
                        'selisih' => null,
                    ]);
                    continue;
                }

                if (filter_var($raw, FILTER_VALIDATE_INT) === false || (int) $raw < 0) {
                    throw new RuntimeException('Stok fisik harus berupa bilangan bulat 0 atau lebih.');
                }

                $fisik = (int) $raw;
                $sistem = (int) $detail['stok_sistem'];
                $db->table('stock_opname_detail')->where('id_detail_opname', $detailId)->update([
                    'stok_fisik' => $fisik,
                    'selisih' => $fisik - $sistem,
                ]);
                $updated++;
            }

            if ($db->transStatus() === false) {
                throw new RuntimeException('Gagal menyimpan hitungan.');
            }
            $db->transCommit();

            $this->auditEvent(
                'STOCK_OPNAME_SAVE',
                'STOCK_OPNAME',
                $id,
                $header['no_opname'],
                'Hitungan fisik stock opname disimpan.',
                null,
                ['baris_terisi' => $updated]
            );

            return redirect()->to('/stok/opname/' . $id)->with('success', 'Hitungan fisik tersimpan. Data masih DRAFT dan belum mengubah stok.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/stok/opname/' . $id)->with('error', $e->getMessage());
        }
    }

    public function finalize(int $id)
    {
        if ($guard = $this->guardSensitivePost(['admin', 'gudang'])) {
            return $guard;
        }

        if (trim((string) $this->request->getPost('confirmation')) !== 'FINALISASI') {
            return redirect()->to('/stok/opname/' . $id)->with('error', 'Ketik FINALISASI untuk mengonfirmasi perubahan stok.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $header = $db->query('SELECT * FROM stock_opname WHERE id_opname = ? FOR UPDATE', [$id])->getRowArray();
            if (!$header || $header['status'] !== 'DRAFT') {
                throw new RuntimeException('Stock opname tidak lagi berstatus DRAFT.');
            }

            $details = $db->query(
                'SELECT d.*, b.nama_barang FROM stock_opname_detail d JOIN barang b ON b.id_barang = d.id_barang WHERE d.id_opname = ? ORDER BY d.id_barang ASC',
                [$id]
            )->getResultArray();

            if (!$details) {
                throw new RuntimeException('Detail stock opname kosong.');
            }

            foreach ($details as $detail) {
                if ($detail['stok_fisik'] === null) {
                    throw new RuntimeException('Semua barang harus dihitung sebelum finalisasi. Masih ada stok fisik yang kosong.');
                }
            }

            $changes = [];
            foreach ($details as $detail) {
                $barang = $db->query('SELECT id_barang, nama_barang, stok FROM barang WHERE id_barang = ? FOR UPDATE', [(int) $detail['id_barang']])->getRowArray();
                if (!$barang) {
                    throw new RuntimeException('Ada barang yang tidak lagi tersedia.');
                }

                $snapshot = (int) $detail['stok_sistem'];
                $current = (int) $barang['stok'];
                if ($current !== $snapshot) {
                    throw new RuntimeException(
                        'Stok ' . $barang['nama_barang'] . ' berubah sejak sesi opname dibuat (' . $snapshot . ' → ' . $current . '). Batalkan sesi ini lalu buat opname baru.'
                    );
                }

                $physical = (int) $detail['stok_fisik'];
                $diff = $physical - $current;
                if ($diff === 0) {
                    continue;
                }

                $db->table('barang')->where('id_barang', (int) $barang['id_barang'])->update(['stok' => $physical]);
                $db->table('mutasi_stok')->insert([
                    'id_barang' => (int) $barang['id_barang'],
                    'id_user' => (int) session()->get('id_user'),
                    'tipe' => 'PENYESUAIAN',
                    'qty' => abs($diff),
                    'stok_sebelum' => $current,
                    'stok_sesudah' => $physical,
                    'referensi_tipe' => 'STOK_OPNAME',
                    'referensi_id' => $id,
                    'keterangan' => 'Finalisasi stock opname ' . $header['no_opname'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $changes[] = [
                    'id_barang' => (int) $barang['id_barang'],
                    'nama_barang' => $barang['nama_barang'],
                    'sebelum' => $current,
                    'sesudah' => $physical,
                    'selisih' => $diff,
                ];
            }

            $db->table('stock_opname')->where('id_opname', $id)->update([
                'status' => 'FINALIZED',
                'active_guard' => null,
                'finalized_by' => (int) session()->get('id_user'),
                'finalized_at' => date('Y-m-d H:i:s'),
            ]);

            if ($db->transStatus() === false) {
                throw new RuntimeException('Finalisasi database gagal.');
            }
            $db->transCommit();

            $this->auditEvent(
                'STOCK_OPNAME_FINALIZE',
                'STOCK_OPNAME',
                $id,
                $header['no_opname'],
                'Stock opname difinalisasi dan selisih diposting ke mutasi stok.',
                null,
                ['jumlah_penyesuaian' => count($changes), 'perubahan' => $changes]
            );

            return redirect()->to('/stok/opname/' . $id)->with('success', 'Stock opname difinalisasi. Semua selisih stok sudah masuk Mutasi Stok.');
        } catch (\Throwable $e) {
            $db->transRollback();
            $this->auditEvent('STOCK_OPNAME_FINALIZE', 'STOCK_OPNAME', $id, null, 'Finalisasi stock opname ditolak/gagal.', null, ['reason' => $e->getMessage()], 'BLOCKED');
            return redirect()->to('/stok/opname/' . $id)->with('error', $e->getMessage());
        }
    }

    public function cancel(int $id)
    {
        if ($guard = $this->guardSensitivePost(['admin', 'gudang'])) {
            return $guard;
        }

        if (trim((string) $this->request->getPost('confirmation')) !== 'BATALKAN') {
            return redirect()->to('/stok/opname/' . $id)->with('error', 'Ketik BATALKAN untuk membatalkan sesi stock opname.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $header = $db->query('SELECT * FROM stock_opname WHERE id_opname = ? FOR UPDATE', [$id])->getRowArray();
            if (!$header || $header['status'] !== 'DRAFT') {
                throw new RuntimeException('Hanya sesi DRAFT yang dapat dibatalkan.');
            }

            $db->table('stock_opname')->where('id_opname', $id)->update([
                'status' => 'CANCELLED',
                'active_guard' => null,
                'cancelled_by' => (int) session()->get('id_user'),
                'cancelled_at' => date('Y-m-d H:i:s'),
            ]);

            if ($db->transStatus() === false) {
                throw new RuntimeException('Pembatalan gagal.');
            }
            $db->transCommit();

            $this->auditEvent('STOCK_OPNAME_CANCEL', 'STOCK_OPNAME', $id, $header['no_opname'], 'Sesi stock opname dibatalkan tanpa mengubah stok.');
            return redirect()->to('/stok/opname/' . $id)->with('success', 'Sesi stock opname dibatalkan. Tidak ada stok yang berubah.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/stok/opname/' . $id)->with('error', $e->getMessage());
        }
    }
}
