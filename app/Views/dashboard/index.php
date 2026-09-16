<?= $this->include('layout/header') ?>
<?= $this->include('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Dashboard</h2>

        <p class="text-muted mb-0">
            Selamat datang kembali,
            <strong><?= esc(session()->get('nama_lengkap')) ?></strong>
        </p>
    </div>


    <!-- STATISTIK -->
    <div class="row g-4 mb-4">

        <!-- TOTAL BARANG -->
        <div class="col-xl-3 col-md-6">

            <div class="dashboard-card">

                <div class="card-icon">
                    <i class="bi bi-box-seam"></i>
                </div>

                <div>
                    <p class="card-label">
                        Total Barang
                    </p>

                    <h3 class="card-value">
                        <?= number_format($totalBarang) ?>
                    </h3>
                </div>

            </div>

        </div>


        <!-- TOTAL CUSTOMER -->
        <div class="col-xl-3 col-md-6">

            <div class="dashboard-card">

                <div class="card-icon">
                    <i class="bi bi-people"></i>
                </div>

                <div>
                    <p class="card-label">
                        Total Customer
                    </p>

                    <h3 class="card-value">
                        <?= number_format($totalCustomer) ?>
                    </h3>
                </div>

            </div>

        </div>


        <!-- TOTAL TRANSAKSI -->
        <div class="col-xl-3 col-md-6">

            <div class="dashboard-card">

                <div class="card-icon">
                    <i class="bi bi-cart-check"></i>
                </div>

                <div>
                    <p class="card-label">
                        Total Transaksi
                    </p>

                    <h3 class="card-value">
                        <?= number_format($totalTransaksi) ?>
                    </h3>

                </div>

            </div>

        </div>


        <!-- TOTAL PENJUALAN -->
        <div class="col-xl-3 col-md-6">

            <div class="dashboard-card">

                <div class="card-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <div>

                    <p class="card-label">
                        Total Penjualan
                    </p>

                    <h3 class="card-value">
                        Rp <?= number_format($totalPenjualan, 0, ',', '.') ?>
                    </h3>

                </div>

            </div>

        </div>

    </div>


    <!-- CONTENT BAWAH -->
    <div class="row g-4">


        <!-- STOK MENIPIS -->
        <div class="col-lg-6">

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>
                        <h5 class="fw-bold mb-1">
                            Stok Menipis
                        </h5>

                        <small class="text-muted">
                            Barang dengan stok 5 atau kurang
                        </small>
                    </div>

                    <i class="bi bi-exclamation-triangle panel-icon"></i>

                </div>


                <?php if (!empty($stokMenipis)): ?>

                    <div class="table-responsive">

                        <table class="table align-middle mb-0">

                            <thead>

                                <tr>

                                    <th>Barang</th>

                                    <th>Kategori</th>

                                    <th class="text-end">
                                        Stok
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($stokMenipis as $barang): ?>

                                    <tr>

                                        <td>
                                            <strong>
                                                <?= esc($barang['nama_barang']) ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= esc($barang['nama_kategori'] ?? '-') ?>
                                        </td>

                                        <td class="text-end">

                                            <span class="badge bg-danger-subtle text-danger">
                                                <?= esc($barang['stok']) ?>
                                                <?= esc($barang['satuan'] ?? 'pcs') ?>
                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <i class="bi bi-check-circle"></i>

                        <p>
                            Semua stok barang masih aman.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- INFORMASI SISTEM -->
        <div class="col-lg-6">

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>
                        <h5 class="fw-bold mb-1">
                            Informasi Sistem
                        </h5>

                        <small class="text-muted">
                            Ringkasan sistem POS
                        </small>
                    </div>

                    <i class="bi bi-info-circle panel-icon"></i>

                </div>


                <div class="system-info">

                    <div class="info-item">

                        <div class="info-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>

                        <div>
                            <small>Pengguna Login</small>

                            <strong>
                                <?= esc(session()->get('nama_lengkap')) ?>
                            </strong>
                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>

                        <div>
                            <small>Role</small>

                            <strong>
                                <?= esc(ucfirst(session()->get('role'))) ?>
                            </strong>
                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-icon">
                            <i class="bi bi-calendar3"></i>
                        </div>

                        <div>
                            <small>Tanggal</small>

                            <strong>
                                <?= date('d F Y') ?>
                            </strong>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

    /* =========================
       DASHBOARD CARD
    ========================= */

    .dashboard-card {

        background: #ffffff;

        border-radius: 18px;

        padding: 22px;

        display: flex;

        align-items: center;

        gap: 18px;

        border: 1px solid #eef0f4;

        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);

        transition: all 0.2s ease;

    }


    .dashboard-card:hover {

        transform: translateY(-3px);

        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);

    }


    .card-icon {

        width: 52px;

        height: 52px;

        border-radius: 14px;

        background: #eaf2ff;

        color: #0d6efd;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 24px;

        flex-shrink: 0;

    }


    .card-label {

        margin: 0;

        color: #6c757d;

        font-size: 13px;

    }


    .card-value {

        margin: 4px 0 0;

        font-size: 23px;

        font-weight: 700;

        color: #212529;

    }


    /* =========================
       PANEL
    ========================= */

    .dashboard-panel {

        background: #ffffff;

        border-radius: 18px;

        border: 1px solid #eef0f4;

        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);

        overflow: hidden;

    }


    .panel-header {

        padding: 20px 22px;

        display: flex;

        justify-content: space-between;

        align-items: center;

        border-bottom: 1px solid #f0f1f3;

    }


    .panel-icon {

        font-size: 22px;

        color: #0d6efd;

    }


    .dashboard-panel .table {

        font-size: 14px;

    }


    .dashboard-panel .table thead th {

        font-size: 12px;

        color: #6c757d;

        font-weight: 600;

        border-bottom: 1px solid #eef0f4;

        padding: 14px 20px;

    }


    .dashboard-panel .table tbody td {

        padding: 15px 20px;

        border-color: #f1f2f4;

    }


    /* =========================
       EMPTY STATE
    ========================= */

    .empty-state {

        padding: 45px 20px;

        text-align: center;

        color: #6c757d;

    }


    .empty-state i {

        font-size: 40px;

        color: #198754;

        display: block;

        margin-bottom: 10px;

    }


    .empty-state p {

        margin: 0;

        font-size: 14px;

    }


    /* =========================
       SYSTEM INFO
    ========================= */

    .system-info {

        padding: 10px 22px 20px;

    }


    .info-item {

        display: flex;

        align-items: center;

        gap: 15px;

        padding: 15px 0;

        border-bottom: 1px solid #f1f2f4;

    }


    .info-item:last-child {

        border-bottom: none;

    }


    .info-icon {

        width: 42px;

        height: 42px;

        border-radius: 12px;

        background: #f1f5ff;

        color: #0d6efd;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 18px;

    }


    .info-item small {

        display: block;

        color: #8a939d;

        font-size: 12px;

        margin-bottom: 2px;

    }


    .info-item strong {

        display: block;

        font-size: 14px;

        color: #212529;

    }


    /* =========================
       RESPONSIVE
    ========================= */

    @media (max-width: 768px) {

        .dashboard-card {

            padding: 18px;

        }

        .card-value {

            font-size: 20px;

        }

    }

</style>


<?= $this->include('layout/footer') ?>