<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/login/process', 'Auth::login');
$routes->post('/logout', 'Auth::logout');

$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/dashboard', 'Dashboard::index');

    // Penjualan dapat dilakukan/dilihat oleh admin maupun kasir.
    $routes->group('', ['filter' => 'role:admin,kasir'], function ($routes) {
        $routes->get('/penjualan', 'Penjualan::index');
        $routes->post('/penjualan/simpan', 'Penjualan::simpan');
        $routes->get('/penjualan/riwayat', 'Penjualan::riwayat');
        $routes->get('/penjualan/sukses/(:num)', 'Penjualan::sukses/$1');
    });

    $routes->group('', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/kategori', 'Kategori::index');
        $routes->get('/kategori/tambah', 'Kategori::tambah');
        $routes->post('/kategori/simpan', 'Kategori::simpan');
        $routes->get('/kategori/edit/(:num)', 'Kategori::edit/$1');
        $routes->post('/kategori/update/(:num)', 'Kategori::update/$1');
        $routes->post('/kategori/hapus/(:num)', 'Kategori::hapus/$1');

        $routes->get('/barang', 'Barang::index');
        $routes->get('/barang/tambah', 'Barang::tambah');
        $routes->post('/barang/simpan', 'Barang::simpan');
        $routes->get('/barang/edit/(:num)', 'Barang::edit/$1');
        $routes->post('/barang/update/(:num)', 'Barang::update/$1');
        $routes->post('/barang/hapus/(:num)', 'Barang::hapus/$1');

        $routes->get('/customer', 'Customer::index');
        $routes->get('/customer/tambah', 'Customer::tambah');
        $routes->post('/customer/simpan', 'Customer::simpan');
        $routes->get('/customer/edit/(:num)', 'Customer::edit/$1');
        $routes->post('/customer/update/(:num)', 'Customer::update/$1');
        $routes->post('/customer/hapus/(:num)', 'Customer::hapus/$1');

        $routes->get('/supplier', 'Supplier::index');
        $routes->get('/supplier/tambah', 'Supplier::tambah');
        $routes->post('/supplier/simpan', 'Supplier::simpan');
        $routes->get('/supplier/edit/(:num)', 'Supplier::edit/$1');
        $routes->post('/supplier/update/(:num)', 'Supplier::update/$1');
        $routes->post('/supplier/hapus/(:num)', 'Supplier::hapus/$1');

        $routes->get('/pembelian', 'Pembelian::index');
        $routes->get('/pembelian/tambah', 'Pembelian::tambah');
        $routes->post('/pembelian/simpan', 'Pembelian::simpan');
        $routes->get('/pembelian/(:num)', 'Pembelian::detail/$1');

        $routes->get('/stok/mutasi', 'Stok::index');
        $routes->get('/stok/opname', 'StockOpname::index');
        $routes->post('/stok/opname/create', 'StockOpname::create');
        $routes->get('/stok/opname/(:num)', 'StockOpname::detail/$1');
        $routes->post('/stok/opname/(:num)/save', 'StockOpname::saveCounts/$1');
        $routes->post('/stok/opname/(:num)/finalize', 'StockOpname::finalize/$1');
        $routes->post('/stok/opname/(:num)/cancel', 'StockOpname::cancel/$1');

        $routes->get('/laporan/penjualan', 'Penjualan::laporan');
        $routes->get('/laporan/barang', 'Barang::laporanBarang');

        // Phase 3: audit trail hanya dapat dibaca oleh Admin.
        $routes->get('/audit', 'AuditLog::index');

        // Phase 4: backup/recovery hanya Admin. Semua mutasi memakai POST + CSRF.
        $routes->get('/system/backup', 'SystemBackup::index');
        $routes->post('/system/backup/create', 'SystemBackup::create');
        $routes->get('/system/backup/download/(:segment)', 'SystemBackup::download/$1');
        $routes->post('/system/backup/restore/(:segment)', 'SystemBackup::restore/$1');
        $routes->post('/system/backup/delete/(:segment)', 'SystemBackup::delete/$1');
    });
});
