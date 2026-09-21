<?= view('layout/header', ['title' => 'Transaksi Berhasil']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <div class="receipt-wrapper">

        <!-- HEADER HALAMAN -->
        <div class="page-header d-flex justify-content-between align-items-center mb-4 no-print">

            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="success-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>

                    <div>
                        <h3 class="fw-bold mb-0">
                            Transaksi Berhasil
                        </h3>

                        <p class="text-muted mb-0">
                            Nota transaksi berhasil dibuat.
                        </p>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">

                <button onclick="window.print()" class="btn btn-dark">
                    <i class="bi bi-printer me-1"></i>
                    Cetak Nota
                </button>

                <a href="<?= base_url('penjualan') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Transaksi Baru
                </a>

            </div>

        </div>


        <!-- NOTA -->
        <div class="card border-0 shadow-sm nota">

            <div class="card-body p-4 p-md-5">

                <!-- HEADER TOKO -->
                <div class="store-header text-center">

                    <div class="store-icon">
                        <i class="bi bi-shop"></i>
                    </div>

                    <h2 class="fw-bold mb-1">
                        POS SYSTEM
                    </h2>

                    <p class="text-muted mb-2">
                        Sistem Informasi Penjualan
                    </p>

                    <span class="nota-label">
                        NOTA PENJUALAN
                    </span>

                </div>


                <div class="divider"></div>


                <!-- STATUS -->
                <div class="transaction-status">

                    <div class="status-icon">
                        <i class="bi bi-check2"></i>
                    </div>

                    <div>
                        <div class="fw-bold">
                            Pembayaran Berhasil
                        </div>

                        <small class="text-muted">
                            Transaksi telah berhasil disimpan ke sistem.
                        </small>
                    </div>

                </div>


                <!-- INFORMASI TRANSAKSI -->
                <div class="info-card mt-4">

                    <div class="row g-4">

                        <div class="col-md-6">

                            <div class="info-item">

                                <div class="info-icon">
                                    <i class="bi bi-receipt"></i>
                                </div>

                                <div>
                                    <small class="text-muted d-block">
                                        No. Transaksi
                                    </small>

                                    <strong>
                                        <?= esc($penjualan['no_transaksi']) ?>
                                    </strong>
                                </div>

                            </div>


                            <div class="info-item">

                                <div class="info-icon">
                                    <i class="bi bi-calendar3"></i>
                                </div>

                                <div>
                                    <small class="text-muted d-block">
                                        Tanggal
                                    </small>

                                    <strong>
                                        <?= date(
                                            'd-m-Y H:i',
                                            strtotime($penjualan['tanggal'])
                                        ) ?>
                                    </strong>
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="info-item">

                                <div class="info-icon">
                                    <i class="bi bi-person"></i>
                                </div>

                                <div>
                                    <small class="text-muted d-block">
                                        Customer
                                    </small>

                                    <strong>
                                        <?= esc(
                                            $penjualan['nama_customer']
                                            ?? 'Customer Umum'
                                        ) ?>
                                    </strong>
                                </div>

                            </div>


                            <div class="info-item">

                                <div class="info-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>

                                <div>
                                    <small class="text-muted d-block">
                                        Kasir
                                    </small>

                                    <strong>
                                        <?= esc($penjualan['nama_lengkap']) ?>
                                    </strong>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- DETAIL BARANG -->
                <div class="section-title mt-4 mb-3">

                    <div>
                        <h5 class="fw-bold mb-1">
                            Detail Pembelian
                        </h5>

                        <small class="text-muted">
                            Daftar barang dalam transaksi
                        </small>
                    </div>

                    <span class="item-count">
                        <?= count($detail) ?> Item
                    </span>

                </div>


                <div class="table-responsive">

                    <table class="table transaction-table align-middle">

                        <thead>

                            <tr>

                                <th width="50">
                                    #
                                </th>

                                <th>
                                    Barang
                                </th>

                                <th class="text-center">
                                    Qty
                                </th>

                                <th class="text-end">
                                    Harga
                                </th>

                                <th class="text-end">
                                    Subtotal
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php $no = 1; ?>

                            <?php foreach ($detail as $item): ?>

                                <tr>

                                    <td class="text-muted">
                                        <?= $no++ ?>
                                    </td>


                                    <td>

                                        <div class="product-name">
                                            <?= esc($item['nama_barang']) ?>
                                        </div>

                                        <small class="product-code">
                                            <?= esc($item['kode_barang']) ?>
                                        </small>

                                    </td>


                                    <td class="text-center">

                                        <span class="qty-badge">
                                            <?= esc($item['qty']) ?>
                                        </span>

                                    </td>


                                    <td class="text-end">

                                        Rp <?= number_format(
                                            $item['harga'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </td>


                                    <td class="text-end fw-bold">

                                        Rp <?= number_format(
                                            $item['subtotal'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <!-- RINGKASAN PEMBAYARAN -->
                <div class="payment-summary">

                    <div class="row justify-content-end">

                        <div class="col-md-5">

                            <div class="payment-row">

                                <span>
                                    Total Belanja
                                </span>

                                <strong class="total-value">
                                    Rp <?= number_format(
                                        $penjualan['total'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </strong>

                            </div>


                            <div class="payment-row">
                                <span>Metode Pembayaran</span>
                                <strong><?= esc($penjualan['metode_pembayaran'] ?? 'Tunai') ?></strong>
                            </div>

                            <div class="payment-row">

                                <span>
                                    Uang Dibayar
                                </span>

                                <span>
                                    Rp <?= number_format(
                                        $penjualan['bayar'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </span>

                            </div>


                            <div class="payment-row change-row">

                                <span>
                                    Kembalian
                                </span>

                                <strong>
                                    Rp <?= number_format(
                                        $penjualan['kembalian'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- FOOTER -->
                <div class="receipt-footer text-center">

                    <div class="footer-icon">
                        <i class="bi bi-heart-fill"></i>
                    </div>

                    <h6 class="fw-bold mb-1">
                        Terima Kasih
                    </h6>

                    <p class="text-muted mb-1">
                        Terima kasih telah melakukan transaksi.
                    </p>

                    <small class="text-muted">
                        Simpan nota ini sebagai bukti transaksi.
                    </small>

                    <div class="mt-3">
                        <small class="text-muted">
                            POS SYSTEM • Sistem Informasi Penjualan
                        </small>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

/* ================================
   PAGE
================================ */

.receipt-wrapper {
    max-width: 1000px;
    margin: 0 auto;
}


/* ================================
   SUCCESS ICON
================================ */

.success-icon {
    width: 46px;
    height: 46px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #198754;
    color: white;

    border-radius: 12px;

    font-size: 24px;

    box-shadow: 0 5px 15px rgba(25, 135, 84, .20);
}


/* ================================
   NOTA
================================ */

.nota {
    max-width: 900px;
    margin: auto;

    border-radius: 18px;
    overflow: hidden;

    background: #fff;
}


/* ================================
   STORE HEADER
================================ */

.store-header {
    padding: 10px 0 5px;
}

.store-icon {
    width: 58px;
    height: 58px;

    margin: 0 auto 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 16px;

    background: #f0f4ff;
    color: #0d6efd;

    font-size: 27px;
}

.nota-label {
    display: inline-block;

    padding: 6px 14px;

    border-radius: 50px;

    background: #f1f3f5;

    font-size: 11px;
    font-weight: 700;

    letter-spacing: 1px;

    color: #6c757d;
}


/* ================================
   DIVIDER
================================ */

.divider {
    border-top: 1px dashed #dee2e6;
    margin: 25px 0;
}


/* ================================
   STATUS
================================ */

.transaction-status {
    display: flex;
    align-items: center;
    gap: 14px;

    padding: 16px 18px;

    background: #f1fdf6;

    border: 1px solid #d1f0dd;

    border-radius: 12px;
}

.status-icon {
    width: 38px;
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #198754;
    color: white;

    border-radius: 50%;

    font-size: 19px;
}


/* ================================
   INFO TRANSAKSI
================================ */

.info-card {
    background: #f8f9fa;

    border: 1px solid #edf0f2;

    border-radius: 14px;

    padding: 20px;
}

.info-item {
    display: flex;
    align-items: center;

    gap: 12px;

    margin-bottom: 18px;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-icon {
    width: 38px;
    height: 38px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    background: white;

    border: 1px solid #e5e7eb;

    border-radius: 10px;

    color: #0d6efd;

    font-size: 17px;
}


/* ================================
   SECTION TITLE
================================ */

.section-title {
    display: flex;

    justify-content: space-between;
    align-items: center;
}

.item-count {
    padding: 6px 12px;

    background: #f1f3f5;

    border-radius: 50px;

    font-size: 12px;
    font-weight: 600;

    color: #6c757d;
}


/* ================================
   TABLE
================================ */

.transaction-table {
    margin-bottom: 0;
}

.transaction-table thead th {
    background: #212529;
    color: white;

    font-size: 13px;

    padding: 13px 12px;

    border: none;
}

.transaction-table tbody td {
    padding: 15px 12px;

    border-color: #edf0f2;
}

.product-name {
    font-weight: 600;
}

.product-code {
    color: #8a94a6;

    font-size: 12px;
}

.qty-badge {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-width: 32px;
    height: 28px;

    padding: 0 8px;

    background: #f1f3f5;

    border-radius: 8px;

    font-weight: 600;

    font-size: 13px;
}


/* ================================
   PAYMENT
================================ */

.payment-summary {
    margin-top: 25px;

    padding: 20px;

    background: #f8f9fa;

    border-radius: 14px;
}

.payment-row {
    display: flex;

    justify-content: space-between;
    align-items: center;

    padding: 10px 0;

    color: #495057;
}

.total-value {
    font-size: 19px;
}

.change-row {
    margin-top: 5px;

    padding: 13px 15px;

    background: #eaf7ef;

    border-radius: 10px;

    color: #198754;

    font-size: 15px;
}


/* ================================
   FOOTER NOTA
================================ */

.receipt-footer {
    margin-top: 35px;

    padding-top: 25px;

    border-top: 1px dashed #dee2e6;
}

.footer-icon {
    color: #dc3545;

    font-size: 15px;

    margin-bottom: 8px;
}


/* ================================
   RESPONSIVE
================================ */

@media (max-width: 768px) {

    .main-content {
        padding: 20px !important;
    }

    .page-header {
        align-items: flex-start !important;

        flex-direction: column;

        gap: 15px;
    }

    .page-header > div:last-child {
        width: 100%;
    }

    .page-header .btn {
        flex: 1;
    }

    .nota .card-body {
        padding: 20px !important;
    }

    .transaction-table {
        min-width: 700px;
    }

}


/* ================================
   PRINT
================================ */

@media print {

    @page {
        size: A4;
        margin: 12mm;
    }

    body {
        background: white !important;
    }

    .sidebar,
    .no-print {
        display: none !important;
    }

    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }

    .receipt-wrapper {
        max-width: 100% !important;
    }

    .nota {
        max-width: 100% !important;

        margin: 0 !important;

        border: none !important;

        box-shadow: none !important;

        border-radius: 0 !important;
    }

    .nota .card-body {
        padding: 0 !important;
    }

    .transaction-status {
        background: #f8f9fa !important;
        border: 1px solid #ddd !important;
    }

    .info-card,
    .payment-summary {
        background: #fff !important;
        border: 1px solid #ddd !important;
    }

    .transaction-table thead th {
        background: #212529 !important;
        color: white !important;

        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .change-row {
        background: #f8f9fa !important;

        color: #198754 !important;

        border: 1px solid #ddd;
    }

}

</style>


<?= view('layout/footer') ?>