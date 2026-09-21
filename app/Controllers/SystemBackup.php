<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\BackupService;
use RuntimeException;

class SystemBackup extends BaseController
{
    private BackupService $backupService;
    private UserModel $userModel;

    public function __construct()
    {
        $this->backupService = new BackupService();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return view('system_backup/index', [
            'title' => 'Backup & Recovery',
            'backups' => $this->backupService->list(),
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        $reason = trim((string) $this->request->getPost('reason'));

        try {
            $result = $this->backupService->create($reason ?: 'Manual backup oleh Admin');
            $this->auditEvent(
                'BACKUP_CREATE',
                'SYSTEM_BACKUP',
                null,
                $result['filename'],
                'Snapshot database dibuat.',
                null,
                [
                    'filename' => $result['filename'],
                    'checksum' => $result['checksum'],
                    'table_count' => $result['table_count'],
                    'row_count' => $result['row_count'],
                    'size' => $result['size'],
                ]
            );

            return redirect()->to('/system/backup')
                ->with('success', 'Backup berhasil dibuat dan diverifikasi.');
        } catch (\Throwable $e) {
            log_message('error', 'Backup KAMELA gagal: {message}', ['message' => $e->getMessage()]);
            $this->auditEvent('BACKUP_CREATE', 'SYSTEM_BACKUP', null, null, 'Pembuatan backup gagal.', null, null, 'FAILED');
            return redirect()->to('/system/backup')->with('error', 'Backup gagal dibuat. Cek writable/backups dan log aplikasi.');
        }
    }

    public function download(string $filename)
    {
        try {
            $path = $this->backupService->pathForDownload($filename);
            $this->auditEvent('BACKUP_DOWNLOAD', 'SYSTEM_BACKUP', null, basename($filename), 'Backup diunduh oleh Admin.');
            return $this->response->download($path, null)->setFileName(basename($filename));
        } catch (\Throwable $e) {
            return redirect()->to('/system/backup')->with('error', 'Backup tidak tersedia atau gagal diverifikasi.');
        }
    }

    public function restore(string $filename)
    {
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        if (!$this->reauthenticateCurrentAdmin()) {
            $this->auditEvent('BACKUP_RESTORE', 'SYSTEM_BACKUP', null, basename($filename), 'Restore diblokir: password konfirmasi salah.', null, null, 'BLOCKED');
            return redirect()->to('/system/backup')->with('error', 'Password Admin tidak cocok. Restore dibatalkan.');
        }

        if (trim((string) $this->request->getPost('confirmation')) !== 'RESTORE KAMELA') {
            return redirect()->to('/system/backup')->with('error', 'Ketik tepat "RESTORE KAMELA" untuk mengonfirmasi restore.');
        }

        try {
            // Safety net: snapshot kondisi saat ini sebelum restore destructive.
            $safety = $this->backupService->create('Automatic pre-restore safety snapshot');
            $result = $this->backupService->restore($filename);

            $this->auditEvent(
                'BACKUP_RESTORE',
                'SYSTEM_BACKUP',
                null,
                basename($filename),
                'Database berhasil direstore dari snapshot terverifikasi.',
                ['safety_backup' => $safety['filename']],
                [
                    'source_backup' => basename($filename),
                    'backup_created_at' => $result['backup_created_at'],
                    'tables' => $result['tables'],
                    'rows' => $result['rows'],
                ]
            );

            return redirect()->to('/system/backup')->with(
                'success',
                'Restore selesai. Safety backup sebelum restore: ' . $safety['filename']
            );
        } catch (\Throwable $e) {
            log_message('error', 'Restore KAMELA gagal: {message}', ['message' => $e->getMessage()]);
            $this->auditEvent('BACKUP_RESTORE', 'SYSTEM_BACKUP', null, basename($filename), 'Restore gagal dan transaction dibatalkan.', null, ['error' => $e->getMessage()], 'FAILED');
            return redirect()->to('/system/backup')->with('error', 'Restore gagal. Database tidak boleh dibiarkan setengah terpulihkan; cek log aplikasi.');
        }
    }

    public function delete(string $filename)
    {
        if ($guard = $this->guardSensitivePost(['admin'])) {
            return $guard;
        }

        if (!$this->reauthenticateCurrentAdmin()) {
            return redirect()->to('/system/backup')->with('error', 'Password Admin tidak cocok. Backup tidak dihapus.');
        }

        if (trim((string) $this->request->getPost('confirmation')) !== 'HAPUS BACKUP') {
            return redirect()->to('/system/backup')->with('error', 'Konfirmasi penghapusan backup tidak cocok.');
        }

        try {
            $this->backupService->delete($filename);
            $this->auditEvent('BACKUP_DELETE', 'SYSTEM_BACKUP', null, basename($filename), 'File backup dihapus oleh Admin.');
            return redirect()->to('/system/backup')->with('success', 'File backup berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/system/backup')->with('error', 'Backup gagal dihapus.');
        }
    }

    private function reauthenticateCurrentAdmin(): bool
    {
        $idUser = (int) session()->get('id_user');
        $password = (string) $this->request->getPost('password');
        if ($idUser <= 0 || $password === '') {
            return false;
        }

        $user = $this->userModel->find($idUser);
        return is_array($user)
            && ($user['role'] ?? null) === 'admin'
            && password_verify($password, (string) ($user['password'] ?? ''));
    }
}
