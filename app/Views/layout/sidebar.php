<?php
$role = (string) session()->get('role');
$nama = (string) session()->get('nama_lengkap');
$path = trim(service('uri')->getPath(), '/');
$active = static function (string $prefix) use ($path): string {
    return ($path === $prefix || str_starts_with($path, $prefix . '/')) ? 'active' : '';
};
?>
<div class="mobile-topbar d-none position-fixed top-0 start-0 end-0 bg-white border-bottom px-3 py-3 align-items-center justify-content-between" style="z-index:1100">
    <strong><i class="bi bi-shop me-2 text-primary"></i>KAMELA</strong>
    <button class="btn btn-light" type="button" onclick="document.querySelector('.sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
</div>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-mark"><i class="bi bi-shop-window"></i></div>
        <div><strong>KAMELA</strong><small>Retail Management</small></div>
    </div>

    <nav class="sidebar-menu">
        <div class="nav-label">Overview</div>
        <a class="sidebar-link <?= $active('dashboard') ?>" href="<?= base_url('dashboard') ?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>

        <div class="nav-label">Transaksi</div>
        <a class="sidebar-link <?= $active('penjualan') && $path === 'penjualan' ? 'active' : '' ?>" href="<?= base_url('penjualan') ?>"><i class="bi bi-cart-check-fill"></i><span>Penjualan</span></a>
        <a class="sidebar-link <?= str_starts_with($path, 'penjualan/riwayat') || str_starts_with($path, 'penjualan/sukses') ? 'active' : '' ?>" href="<?= base_url('penjualan/riwayat') ?>"><i class="bi bi-receipt"></i><span>Riwayat Penjualan</span></a>

        <?php if ($role === 'admin'): ?>
            <div class="nav-label">Persediaan</div>
            <a class="sidebar-link <?= $active('pembelian') ?>" href="<?= base_url('pembelian') ?>"><i class="bi bi-bag-plus-fill"></i><span>Pembelian / Restock</span></a>
            <a class="sidebar-link <?= $path === 'stok/mutasi' ? 'active' : '' ?>" href="<?= base_url('stok/mutasi') ?>"><i class="bi bi-arrow-left-right"></i><span>Mutasi Stok</span></a>
            <a class="sidebar-link <?= $active('stok/opname') ?>" href="<?= base_url('stok/opname') ?>"><i class="bi bi-clipboard-check"></i><span>Stock Opname</span></a>

            <div class="nav-label">Master Data</div>
            <a class="sidebar-link <?= $active('barang') ?>" href="<?= base_url('barang') ?>"><i class="bi bi-box-seam-fill"></i><span>Barang</span></a>
            <a class="sidebar-link <?= $active('kategori') ?>" href="<?= base_url('kategori') ?>"><i class="bi bi-tags-fill"></i><span>Kategori</span></a>
            <a class="sidebar-link <?= $active('supplier') ?>" href="<?= base_url('supplier') ?>"><i class="bi bi-truck"></i><span>Supplier</span></a>
            <a class="sidebar-link <?= $active('customer') ?>" href="<?= base_url('customer') ?>"><i class="bi bi-people-fill"></i><span>Customer</span></a>

            <div class="nav-label">Analitik</div>
            <a class="sidebar-link <?= $active('laporan/penjualan') ?>" href="<?= base_url('laporan/penjualan') ?>"><i class="bi bi-graph-up-arrow"></i><span>Laporan Penjualan</span></a>
            <a class="sidebar-link <?= $active('laporan/barang') ?>" href="<?= base_url('laporan/barang') ?>"><i class="bi bi-clipboard-data-fill"></i><span>Laporan Persediaan</span></a>

            <div class="nav-label">Kontrol Sistem</div>
            <a class="sidebar-link <?= $active('audit') ?>" href="<?= base_url('audit') ?>"><i class="bi bi-shield-check"></i><span>Audit Aktivitas</span></a>
            <a class="sidebar-link <?= $active('system/backup') ?>" href="<?= base_url('system/backup') ?>"><i class="bi bi-database-lock"></i><span>Backup & Recovery</span></a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <div class="avatar"><?= esc(strtoupper(substr($nama ?: 'U', 0, 1))) ?></div>
        <div class="user-copy"><strong><?= esc($nama ?: 'Pengguna') ?></strong><small><?= esc(ucfirst($role)) ?></small></div>
        <form action="<?= base_url('logout') ?>" method="post" class="logout-form m-0">
            <?= csrf_field() ?>
            <button type="submit" class="logout-icon" title="Logout" aria-label="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>
</aside>
<style>
.sidebar{position:fixed;left:0;top:0;width:268px;height:100vh;background:#111827;color:#fff;display:flex;flex-direction:column;z-index:1050;transition:.25s ease;box-shadow:12px 0 32px rgba(15,23,42,.08)}
.sidebar-brand{height:78px;padding:0 20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid rgba(255,255,255,.08)}
.brand-mark{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:grid;place-items:center;font-size:20px;box-shadow:0 8px 20px rgba(37,99,235,.28)}
.sidebar-brand strong{display:block;font-size:17px;letter-spacing:-.02em}.sidebar-brand small{display:block;color:#94a3b8;font-size:10px;margin-top:2px}
.sidebar-menu{flex:1;overflow-y:auto;padding:18px 12px}.nav-label{padding:14px 11px 7px;color:#64748b;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.12em}
.sidebar-link{display:flex;align-items:center;gap:11px;padding:10px 12px;margin:2px 0;border-radius:10px;color:#cbd5e1;text-decoration:none;font-size:13px;font-weight:600;transition:.18s}
.sidebar-link i{width:20px;font-size:16px}.sidebar-link:hover{background:#1f2937;color:#fff}.sidebar-link.active{background:#2563eb;color:#fff;box-shadow:0 6px 16px rgba(37,99,235,.22)}
.sidebar-user{display:flex;align-items:center;gap:10px;padding:16px;border-top:1px solid rgba(255,255,255,.08);background:#0f172a}.avatar{width:38px;height:38px;border-radius:11px;background:#263449;display:grid;place-items:center;font-weight:800}.user-copy{min-width:0;flex:1}.user-copy strong,.user-copy small{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.user-copy strong{font-size:12px}.user-copy small{font-size:10px;color:#94a3b8;margin-top:2px}.logout-form{display:flex}.logout-icon{appearance:none;background:transparent;border:0;cursor:pointer;color:#94a3b8;font-size:18px;padding:7px}.logout-icon:hover{color:#fff}
</style>
