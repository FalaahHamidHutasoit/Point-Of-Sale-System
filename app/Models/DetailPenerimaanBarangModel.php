<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailPenerimaanBarangModel extends Model
{
    protected $table = 'detail_penerimaan_barang';
    protected $primaryKey = 'id_detail_penerimaan';
    protected $allowedFields = [
        'id_penerimaan', 'id_detail_pembelian', 'id_barang', 'qty_diterima', 'harga_beli', 'subtotal'
    ];
    protected $useTimestamps = false;
}
