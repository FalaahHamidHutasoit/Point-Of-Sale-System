<?php

namespace App\Controllers;

use App\Models\UserModel;

class Setup extends BaseController
{
    public function createUser()
    {
        $model = new UserModel();

        $model->insert([
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'nama_lengkap' => 'Administrator',
            'role' => 'admin'
        ]);

        return 'User admin berhasil dibuat.';
    }
}