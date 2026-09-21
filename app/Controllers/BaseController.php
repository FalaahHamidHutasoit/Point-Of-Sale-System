<?php

namespace App\Controllers;

use App\Services\AuditService;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Base controller KAMELA.
 *
 * Phase 2: defense-in-depth untuk endpoint mutasi.
 * Phase 3: satu jalur audit terpusat untuk aksi penting.
 */
abstract class BaseController extends Controller
{
    /** @var CLIRequest|IncomingRequest */
    protected $request;

    /** @var list<string> */
    protected $helpers = [];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Defense-in-depth untuk endpoint yang mengubah data.
     *
     * @param list<string> $allowedRoles
     */
    protected function guardSensitivePost(array $allowedRoles): ?RedirectResponse
    {
        $session = session();

        if (!$session->get('logged_in')) {
            $this->auditEvent(
                'AUTHZ_BLOCKED',
                'SECURITY',
                null,
                'Sensitive POST',
                'Request mutasi ditolak karena tidak memiliki session login.',
                null,
                null,
                'BLOCKED'
            );

            return redirect()->to('/login')
                ->with('error', 'Sesi login diperlukan untuk melanjutkan.');
        }

        if (strtoupper((string) $this->request->getMethod()) !== 'POST') {
            $this->auditEvent(
                'AUTHZ_BLOCKED',
                'SECURITY',
                null,
                'Sensitive endpoint',
                'Request mutasi ditolak karena HTTP method bukan POST.',
                null,
                ['allowed_method' => 'POST'],
                'BLOCKED'
            );

            return redirect()->to('/dashboard')
                ->with('error', 'Metode request tidak diizinkan.');
        }

        $role = (string) $session->get('role');
        if (!in_array($role, $allowedRoles, true)) {
            $this->auditEvent(
                'AUTHZ_BLOCKED',
                'SECURITY',
                (int) $session->get('id_user'),
                (string) $session->get('username'),
                'Aksi ditolak karena role tidak memiliki hak akses.',
                ['role' => $role],
                ['allowed_roles' => $allowedRoles],
                'BLOCKED'
            );

            return redirect()->to('/dashboard')
                ->with('error', 'Anda tidak memiliki hak akses untuk tindakan tersebut.');
        }

        if ((int) $session->get('id_user') <= 0) {
            $this->auditEvent(
                'AUTHZ_BLOCKED',
                'SECURITY',
                null,
                (string) $session->get('username'),
                'Session ditolak karena id_user tidak valid.',
                null,
                null,
                'BLOCKED'
            );

            $session->destroy();
            return redirect()->to('/login')
                ->with('error', 'Session tidak valid. Silakan login kembali.');
        }

        return null;
    }

    /**
     * Menulis audit log tanpa membuat kegagalan audit merusak transaksi utama.
     * Jika tabel audit belum terpasang/bermasalah, error masuk application log.
     *
     * @param array<string,mixed>|null $before
     * @param array<string,mixed>|null $after
     * @param array{id_user?:int|null,username?:string|null,name?:string|null,role?:string|null}|null $actorOverride
     */
    protected function auditEvent(
        string $action,
        string $entityType,
        int|string|null $entityId = null,
        ?string $entityLabel = null,
        ?string $description = null,
        ?array $before = null,
        ?array $after = null,
        string $status = 'SUCCESS',
        ?array $actorOverride = null
    ): void {
        try {
            $session = session();
            $actorOverride ??= [];

            $service = new AuditService();
            $service->record([
                'id_user' => $actorOverride['id_user'] ?? $session->get('id_user'),
                'actor_username' => $actorOverride['username'] ?? $session->get('username'),
                'actor_name' => $actorOverride['name'] ?? $session->get('nama_lengkap'),
                'actor_role' => $actorOverride['role'] ?? $session->get('role'),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'entity_label' => $entityLabel,
                'description' => $description,
                'before_data' => $before,
                'after_data' => $after,
                'status' => $status,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'http_method' => strtoupper((string) $this->request->getMethod()),
                'request_uri' => $this->request->getUri()->getPath(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Audit log gagal ditulis: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
