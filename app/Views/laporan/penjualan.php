<?= view('layout/header', ['title' => 'Laporan Penjualan']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="mb-4">

        <h3 class="fw-bold mb-1">
            <i class="bi bi-bar-chart-line"></i>
            Laporan Penjualan
        </h3>

        <p class="text-muted mb-0">
            Laporan transaksi penjualan berdasarkan periode
        </p>

    </div>


    <!-- FILTER -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <h5 class="fw-bold mb-3">
                <i class="bi bi-funnel"></i>
                Filter Laporan
            </h5>

            <form
                action="<?= base_url('laporan/penjualan') ?>"
                method="get"
            >

                <div class="row g-3 align-items-end">

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Tanggal Mulai
                        </label>

                        <input
                            type="date"
                            name="tanggal_mulai"
                            class="form-control"
                            value="<?= esc($tanggalMulai ?? '') ?>"
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Tanggal Akhir
                        </label>

                        <input
                            type="date"
                            name="tanggal_akhir"
                            class="form-control"
                            value="<?= esc($tanggalAkhir ?? '') ?>"
                        >

                    </div>


                    <div class="col-md-4 d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-search"></i>
                            Tampilkan
                        </button>

                        <a
                            href="<?= base_url('laporan/penjualan') ?>"
                            class="btn btn-outline-secondary"
                        >
                            Reset
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- RINGKASAN -->
    <div class="row g-4 mb-4">

        <!-- JUMLAH TRANSAKSI -->
        <div class="col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <p class="text-muted mb-1">
                                Total Transaksi
                            </p>

                            <h3 class="fw-bold mb-0">
                                <?= $totalTransaksi ?>
                            </h3>

                        </div>

                        <div
                            class="bg-primary bg-opacity-10 rounded-circle p-3"
                        >

                            <i
                                class="bi bi-receipt fs-3 text-primary"
                            ></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- TOTAL PENJUALAN -->
        <div class="col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <p class="text-muted mb-1">
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

                        </div>

                        <div
                            class="bg-success bg-opacity-10 rounded-circle p-3"
                        >

                            <i
                                class="bi bi-cash-stack fs-3 text-success"
                            ></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- TABEL -->
    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h5 class="fw-bold mb-1">
                        Detail Penjualan
                    </h5>

                    <?php if ($tanggalMulai || $tanggalAkhir): ?>

                        <small class="text-muted">

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
                            Semua transaksi
                        </small>

                    <?php endif; ?>

                </div>


                <button
                    type="button"
                    onclick="window.print()"
                    class="btn btn-dark"
                >
                    <i class="bi bi-printer"></i>
                    Cetak
                </button>

            </div>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th width="60">
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

                                    <td>
                                        <?= $no++ ?>
                                    </td>

                                    <td>
                                        <span class="fw-semibold">
                                            <?= esc($row['no_transaksi']) ?>
                                        </span>
                                    </td>

                                    <td>

                                        <?= date(
                                            'd/m/Y H:i',
                                            strtotime($row['tanggal'])
                                        ) ?>

                                    </td>

                                    <td>

                                        <?= esc(
                                            $row['nama_customer']
                                            ?? 'Customer Umum'
                                        ) ?>

                                    </td>

                                    <td>

                                        <?= esc(
                                            $row['nama_lengkap']
                                            ?? '-'
                                        ) ?>

                                    </td>

                                    <td class="text-end">

                                        <strong>

                                            Rp <?= number_format(
                                                $row['total'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>

                                        </strong>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                            <!-- TOTAL -->

                            <tr class="table-light">

                                <td
                                    colspan="5"
                                    class="text-end fw-bold"
                                >
                                    TOTAL PENJUALAN
                                </td>

                                <td class="text-end">

                                    <strong class="text-success">

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

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center text-muted py-5"
                                >

                                    <i
                                        class="bi bi-inbox fs-1 d-block mb-2"
                                    ></i>

                                    Tidak ada transaksi pada periode
                                    yang dipilih.

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

@media print {

    .sidebar {
        display: none !important;
    }

    .main-content {
        margin-left: 0 !important;
        padding: 20px !important;
    }

    .btn {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }

}

</style>


<?= view('layout/footer') ?>