<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailPenjualanModel extends Model
{
    protected $table = 'detail_penjualan';
    protected $primaryKey = 'id_detail';

    protected $allowedFields = [
        'id_penjualan',
        'id_barang',
        'qty',
        'harga',
        'subtotal'
    ];

    protected $useTimestamps = false;
}