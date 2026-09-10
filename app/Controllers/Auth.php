<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return view('auth/login');
    }

    public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->userModel
            ->where('username', $username)
            ->first();

        if (!$user) {
            return redirect()->back()
                ->with('error', 'Username atau password salah.');
        }

        if (!password_verify($password, $user['password'])) {
            return redirect()->back()
                ->with('error', 'Username atau password salah.');
        }

        session()->set([
            'logged_in'   => true,
            'id_user'     => $user['id_user'],
            'username'    => $user['username'],
            'nama_lengkap'=> $user['nama_lengkap'],
            'role'        => $user['role']
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}