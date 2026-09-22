<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<div class="main-content">
    <div class="page-heading">
        <div>
            <div class="text-primary fw-bold small mb-1">OPERASIONAL & RECOVERY</div>
            <h2>Backup & Recovery</h2>
            <p>Snapshot database KAMELA disimpan di luar folder public dan diverifikasi dengan checksum sebelum restore.</p>
        </div>
        <span class="badge badge-soft-primary px-3 py-2"><i class="bi bi-database-lock me-1"></i>Admin Only</span>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="surface-card p-4 h-100">
                <h5 class="fw-bold mb-1"><i class="bi bi-shield-check text-primary me-2"></i>Buat Snapshot Baru</h5>
                <p class="text-muted small mb-3">Gunakan sebelum perubahan besar, import data, atau maintenance database.</p>
                <form method="post" action="<?= base_url('system/backup/create') ?>" class="row g-2">
                    <?= csrf_field() ?>
                    <div class="col-md-9">
                        <label class="form-label small fw-semibold">Alasan / catatan backup</label>
                        <input type="text" name="reason" maxlength="150" class="form-control" placeholder="Contoh: Sebelum demo presentasi">
                    </div>
                    <div class="col-md-3 d-grid align-items-end">
                        <button class="btn btn-primary mt-md-4"><i class="bi bi-database-add me-1"></i>Buat Backup</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="surface-card p-4 h-100 border-warning-subtle">
                <h5 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Aturan Recovery</h5>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Restore hanya dari snapshot yang dibuat KAMELA.</li>
                    <li>Checksum harus valid; file yang berubah akan ditolak.</li>
                    <li>KAMELA otomatis membuat safety backup sebelum restore.</li>
                    <li>Restore membutuhkan password Admin + frasa konfirmasi.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="surface-card overflow-hidden">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
            <div><strong>Daftar Backup</strong><small class="d-block text-muted">Lokasi server: writable/backups</small></div>
            <span class="badge badge-soft-primary"><?= count($backups) ?> file</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Backup</th><th>Dibuat</th><th>Isi</th><th>Ukuran</th><th>Integritas</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php if ($backups): ?>
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><strong class="small"><?= esc($backup['filename']) ?></strong><small class="d-block text-muted"><?= esc($backup['reason'] ?: '-') ?></small></td>
                            <td class="text-nowrap"><?= $backup['created_at'] ? date('d M Y H:i:s', strtotime($backup['created_at'])) : '-' ?></td>
                            <td><?= number_format((int) $backup['row_count'], 0, ',', '.') ?> rows</td>
                            <td><?= number_format(((int) $backup['size']) / 1024, 1, ',', '.') ?> KB</td>
                            <td>
                                <?php if ($backup['valid']): ?>
                                    <span class="badge badge-soft-success">Checksum valid</span>
                                    <small class="d-block text-muted font-monospace mt-1"><?= esc(substr((string) $backup['checksum'], 0, 12)) ?>…</small>
                                <?php else: ?>
                                    <span class="badge badge-soft-danger">INVALID</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <?php if ($backup['valid']): ?>
                                    <a class="btn btn-sm btn-outline-primary table-action" href="<?= base_url('system/backup/download/' . rawurlencode($backup['filename'])) ?>"><i class="bi bi-download"></i></a>
                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#restoreModal" data-file="<?= esc($backup['filename'], 'attr') ?>"><i class="bi bi-arrow-counterclockwise"></i></button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-danger table-action" data-bs-toggle="modal" data-bs-target="#deleteModal" data-file="<?= esc($backup['filename'], 'attr') ?>"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">Belum ada backup. Buat snapshot pertama sebelum melakukan perubahan besar.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4">
        <form method="post" id="restoreForm">
            <?= csrf_field() ?>
            <div class="modal-header"><h5 class="modal-title fw-bold">Restore Database</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-warning small">Restore mengganti isi database dengan snapshot terpilih. Safety backup kondisi saat ini akan dibuat otomatis.</div>
                <div class="mb-3"><label class="form-label fw-semibold">Password Admin</label><input type="password" name="password" class="form-control" autocomplete="current-password" required></div>
                <div><label class="form-label fw-semibold">Ketik RESTORE KAMELA</label><input type="text" name="confirmation" class="form-control" autocomplete="off" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-warning">Restore Snapshot</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4">
        <form method="post" id="deleteForm">
            <?= csrf_field() ?>
            <div class="modal-header"><h5 class="modal-title fw-bold">Hapus File Backup</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label fw-semibold">Password Admin</label><input type="password" name="password" class="form-control" autocomplete="current-password" required></div>
                <div><label class="form-label fw-semibold">Ketik HAPUS BACKUP</label><input type="text" name="confirmation" class="form-control" autocomplete="off" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Hapus Permanen</button></div>
        </form>
    </div></div>
</div>
<script>
document.getElementById('restoreModal')?.addEventListener('show.bs.modal', event => {
    const file = event.relatedTarget?.getAttribute('data-file') || '';
    document.getElementById('restoreForm').action = <?= json_encode(base_url('system/backup/restore')) ?> + '/' + encodeURIComponent(file);
});
document.getElementById('deleteModal')?.addEventListener('show.bs.modal', event => {
    const file = event.relatedTarget?.getAttribute('data-file') || '';
    document.getElementById('deleteForm').action = <?= json_encode(base_url('system/backup/delete')) ?> + '/' + encodeURIComponent(file);
});
</script>
<?= view('layout/footer') ?>
