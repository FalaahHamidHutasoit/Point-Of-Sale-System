<?php
$role = session()->get('role');
$nama = session()->get('nama_lengkap');
?>

<div class="sidebar">

    <!-- BRAND -->
    <div class="sidebar-brand">

        <div class="brand-icon">
            <i class="bi bi-shop"></i>
        </div>

        <div>
            <h5>POS SYSTEM</h5>
            <small>Point of Sale</small>
        </div>

    </div>


    <!-- MENU -->
    <div class="sidebar-menu">

        <!-- DASHBOARD -->
        <div class="menu-title">
            MENU UTAMA
        </div>

        <a href="<?= base_url('dashboard') ?>" class="sidebar-link">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>


        <?php if ($role === 'admin'): ?>

            <!-- MASTER DATA -->
            <div class="menu-title mt-4">
                MASTER DATA
            </div>

            <a href="<?= base_url('kategori') ?>" class="sidebar-link">
                <i class="bi bi-tags"></i>
                Kategori
            </a>

            <a href="<?= base_url('barang') ?>" class="sidebar-link">
                <i class="bi bi-box-seam"></i>
                Barang
            </a>

            <a href="<?= base_url('customer') ?>" class="sidebar-link">
                <i class="bi bi-people"></i>
                Customer
            </a>

            <a href="<?= base_url('supplier') ?>" class="sidebar-link">
                <i class="bi bi-truck"></i>
                Supplier
            </a>


            <!-- TRANSAKSI -->
            <div class="menu-title mt-4">
                MASTER DATA
            </div>

            <a href="<?= base_url('penjualan/riwayat') ?>" class="sidebar-link">
            <i class="bi bi-clock-history"></i>
            Riwayat Transaksi
            </a>


        <?php endif; ?>

         <?php if ($role === 'kasir'): ?>
        <!-- TRANSAKSI -->
        <div class="menu-title mt-4">
            TRANSAKSI
        </div>

        <a href="<?= base_url('penjualan') ?>" class="sidebar-link">
            <i class="bi bi-cart3"></i>
            Penjualan
        </a>

        <a href="<?= base_url('penjualan/riwayat') ?>" class="sidebar-link">
            <i class="bi bi-clock-history"></i>
            Riwayat Transaksi
        </a>
            <?php endif; ?>

        <?php if ($role === 'admin'): ?>

            <!-- LAPORAN -->
            <div class="menu-title mt-4">
                LAPORAN
            </div>

            <a href="<?= base_url('laporan/barang') ?>" class="sidebar-link">
                <i class="bi bi-boxes"></i>
                Laporan Barang
            </a>

            <a href="<?= base_url('laporan/penjualan') ?>" class="sidebar-link">
                <i class="bi bi-bar-chart-line"></i>
                Laporan Penjualan
            </a>

        <?php endif; ?>

    </div>


    <!-- USER -->
    <div class="sidebar-user">

        <div class="user-info">

            <div class="user-icon">
                <i class="bi bi-person-fill"></i>
            </div>

            <div>

                <strong>
                    <?= esc($nama) ?>
                </strong>

                <small>
                    <?= esc(ucfirst($role)) ?>
                </small>

            </div>

        </div>


        <a href="<?= base_url('logout') ?>" class="logout-btn">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>

</div>
<style>

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 260px;
    height: 100vh;

    background: #111827;

    color: white;

    display: flex;

    flex-direction: column;

    z-index: 1000;

}


/* BRAND */

.sidebar-brand {

    height: 80px;

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 0 22px;

    border-bottom: 1px solid rgba(255,255,255,.08);

}

.brand-icon {

    width: 40px;
    height: 40px;

    border-radius: 10px;

    background: #2563eb;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 20px;

}

.sidebar-brand h5 {

    margin: 0;

    font-weight: 700;

}

.sidebar-brand small {

    color: #9ca3af;

}


/* MENU */

.sidebar-menu {

    flex: 1;

    overflow-y: auto;

    padding: 20px 14px;

}

.menu-title {

    font-size: 12px;

    font-weight: 700;

    color: #6b7280;

    letter-spacing: 1px;

    padding: 0 12px;

    margin-bottom: 8px;

}


.sidebar-link {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 11px 13px;

    margin-bottom: 4px;

    border-radius: 8px;

    color: #d1d5db;

    text-decoration: none;

    transition: .2s;

}


.sidebar-link i {

    font-size: 18px;

}


.sidebar-link:hover {

    background: #1f2937;

    color: white;

}


.sidebar-link.active {

    background: #2563eb;

    color: white;

}


/* USER */

.sidebar-user {

    padding: 18px;

    border-top: 1px solid rgba(255,255,255,.08);

    background: #111827;

}


.user-info {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-bottom: 15px;

}


.user-icon {

    width: 46px;
    height: 46px;

    border-radius: 50%;

    background: #374151;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 20px;

}


.user-info strong {

    display: block;

    color: white;

}


.user-info small {

    display: block;

    color: #9ca3af;

    margin-top: 3px;

}


.logout-btn {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    width: 100%;

    padding: 10px;

    border-radius: 6px;

    background: #e3344f;

    color: white;

    text-decoration: none;

    font-weight: 500;

}


.logout-btn:hover {

    background: #c8233d;

    color: white;

}

</style>