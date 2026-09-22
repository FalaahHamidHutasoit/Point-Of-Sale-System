<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\CustomerModel;
use App\Models\DetailPenjualanModel;
use App\Models\MutasiStokModel;
use App\Models\PenjualanModel;
use App\Models\DemoPaymentModel;

class Penjualan extends BaseController
{
    protected $penjualanModel;
    protected $detailModel;
    protected $barangModel;
    protected $customerModel;
    protected $mutasiModel;
    protected $demoPaymentModel;

    public function __construct()
    {
        $this->penjualanModel = new PenjualanModel();
        $this->detailModel = new DetailPenjualanModel();
        $this->barangModel = new BarangModel();
        $this->customerModel = new CustomerModel();
        $this->mutasiModel = new MutasiStokModel();
        $this->demoPaymentModel = new DemoPaymentModel();
    }

    public function index()
    {
        return view('penjualan/index', [
            'title' => 'Transaksi Penjualan',
            'customer' => $this->customerModel->orderBy('nama_customer', 'ASC')->findAll(),
            'barang' => $this->barangModel
                ->select('barang.*, kategori.nama_kategori')
                ->join('kategori', 'kategori.id_kategori = barang.id_kategori', 'left')
                ->where('barang.stok >', 0)
                ->orderBy('barang.nama_barang', 'ASC')
                ->findAll(),
        ]);
    }

