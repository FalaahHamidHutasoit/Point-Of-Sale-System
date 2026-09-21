<?php

namespace App\Filters;

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
