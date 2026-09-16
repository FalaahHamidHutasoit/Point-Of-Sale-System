<?= view('layout/header', ['title' => 'Laporan Penjualan']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-bar-chart-line me-2 text-primary"></i>
                Laporan Penjualan
            </h2>

            <p class="text-muted mb-0">
                Informasi transaksi penjualan berdasarkan periode.
            </p>
        </div>

        <button type="button"
                onclick="window.print()"
                class="btn btn-dark px-4">

            <i class="bi bi-printer me-2"></i>
            Cetak Laporan

        </button>

    </div>


    <!-- FILTER -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-4">

                <div class="filter-icon me-3">

                    <i class="bi bi-calendar-range"></i>

                </div>

                <div>

                    <h5 class="fw-bold mb-1">
                        Filter Periode
                    </h5>

                    <small class="text-muted">
                        Pilih rentang tanggal untuk melihat laporan penjualan.
                    </small>

                </div>

            </div>


            <form method="get"
                  action="<?= base_url('laporan/penjualan') ?>">

                <div class="row g-3 align-items-end">

                    <!-- TANGGAL MULAI -->
                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Tanggal Mulai
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">

                                <i class="bi bi-calendar3"></i>

                            </span>

                            <input
                                type="date"
                                name="tanggal_mulai"
                                class="form-control"
                                value="<?= esc($tanggalMulai ?? '') ?>"
                            >

                        </div>

                    </div>


                    <!-- TANGGAL AKHIR -->
                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Tanggal Akhir
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">

                                <i class="bi bi-calendar3"></i>

                            </span>

                            <input
                                type="date"
                                name="tanggal_akhir"
                                class="form-control"
                                value="<?= esc($tanggalAkhir ?? '') ?>"
                            >

                        </div>

                    </div>


                    <!-- BUTTON -->
                    <div class="col-md-4 d-flex gap-2">

                        <button type="submit"
                                class="btn btn-primary flex-grow-1">

                            <i class="bi bi-search me-1"></i>
                            Tampilkan

                        </button>

                        <a href="<?= base_url('laporan/penjualan') ?>"
                           class="btn btn-light border">

                            <i class="bi bi-arrow-counterclockwise"></i>

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- SUMMARY -->
    <div class="row g-3 mb-4">

        <!-- TOTAL TRANSAKSI -->
        <div class="col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1 small">
                                Total Transaksi
                            </p>

                            <h3 class="fw-bold mb-0">
                                <?= $totalTransaksi ?>
                            </h3>

                            <small class="text-muted">
                                Transaksi pada periode terpilih
                            </small>

                        </div>

                        <div class="summary-icon bg-primary bg-opacity-10 text-primary">

                            <i class="bi bi-receipt"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- TOTAL PENJUALAN -->
        <div class="col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1 small">
                                Total Penjualan
                            </p>

                            <h3 class="fw-bold text-success mb-0">

                                Rp <?= number_format(
                                    $totalPenjualan,
                                    0,
                                    ',',
                                    '.'
                                ) ?>

                            </h3>

                            <small class="text-muted">
                                Total nilai transaksi
                            </small>

                        </div>

                        <div class="summary-icon bg-success bg-opacity-10 text-success">

                            <i class="bi bi-cash-stack"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- DATA TRANSAKSI -->
    <div class="card border-0 shadow-sm">

        <!-- CARD HEADER -->
        <div class="card-header bg-white border-0 p-4 pb-2">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="fw-bold mb-1">
                        Detail Penjualan
                    </h5>

                    <?php if ($tanggalMulai || $tanggalAkhir): ?>

                        <small class="text-muted">

                            <i class="bi bi-calendar3 me-1"></i>

                            Periode:

                            <?= $tanggalMulai
                                ? date('d/m/Y', strtotime($tanggalMulai))
                                : '-' ?>

                            s/d

                            <?= $tanggalAkhir
                                ? date('d/m/Y', strtotime($tanggalAkhir))
                                : '-' ?>

                        </small>

                    <?php else: ?>

                        <small class="text-muted">

                            <i class="bi bi-calendar3 me-1"></i>

                            Semua transaksi

                        </small>

                    <?php endif; ?>

                </div>


                <span class="badge bg-primary rounded-pill px-3 py-2">

                    <?= $totalTransaksi ?> Transaksi

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


                                    <!-- NO TRANSAKSI -->
                                    <td>

                                        <span class="transaction-badge">

                                            <i class="bi bi-receipt me-1"></i>

                                            <?= esc($row['no_transaksi']) ?>

                                        </span>

                                    </td>


                                    <!-- TANGGAL -->
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

                                                    <?= esc($row['nama_customer']) ?>

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

                                </tr>

                            <?php endforeach; ?>


                            <!-- TOTAL -->
                            <tr class="total-row">

                                <td colspan="5"
                                    class="text-end fw-bold">

                                    TOTAL PENJUALAN

                                </td>

                                <td class="text-end">

                                    <strong class="text-success fs-6">

                                        Rp <?= number_format(
                                            $totalPenjualan,
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </strong>

                                </td>

                            </tr>


                        <?php else: ?>

                            <!-- EMPTY STATE -->
                            <tr>

                                <td colspan="6"
                                    class="text-center py-5">

                                    <div class="empty-icon mb-3">

                                        <i class="bi bi-receipt"></i>

                                    </div>

                                    <h6 class="fw-bold">
                                        Tidak Ada Transaksi
                                    </h6>

                                    <p class="text-muted mb-0">

                                        Tidak ada transaksi pada periode yang dipilih.

                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<style>

/* SUMMARY ICON */

.summary-icon {

    width: 52px;
    height: 52px;

    border-radius: 14px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 23px;

}


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

    padding: 6px 10px;

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


/* EMPTY */

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


/* TOTAL ROW */

.total-row td {

    border-top: 2px solid #dee2e6;

    background: #f8f9fa;

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
    .summary-icon,
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