    public function simpan()
    {
        if ($guard = $this->guardSensitivePost(['admin', 'kasir'])) {
            return $guard;
        }

        $customerRaw = $this->request->getPost('id_customer');
        $customerId = ($customerRaw === null || $customerRaw === '') ? null : (int) $customerRaw;
        $barangIds = $this->request->getPost('id_barang');
        $qtys = $this->request->getPost('qty');
        $bayar = (float) $this->request->getPost('bayar');
        $metode = trim((string) $this->request->getPost('metode_pembayaran')) ?: 'Tunai';

        // Phase 14: endpoint /penjualan/simpan hanya untuk pembayaran Tunai.
        // QRIS/Transfer wajib melalui prepare -> token/reference -> confirm agar tidak
        // dapat melewati lifecycle PENDING/PAID hanya dengan POST manual ke endpoint ini.
        if ($metode !== 'Tunai') {
            $this->auditEvent(
                'PAYMENT_FLOW_BYPASS_BLOCKED',
                'SECURITY',
                (int) session()->get('id_user'),
                (string) session()->get('username'),
                'Percobaan menyimpan transaksi non-tunai melalui endpoint tunai diblokir.',
                ['requested_method' => $metode],
                ['required_flow' => $metode === 'QRIS' ? 'QR_DEMO' : ($metode === 'Transfer' ? 'TRANSFER_DEMO' : 'UNKNOWN')],
                'BLOCKED'
            );

            return redirect()->back()->withInput()->with(
                'error',
                in_array($metode, ['QRIS', 'Transfer'], true)
                    ? 'Pembayaran ' . $metode . ' harus diselesaikan melalui alur verifikasi pembayaran.'
                    : 'Metode pembayaran tidak valid.'
            );
        }

        if (!is_array($barangIds) || !is_array($qtys)) {
            return redirect()->back()->withInput()->with('error', 'Keranjang masih kosong.');
        }

        // Gabungkan ID barang yang sama agar satu barang hanya divalidasi satu kali.
        $requested = [];
        foreach ($barangIds as $i => $idBarang) {
            $idBarang = (int) $idBarang;
            $qty = (int) ($qtys[$i] ?? 0);

            if ($idBarang <= 0 || $qty <= 0) {
                continue;
            }
            if ($qty > 100000) {
                return redirect()->back()->withInput()->with('error', 'Jumlah barang tidak wajar.');
            }

            $requested[$idBarang] = ($requested[$idBarang] ?? 0) + $qty;
        }

        if (!$requested) {
            return redirect()->back()->withInput()->with('error', 'Minimal harus ada satu barang.');
        }

        // Urutan lock yang konsisten mengurangi peluang deadlock pada transaksi bersamaan.
        ksort($requested, SORT_NUMERIC);

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if ($customerId !== null) {
                if ($customerId <= 0) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Customer tidak valid.');
                }

                // Lock customer agar tidak dapat dihapus di tengah proses penjualan.
                $customer = $db->query(
                    'SELECT id_customer FROM customer WHERE id_customer = ? FOR UPDATE',
                    [$customerId]
                )->getRowArray();

                if (!$customer) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Customer tidak ditemukan atau sudah dihapus.');
                }
            }

            $items = [];
            $total = 0.0;

            foreach ($requested as $idBarang => $qty) {
                // Row-level lock: penjualan/pembelian lain untuk barang yang sama harus menunggu.
                $barang = $db->query(
                    'SELECT id_barang, nama_barang, harga_jual, harga_beli, stok, satuan '
                    . 'FROM barang WHERE id_barang = ? FOR UPDATE',
                    [$idBarang]
                )->getRowArray();

                if (!$barang) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Ada barang yang tidak ditemukan.');
                }

                $stokSebelum = (int) $barang['stok'];
                if ($qty > $stokSebelum) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with(
                        'error',
                        'Stok ' . $barang['nama_barang'] . ' tidak cukup. Tersedia: ' . $stokSebelum . '.'
                    );
                }

                $harga = (float) $barang['harga_jual'];
                $hargaModal = (float) $barang['harga_beli'];
                $subtotal = $harga * $qty;
                $total += $subtotal;

                $items[] = [
                    'idBarang' => (int) $idBarang,
                    'qty' => (int) $qty,
                    'harga' => $harga,
                    'hargaModal' => $hargaModal,
                    'subtotal' => $subtotal,
                    'stokSebelum' => $stokSebelum,
                ];
            }

            if ($metode !== 'Tunai') {
                $bayar = $total;
                $kembalian = 0.0;
            } else {
                if ($bayar < $total) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with(
                        'error',
                        'Pembayaran kurang. Total Rp ' . number_format($total, 0, ',', '.') . '.'
                    );
                }
                $kembalian = $bayar - $total;
            }

            $noTransaksi = $this->buatNomorTransaksi();
            $insertHeader = $this->penjualanModel->insert([
                'no_transaksi' => $noTransaksi,
                'tanggal' => date('Y-m-d H:i:s'),
                'id_customer' => $customerId,
                'id_user' => (int) session()->get('id_user'),
                'total' => $total,
                'metode_pembayaran' => $metode,
                'bayar' => $bayar,
                'kembalian' => $kembalian,
            ]);
            if ($insertHeader === false) {
                throw new \RuntimeException('Header penjualan gagal disimpan.');
            }

            $idPenjualan = (int) $this->penjualanModel->getInsertID();

            foreach ($items as $item) {
                $stokSesudah = $item['stokSebelum'] - $item['qty'];

                if ($this->detailModel->insert([
                    'id_penjualan' => $idPenjualan,
                    'id_barang' => $item['idBarang'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'harga_modal' => $item['hargaModal'],
                    'subtotal' => $item['subtotal'],
                ]) === false) {
                    throw new \RuntimeException('Detail penjualan gagal disimpan.');
                }

                if (!$this->barangModel->update($item['idBarang'], ['stok' => $stokSesudah])) {
                    throw new \RuntimeException('Stok barang gagal diperbarui.');
                }

                if ($this->mutasiModel->insert([
                    'id_barang' => $item['idBarang'],
                    'id_user' => (int) session()->get('id_user'),
                    'tipe' => 'KELUAR',
                    'qty' => $item['qty'],
                    'stok_sebelum' => $item['stokSebelum'],
                    'stok_sesudah' => $stokSesudah,
                    'referensi_tipe' => 'PENJUALAN',
                    'referensi_id' => $idPenjualan,
                    'keterangan' => 'Penjualan ' . $noTransaksi,
                ]) === false) {
                    throw new \RuntimeException('Mutasi stok penjualan gagal disimpan.');
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database transaction failed.');
            }

            $db->transCommit();

            $this->auditEvent(
                'CREATE',
                'PENJUALAN',
                $idPenjualan,
                $noTransaksi,
                'Transaksi penjualan berhasil disimpan.',
                null,
                [
                    'no_transaksi' => $noTransaksi,
                    'id_customer' => $customerId,
                    'total' => $total,
                    'metode_pembayaran' => $metode,
                    'bayar' => $bayar,
                    'kembalian' => $kembalian,
                    'jumlah_baris_barang' => count($items),
                    'items' => array_map(
                        static fn(array $item): array => [
                            'id_barang' => $item['idBarang'],
                            'qty' => $item['qty'],
                            'harga' => $item['harga'],
                            'subtotal' => $item['subtotal'],
                        ],
                        $items
                    ),
                ]
            );

            return redirect()->to('/penjualan/sukses/' . $idPenjualan)
                ->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menyimpan penjualan: {message}', ['message' => $e->getMessage()]);
            $this->auditEvent(
                'CREATE',
                'PENJUALAN',
                null,
                null,
                'Transaksi penjualan gagal dan seluruh perubahan di-rollback.',
                null,
                [
                    'metode_pembayaran' => $metode,
                    'jumlah_barang_diminta' => count($requested ?? []),
                ],
                'FAILED'
            );
            return redirect()->back()->withInput()->with(
                'error',
                'Transaksi gagal disimpan. Tidak ada perubahan stok yang diterapkan.'
            );
        }
    }


    /**
     * Phase 7: membuat pembayaran QR demo tanpa mengurangi stok.
     * QR berisi URL token sekali pakai. Transaksi penjualan baru dibuat saat token dikonfirmasi dari HP.
     */
    public function prepareDemoQris()
    {
        if ($guard = $this->guardSensitivePost(['admin', 'kasir'])) {
            return $guard;
        }

        $customerRaw = $this->request->getPost('id_customer');
        $customerId = ($customerRaw === null || $customerRaw === '') ? null : (int) $customerRaw;
        $barangIds = $this->request->getPost('id_barang');
        $qtys = $this->request->getPost('qty');

        if (!is_array($barangIds) || !is_array($qtys)) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Keranjang masih kosong.']);
        }

        $requested = [];
        foreach ($barangIds as $i => $idBarang) {
            $idBarang = (int) $idBarang;
            $qty = (int) ($qtys[$i] ?? 0);
            if ($idBarang > 0 && $qty > 0) {
                $requested[$idBarang] = ($requested[$idBarang] ?? 0) + $qty;
            }
        }
        if (!$requested) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Minimal harus ada satu barang.']);
        }

        if ($customerId !== null && !$this->customerModel->find($customerId)) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Customer tidak ditemukan.']);
        }

        ksort($requested, SORT_NUMERIC);
        $items = [];
        $total = 0.0;
        foreach ($requested as $idBarang => $qty) {
            $barang = $this->barangModel->find($idBarang);
            if (!$barang) {
                return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Ada barang yang tidak ditemukan.']);
            }
            if ($qty > (int) $barang['stok']) {
                return $this->response->setStatusCode(422)->setJSON([
                    'ok' => false,
                    'message' => 'Stok ' . $barang['nama_barang'] . ' tidak cukup. Tersedia: ' . (int) $barang['stok'] . '.',
                ]);
            }
            $harga = (float) $barang['harga_jual'];
            $subtotal = $harga * $qty;
            $total += $subtotal;
            $items[] = [
                'id_barang' => (int) $idBarang,
                'nama_barang' => (string) $barang['nama_barang'],
                'qty' => $qty,
                'harga' => $harga,
                'harga_modal' => (float) $barang['harga_beli'],
                'subtotal' => $subtotal,
            ];
        }

        $token = bin2hex(random_bytes(32));
        $paymentReference = $this->buatQrReferenceDemo();
        $expiresAt = date('Y-m-d H:i:s', time() + 300);
        $payload = json_encode([
            'customer_id' => $customerId,
            'items' => $items,
            'total' => $total,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false || $this->demoPaymentModel->insert([
            'token' => $token,
            'method' => 'QRIS',
            'payment_reference' => $paymentReference,
            'id_user' => (int) session()->get('id_user'),
            'id_customer' => $customerId,
            'amount' => $total,
            'payload' => $payload,
            'status' => 'PENDING',
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]) === false) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => 'Gagal membuat QR pembayaran demo.']);
        }

        $paymentId = (int) $this->demoPaymentModel->getInsertID();
        $this->auditEvent('QR_CREATED', 'DEMO_PAYMENT', $paymentId, $paymentReference, 'QR pembayaran demo dibuat.', null, [
            'method' => 'QRIS',
            'payment_reference' => $paymentReference,
            'amount' => $total,
            'expires_at' => $expiresAt,
            'item_count' => count($items),
        ]);

        return $this->response->setJSON([
            'ok' => true,
            'token' => $token,
            'payment_reference' => $paymentReference,
            'amount' => $total,
            'expires_at' => $expiresAt,
            'expires_in' => 300,
            'confirm_path' => '/payment/demo/' . $token,
            'status_path' => '/penjualan/qris-demo/status/' . $token,
            'cancel_path' => '/penjualan/payment-demo/cancel/' . $token,
            'csrf_name' => csrf_token(),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function demoQrisStatus(string $token)
    {
        $payment = $this->demoPaymentModel->where('token', $token)->first();
        if (!$payment) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'status' => 'NOT_FOUND']);
        }
        $payment = $this->expireDemoPaymentIfNeeded($payment);
        return $this->response->setJSON([
            'ok' => true,
            'status' => $payment['status'],
            'remaining_seconds' => max(0, strtotime($payment['expires_at']) - time()),
            'success_url' => $payment['id_penjualan'] ? site_url('penjualan/sukses/' . $payment['id_penjualan']) : null,
        ]);
    }

    public function demoPayment(string $token)
    {
        $payment = $this->demoPaymentModel->where('token', $token)->first();
        if (!$payment) {
            return view('penjualan/demo_payment', ['payment' => null, 'title' => 'KAMELA Payment Demo']);
        }
        $payment = $this->expireDemoPaymentIfNeeded($payment);
        return view('penjualan/demo_payment', ['payment' => $payment, 'title' => 'KAMELA Payment Demo']);
    }

    public function confirmDemoPayment(string $token)
    {
        if (strtoupper((string) $this->request->getMethod()) !== 'POST') {
            return redirect()->to('/payment/demo/' . $token);
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $payment = $db->query('SELECT * FROM demo_payments WHERE token = ? FOR UPDATE', [$token])->getRowArray();
            if (!$payment) {
                $db->transRollback();
                return view('penjualan/demo_payment_result', ['ok' => false, 'message' => 'QR pembayaran tidak ditemukan.']);
            }
            if (($payment['method'] ?? 'QRIS') !== 'QRIS') {
                $db->transRollback();
                return view('penjualan/demo_payment_result', ['ok' => false, 'message' => 'Token ini bukan pembayaran QR demo.']);
            }
            if ($payment['status'] === 'PAID') {
                $db->transRollback();
                $this->auditPaymentReplay($payment, 'QRIS');
                return view('penjualan/demo_payment_result', ['ok' => true, 'message' => 'Pembayaran ini sudah selesai. Token sekali pakai tidak dapat digunakan dua kali.']);
            }
            if ($payment['status'] !== 'PENDING') {
                $db->transRollback();
                $message = $payment['status'] === 'CANCELLED'
                    ? 'Pembayaran ini sudah dibatalkan oleh kasir.'
                    : 'QR pembayaran sudah tidak aktif (' . $payment['status'] . ').';
                return view('penjualan/demo_payment_result', ['ok' => false, 'message' => $message]);
            }
            if (strtotime($payment['expires_at']) < time()) {
                $db->table('demo_payments')->where('id_demo_payment', $payment['id_demo_payment'])->update(['status' => 'EXPIRED']);
                $db->transCommit();
                $payment['status'] = 'EXPIRED';
                $this->auditPaymentExpired($payment);
                return view('penjualan/demo_payment_result', ['ok' => false, 'message' => 'QR pembayaran sudah kedaluwarsa.']);
            }

            $payload = json_decode($payment['payload'], true, 512, JSON_THROW_ON_ERROR);
            $customerId = $payload['customer_id'] ?? null;
            if ($customerId !== null) {
                $customer = $db->query('SELECT id_customer FROM customer WHERE id_customer = ? FOR UPDATE', [(int) $customerId])->getRowArray();
                if (!$customer) throw new \RuntimeException('Customer tidak lagi tersedia.');
            }

            $lockedItems = [];
            foreach ($payload['items'] as $item) {
                $barang = $db->query(
                    'SELECT id_barang, nama_barang, stok FROM barang WHERE id_barang = ? FOR UPDATE',
                    [(int) $item['id_barang']]
                )->getRowArray();
                if (!$barang || (int) $item['qty'] > (int) $barang['stok']) {
                    throw new \RuntimeException('Stok ' . ($barang['nama_barang'] ?? 'barang') . ' sudah tidak mencukupi.');
                }
                $lockedItems[] = [$item, $barang];
            }

            $noTransaksi = $this->buatNomorTransaksi();
            if ($this->penjualanModel->insert([
                'no_transaksi' => $noTransaksi,
                'tanggal' => date('Y-m-d H:i:s'),
                'id_customer' => $customerId,
                'id_user' => (int) $payment['id_user'],
                'total' => (float) $payment['amount'],
                'metode_pembayaran' => 'QRIS',
                'bayar' => (float) $payment['amount'],
                'kembalian' => 0,
            ]) === false) throw new \RuntimeException('Header penjualan gagal disimpan.');

            $idPenjualan = (int) $this->penjualanModel->getInsertID();
            foreach ($lockedItems as [$item, $barang]) {
                $stokSebelum = (int) $barang['stok'];
                $stokSesudah = $stokSebelum - (int) $item['qty'];
                if ($this->detailModel->insert([
                    'id_penjualan' => $idPenjualan,
                    'id_barang' => (int) $item['id_barang'],
                    'qty' => (int) $item['qty'],
                    'harga' => (float) $item['harga'],
                    'harga_modal' => (float) $item['harga_modal'],
                    'subtotal' => (float) $item['subtotal'],
                ]) === false) throw new \RuntimeException('Detail penjualan gagal disimpan.');
                if (!$this->barangModel->update((int) $item['id_barang'], ['stok' => $stokSesudah])) throw new \RuntimeException('Stok gagal diperbarui.');
                if ($this->mutasiModel->insert([
                    'id_barang' => (int) $item['id_barang'],
                    'id_user' => (int) $payment['id_user'],
                    'tipe' => 'KELUAR',
                    'qty' => (int) $item['qty'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi_tipe' => 'PENJUALAN',
                    'referensi_id' => $idPenjualan,
                    'keterangan' => 'Penjualan QR Demo ' . $noTransaksi,
                ]) === false) throw new \RuntimeException('Mutasi stok gagal disimpan.');
            }

            $db->table('demo_payments')->where('id_demo_payment', $payment['id_demo_payment'])->update([
                'status' => 'PAID', 'paid_at' => date('Y-m-d H:i:s'), 'id_penjualan' => $idPenjualan,
            ]);
            if ($db->transStatus() === false) throw new \RuntimeException('Database transaction failed.');
            $db->transCommit();

            $actor = $db->table('users')->where('id_user', (int) $payment['id_user'])->get()->getRowArray();
            $this->auditEvent('PAYMENT_PAID', 'DEMO_PAYMENT', (int) $payment['id_demo_payment'], $payment['payment_reference'] ?? 'QR Payment Demo',
                'Pembayaran QR demo dikonfirmasi dari perangkat pemindai.',
                ['status' => 'PENDING'],
                ['status' => 'PAID', 'id_penjualan' => $idPenjualan, 'total' => (float) $payment['amount'], 'metode_pembayaran' => 'QRIS', 'demo' => true], 'SUCCESS',
                ['id_user' => (int) $payment['id_user'], 'username' => $actor['username'] ?? null, 'name' => $actor['nama_lengkap'] ?? null, 'role' => $actor['role'] ?? null]
            );

            return view('penjualan/demo_payment_result', ['ok' => true, 'message' => 'Pembayaran demo berhasil dikonfirmasi.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Konfirmasi QR demo gagal: {message}', ['message' => $e->getMessage()]);
            $failedPayment = $this->demoPaymentModel->where('token', $token)->first();
            if ($failedPayment && $failedPayment['status'] === 'PENDING') {
                $this->demoPaymentModel->update($failedPayment['id_demo_payment'], ['status' => 'FAILED']);
                $this->auditEvent('PAYMENT_FAILED', 'DEMO_PAYMENT', (int) $failedPayment['id_demo_payment'], $failedPayment['payment_reference'] ?? 'QR Payment Demo',
                    'Pembayaran QR demo gagal diproses.', ['status' => 'PENDING'], ['status' => 'FAILED', 'reason' => $e->getMessage()], 'FAILED', $this->paymentActor($failedPayment));
            }
            return view('penjualan/demo_payment_result', [
                'ok' => false,
                'message' => 'Pembayaran demo tidak dapat diselesaikan. Silakan kembali ke kasir dan buat instruksi pembayaran baru.',
            ]);
        }
    }


    /**
     * Phase 8: membuat instruksi Transfer Demo.
     * QR pada modal hanya shortcut untuk membuka KAMELA Bank Demo di HP.
     */
    public function prepareDemoTransfer()
    {
        if ($guard = $this->guardSensitivePost(['admin', 'kasir'])) {
            return $guard;
        }

        $customerRaw = $this->request->getPost('id_customer');
        $customerId = ($customerRaw === null || $customerRaw === '') ? null : (int) $customerRaw;
        $barangIds = $this->request->getPost('id_barang');
        $qtys = $this->request->getPost('qty');

        if (!is_array($barangIds) || !is_array($qtys)) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Keranjang masih kosong.']);
        }

        $requested = [];
        foreach ($barangIds as $i => $idBarang) {
            $idBarang = (int) $idBarang;
            $qty = (int) ($qtys[$i] ?? 0);
            if ($idBarang > 0 && $qty > 0) {
                $requested[$idBarang] = ($requested[$idBarang] ?? 0) + $qty;
            }
        }

        if (!$requested) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Minimal harus ada satu barang.']);
        }

        if ($customerId !== null && !$this->customerModel->find($customerId)) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Customer tidak ditemukan.']);
        }

        ksort($requested, SORT_NUMERIC);
        $items = [];
        $total = 0.0;

        foreach ($requested as $idBarang => $qty) {
            $barang = $this->barangModel->find($idBarang);
            if (!$barang) {
                return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Ada barang yang tidak ditemukan.']);
            }
            if ($qty > (int) $barang['stok']) {
                return $this->response->setStatusCode(422)->setJSON([
                    'ok' => false,
                    'message' => 'Stok ' . $barang['nama_barang'] . ' tidak cukup. Tersedia: ' . (int) $barang['stok'] . '.',
                ]);
            }

            $harga = (float) $barang['harga_jual'];
            $subtotal = $harga * $qty;
            $total += $subtotal;
            $items[] = [
                'id_barang' => (int) $idBarang,
                'nama_barang' => (string) $barang['nama_barang'],
                'qty' => $qty,
                'harga' => $harga,
                'harga_modal' => (float) $barang['harga_beli'],
                'subtotal' => $subtotal,
            ];
        }

        $token = bin2hex(random_bytes(32));
        $virtualAccount = $this->buatVirtualAccountDemo();
        $expiresAt = date('Y-m-d H:i:s', time() + 600);
        $payload = json_encode([
            'customer_id' => $customerId,
            'items' => $items,
            'total' => $total,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false || $this->demoPaymentModel->insert([
            'token' => $token,
            'method' => 'TRANSFER',
            'payment_reference' => $virtualAccount,
            'id_user' => (int) session()->get('id_user'),
            'id_customer' => $customerId,
            'amount' => $total,
            'payload' => $payload,
            'status' => 'PENDING',
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]) === false) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => 'Gagal membuat instruksi transfer demo.']);
        }

        $paymentId = (int) $this->demoPaymentModel->getInsertID();
        $this->auditEvent('TRANSFER_CREATED', 'DEMO_PAYMENT', $paymentId, $virtualAccount, 'Instruksi transfer demo dibuat.', null, [
            'method' => 'TRANSFER',
            'virtual_account' => $virtualAccount,
            'amount' => $total,
            'expires_at' => $expiresAt,
            'item_count' => count($items),
        ]);

        return $this->response->setJSON([
            'ok' => true,
            'token' => $token,
            'virtual_account' => $virtualAccount,
            'amount' => $total,
            'expires_at' => $expiresAt,
            'expires_in' => 600,
            'bank_path' => '/demo-bank/pay/' . $token,
            'status_path' => '/penjualan/transfer-demo/status/' . $token,
            'cancel_path' => '/penjualan/payment-demo/cancel/' . $token,
            'csrf_name' => csrf_token(),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function demoTransferStatus(string $token)
    {
        $payment = $this->demoPaymentModel
            ->where('token', $token)
            ->where('method', 'TRANSFER')
            ->first();

        if (!$payment) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'status' => 'NOT_FOUND']);
        }

        $payment = $this->expireDemoPaymentIfNeeded($payment);

        return $this->response->setJSON([
            'ok' => true,
            'status' => $payment['status'],
            'remaining_seconds' => max(0, strtotime($payment['expires_at']) - time()),
            'success_url' => $payment['id_penjualan'] ? site_url('penjualan/sukses/' . $payment['id_penjualan']) : null,
        ]);
    }

    public function demoBankTransfer(string $token)
    {
        $payment = $this->demoPaymentModel
            ->where('token', $token)
            ->where('method', 'TRANSFER')
            ->first();

        if (!$payment) {
            return view('penjualan/demo_bank', [
                'title' => 'KAMELA Bank Demo',
                'payment' => null,
                'error' => 'Instruksi transfer tidak ditemukan.',
            ]);
        }

        $payment = $this->expireDemoPaymentIfNeeded($payment);

        return view('penjualan/demo_bank', [
            'title' => 'KAMELA Bank Demo',
            'payment' => $payment,
            'error' => null,
        ]);
    }

    public function confirmDemoTransfer(string $token)
    {
        if (strtoupper((string) $this->request->getMethod()) !== 'POST') {
            return redirect()->to('/demo-bank/pay/' . $token);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $payment = $db->query(
                'SELECT * FROM demo_payments WHERE token = ? AND method = ? FOR UPDATE',
                [$token, 'TRANSFER']
            )->getRowArray();

            if (!$payment) {
                $db->transRollback();
                return view('penjualan/demo_bank_result', [
                    'ok' => false,
                    'message' => 'Instruksi transfer tidak ditemukan.',
                    'payment' => null,
                ]);
            }

            if ($payment['status'] === 'PAID') {
                $db->transRollback();
                $this->auditPaymentReplay($payment, 'TRANSFER');
                return view('penjualan/demo_bank_result', [
                    'ok' => true,
                    'message' => 'Transfer ini sudah selesai sebelumnya. Virtual Account demo tidak dapat dipakai dua kali.',
                    'payment' => $payment,
                ]);
            }

            if ($payment['status'] !== 'PENDING') {
                $db->transRollback();
                return view('penjualan/demo_bank_result', [
                    'ok' => false,
                    'message' => $payment['status'] === 'CANCELLED'
                        ? 'Instruksi transfer sudah dibatalkan oleh kasir.'
                        : 'Instruksi transfer sudah tidak aktif (' . $payment['status'] . ').',
                    'payment' => $payment,
                ]);
            }

            if (strtotime($payment['expires_at']) < time()) {
                $db->table('demo_payments')
                    ->where('id_demo_payment', $payment['id_demo_payment'])
                    ->update(['status' => 'EXPIRED']);
                $db->transCommit();
                $payment['status'] = 'EXPIRED';
                $this->auditPaymentExpired($payment);

                return view('penjualan/demo_bank_result', [
                    'ok' => false,
                    'message' => 'Instruksi transfer sudah kedaluwarsa.',
                    'payment' => $payment,
                ]);
            }

            $nominalTransfer = (float) $this->request->getPost('amount');
            $expectedAmount = (float) $payment['amount'];

            if (abs($nominalTransfer - $expectedAmount) >= 0.01) {
                $db->transRollback();
                $payment['status'] = 'PENDING';
                return view('penjualan/demo_bank', [
                    'title' => 'KAMELA Bank Demo',
                    'payment' => $payment,
                    'error' => 'Nominal transfer harus tepat Rp ' . number_format($expectedAmount, 0, ',', '.') . '.',
                ]);
            }

            $payload = json_decode($payment['payload'], true, 512, JSON_THROW_ON_ERROR);
            $customerId = $payload['customer_id'] ?? null;

            if ($customerId !== null) {
                $customer = $db->query(
                    'SELECT id_customer FROM customer WHERE id_customer = ? FOR UPDATE',
                    [(int) $customerId]
                )->getRowArray();
                if (!$customer) {
                    throw new \RuntimeException('Customer tidak lagi tersedia.');
                }
            }

            $lockedItems = [];
            foreach ($payload['items'] as $item) {
                $barang = $db->query(
                    'SELECT id_barang, nama_barang, stok FROM barang WHERE id_barang = ? FOR UPDATE',
                    [(int) $item['id_barang']]
                )->getRowArray();

                if (!$barang || (int) $item['qty'] > (int) $barang['stok']) {
                    throw new \RuntimeException('Stok ' . ($barang['nama_barang'] ?? 'barang') . ' sudah tidak mencukupi.');
                }
                $lockedItems[] = [$item, $barang];
            }

            $noTransaksi = $this->buatNomorTransaksi();
            if ($this->penjualanModel->insert([
                'no_transaksi' => $noTransaksi,
                'tanggal' => date('Y-m-d H:i:s'),
                'id_customer' => $customerId,
                'id_user' => (int) $payment['id_user'],
                'total' => $expectedAmount,
                'metode_pembayaran' => 'Transfer',
                'bayar' => $nominalTransfer,
                'kembalian' => 0,
            ]) === false) {
                throw new \RuntimeException('Header penjualan gagal disimpan.');
            }

            $idPenjualan = (int) $this->penjualanModel->getInsertID();

            foreach ($lockedItems as [$item, $barang]) {
                $stokSebelum = (int) $barang['stok'];
                $stokSesudah = $stokSebelum - (int) $item['qty'];

                if ($this->detailModel->insert([
                    'id_penjualan' => $idPenjualan,
                    'id_barang' => (int) $item['id_barang'],
                    'qty' => (int) $item['qty'],
                    'harga' => (float) $item['harga'],
                    'harga_modal' => (float) $item['harga_modal'],
                    'subtotal' => (float) $item['subtotal'],
                ]) === false) {
                    throw new \RuntimeException('Detail penjualan gagal disimpan.');
                }

                if (!$this->barangModel->update((int) $item['id_barang'], ['stok' => $stokSesudah])) {
                    throw new \RuntimeException('Stok gagal diperbarui.');
                }

                if ($this->mutasiModel->insert([
                    'id_barang' => (int) $item['id_barang'],
                    'id_user' => (int) $payment['id_user'],
                    'tipe' => 'KELUAR',
                    'qty' => (int) $item['qty'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi_tipe' => 'PENJUALAN',
                    'referensi_id' => $idPenjualan,
                    'keterangan' => 'Penjualan Transfer Demo ' . $noTransaksi,
                ]) === false) {
                    throw new \RuntimeException('Mutasi stok gagal disimpan.');
                }
            }

            $db->table('demo_payments')
                ->where('id_demo_payment', $payment['id_demo_payment'])
                ->update([
                    'status' => 'PAID',
                    'paid_at' => date('Y-m-d H:i:s'),
                    'id_penjualan' => $idPenjualan,
                ]);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database transaction failed.');
            }

            $db->transCommit();

            $actor = $db->table('users')
                ->where('id_user', (int) $payment['id_user'])
                ->get()
                ->getRowArray();

            $this->auditEvent(
                'TRANSFER_CONFIRMED',
                'DEMO_PAYMENT',
                (int) $payment['id_demo_payment'],
                $payment['payment_reference'],
                'Transfer demo dikonfirmasi dari KAMELA Bank Demo.',
                ['status' => 'PENDING'],
                [
                    'status' => 'PAID',
                    'id_penjualan' => $idPenjualan,
                    'total' => $expectedAmount,
                    'metode_pembayaran' => 'Transfer',
                    'virtual_account' => $payment['payment_reference'],
                    'demo' => true,
                ],
                'SUCCESS',
                [
                    'id_user' => (int) $payment['id_user'],
                    'username' => $actor['username'] ?? null,
                    'name' => $actor['nama_lengkap'] ?? null,
                    'role' => $actor['role'] ?? null,
                ]
            );

            $payment['status'] = 'PAID';
            $payment['paid_at'] = date('Y-m-d H:i:s');
            $payment['id_penjualan'] = $idPenjualan;

            return view('penjualan/demo_bank_result', [
                'ok' => true,
                'message' => 'Transfer demo berhasil.',
                'payment' => $payment,
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Konfirmasi Transfer Demo gagal: {message}', ['message' => $e->getMessage()]);
            $failedPayment = $this->demoPaymentModel->where('token', $token)->where('method', 'TRANSFER')->first();
            if ($failedPayment && $failedPayment['status'] === 'PENDING') {
                $this->demoPaymentModel->update($failedPayment['id_demo_payment'], ['status' => 'FAILED']);
                $this->auditEvent('PAYMENT_FAILED', 'DEMO_PAYMENT', (int) $failedPayment['id_demo_payment'], $failedPayment['payment_reference'],
                    'Transfer demo gagal diproses.', ['status' => 'PENDING'], ['status' => 'FAILED', 'reason' => $e->getMessage()], 'FAILED', $this->paymentActor($failedPayment));
            }
            return view('penjualan/demo_bank_result', [
                'ok' => false,
                'message' => 'Transfer demo tidak dapat diselesaikan. Silakan kembali ke kasir dan buat instruksi transfer baru.',
                'payment' => $failedPayment ?? null,
            ]);
        }
    }

    /**
     * Membatalkan payment demo yang masih PENDING. Tidak ada stok/transaksi penjualan yang berubah.
     */
    public function cancelDemoPayment(string $token)
    {
        if ($guard = $this->guardSensitivePost(['admin', 'kasir'])) {
            return $guard;
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $payment = $db->query('SELECT * FROM demo_payments WHERE token = ? FOR UPDATE', [$token])->getRowArray();
            if (!$payment) {
                $db->transRollback();
                return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'status' => 'NOT_FOUND', 'message' => 'Pembayaran demo tidak ditemukan.', 'csrf_name' => csrf_token(), 'csrf_hash' => csrf_hash()]);
            }

            $sessionUserId = (int) session()->get('id_user');
            $sessionRole = (string) session()->get('role');
            if ($sessionRole !== 'admin' && (int) $payment['id_user'] !== $sessionUserId) {
                $db->transRollback();
                return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'status' => 'BLOCKED', 'message' => 'Pembayaran ini dibuat oleh kasir lain.', 'csrf_name' => csrf_token(), 'csrf_hash' => csrf_hash()]);
            }

            if ($payment['status'] === 'PAID') {
                $db->transRollback();
                return $this->response->setStatusCode(409)->setJSON(['ok' => false, 'status' => 'PAID', 'message' => 'Pembayaran sudah selesai dan tidak dapat dibatalkan.', 'csrf_name' => csrf_token(), 'csrf_hash' => csrf_hash()]);
            }

            if ($payment['status'] !== 'PENDING') {
                $db->transRollback();
                return $this->response->setJSON(['ok' => true, 'status' => $payment['status'], 'message' => 'Pembayaran sudah tidak aktif.', 'csrf_name' => csrf_token(), 'csrf_hash' => csrf_hash()]);
            }

            if (strtotime($payment['expires_at']) < time()) {
                $db->table('demo_payments')->where('id_demo_payment', $payment['id_demo_payment'])->update(['status' => 'EXPIRED']);
                $db->transCommit();
                $payment['status'] = 'EXPIRED';
                $this->auditPaymentExpired($payment);
                return $this->response->setJSON([
                    'ok' => true,
                    'status' => 'EXPIRED',
                    'message' => 'Pembayaran sudah kedaluwarsa.',
                    'csrf_name' => csrf_token(),
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            $cancelledAt = date('Y-m-d H:i:s');
            $db->table('demo_payments')->where('id_demo_payment', $payment['id_demo_payment'])->update([
                'status' => 'CANCELLED',
                'cancelled_at' => $cancelledAt,
            ]);
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal membatalkan pembayaran demo.');
            }
            $db->transCommit();

            $this->auditEvent('PAYMENT_CANCELLED', 'DEMO_PAYMENT', (int) $payment['id_demo_payment'], $payment['payment_reference'] ?? strtoupper((string) $payment['method']),
                'Pembayaran demo dibatalkan oleh kasir.', ['status' => 'PENDING'], ['status' => 'CANCELLED', 'cancelled_at' => $cancelledAt, 'method' => $payment['method']]);

            return $this->response->setJSON([
                'ok' => true,
                'status' => 'CANCELLED',
                'message' => 'Pembayaran dibatalkan. Stok tidak berubah.',
                'csrf_name' => csrf_token(),
                'csrf_hash' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Pembatalan payment demo gagal: {message}', ['message' => $e->getMessage()]);
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => 'Gagal membatalkan pembayaran.', 'csrf_name' => csrf_token(), 'csrf_hash' => csrf_hash()]);
        }
    }

    private function expireDemoPaymentIfNeeded(array $payment): array
    {
        if ($payment['status'] !== 'PENDING' || strtotime($payment['expires_at']) >= time()) {
            return $payment;
        }

        $db = \Config\Database::connect();
        $db->table('demo_payments')
            ->where('id_demo_payment', $payment['id_demo_payment'])
            ->where('status', 'PENDING')
            ->update(['status' => 'EXPIRED']);

        if ($db->affectedRows() > 0) {
            $payment['status'] = 'EXPIRED';
            $this->auditPaymentExpired($payment);
        } else {
            $latest = $this->demoPaymentModel->find($payment['id_demo_payment']);
            if ($latest) {
                $payment = $latest;
            }
        }

        return $payment;
    }

    private function auditPaymentExpired(array $payment): void
    {
        $this->auditEvent(
            'PAYMENT_EXPIRED',
            'DEMO_PAYMENT',
            (int) $payment['id_demo_payment'],
            $payment['payment_reference'] ?? strtoupper((string) $payment['method']),
            'Payment demo kedaluwarsa sebelum pembayaran selesai.',
            ['status' => 'PENDING'],
            ['status' => 'EXPIRED', 'method' => $payment['method'], 'expires_at' => $payment['expires_at']],
            'SUCCESS',
            $this->paymentActor($payment)
        );
    }

    private function auditPaymentReplay(array $payment, string $method): void
    {
        $this->auditEvent(
            'PAYMENT_REPLAY_BLOCKED',
            'DEMO_PAYMENT',
            (int) $payment['id_demo_payment'],
            $payment['payment_reference'] ?? $method,
            'Percobaan konfirmasi ulang terhadap payment yang sudah PAID diblokir.',
            ['status' => 'PAID'],
            ['status' => 'PAID', 'method' => $method],
            'BLOCKED',
            $this->paymentActor($payment)
        );
    }

    private function paymentActor(array $payment): array
    {
        $db = \Config\Database::connect();
        $actor = $db->table('users')->where('id_user', (int) $payment['id_user'])->get()->getRowArray();
        return [
            'id_user' => (int) $payment['id_user'],
            'username' => $actor['username'] ?? null,
            'name' => $actor['nama_lengkap'] ?? null,
            'role' => $actor['role'] ?? null,
        ];
    }

    private function buatQrReferenceDemo(): string
    {
        do {
            $reference = 'QRD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $exists = $this->demoPaymentModel->where('payment_reference', $reference)->first();
        } while ($exists);

        return $reference;
    }

    private function buatVirtualAccountDemo(): string
    {
        do {
            $virtualAccount = '8808' . str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
            $exists = $this->demoPaymentModel->where('payment_reference', $virtualAccount)->first();
        } while ($exists);

        return $virtualAccount;
    }

    public function sukses($id)
    {
        $penjualan = $this->penjualanModel
            ->select('penjualan.*, customer.nama_customer, users.nama_lengkap')
            ->join('customer', 'customer.id_customer = penjualan.id_customer', 'left')
            ->join('users', 'users.id_user = penjualan.id_user')
            ->where('penjualan.id_penjualan', (int) $id)
            ->first();

        if (!$penjualan) {
            return redirect()->to('/penjualan/riwayat')->with('error', 'Transaksi tidak ditemukan.');
        }

        $detail = $this->detailModel
            ->select('detail_penjualan.*, barang.kode_barang, barang.nama_barang, barang.satuan')
            ->join('barang', 'barang.id_barang = detail_penjualan.id_barang')
            ->where('detail_penjualan.id_penjualan', (int) $id)
            ->findAll();

        $payment = $this->demoPaymentModel
            ->where('id_penjualan', (int) $id)
            ->orderBy('id_demo_payment', 'DESC')
            ->first();

        return view('penjualan/sukses', [
            'title' => 'Detail Transaksi',
            'penjualan' => $penjualan,
            'detail' => $detail,
            'payment' => $payment,
        ]);
    }

    public function riwayat()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));

        // Pastikan payment yang sudah lewat waktu tampil sebagai EXPIRED di riwayat.
        $expiredCandidates = $this->demoPaymentModel
            ->where('status', 'PENDING')
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->findAll(100);
        foreach ($expiredCandidates as $candidate) {
            $this->expireDemoPaymentIfNeeded($candidate);
        }

        $builder = $this->penjualanModel
            ->select('penjualan.*, customer.nama_customer, users.nama_lengkap, demo_payments.status AS payment_status, demo_payments.method AS payment_method, demo_payments.payment_reference, demo_payments.paid_at')
            ->join('customer', 'customer.id_customer = penjualan.id_customer', 'left')
            ->join('users', 'users.id_user = penjualan.id_user', 'left')
            ->join('demo_payments', 'demo_payments.id_penjualan = penjualan.id_penjualan', 'left');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('penjualan.no_transaksi', $keyword)
                ->orLike('customer.nama_customer', $keyword)
                ->orLike('users.nama_lengkap', $keyword)
                ->orLike('demo_payments.payment_reference', $keyword)
                ->groupEnd();
        }

        $paymentAttempts = $this->demoPaymentModel
            ->select('demo_payments.*, customer.nama_customer, users.nama_lengkap')
            ->join('customer', 'customer.id_customer = demo_payments.id_customer', 'left')
            ->join('users', 'users.id_user = demo_payments.id_user', 'left')
            ->orderBy('demo_payments.id_demo_payment', 'DESC')
            ->findAll(30);

        return view('penjualan/riwayat', [
            'title' => 'Riwayat Penjualan',
            'penjualan' => $builder->orderBy('penjualan.id_penjualan', 'DESC')->findAll(),
            'paymentAttempts' => $paymentAttempts,
            'keyword' => $keyword,
        ]);
    }

    public function laporan()
    {
        $tanggalMulai = $this->request->getGet('tanggal_mulai');
        $tanggalAkhir = $this->request->getGet('tanggal_akhir');
        $builder = $this->penjualanModel
            ->select('penjualan.*, customer.nama_customer, users.nama_lengkap')
            ->join('customer', 'customer.id_customer = penjualan.id_customer', 'left')
            ->join('users', 'users.id_user = penjualan.id_user', 'left');

        if ($tanggalMulai) {
            $builder->where('DATE(penjualan.tanggal) >=', $tanggalMulai);
        }
        if ($tanggalAkhir) {
            $builder->where('DATE(penjualan.tanggal) <=', $tanggalAkhir);
        }

        $rows = $builder->orderBy('penjualan.tanggal', 'DESC')->findAll();
        $total = array_sum(array_map(static fn($row) => (float) $row['total'], $rows));

        return view('laporan/penjualan', [
            'title' => 'Laporan Penjualan',
            'penjualan' => $rows,
            'tanggalMulai' => $tanggalMulai,
            'tanggalAkhir' => $tanggalAkhir,
            'totalTransaksi' => count($rows),
            'totalPenjualan' => $total,
        ]);
    }

    private function buatNomorTransaksi(): string
    {
        // Timestamp sampai mikrodetik + random membuat collision jauh lebih kecil.
        $now = new \DateTimeImmutable();
        return 'TRX-' . $now->format('YmdHisu') . '-' . random_int(1000, 9999);
    }
}
