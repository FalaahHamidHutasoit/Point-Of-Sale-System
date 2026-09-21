<?php

namespace App\Models;

use CodeIgniter\Model;

class StockOpnameModel extends Model
{
    protected $table = 'stock_opname';
    protected $primaryKey = 'id_opname';
    protected $allowedFields = [
        'no_opname', 'tanggal', 'id_user', 'status', 'catatan', 'active_guard',
        'finalized_by', 'finalized_at', 'cancelled_by', 'cancelled_at', 'created_at'
    ];
    protected $useTimestamps = false;
}
