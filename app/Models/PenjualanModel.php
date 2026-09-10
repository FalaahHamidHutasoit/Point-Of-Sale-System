<?php

namespace App\Models;

use CodeIgniter\Model;

class PenjualanModel extends Model
{
    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';

    protected $allowedFields = [
        'no_transaksi',
        'tanggal',
        'id_customer',
        'id_user',
        'total',
        'bayar',
        'kembalian'
    ];

    protected $useTimestamps = false;
}