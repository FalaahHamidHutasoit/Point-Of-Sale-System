<?php

namespace App\Filters;

use App\Services\AuditService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('logged_in')) {
            $this->writeBlockedAudit($request, [], 'Route role-protected diakses tanpa session login.');
            return redirect()->to('/login');
        }

        $role = (string) $session->get('role');

        if ($arguments && !in_array($role, $arguments, true)) {
            $this->writeBlockedAudit(
                $request,
                $arguments,
                'Akses route diblokir karena role tidak diizinkan.'
            );

            return redirect()->to('/dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    /** @param list<string> $allowedRoles */
    private function writeBlockedAudit(RequestInterface $request, array $allowedRoles, string $description): void
    {
        try {
            $session = session();
            (new AuditService())->record([
                'id_user' => $session->get('id_user'),
                'actor_username' => $session->get('username'),
                'actor_name' => $session->get('nama_lengkap'),
                'actor_role' => $session->get('role'),
                'action' => 'AUTHZ_BLOCKED',
                'entity_type' => 'SECURITY',
                'entity_label' => $request->getUri()->getPath(),
                'description' => $description,
                'before_data' => ['role' => $session->get('role')],
                'after_data' => ['allowed_roles' => $allowedRoles],
                'status' => 'BLOCKED',
                'ip_address' => $request->getIPAddress(),
                'user_agent' => $request->getUserAgent()->getAgentString(),
                'http_method' => strtoupper((string) $request->getMethod()),
                'request_uri' => $request->getUri()->getPath(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Audit role filter gagal: {message}', ['message' => $e->getMessage()]);
        }
    }
}
