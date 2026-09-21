<?php

namespace App\Models;

use CodeIgniter\Model;

class StockOpnameDetailModel extends Model
{
    protected $table = 'stock_opname_detail';
    protected $primaryKey = 'id_detail_opname';
    protected $allowedFields = [
        'id_opname', 'id_barang', 'stok_sistem', 'stok_fisik', 'selisih'
    ];
    protected $useTimestamps = false;
}
