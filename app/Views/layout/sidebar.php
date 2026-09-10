<div class="sidebar">

    <!-- LOGO -->
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

        <div class="menu-title">
            MENU UTAMA
        </div>

        <a href="<?= base_url('/dashboard') ?>"
           class="menu-item">

            <i class="bi bi-grid-1x2-fill"></i>

            <span>Dashboard</span>

        </a>


        <div class="menu-title">
            MASTER DATA
        </div>


        <a href="<?= base_url('/kategori') ?>"
           class="menu-item">

            <i class="bi bi-tags-fill"></i>

            <span>Kategori</span>

        </a>


        <a href="<?= base_url('/barang') ?>"
           class="menu-item">

            <i class="bi bi-box-seam-fill"></i>

            <span>Barang</span>

        </a>


        <a href="<?= base_url('/customer') ?>"
           class="menu-item">

            <i class="bi bi-people-fill"></i>

            <span>Customer</span>

        </a>


        <a href="<?= base_url('/supplier') ?>"
           class="menu-item">

            <i class="bi bi-truck"></i>

            <span>Supplier</span>

        </a>


        <div class="menu-title">
            TRANSAKSI
        </div>


        <a href="<?= base_url('/penjualan') ?>"
           class="menu-item">

           
            <i class="bi bi-cart-check-fill"></i>

            <span>Penjualan</span>

        </a>

        <a
            href="<?= base_url('penjualan/riwayat') ?>"
            class="sidebar-link"
        >
            <i class="bi bi-clock-history"></i>
            Riwayat Transaksi
        </a>


        <div class="menu-title">
            LAPORAN
        </div>


        <a href="<?= base_url('/laporan/barang') ?>"
           class="menu-item">

            <i class="bi bi-boxes"></i>

            <span>Laporan Barang</span>

        </a>

        <a
            href="<?= base_url('laporan/penjualan') ?>"
            class="sidebar-link"
        >
            <i class="bi bi-bar-chart-line"></i>
            Laporan Penjualan
        </a>


        <a href="<?= base_url('/laporan/penjualan') ?>"
           class="menu-item">

            <i class="bi bi-file-earmark-bar-graph-fill"></i>

            <span>Laporan Penjualan</span>

        </a>

    </div>


    <!-- USER -->
    <div class="sidebar-user">

        <div class="user-info">

            <div class="user-icon">
                <i class="bi bi-person-fill"></i>
            </div>

            <div>

                <strong>
                    <?= esc(session()->get('nama_lengkap') ?? 'User') ?>
                </strong>

                <small>
                    <?= esc(ucfirst(session()->get('role') ?? '')) ?>
                </small>

            </div>

        </div>


        <a href="<?= base_url('/logout') ?>"
           class="btn btn-danger btn-sm w-100 mt-3">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>

</div>


<style>

.sidebar {

    position: fixed;

    top: 0;
    left: 0;

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

    padding: 25px 20px;

    display: flex;

    align-items: center;

    gap: 12px;

    border-bottom: 1px solid #273244;

}

.brand-icon {

    width: 42px;
    height: 42px;

    background: #2563eb;

    border-radius: 10px;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 20px;

}

.sidebar-brand h5 {

    margin: 0;

    font-weight: 600;

}

.sidebar-brand small {

    color: #9ca3af;

}


/* MENU */

.sidebar-menu {

    padding: 20px 12px;

    flex: 1;

    overflow-y: auto;

}

.menu-title {

    font-size: 11px;

    color: #6b7280;

    font-weight: 600;

    margin:

        18px 10px 8px;

    letter-spacing: .5px;

}


.menu-item {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 11px 13px;

    margin-bottom: 4px;

    color: #d1d5db;

    text-decoration: none;

    border-radius: 8px;

    transition: .2s;

}


.menu-item i {

    font-size: 17px;

}


.menu-item:hover {

    background: #1f2937;

    color: white;

}


.menu-item.active {

    background: #2563eb;

    color: white;

}


/* USER */

.sidebar-user {

    padding: 18px;

    border-top: 1px solid #273244;

}


.user-info {

    display: flex;

    align-items: center;

    gap: 10px;

}


.user-icon {

    width: 38px;

    height: 38px;

    background: #374151;

    border-radius: 50%;

    display: flex;

    align-items: center;
    justify-content: center;

}


.user-info strong {

    display: block;

    font-size: 13px;

}


.user-info small {

    color: #9ca3af;

    font-size: 11px;

}

</style>