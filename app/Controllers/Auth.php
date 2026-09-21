<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Cache\CacheInterface;
use Config\Services;

class Auth extends BaseController
{
    private const ACCOUNT_MAX_FAILED_ATTEMPTS = 5;
    private const IP_MAX_FAILED_ATTEMPTS = 20;
    private const ATTEMPT_WINDOW_SECONDS = 900; // 15 menit

    /**
     * Hash dummy dipakai saat username tidak ditemukan agar waktu verifikasi
     * password tetap relatif seragam dan tidak mudah dipakai untuk enumerasi user.
     */
    private const DUMMY_PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected UserModel $userModel;
    protected CacheInterface $cache;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->cache = Services::cache();
    }

    public function index()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function login()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $ipAddress = (string) $this->request->getIPAddress();

        $accountKey = $this->attemptKey('account', mb_strtolower($username) . '|' . $ipAddress);
        $ipKey = $this->attemptKey('ip', $ipAddress);

        $retryAfter = max(
            $this->remainingLockSeconds($accountKey),
            $this->remainingLockSeconds($ipKey)
        );

        if ($retryAfter > 0) {
            $minutes = max(1, (int) ceil($retryAfter / 60));

            $this->auditEvent(
                'LOGIN_BLOCKED',
                'AUTH',
                null,
                $username !== '' ? $username : '(kosong)',
                'Percobaan login diblokir oleh rate limit.',
                null,
                ['retry_after_seconds' => $retryAfter],
                'BLOCKED',
                ['username' => $username !== '' ? $username : null]
            );

            return redirect()->back()->with(
                'error',
                "Terlalu banyak percobaan login. Coba lagi sekitar {$minutes} menit."
            );
        }

        // Tolak input kosong/abnormal lebih awal tanpa memproses query yang tidak perlu.
        if (
            $username === ''
            || $password === ''
            || mb_strlen($username) > 50
            || strlen($password) > 255
        ) {
            $this->registerFailure($accountKey, self::ACCOUNT_MAX_FAILED_ATTEMPTS);
            $this->registerFailure($ipKey, self::IP_MAX_FAILED_ATTEMPTS);

            $this->auditEvent(
                'LOGIN_FAILED',
                'AUTH',
                null,
                $username !== '' ? $username : '(kosong)',
                'Login gagal karena kredensial tidak valid.',
                null,
                null,
                'FAILED',
                ['username' => $username !== '' ? $username : null]
            );

            return $this->invalidCredentialsResponse();
        }

        $user = $this->userModel
            ->where('username', $username)
            ->first();

        // Selalu lakukan password_verify, termasuk saat username tidak ada.
        $hashToVerify = $user['password'] ?? self::DUMMY_PASSWORD_HASH;
        $passwordValid = password_verify($password, $hashToVerify);

        if (!$user || !$passwordValid) {
            $this->registerFailure($accountKey, self::ACCOUNT_MAX_FAILED_ATTEMPTS);
            $this->registerFailure($ipKey, self::IP_MAX_FAILED_ATTEMPTS);

            $this->auditEvent(
                'LOGIN_FAILED',
                'AUTH',
                $user['id_user'] ?? null,
                $username !== '' ? $username : '(kosong)',
                'Login gagal karena kredensial tidak valid.',
                null,
                null,
                'FAILED',
                [
                    'id_user' => $user['id_user'] ?? null,
                    'username' => $username !== '' ? $username : null,
                    'name' => $user['nama_lengkap'] ?? null,
                    'role' => $user['role'] ?? null,
                ]
            );

            return $this->invalidCredentialsResponse();
        }

        // Login berhasil: reset counter gagal untuk identitas ini.
        $this->cache->delete($accountKey);
        $this->cache->delete($ipKey);

        // Jika algoritma/default cost PHP berubah, hash lama ikut diperbarui saat login sukses.
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $this->userModel->update($user['id_user'], [
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        }

        $session = session();

        // Cegah session fixation: session ID lama tidak boleh dipakai setelah autentikasi.
        $session->regenerate(true);

        $now = time();
        $session->set([
            'logged_in'           => true,
            'id_user'             => $user['id_user'],
            'username'            => $user['username'],
            'nama_lengkap'        => $user['nama_lengkap'],
            'role'                => $user['role'],
            'login_at'            => $now,
            'last_activity'       => $now,
            'session_fingerprint' => $this->currentFingerprint(),
        ]);

        $this->auditEvent(
            'LOGIN_SUCCESS',
            'AUTH',
            (int) $user['id_user'],
            (string) $user['username'],
            'Login berhasil.',
            null,
            ['role' => $user['role']],
            'SUCCESS'
        );

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        $session = session();

        $this->auditEvent(
            'LOGOUT',
            'AUTH',
            (int) $session->get('id_user'),
            (string) $session->get('username'),
            'Logout berhasil.',
            null,
            null,
            'SUCCESS'
        );

        $session->destroy();

        return redirect()->to('/login');
    }

    private function invalidCredentialsResponse()
    {
        // Pesan dibuat sama untuk username tidak ada maupun password salah.
        return redirect()->back()->with('error', 'Username atau password salah.');
    }

    private function attemptKey(string $scope, string $identity): string
    {
        return 'kamela_login_' . $scope . '_' . hash('sha256', $identity);
    }

    private function registerFailure(string $key, int $limit): void
    {
        $state = $this->readAttemptState($key);
        $attempts = ((int) ($state['attempts'] ?? 0)) + 1;
        $lockedUntil = $attempts >= $limit
            ? time() + self::ATTEMPT_WINDOW_SECONDS
            : 0;

        $this->cache->save(
            $key,
            [
                'attempts'     => $attempts,
                'locked_until' => $lockedUntil,
            ],
            self::ATTEMPT_WINDOW_SECONDS
        );
    }

    private function remainingLockSeconds(string $key): int
    {
        $state = $this->readAttemptState($key);
        $lockedUntil = (int) ($state['locked_until'] ?? 0);

        if ($lockedUntil <= time()) {
            return 0;
        }

        return $lockedUntil - time();
    }

    /**
     * @return array{attempts:int,locked_until:int}
     */
    private function readAttemptState(string $key): array
    {
        $state = $this->cache->get($key);

        if (!is_array($state)) {
            return [
                'attempts'     => 0,
                'locked_until' => 0,
            ];
        }

        return [
            'attempts'     => (int) ($state['attempts'] ?? 0),
            'locked_until' => (int) ($state['locked_until'] ?? 0),
        ];
    }

    private function currentFingerprint(): string
    {
        return hash('sha256', $this->request->getUserAgent()->getAgentString());
    }
}
