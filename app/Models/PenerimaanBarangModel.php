<?php

namespace App\Models;

use CodeIgniter\Model;

class PenerimaanBarangModel extends Model
{
    protected $table = 'penerimaan_barang';
    protected $primaryKey = 'id_penerimaan';
    protected $allowedFields = [
        'no_penerimaan', 'id_pembelian', 'id_user', 'tanggal', 'no_surat_jalan', 'catatan', 'created_at'
    ];
    protected $useTimestamps = false;
}
