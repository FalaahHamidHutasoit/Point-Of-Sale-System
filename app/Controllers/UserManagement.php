<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserManagement extends BaseController
{
    private const ROLES = ['admin', 'kasir', 'gudang', 'purchasing', 'manager'];

    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));
        $role = trim((string) $this->request->getGet('role'));
        $status = trim((string) $this->request->getGet('status'));

        $model = new UserModel();

        if ($q !== '') {
            $model->groupStart()
                ->like('kode_pegawai', $q)
                ->orLike('username', $q)
                ->orLike('nama_lengkap', $q)
                ->orLike('email', $q)
                ->orLike('no_telp', $q)
                ->groupEnd();
        }

        if (in_array($role, self::ROLES, true)) {
            $model->where('role', $role);
        }

        if ($status === 'active') {
            $model->where('is_active', 1);
        } elseif ($status === 'inactive') {
            $model->where('is_active', 0);
        }

        $users = $model
            ->orderBy('is_active', 'DESC')
            ->orderBy('nama_lengkap', 'ASC')
            ->paginate(20, 'users');

        $stats = [
            'total' => (new UserModel())->countAllResults(),
            'active' => (new UserModel())->where('is_active', 1)->countAllResults(),
            'inactive' => (new UserModel())->where('is_active', 0)->countAllResults(),
            'admins' => (new UserModel())->where('role', 'admin')->where('is_active', 1)->countAllResults(),
        ];

        return view('users/index', [
            'title' => 'Pegawai & Akun',
            'users' => $users,
            'pager' => $model->pager,
            'stats' => $stats,
            'filters' => compact('q', 'role', 'status'),
            'roleLabels' => $this->roleLabels(),
        ]);
    }

    public function tambah()
    {
        return view('users/form', [
            'title' => 'Tambah Pegawai',
            'mode' => 'create',
            'user' => null,
            'roleLabels' => $this->roleLabels(),
        ]);
    }

    public function simpan()
    {
        if ($response = $this->guardSensitivePost(['admin'])) {
            return $response;
        }

        $input = $this->normalizeInput();
        $password = (string) $this->request->getPost('password');
        $passwordConfirmation = (string) $this->request->getPost('password_confirmation');

        $errors = $this->validateEmployeeInput($input, null);
        if (strlen($password) < 8) {
            $errors[] = 'Password sementara minimal 8 karakter.';
        }
        if (!hash_equals($password, $passwordConfirmation)) {
            $errors[] = 'Konfirmasi password tidak sama.';
        }

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        if ($input['kode_pegawai'] === '') {
            $input['kode_pegawai'] = $this->generateEmployeeCode($input['role']);
        }

        $data = array_merge($input, [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'is_active' => 1,
            'must_change_password' => 1,
            'password_changed_at' => null,
        ]);

        try {
            $id = $this->userModel->insert($data, true);
            if (!$id) {
                throw new \RuntimeException('Insert user gagal.');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Tambah user gagal: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Pegawai gagal dibuat. Pastikan username, kode pegawai, dan email belum digunakan.');
        }

        $this->auditEvent(
            'USER_CREATED',
            'USER',
            (int) $id,
            $input['username'],
            'Admin membuat akun pegawai baru.',
            null,
            $this->safeSnapshot(array_merge($input, ['is_active' => 1, 'must_change_password' => 1])),
            'SUCCESS'
        );

        return redirect()->to('/users')->with(
            'success',
            'Pegawai berhasil dibuat. Password yang diberikan bersifat sementara dan wajib diganti saat login pertama.'
        );
    }

    public function edit(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Pegawai tidak ditemukan.');
        }

        return view('users/form', [
            'title' => 'Edit Pegawai',
            'mode' => 'edit',
            'user' => $user,
            'roleLabels' => $this->roleLabels(),
        ]);
    }

    public function update(int $id)
    {
        if ($response = $this->guardSensitivePost(['admin'])) {
            return $response;
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Pegawai tidak ditemukan.');
        }

        $input = $this->normalizeInput();
        $errors = $this->validateEmployeeInput($input, $id);
        $sessionUserId = (int) session()->get('id_user');

        if ($sessionUserId === $id && $input['role'] !== 'admin') {
            $errors[] = 'Administrator yang sedang login tidak dapat mengubah role dirinya sendiri.';
        }

        if ($user['role'] === 'admin' && (int) ($user['is_active'] ?? 1) === 1 && $input['role'] !== 'admin' && $this->activeAdminCount() <= 1) {
            $errors[] = 'Role administrator terakhir tidak dapat diubah.';
        }

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        if ($input['kode_pegawai'] === '') {
            $input['kode_pegawai'] = (string) ($user['kode_pegawai'] ?: $this->generateEmployeeCode($input['role']));
        }

        $before = $this->safeSnapshot($user);

        try {
            $this->userModel->update($id, $input);
        } catch (\Throwable $e) {
            log_message('error', 'Update user gagal: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Data pegawai gagal diperbarui. Pastikan data unik belum dipakai akun lain.');
        }

        $after = $this->userModel->find($id) ?: array_merge($user, $input);
        $this->auditEvent(
            'USER_UPDATED',
            'USER',
            $id,
            (string) $after['username'],
            'Admin memperbarui profil/role pegawai.',
            $before,
            $this->safeSnapshot($after),
            'SUCCESS'
        );

        return redirect()->to('/users')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function toggleStatus(int $id)
    {
        if ($response = $this->guardSensitivePost(['admin'])) {
            return $response;
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Pegawai tidak ditemukan.');
        }

        $currentlyActive = (int) ($user['is_active'] ?? 1) === 1;
        $newStatus = $currentlyActive ? 0 : 1;

        if ((int) session()->get('id_user') === $id && $newStatus === 0) {
            $this->auditEvent('USER_STATUS_BLOCKED', 'USER', $id, (string) $user['username'], 'Admin mencoba menonaktifkan akun sendiri.', null, null, 'BLOCKED');
            return redirect()->to('/users')->with('error', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        if ($user['role'] === 'admin' && $newStatus === 0 && $this->activeAdminCount() <= 1) {
            $this->auditEvent('USER_STATUS_BLOCKED', 'USER', $id, (string) $user['username'], 'Penonaktifan administrator terakhir diblokir.', null, null, 'BLOCKED');
            return redirect()->to('/users')->with('error', 'Administrator aktif terakhir tidak dapat dinonaktifkan.');
        }

        $this->userModel->update($id, ['is_active' => $newStatus]);

        $this->auditEvent(
            'USER_STATUS_CHANGED',
            'USER',
            $id,
            (string) $user['username'],
            $newStatus === 1 ? 'Akun pegawai diaktifkan.' : 'Akun pegawai dinonaktifkan.',
            ['is_active' => (int) $user['is_active']],
            ['is_active' => $newStatus],
            'SUCCESS'
        );

        return redirect()->to('/users')->with('success', $newStatus === 1 ? 'Akun berhasil diaktifkan.' : 'Akun berhasil dinonaktifkan.');
    }

    public function resetPasswordForm(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Pegawai tidak ditemukan.');
        }

        return view('users/reset_password', [
            'title' => 'Reset Password',
            'user' => $user,
        ]);
    }

    public function resetPassword(int $id)
    {
        if ($response = $this->guardSensitivePost(['admin'])) {
            return $response;
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Pegawai tidak ditemukan.');
        }

        $password = (string) $this->request->getPost('password');
        $confirmation = (string) $this->request->getPost('password_confirmation');

        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password sementara minimal 8 karakter.');
        }
        if (!hash_equals($password, $confirmation)) {
            return redirect()->back()->with('error', 'Konfirmasi password tidak sama.');
        }

        $this->userModel->update($id, [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'must_change_password' => 1,
            'password_changed_at' => null,
        ]);

        $this->auditEvent(
            'USER_PASSWORD_RESET',
            'USER',
            $id,
            (string) $user['username'],
            'Admin mereset password pegawai. Pegawai wajib mengganti password saat login berikutnya.',
            ['must_change_password' => (int) ($user['must_change_password'] ?? 0)],
            ['must_change_password' => 1],
            'SUCCESS'
        );

        return redirect()->to('/users')->with('success', 'Password berhasil direset. Pegawai wajib mengganti password saat login berikutnya.');
    }

    public function changePasswordForm()
    {
        $user = $this->userModel->find((int) session()->get('id_user'));
        if (!$user) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'Akun tidak ditemukan.');
        }

        return view('account/change_password', [
            'title' => 'Ganti Password',
            'user' => $user,
            'forced' => (int) ($user['must_change_password'] ?? 0) === 1,
        ]);
    }

    public function changePassword()
    {
        if ($response = $this->guardSensitivePost(self::ROLES)) {
            return $response;
        }

        $id = (int) session()->get('id_user');
        $user = $this->userModel->find($id);
        if (!$user) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'Akun tidak ditemukan.');
        }

        $current = (string) $this->request->getPost('current_password');
        $password = (string) $this->request->getPost('password');
        $confirmation = (string) $this->request->getPost('password_confirmation');

        if (!password_verify($current, (string) $user['password'])) {
            return redirect()->back()->with('error', 'Password saat ini tidak sesuai.');
        }
        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password baru minimal 8 karakter.');
        }
        if (!hash_equals($password, $confirmation)) {
            return redirect()->back()->with('error', 'Konfirmasi password baru tidak sama.');
        }
        if (password_verify($password, (string) $user['password'])) {
            return redirect()->back()->with('error', 'Password baru harus berbeda dari password saat ini.');
        }

        $this->userModel->update($id, [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'must_change_password' => 0,
            'password_changed_at' => date('Y-m-d H:i:s'),
        ]);

        session()->set('must_change_password', false);
        session()->regenerate(true);

        $this->auditEvent(
            'PASSWORD_CHANGED',
            'USER',
            $id,
            (string) $user['username'],
            'Pegawai mengganti password akun sendiri.',
            ['must_change_password' => (int) ($user['must_change_password'] ?? 0)],
            ['must_change_password' => 0, 'password_changed_at' => date('Y-m-d H:i:s')],
            'SUCCESS'
        );

        return redirect()->to('/dashboard')->with('success', 'Password berhasil diperbarui.');
    }

    /** @return array<string,string> */
    private function normalizeInput(): array
    {
        return [
            'kode_pegawai' => strtoupper(trim((string) $this->request->getPost('kode_pegawai'))),
            'username' => strtolower(trim((string) $this->request->getPost('username'))),
            'nama_lengkap' => trim((string) $this->request->getPost('nama_lengkap')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))) ?: null,
            'no_telp' => trim((string) $this->request->getPost('no_telp')) ?: null,
            'tanggal_masuk' => trim((string) $this->request->getPost('tanggal_masuk')) ?: null,
            'role' => trim((string) $this->request->getPost('role')),
        ];
    }

    /** @param array<string,mixed> $input @return list<string> */
    private function validateEmployeeInput(array $input, ?int $ignoreId): array
    {
        $errors = [];

        if (!preg_match('/^[a-z0-9._-]{3,50}$/', (string) $input['username'])) {
            $errors[] = 'Username harus 3-50 karakter dan hanya boleh berisi huruf kecil, angka, titik, garis bawah, atau strip.';
        }
        if (mb_strlen((string) $input['nama_lengkap']) < 3 || mb_strlen((string) $input['nama_lengkap']) > 100) {
            $errors[] = 'Nama lengkap harus 3-100 karakter.';
        }
        if (!in_array($input['role'], self::ROLES, true)) {
            $errors[] = 'Role pegawai tidak valid.';
        }
        if ($input['kode_pegawai'] !== '' && !preg_match('/^[A-Z0-9-]{3,20}$/', (string) $input['kode_pegawai'])) {
            $errors[] = 'Kode pegawai hanya boleh berisi huruf kapital, angka, dan strip.';
        }
        if ($input['email'] !== null && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        }
        if ($input['no_telp'] !== null && !preg_match('/^[0-9+()\s.-]{7,20}$/', (string) $input['no_telp'])) {
            $errors[] = 'Nomor telepon tidak valid.';
        }
        if ($input['tanggal_masuk'] !== null) {
            $date = \DateTime::createFromFormat('Y-m-d', (string) $input['tanggal_masuk']);
            if (!$date || $date->format('Y-m-d') !== $input['tanggal_masuk']) {
                $errors[] = 'Tanggal masuk tidak valid.';
            }
        }

        if ($this->existsUnique('username', (string) $input['username'], $ignoreId)) {
            $errors[] = 'Username sudah digunakan.';
        }
        if ($input['kode_pegawai'] !== '' && $this->existsUnique('kode_pegawai', (string) $input['kode_pegawai'], $ignoreId)) {
            $errors[] = 'Kode pegawai sudah digunakan.';
        }
        if ($input['email'] !== null && $this->existsUnique('email', (string) $input['email'], $ignoreId)) {
            $errors[] = 'Email sudah digunakan.';
        }

        return $errors;
    }

    private function existsUnique(string $field, string $value, ?int $ignoreId): bool
    {
        $model = new UserModel();
        $model->where($field, $value);
        if ($ignoreId !== null) {
            $model->where('id_user !=', $ignoreId);
        }
        return $model->first() !== null;
    }

    private function activeAdminCount(): int
    {
        return (new UserModel())->where('role', 'admin')->where('is_active', 1)->countAllResults();
    }

    private function generateEmployeeCode(string $role): string
    {
        $prefixes = [
            'admin' => 'ADM',
            'kasir' => 'KSR',
            'gudang' => 'GDG',
            'purchasing' => 'PCH',
            'manager' => 'MGR',
        ];
        $prefix = $prefixes[$role] ?? 'PGW';

        $last = (new UserModel())
            ->like('kode_pegawai', $prefix, 'after')
            ->orderBy('kode_pegawai', 'DESC')
            ->first();

        $number = 1;
        if ($last && preg_match('/^(?:[A-Z]+)(\d+)$/', (string) ($last['kode_pegawai'] ?? ''), $m)) {
            $number = ((int) $m[1]) + 1;
        }

        return $prefix . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    /** @return array<string,string> */
    private function roleLabels(): array
    {
        return [
            'admin' => 'Administrator',
            'kasir' => 'Kasir',
            'gudang' => 'Staf Gudang',
            'purchasing' => 'Purchasing',
            'manager' => 'Manager',
        ];
    }

    /** @param array<string,mixed> $data @return array<string,mixed> */
    private function safeSnapshot(array $data): array
    {
        unset($data['password']);
        return $data;
    }
}
