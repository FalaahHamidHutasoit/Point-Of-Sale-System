<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<div class="main-content">
    <div class="page-heading"><div><h2>Reset Password</h2><p>Buat password sementara untuk <?= esc($user['nama_lengkap']) ?>.</p></div><a class="btn btn-light border" href="<?= base_url('users') ?>"><i class="bi bi-arrow-left me-1"></i>Kembali</a></div>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <div class="row justify-content-center"><div class="col-lg-6"><div class="surface-card p-4">
        <div class="d-flex align-items-center gap-3 mb-4"><div class="rounded-3 bg-primary-subtle text-primary d-grid" style="width:48px;height:48px;place-items:center"><i class="bi bi-key fs-4"></i></div><div><div class="fw-bold"><?= esc($user['nama_lengkap']) ?></div><div class="small text-muted"><?= esc($user['kode_pegawai']) ?> · @<?= esc($user['username']) ?></div></div></div>
        <form method="post" action="<?= base_url('users/reset-password/' . $user['id_user']) ?>"><?= csrf_field() ?><div class="mb-3"><label class="form-label fw-semibold">Password Sementara Baru</label><input type="password" required minlength="8" class="form-control" name="password" autocomplete="new-password"></div><div class="mb-3"><label class="form-label fw-semibold">Konfirmasi Password</label><input type="password" required minlength="8" class="form-control" name="password_confirmation" autocomplete="new-password"></div><div class="alert alert-warning small"><i class="bi bi-exclamation-triangle me-1"></i>Setelah reset, pegawai akan diwajibkan mengganti password ini pada login berikutnya.</div><button class="btn btn-primary w-100"><i class="bi bi-key-fill me-1"></i>Reset Password</button></form>
    </div></div></div>
</div>
</body></html>
