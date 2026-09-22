<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<div class="main-content">
    <div class="page-heading">
        <div>
            <h2>Pegawai & Akun</h2>
            <p>Kelola identitas pegawai, role akses, status akun, dan keamanan login.</p>
        </div>
        <a href="<?= base_url('users/tambah') ?>" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Tambah Pegawai</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><div class="surface-card p-3"><small class="text-muted">Total Pegawai</small><div class="fs-3 fw-bold"><?= (int) $stats['total'] ?></div></div></div>
        <div class="col-6 col-lg-3"><div class="surface-card p-3"><small class="text-muted">Akun Aktif</small><div class="fs-3 fw-bold text-success"><?= (int) $stats['active'] ?></div></div></div>
        <div class="col-6 col-lg-3"><div class="surface-card p-3"><small class="text-muted">Nonaktif</small><div class="fs-3 fw-bold text-secondary"><?= (int) $stats['inactive'] ?></div></div></div>
        <div class="col-6 col-lg-3"><div class="surface-card p-3"><small class="text-muted">Admin Aktif</small><div class="fs-3 fw-bold text-primary"><?= (int) $stats['admins'] ?></div></div></div>
    </div>

    <div class="surface-card p-3 mb-3">
        <form method="get" action="<?= base_url('users') ?>" class="row g-2 align-items-end">
            <div class="col-lg-6"><label class="form-label small fw-semibold">Cari pegawai</label><input class="form-control" name="q" value="<?= esc($filters['q']) ?>" placeholder="Kode, nama, username, email, telepon"></div>
            <div class="col-md-3 col-lg-2"><label class="form-label small fw-semibold">Role</label><select class="form-select" name="role"><option value="">Semua</option><?php foreach ($roleLabels as $key => $label): ?><option value="<?= esc($key) ?>" <?= $filters['role'] === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3 col-lg-2"><label class="form-label small fw-semibold">Status</label><select class="form-select" name="status"><option value="">Semua</option><option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Aktif</option><option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Nonaktif</option></select></div>
            <div class="col-lg-2 d-grid"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Filter</button></div>
        </form>
    </div>

    <div class="surface-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Pegawai</th><th>Role</th><th>Kontak</th><th>Status</th><th>Login Terakhir</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php if (!$users): ?><tr><td colspan="6" class="text-center text-muted py-5">Belum ada data pegawai.</td></tr><?php endif; ?>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><div class="fw-bold"><?= esc($row['nama_lengkap']) ?></div><div class="small text-muted"><?= esc($row['kode_pegawai']) ?> · @<?= esc($row['username']) ?></div></td>
                        <td><span class="badge badge-soft-primary"><?= esc($roleLabels[$row['role']] ?? ucfirst($row['role'])) ?></span></td>
                        <td><div class="small"><?= esc($row['email'] ?: '-') ?></div><div class="small text-muted"><?= esc($row['no_telp'] ?: '-') ?></div></td>
                        <td><?php if ((int) $row['is_active'] === 1): ?><span class="badge badge-soft-success">Aktif</span><?php else: ?><span class="badge bg-secondary-subtle text-secondary">Nonaktif</span><?php endif; ?><?php if ((int) ($row['must_change_password'] ?? 0) === 1): ?><div class="small text-warning mt-1"><i class="bi bi-key"></i> Ganti password</div><?php endif; ?></td>
                        <td><span class="small"><?= $row['last_login_at'] ? esc(date('d M Y H:i', strtotime($row['last_login_at']))) : '<span class="text-muted">Belum pernah</span>' ?></span></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-light border" href="<?= base_url('users/edit/' . $row['id_user']) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a class="btn btn-sm btn-light border" href="<?= base_url('users/reset-password/' . $row['id_user']) ?>" title="Reset password"><i class="bi bi-key"></i></a>
                            <form class="d-inline" method="post" action="<?= base_url('users/toggle-status/' . $row['id_user']) ?>" onsubmit="return confirm('Ubah status akun <?= esc(addslashes($row['username'])) ?>?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm <?= (int) $row['is_active'] === 1 ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= (int) $row['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>"><i class="bi <?= (int) $row['is_active'] === 1 ? 'bi-person-x' : 'bi-person-check' ?>"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($pager): ?><div class="p-3 border-top"><?= $pager->links('users', 'default_full') ?></div><?php endif; ?>
    </div>

    <div class="alert alert-light border mt-3 mb-0 small text-muted"><i class="bi bi-info-circle me-1"></i>Akun pegawai tidak dihapus permanen agar histori transaksi dan audit tetap dapat ditelusuri. Gunakan status <strong>Nonaktif</strong> untuk pegawai yang sudah tidak bekerja.</div>
</div>
</body></html>
