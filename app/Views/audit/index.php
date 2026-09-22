<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<?php
$prettyJson = static function (?string $json): ?string {
    if ($json === null || trim($json) === '') {
        return null;
    }
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return $json;
    }
    return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
};
?>
<div class="main-content">
    <div class="page-heading">
        <div>
            <div class="text-primary fw-bold small mb-1">KEAMANAN & KONTROL</div>
            <h2>Audit Aktivitas</h2>
            <p>Jejak perubahan dan aktivitas penting. Audit log hanya dibaca, tidak diedit dari aplikasi.</p>
        </div>
        <span class="badge badge-soft-primary px-3 py-2"><i class="bi bi-shield-check me-1"></i>Admin Only</span>
    </div>

    <div class="surface-card p-3 mb-3">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-xl-3 col-md-6">
                <label class="form-label small fw-semibold">Pencarian</label>
                <input type="search" name="keyword" class="form-control" value="<?= esc($keyword) ?>" placeholder="User, aksi, entitas, deskripsi...">
            </div>
            <div class="col-xl-2 col-md-3">
                <label class="form-label small fw-semibold">Aksi</label>
                <select name="action" class="form-select">
                    <option value="">Semua aksi</option>
                    <?php foreach ($actions as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $action === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xl-2 col-md-3">
                <label class="form-label small fw-semibold">Entitas</label>
                <select name="entity_type" class="form-select">
                    <option value="">Semua entitas</option>
                    <?php foreach ($entityTypes as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $entityType === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xl-2 col-md-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <?php foreach (['SUCCESS','FAILED','BLOCKED'] as $option): ?>
                        <option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xl-1 col-md-3">
                <label class="form-label small fw-semibold">Dari</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="<?= esc($tanggalMulai) ?>">
            </div>
            <div class="col-xl-1 col-md-3">
                <label class="form-label small fw-semibold">Sampai</label>
                <input type="date" name="tanggal_akhir" class="form-control" value="<?= esc($tanggalAkhir) ?>">
            </div>
            <div class="col-xl-1 col-md-3 d-grid">
                <button class="btn btn-outline-primary" title="Terapkan filter"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
        </form>
    </div>

    <div class="surface-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pelaku</th>
                        <th>Aksi</th>
                        <th>Objek</th>
                        <th>Status</th>
                        <th style="min-width:280px">Keterangan / Perubahan</th>
                        <th>Request</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($logs): ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                            $before = $prettyJson($log['before_data'] ?? null);
                            $after = $prettyJson($log['after_data'] ?? null);
                            $badge = match ($log['status']) {
                                'SUCCESS' => 'badge-soft-success',
                                'BLOCKED' => 'badge-soft-warning',
                                default => 'badge-soft-danger',
                            };
                        ?>
                        <tr>
                            <td class="text-nowrap">
                                <strong><?= date('d M Y', strtotime($log['created_at'])) ?></strong>
                                <small class="d-block text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></small>
                            </td>
                            <td>
                                <strong><?= esc($log['actor_name'] ?: $log['actor_username'] ?: 'Guest/System') ?></strong>
                                <small class="d-block text-muted">
                                    <?= esc($log['actor_username'] ?: '-') ?> · <?= esc($log['actor_role'] ?: '-') ?>
                                </small>
                            </td>
                            <td><span class="badge badge-soft-primary"><?= esc($log['action']) ?></span></td>
                            <td>
                                <strong><?= esc($log['entity_type']) ?></strong>
                                <small class="d-block text-muted"><?= esc($log['entity_label'] ?: ($log['entity_id'] ?: '-')) ?></small>
                            </td>
                            <td><span class="badge <?= $badge ?>"><?= esc($log['status']) ?></span></td>
                            <td>
                                <div><?= esc($log['description'] ?: '-') ?></div>
                                <?php if ($before !== null || $after !== null): ?>
                                    <details class="mt-2">
                                        <summary class="small text-primary fw-semibold" style="cursor:pointer">Lihat snapshot perubahan</summary>
                                        <div class="row g-2 mt-1">
                                            <?php if ($before !== null): ?>
                                                <div class="col-lg-6">
                                                    <small class="fw-bold text-muted">SEBELUM</small>
                                                    <pre class="bg-light border rounded p-2 small mb-0 mt-1" style="max-height:240px;overflow:auto;white-space:pre-wrap"><?= esc($before) ?></pre>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($after !== null): ?>
                                                <div class="col-lg-6">
                                                    <small class="fw-bold text-muted">SESUDAH</small>
                                                    <pre class="bg-light border rounded p-2 small mb-0 mt-1" style="max-height:240px;overflow:auto;white-space:pre-wrap"><?= esc($after) ?></pre>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="d-block"><strong><?= esc($log['http_method'] ?: '-') ?></strong> <?= esc($log['request_uri'] ?: '-') ?></small>
                                <small class="d-block text-muted"><?= esc($log['ip_address'] ?: '-') ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">Belum ada audit log sesuai filter.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($pager): ?>
            <?= view('components/pagination', ['pager' => $pager, 'group' => 'default', 'label' => 'audit log']) ?>
        <?php endif; ?>
    </div>
</div>
<?= view('layout/footer') ?>
