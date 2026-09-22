<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailPembelianModel extends Model
{
    protected $table = 'detail_pembelian';
    protected $primaryKey = 'id_detail_pembelian';
    protected $allowedFields = [
        'id_pembelian', 'id_barang', 'qty', 'qty_diterima', 'harga_beli', 'subtotal'
    ];
    protected $useTimestamps = false;
}
