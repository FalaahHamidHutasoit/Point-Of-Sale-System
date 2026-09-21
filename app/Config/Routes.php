<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/login/process', 'Auth::login');
$routes->get('/logout', 'Auth::logout');
$routes->get('/setup-user', 'Setup::createUser');

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

        $routes->get('/laporan/penjualan', 'Penjualan::laporan');
        $routes->get('/laporan/barang', 'Barang::laporanBarang');
    });
});
