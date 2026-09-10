<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customer';
    protected $primaryKey = 'id_customer';

    protected $allowedFields = [
        'nama_customer',
        'no_telp',
        'alamat'
    ];

    // Matikan otomatis timestamp karena tabel customer
    // hanya memiliki created_at
    protected $useTimestamps = false;
}