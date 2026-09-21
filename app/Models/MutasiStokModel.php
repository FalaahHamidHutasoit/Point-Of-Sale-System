<?php

namespace App\Models;

use CodeIgniter\Model;

class MutasiStokModel extends Model
{
    protected $table = 'mutasi_stok';
    protected $primaryKey = 'id_mutasi';
    protected $allowedFields = [
        'id_barang', 'id_user', 'tipe', 'qty', 'stok_sebelum', 'stok_sesudah',
        'referensi_tipe', 'referensi_id', 'keterangan', 'created_at'
    ];
    protected $useTimestamps = false;
}
