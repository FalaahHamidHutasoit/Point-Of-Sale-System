<?php

namespace App\Filters;

use App\Models\UserModel;
use App\Services\AuditService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    private const IDLE_TIMEOUT_SECONDS = 1800;
    private const ABSOLUTE_TIMEOUT_SECONDS = 28800;

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $now = time();
        $loginAt = (int) $session->get('login_at');
        $lastActivity = (int) $session->get('last_activity');
        $expectedFingerprint = (string) $session->get('session_fingerprint');

        if ($loginAt <= 0 || $lastActivity <= 0 || $expectedFingerprint === '') {
            return $this->expireSession($request, 'SESSION_LEGACY', 'Sesi keamanan diperbarui. Silakan login kembali.');
        }

        if (($now - $lastActivity) > self::IDLE_TIMEOUT_SECONDS) {
            return $this->expireSession($request, 'SESSION_IDLE_TIMEOUT', 'Sesi berakhir karena tidak ada aktivitas selama 30 menit.');
        }

        if (($now - $loginAt) > self::ABSOLUTE_TIMEOUT_SECONDS) {
            return $this->expireSession($request, 'SESSION_ABSOLUTE_TIMEOUT', 'Sesi telah mencapai batas 8 jam. Silakan login kembali.');
        }

        $actualFingerprint = hash('sha256', $request->getUserAgent()->getAgentString());
        if (!hash_equals($expectedFingerprint, $actualFingerprint)) {
            return $this->expireSession($request, 'SESSION_FINGERPRINT_MISMATCH', 'Sesi tidak valid. Silakan login kembali.');
        }

        // Phase 11: status/role akun dicek ke database di setiap request terautentikasi.
        // Ini membuat penonaktifan akun atau perubahan role efektif tanpa menunggu sesi 8 jam habis.
        $user = (new UserModel())->find((int) $session->get('id_user'));
        if (!$user || (int) ($user['is_active'] ?? 1) !== 1) {
            return $this->expireSession($request, 'SESSION_ACCOUNT_DISABLED', 'Akun tidak aktif atau tidak lagi tersedia. Silakan hubungi administrator.');
        }

        if (
            (string) $user['username'] !== (string) $session->get('username')
            || (string) $user['role'] !== (string) $session->get('role')
        ) {
            return $this->expireSession($request, 'SESSION_ACCOUNT_CHANGED', 'Data akses akun berubah. Silakan login kembali.');
        }

        $mustChange = (int) ($user['must_change_password'] ?? 0) === 1;
        $session->set('must_change_password', $mustChange);
        $path = trim($request->getUri()->getPath(), '/');
        if ($mustChange && !in_array($path, ['account/password', 'logout'], true)) {
            $session->set('last_activity', $now);
            return redirect()->to('/account/password')->with('warning', 'Silakan ganti password sementara sebelum melanjutkan.');
        }

        $session->set('last_activity', $now);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function expireSession(RequestInterface $request, string $action, string $message)
    {
        $session = session();

        try {
            (new AuditService())->record([
                'id_user' => $session->get('id_user'),
                'actor_username' => $session->get('username'),
                'actor_name' => $session->get('nama_lengkap'),
                'actor_role' => $session->get('role'),
                'action' => $action,
                'entity_type' => 'AUTH',
                'entity_id' => $session->get('id_user'),
                'entity_label' => $session->get('username'),
                'description' => $message,
                'status' => 'BLOCKED',
                'ip_address' => $request->getIPAddress(),
                'user_agent' => $request->getUserAgent()->getAgentString(),
                'http_method' => strtoupper((string) $request->getMethod()),
                'request_uri' => $request->getUri()->getPath(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Audit session expiry gagal: {message}', ['message' => $e->getMessage()]);
        }

        $session->destroy();
        return redirect()->to('/login')->with('error', $message);
    }
}
