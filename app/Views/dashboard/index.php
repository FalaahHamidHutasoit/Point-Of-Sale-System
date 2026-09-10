<?= view('layout/header') ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="mb-4">
        <h3 class="fw-bold mb-1">
            Dashboard
        </h3>

        <p class="text-muted mb-0">
            Selamat datang kembali,
            <strong><?= esc(session()->get('nama_lengkap')) ?></strong>
        </p>
    </div>


    <!-- STATISTIK -->
    <div class="row g-4 mb-4">

        <!-- TOTAL BARANG -->
        <div class="col-md-6 col-xl-3">

            <div class="card dashboard-card shadow-sm border-0">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted mb-1">
                                Total Barang
                            </p>

                            <h3 class="fw-bold mb-0">
                                <?= number_format($totalBarang) ?>
                            </h3>
                        </div>

                        <div class="icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-box-seam"></i>
                        </div>

                    </div>

                    <a href="<?= base_url('barang') ?>"
                       class="small text-decoration-none">
                        Lihat barang →
                    </a>

                </div>

            </div>

        </div>


        <!-- CUSTOMER -->
        <div class="col-md-6 col-xl-3">

            <div class="card dashboard-card shadow-sm border-0">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Total Customer
                            </p>

                            <h3 class="fw-bold mb-0">
                                <?= number_format($totalCustomer) ?>
                            </h3>

                        </div>

                        <div class="icon-box bg-success-subtle text-success">
                            <i class="bi bi-people"></i>
                        </div>

                    </div>

                    <a href="<?= base_url('customer') ?>"
                       class="small text-decoration-none">
                        Lihat customer →
                    </a>

                </div>

            </div>

        </div>


        <!-- TRANSAKSI -->
        <div class="col-md-6 col-xl-3">

            <div class="card dashboard-card shadow-sm border-0">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Total Transaksi
                            </p>

                            <h3 class="fw-bold mb-0">
                                <?= number_format($totalTransaksi) ?>
                            </h3>

                        </div>

                        <div class="icon-box bg-warning-subtle text-warning">
                            <i class="bi bi-receipt"></i>
                        </div>

                    </div>

                    <a href="<?= base_url('penjualan/riwayat') ?>"
                       class="small text-decoration-none">
                        Lihat transaksi →
                    </a>

                </div>

            </div>

        </div>


        <!-- STOK MENIPIS -->
        <div class="col-md-6 col-xl-3">

            <div class="card dashboard-card shadow-sm border-0">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Stok Menipis
                            </p>

                            <h3 class="fw-bold mb-0">
                                <?= number_format($stokMenipis) ?>
                            </h3>

                        </div>

                        <div class="icon-box bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>

                    </div>

                    <a href="<?= base_url('laporan/barang') ?>"
                       class="small text-decoration-none">
                        Cek stok →
                    </a>

                </div>

            </div>

        </div>

    </div>


    <!-- TOTAL PENJUALAN -->
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="d-flex align-items-center">

                <div class="icon-box bg-success-subtle text-success me-3">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <div>

                    <p class="text-muted mb-1">
                        Total Penjualan
                    </p>

                    <h2 class="fw-bold mb-0">
                        Rp <?= number_format($totalPenjualan, 0, ',', '.') ?>
                    </h2>

                </div>

            </div>

        </div>

    </div>


    <!-- WELCOME -->
    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            <h5 class="fw-bold">
                <i class="bi bi-shop me-2"></i>
                POS System
            </h5>

            <p class="text-muted mb-0">
                Sistem informasi penjualan untuk mengelola
                barang, customer, transaksi, stok, dan laporan
                secara terintegrasi.
            </p>

        </div>

    </div>

</div>


<style>

.dashboard-card {
    transition: 0.2s ease;
}

.dashboard-card:hover {
    transform: translateY(-4px);
}

.icon-box {
    width: 50px;
    height: 50px;
    border-radius: 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 22px;
}

</style>

<?= view('layout/footer') ?>