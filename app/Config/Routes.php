<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


/*
|--------------------------------------------------------------------------
| SETUP USER
|--------------------------------------------------------------------------
*/

$routes->get('/setup-user', 'Setup::createUser');


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

$routes->get('/login', 'Auth::index');
$routes->post('/login/process', 'Auth::login');
$routes->get('/logout', 'Auth::logout');


/*
|--------------------------------------------------------------------------
| ROUTE YANG HARUS LOGIN
|--------------------------------------------------------------------------
*/

$routes->group('', ['filter' => 'auth'], function ($routes) {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    |
    | Dashboard bisa diakses Admin dan Kasir
    |
    */

    $routes->get('/dashboard', 'Dashboard::index');


    /*
    |--------------------------------------------------------------------------
    | KHUSUS ADMIN
    |--------------------------------------------------------------------------
    */

    $routes->group('', ['filter' => 'role:admin'], function ($routes) {

        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $routes->get('/kategori', 'Kategori::index');
        $routes->get('/kategori/tambah', 'Kategori::tambah');
        $routes->post('/kategori/simpan', 'Kategori::simpan');
        $routes->get('/kategori/edit/(:num)', 'Kategori::edit/$1');
        $routes->post('/kategori/update/(:num)', 'Kategori::update/$1');
        $routes->get('/kategori/hapus/(:num)', 'Kategori::hapus/$1');


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $routes->get('/barang', 'Barang::index');
        $routes->get('/barang/tambah', 'Barang::tambah');
        $routes->post('/barang/simpan', 'Barang::simpan');
        $routes->get('/barang/edit/(:num)', 'Barang::edit/$1');
        $routes->post('/barang/update/(:num)', 'Barang::update/$1');
        $routes->get('/barang/hapus/(:num)', 'Barang::hapus/$1');


        /*
        |--------------------------------------------------------------------------
        | CUSTOMER / MEMBER
        |--------------------------------------------------------------------------
        */

        $routes->get('/customer', 'Customer::index');
        $routes->get('/customer/tambah', 'Customer::tambah');
        $routes->post('/customer/simpan', 'Customer::simpan');
        $routes->get('/customer/edit/(:num)', 'Customer::edit/$1');
        $routes->post('/customer/update/(:num)', 'Customer::update/$1');
        $routes->get('/customer/hapus/(:num)', 'Customer::hapus/$1');


        /*
        |--------------------------------------------------------------------------
        | SUPPLIER
        |--------------------------------------------------------------------------
        */

        $routes->get('/supplier', 'Supplier::index');
        $routes->get('/supplier/tambah', 'Supplier::tambah');
        $routes->post('/supplier/simpan', 'Supplier::simpan');
        $routes->get('/supplier/edit/(:num)', 'Supplier::edit/$1');
        $routes->post('/supplier/update/(:num)', 'Supplier::update/$1');
        $routes->get('/supplier/hapus/(:num)', 'Supplier::hapus/$1');


        /*
        |--------------------------------------------------------------------------
        | LAPORAN
        |--------------------------------------------------------------------------
        */

        $routes->get('/laporan/penjualan', 'Penjualan::laporan');
        $routes->get('/laporan/barang', 'Barang::laporanBarang');

    });


    /*
    |--------------------------------------------------------------------------
    | KHUSUS KASIR
    |--------------------------------------------------------------------------
    */

    $routes->group('', ['filter' => 'role:kasir'], function ($routes) {

        /*
        |--------------------------------------------------------------------------
        | PENJUALAN
        |--------------------------------------------------------------------------
        */

        $routes->get('/penjualan', 'Penjualan::index');
        $routes->post('/penjualan/simpan', 'Penjualan::simpan');


        /*
        |--------------------------------------------------------------------------
        | RIWAYAT TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $routes->get('/penjualan/riwayat', 'Penjualan::riwayat');


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI BERHASIL / NOTA
        |--------------------------------------------------------------------------
        */

        $routes->get('/penjualan/sukses/(:num)', 'Penjualan::sukses/$1');

    });

});