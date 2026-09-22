<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/login/process', 'Auth::login');
$routes->post('/logout', 'Auth::logout');

// Public payment simulation endpoints. GET renders the external actor page;
// POST performs the state transition with CSRF + server-side validation.
$routes->get('/payment/demo/(:segment)', 'Penjualan::demoPayment/$1');
$routes->post('/payment/demo/(:segment)/confirm', 'Penjualan::confirmDemoPayment/$1');
$routes->get('/demo-bank/pay/(:segment)', 'Penjualan::demoBankTransfer/$1');
$routes->post('/demo-bank/pay/(:segment)/confirm', 'Penjualan::confirmDemoTransfer/$1');

$routes->group('', ['filter' => 'auth'], function ($routes) {
    // Semua pegawai yang login mendapat dashboard, tetapi isi dashboard menyesuaikan role.
    $routes->get('/dashboard', 'Dashboard::index');

    // ---------------------------------------------------------------------
    // KASIR + ADMIN: operasi penjualan dan payment initiation.
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'role:admin,kasir'], function ($routes) {
        $routes->get('/penjualan', 'Penjualan::index');
        $routes->post('/penjualan/simpan', 'Penjualan::simpan');
        $routes->post('/penjualan/qris-demo/prepare', 'Penjualan::prepareDemoQris');
        $routes->get('/penjualan/qris-demo/status/(:segment)', 'Penjualan::demoQrisStatus/$1');
        $routes->post('/penjualan/transfer-demo/prepare', 'Penjualan::prepareDemoTransfer');
        $routes->get('/penjualan/transfer-demo/status/(:segment)', 'Penjualan::demoTransferStatus/$1');
        $routes->post('/penjualan/payment-demo/cancel/(:segment)', 'Penjualan::cancelDemoPayment/$1');
    });

    // Riwayat penjualan adalah data operasional Kasir dan bahan monitoring Manager.
    $routes->group('', ['filter' => 'role:admin,kasir,manager'], function ($routes) {
        $routes->get('/penjualan/riwayat', 'Penjualan::riwayat');
        $routes->get('/penjualan/sukses/(:num)', 'Penjualan::sukses/$1');
    });

    // ---------------------------------------------------------------------
    // CUSTOMER: Kasir dapat mendaftarkan/memperbarui customer saat transaksi.
    // Delete tetap Admin untuk mengurangi risiko menghilangkan master data.
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'role:admin,kasir'], function ($routes) {
        $routes->get('/customer', 'Customer::index');
        $routes->get('/customer/tambah', 'Customer::tambah');
        $routes->post('/customer/simpan', 'Customer::simpan');
        $routes->get('/customer/edit/(:num)', 'Customer::edit/$1');
        $routes->post('/customer/update/(:num)', 'Customer::update/$1');
    });
    $routes->post('/customer/hapus/(:num)', 'Customer::hapus/$1', ['filter' => 'role:admin']);

    // ---------------------------------------------------------------------
    // GUDANG + ADMIN: master persediaan dan supplier.
    // Manager hanya diberi read-only ke daftar barang untuk monitoring.
    // ---------------------------------------------------------------------
    $routes->get('/barang', 'Barang::index', ['filter' => 'role:admin,gudang,purchasing,manager']);
    $routes->group('', ['filter' => 'role:admin,gudang'], function ($routes) {
        $routes->get('/barang/tambah', 'Barang::tambah');
        $routes->post('/barang/simpan', 'Barang::simpan');
        $routes->get('/barang/edit/(:num)', 'Barang::edit/$1');
        $routes->post('/barang/update/(:num)', 'Barang::update/$1');

        $routes->get('/kategori', 'Kategori::index');
        $routes->get('/kategori/tambah', 'Kategori::tambah');
        $routes->post('/kategori/simpan', 'Kategori::simpan');
        $routes->get('/kategori/edit/(:num)', 'Kategori::edit/$1');
        $routes->post('/kategori/update/(:num)', 'Kategori::update/$1');

    });
    $routes->post('/barang/hapus/(:num)', 'Barang::hapus/$1', ['filter' => 'role:admin']);
    $routes->post('/kategori/hapus/(:num)', 'Kategori::hapus/$1', ['filter' => 'role:admin']);

    // Supplier dikelola Purchasing/Admin; Gudang boleh melihat data supplier untuk pencocokan dokumen.
    $routes->get('/supplier', 'Supplier::index', ['filter' => 'role:admin,purchasing,gudang']);
    $routes->group('', ['filter' => 'role:admin,purchasing'], function ($routes) {
        $routes->get('/supplier/tambah', 'Supplier::tambah');
        $routes->post('/supplier/simpan', 'Supplier::simpan');
        $routes->get('/supplier/edit/(:num)', 'Supplier::edit/$1');
        $routes->post('/supplier/update/(:num)', 'Supplier::update/$1');
    });
    $routes->post('/supplier/hapus/(:num)', 'Supplier::hapus/$1', ['filter' => 'role:admin']);

    // PROCUREMENT: Purchasing membuat PO; Gudang hanya menerima barang secara fisik.
    // Manager mendapat akses read-only untuk monitoring.
    $routes->group('', ['filter' => 'role:admin,purchasing,gudang,manager'], function ($routes) {
        $routes->get('/pembelian', 'Pembelian::index');
        $routes->get('/pembelian/(:num)', 'Pembelian::detail/$1');
        $routes->get('/penerimaan', 'PenerimaanBarang::index');
        $routes->get('/penerimaan/(:num)', 'PenerimaanBarang::detail/$1');
    });
    $routes->group('', ['filter' => 'role:admin,purchasing'], function ($routes) {
        $routes->get('/pembelian/tambah', 'Pembelian::tambah');
        $routes->post('/pembelian/simpan', 'Pembelian::simpan');
        $routes->post('/pembelian/(:num)/kirim', 'Pembelian::kirim/$1');
        $routes->post('/pembelian/(:num)/batalkan', 'Pembelian::batalkan/$1');
    });
    $routes->group('', ['filter' => 'role:admin,gudang'], function ($routes) {
        $routes->get('/penerimaan/tambah/(:num)', 'PenerimaanBarang::tambah/$1');
        $routes->post('/penerimaan/simpan/(:num)', 'PenerimaanBarang::simpan/$1');
    });

    // Mutasi stok dapat dimonitor Manager, tetapi stock opname hanya dilakukan Gudang/Admin.
    $routes->get('/stok/mutasi', 'Stok::index', ['filter' => 'role:admin,gudang,manager']);
    $routes->group('', ['filter' => 'role:admin,gudang'], function ($routes) {
        $routes->get('/stok/opname', 'StockOpname::index');
        $routes->post('/stok/opname/create', 'StockOpname::create');
        $routes->get('/stok/opname/(:num)', 'StockOpname::detail/$1');
        $routes->post('/stok/opname/(:num)/save', 'StockOpname::saveCounts/$1');
        $routes->post('/stok/opname/(:num)/finalize', 'StockOpname::finalize/$1');
        $routes->post('/stok/opname/(:num)/cancel', 'StockOpname::cancel/$1');
    });

    // ---------------------------------------------------------------------
    // MANAGERIAL INFORMATION: read-only untuk Manager, Admin tetap dapat akses.
    // Gudang diberi laporan persediaan untuk operasional restock.
    // ---------------------------------------------------------------------
    $routes->get('/management', 'Management::index', ['filter' => 'role:admin,manager']);
    $routes->get('/laporan/penjualan', 'Penjualan::laporan', ['filter' => 'role:admin,manager']);
    $routes->get('/laporan/barang', 'Barang::laporanBarang', ['filter' => 'role:admin,gudang,purchasing,manager']);
    $routes->get('/audit', 'AuditLog::index', ['filter' => 'role:admin,manager']);

    // Setiap pegawai dapat mengganti password miliknya sendiri.
    $routes->get('/account/password', 'UserManagement::changePasswordForm');
    $routes->post('/account/password', 'UserManagement::changePassword');

    // Kontrol sistem tetap eksklusif Admin.
    $routes->group('', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/users', 'UserManagement::index');
        $routes->get('/users/tambah', 'UserManagement::tambah');
        $routes->post('/users/simpan', 'UserManagement::simpan');
        $routes->get('/users/edit/(:num)', 'UserManagement::edit/$1');
        $routes->post('/users/update/(:num)', 'UserManagement::update/$1');
        $routes->post('/users/toggle-status/(:num)', 'UserManagement::toggleStatus/$1');
        $routes->get('/users/reset-password/(:num)', 'UserManagement::resetPasswordForm/$1');
        $routes->post('/users/reset-password/(:num)', 'UserManagement::resetPassword/$1');

        $routes->get('/system/backup', 'SystemBackup::index');
        $routes->post('/system/backup/create', 'SystemBackup::create');
        $routes->get('/system/backup/download/(:segment)', 'SystemBackup::download/$1');
        $routes->post('/system/backup/restore/(:segment)', 'SystemBackup::restore/$1');
        $routes->post('/system/backup/delete/(:segment)', 'SystemBackup::delete/$1');
    });
});
