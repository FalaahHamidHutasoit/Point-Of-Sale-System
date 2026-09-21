<?= view('layout/header', ['title' => 'Riwayat Transaksi']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">

                <i class="bi bi-clock-history me-2 text-primary"></i>

                Riwayat Transaksi

            </h2>

            <p class="text-muted mb-0">

                Daftar seluruh transaksi penjualan yang telah dilakukan.

            </p>

        </div>


        <a href="<?= base_url('penjualan') ?>"
           class="btn btn-primary px-4">

            <i class="bi bi-plus-lg me-1"></i>

            Transaksi Baru

        </a>

    </div>


    <!-- SUCCESS ALERT -->
    <?php if (session()->getFlashdata('success')): ?>

        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center">

            <i class="bi bi-check-circle-fill me-2"></i>

            <div>

                <?= session()->getFlashdata('success') ?>

            </div>

        </div>

    <?php endif; ?>


    <!-- SEARCH -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-3">

                <div class="filter-icon me-3">

                    <i class="bi bi-search"></i>

                </div>

                <div>

                    <h5 class="fw-bold mb-1">
                        Cari Transaksi
                    </h5>

                    <small class="text-muted">
                        Cari berdasarkan nomor transaksi atau nama customer.
                    </small>

                </div>

            </div>


            <form
                action="<?= base_url('penjualan/riwayat') ?>"
                method="get"
            >

                <div class="row g-2">

                    <div class="col-md-10">

                        <div class="input-group">

                            <span class="input-group-text bg-light">

                                <i class="bi bi-search"></i>

                            </span>

                            <input
                                type="text"
                                name="keyword"
                                class="form-control"
                                placeholder="Cari nomor transaksi atau customer..."
                                value="<?= esc($keyword ?? '') ?>"
                            >

                        </div>

                    </div>


                    <div class="col-md-2 d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary flex-grow-1"
                        >

                            <i class="bi bi-search me-1"></i>

                            Cari

                        </button>


                        <?php if (!empty($keyword)): ?>

                            <a
                                href="<?= base_url('penjualan/riwayat') ?>"
                                class="btn btn-light border"
                                title="Reset pencarian"
                            >

                                <i class="bi bi-arrow-counterclockwise"></i>

                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- TRANSACTION TABLE -->
    <div class="card border-0 shadow-sm">

        <!-- CARD HEADER -->
        <div class="card-header bg-white border-0 p-4 pb-2">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="fw-bold mb-1">
                        Data Transaksi
                    </h5>

                    <small class="text-muted">

                        <?php if (!empty($keyword)): ?>

                            Menampilkan hasil pencarian untuk:

                            <strong>
                                "<?= esc($keyword) ?>"
                            </strong>

                        <?php else: ?>

                            Daftar seluruh transaksi penjualan.

                        <?php endif; ?>

                    </small>

                </div>


                <span class="badge bg-primary rounded-pill px-3 py-2">

                    <?= count($penjualan ?? []) ?> Transaksi

                </span>

            </div>

        </div>


        <!-- TABLE -->
        <div class="card-body p-4 pt-3">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>

                        <tr>

                            <th class="text-center">
                                No
                            </th>

                            <th>
                                No. Transaksi
                            </th>

                            <th>
                                Tanggal
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Kasir
                            </th>

                            <th class="text-end">
                                Total
                            </th>

                            <th>
                                Pembayaran
                            </th>

                            <th class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (!empty($penjualan)): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($penjualan as $row): ?>

                                <tr>

                                    <!-- NO -->
                                    <td class="text-center text-muted">

                                        <?= $no++ ?>

                                    </td>


                                    <!-- TRANSACTION NUMBER -->
                                    <td>

                                        <span class="transaction-badge">

                                            <i class="bi bi-receipt me-1"></i>

                                            <?= esc($row['no_transaksi']) ?>

                                        </span>

                                    </td>


                                    <!-- DATE -->
                                    <td>

                                        <div class="d-flex align-items-center">

                                            <div class="date-icon me-2">

                                                <i class="bi bi-calendar3"></i>

                                            </div>

                                            <div>

                                                <div class="fw-semibold">

                                                    <?= date(
                                                        'd/m/Y',
                                                        strtotime($row['tanggal'])
                                                    ) ?>

                                                </div>

                                                <small class="text-muted">

                                                    <?= date(
                                                        'H:i',
                                                        strtotime($row['tanggal'])
                                                    ) ?>

                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- CUSTOMER -->
                                    <td>

                                        <?php if (!empty($row['nama_customer'])): ?>

                                            <div class="d-flex align-items-center">

                                                <div class="avatar-icon me-2">

                                                    <i class="bi bi-person"></i>

                                                </div>

                                                <span>

                                                    <?= esc(
                                                        $row['nama_customer']
                                                    ) ?>

                                                </span>

                                            </div>

                                        <?php else: ?>

                                            <span class="badge bg-light text-secondary border">

                                                <i class="bi bi-people me-1"></i>

                                                Customer Umum

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- KASIR -->
                                    <td>

                                        <div class="d-flex align-items-center">

                                            <div class="avatar-icon me-2">

                                                <i class="bi bi-person-badge"></i>

                                            </div>

                                            <span>

                                                <?= esc(
                                                    $row['nama_lengkap'] ?? '-'
                                                ) ?>

                                            </span>

                                        </div>

                                    </td>


                                    <!-- TOTAL -->
                                    <td class="text-end">

                                        <span class="fw-bold text-success">

                                            Rp <?= number_format(
                                                $row['total'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- PAYMENT -->
                                    <td>
                                        <?php
                                            $paymentStatus = $row['payment_status'] ?? 'PAID';
                                            $statusClass = match ($paymentStatus) {
                                                'PAID' => 'text-bg-success',
                                                'PENDING' => 'text-bg-warning',
                                                'EXPIRED', 'FAILED' => 'text-bg-danger',
                                                'CANCELLED' => 'text-bg-secondary',
                                                default => 'text-bg-light',
                                            };
                                        ?>
                                        <div class="fw-semibold small mb-1"><?= esc($row['metode_pembayaran'] ?? 'Tunai') ?></div>
                                        <span class="badge <?= $statusClass ?>"><?= esc($paymentStatus) ?></span>
                                        <?php if (!empty($row['payment_reference'])): ?>
                                            <div class="small text-muted font-monospace mt-1"><?= esc($row['payment_reference']) ?></div>
                                        <?php endif; ?>
                                    </td>


                                    <!-- ACTION -->
                                    <td class="text-center">

                                        <a
                                            href="<?= base_url(
                                                'penjualan/sukses/' .
                                                $row['id_penjualan']
                                            ) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Lihat detail transaksi"
                                        >

                                            <i class="bi bi-eye me-1"></i>

                                            Detail

                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                        <?php else: ?>

                            <!-- EMPTY STATE -->
                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center py-5"
                                >

                                    <div class="empty-icon mb-3">

                                        <i class="bi bi-receipt"></i>

                                    </div>

                                    <h6 class="fw-bold">

                                        Belum Ada Transaksi

                                    </h6>

                                    <p class="text-muted mb-3">

                                        Belum terdapat transaksi penjualan
                                        atau data tidak ditemukan.

                                    </p>


                                    <?php if (!empty($keyword)): ?>

                                        <a
                                            href="<?= base_url('penjualan/riwayat') ?>"
                                            class="btn btn-outline-secondary btn-sm"
                                        >

                                            <i class="bi bi-arrow-counterclockwise me-1"></i>

                                            Reset Pencarian

                                        </a>

                                    <?php else: ?>

                                        <a
                                            href="<?= base_url('penjualan') ?>"
                                            class="btn btn-primary btn-sm"
                                        >

                                            <i class="bi bi-plus-lg me-1"></i>

                                            Buat Transaksi

                                        </a>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- PAYMENT ATTEMPTS -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-0 p-4 pb-2">
            <h5 class="fw-bold mb-1"><i class="bi bi-credit-card-2-front me-2 text-primary"></i>Aktivitas Pembayaran Demo</h5>
            <small class="text-muted">Menampilkan payment attempt terbaru, termasuk yang pending, expired, gagal, atau dibatalkan.</small>
        </div>
        <div class="card-body p-4 pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Metode</th>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th class="text-end">Nominal</th>
                            <th>Status</th>
                            <th>Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($paymentAttempts)): ?>
                        <?php foreach ($paymentAttempts as $paymentRow): ?>
                            <?php
                                $attemptStatus = $paymentRow['status'] ?? 'UNKNOWN';
                                $attemptClass = match ($attemptStatus) {
                                    'PAID' => 'text-bg-success',
                                    'PENDING' => 'text-bg-warning',
                                    'EXPIRED', 'FAILED' => 'text-bg-danger',
                                    'CANCELLED' => 'text-bg-secondary',
                                    default => 'text-bg-light',
                                };
                                $finishedAt = $paymentRow['paid_at'] ?: ($paymentRow['cancelled_at'] ?? null);
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= date('d/m/Y', strtotime($paymentRow['created_at'])) ?></div>
                                    <small class="text-muted"><?= date('H:i:s', strtotime($paymentRow['created_at'])) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= esc($paymentRow['method']) ?></span></td>
                                <td class="font-monospace small"><?= esc($paymentRow['payment_reference'] ?: '-') ?></td>
                                <td><?= esc($paymentRow['nama_customer'] ?: 'Customer Umum') ?></td>
                                <td class="text-end fw-semibold">Rp <?= number_format((float)$paymentRow['amount'], 0, ',', '.') ?></td>
                                <td><span class="badge <?= $attemptClass ?>"><?= esc($attemptStatus) ?></span></td>
                                <td>
                                    <?php if ($finishedAt): ?>
                                        <small><?= date('d/m/Y H:i:s', strtotime($finishedAt)) ?></small>
                                    <?php elseif ($attemptStatus === 'EXPIRED'): ?>
                                        <small class="text-muted">Expired <?= date('H:i:s', strtotime($paymentRow['expires_at'])) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada payment demo.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<style>

/* FILTER ICON */

.filter-icon {

    width: 44px;
    height: 44px;

    border-radius: 11px;

    background: rgba(13, 110, 253, 0.1);

    color: #0d6efd;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 19px;

}


/* TRANSACTION BADGE */

.transaction-badge {

    display: inline-flex;

    align-items: center;

    padding: 7px 10px;

    border-radius: 8px;

    background: #f1f3f5;

    border: 1px solid #dee2e6;

    font-size: 13px;

    font-weight: 600;

}


/* DATE ICON */

.date-icon {

    width: 34px;
    height: 34px;

    border-radius: 8px;

    background: rgba(13, 110, 253, 0.08);

    color: #0d6efd;

    display: flex;
    align-items: center;
    justify-content: center;

}


/* AVATAR */

.avatar-icon {

    width: 34px;
    height: 34px;

    border-radius: 50%;

    background: #f1f3f5;

    color: #6c757d;

    display: flex;
    align-items: center;
    justify-content: center;

}


/* EMPTY STATE */

.empty-icon {

    width: 64px;
    height: 64px;

    margin: auto;

    border-radius: 16px;

    background: #f1f3f5;

    color: #6c757d;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 30px;

}


/* TABLE */

.table thead th {

    font-size: 13px;

    font-weight: 600;

    color: #6c757d;

    text-transform: uppercase;

    letter-spacing: 0.3px;

    border-bottom: 1px solid #dee2e6;

}


.table tbody td {

    padding-top: 14px;
    padding-bottom: 14px;

}


/* PRINT */

@media print {

    .sidebar,
    .btn,
    form,
    .filter-icon,
    .date-icon,
    .avatar-icon {

        display: none !important;

    }


    .main-content {

        margin-left: 0 !important;

        padding: 10px !important;

    }


    body {

        background: white !important;

    }


    .card {

        box-shadow: none !important;

        border: none !important;

    }


    .card-header {

        padding-left: 0 !important;
        padding-right: 0 !important;

    }


    .table {

        font-size: 12px;

    }


    .table th,
    .table td {

        padding: 7px;

    }


    h2 {

        font-size: 22px;

    }

}

</style>


<?= view('layout/footer') ?>