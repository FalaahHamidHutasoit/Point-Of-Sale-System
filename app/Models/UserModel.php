<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $allowedFields = [
        'kode_pegawai',
        'username',
        'password',
        'nama_lengkap',
        'email',
        'no_telp',
        'tanggal_masuk',
        'role',
        'is_active',
        'must_change_password',
        'password_changed_at',
        'last_login_at',
    ];

    protected $useTimestamps = false;
}
