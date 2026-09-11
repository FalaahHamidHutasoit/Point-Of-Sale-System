<?php

namespace App\Controllers;

use App\Models\UserModel;

class Setup extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();

        // Cek apakah admin sudah ada
        $admin = $userModel
            ->where('username', 'admin')
            ->first();

        if (!$admin) {
            $userModel->insert([
                'username'      => 'admin',
                'password'      => password_hash('admin123', PASSWORD_DEFAULT),
                'nama_lengkap'  => 'Administrator',
                'role'          => 'admin'
            ]);
        }

        // Cek apakah kasir sudah ada
        $kasir = $userModel
            ->where('username', 'kasir')
            ->first();

        if (!$kasir) {
            $userModel->insert([
                'username'      => 'kasir',
                'password'      => password_hash('kasir123', PASSWORD_DEFAULT),
                'nama_lengkap'  => 'Kasir',
                'role'          => 'kasir'
            ]);
        }

        return "
            <h3>Setup berhasil!</h3>
            <p>Admin: <b>admin</b> / <b>admin123</b></p>
            <p>Kasir: <b>kasir</b> / <b>kasir123</b></p>
            <p><a href='" . base_url('login') . "'>Ke halaman login</a></p>
        ";
    }
}