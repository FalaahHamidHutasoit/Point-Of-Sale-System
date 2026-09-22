<?php

namespace App\Models;

use CodeIgniter\Model;

class PembelianModel extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';
    protected $allowedFields = [
        'no_pembelian', 'tanggal', 'tanggal_target', 'id_supplier', 'id_user', 'total', 'catatan',
        'status', 'ordered_at', 'cancelled_at', 'created_at'
    ];
    protected $useTimestamps = false;
}
