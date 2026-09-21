<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class AuditLog extends BaseController
{
    private AuditLogModel $auditModel;

    public function __construct()
    {
        $this->auditModel = new AuditLogModel();
    }

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $action = trim((string) $this->request->getGet('action'));
        $entityType = trim((string) $this->request->getGet('entity_type'));
        $status = strtoupper(trim((string) $this->request->getGet('status')));
        $tanggalMulai = trim((string) $this->request->getGet('tanggal_mulai'));
        $tanggalAkhir = trim((string) $this->request->getGet('tanggal_akhir'));

        if (!in_array($status, ['', 'SUCCESS', 'FAILED', 'BLOCKED'], true)) {
            $status = '';
        }

        $builder = $this->auditModel;

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('actor_username', $keyword)
                ->orLike('actor_name', $keyword)
                ->orLike('action', $keyword)
                ->orLike('entity_type', $keyword)
                ->orLike('entity_label', $keyword)
                ->orLike('description', $keyword)
                ->groupEnd();
        }
        if ($action !== '') {
            $builder->where('action', $action);
        }
        if ($entityType !== '') {
            $builder->where('entity_type', $entityType);
        }
        if ($status !== '') {
            $builder->where('status', $status);
        }
        if ($tanggalMulai !== '') {
            $builder->where('created_at >=', $tanggalMulai . ' 00:00:00');
        }
        if ($tanggalAkhir !== '') {
            $builder->where('created_at <=', $tanggalAkhir . ' 23:59:59');
        }

        $db = \Config\Database::connect();
        $actions = array_column(
            $db->table('audit_logs')->select('action')->distinct()->orderBy('action', 'ASC')->get()->getResultArray(),
            'action'
        );
        $entityTypes = array_column(
            $db->table('audit_logs')->select('entity_type')->distinct()->orderBy('entity_type', 'ASC')->get()->getResultArray(),
            'entity_type'
        );

        return view('audit/index', [
            'title' => 'Audit Aktivitas',
            'logs' => $builder->orderBy('id_audit', 'DESC')->paginate(50),
            'pager' => $this->auditModel->pager,
            'keyword' => $keyword,
            'action' => $action,
            'entityType' => $entityType,
            'status' => $status,
            'tanggalMulai' => $tanggalMulai,
            'tanggalAkhir' => $tanggalAkhir,
            'actions' => $actions,
            'entityTypes' => $entityTypes,
        ]);
    }
}
