<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<div class="main-content">
    <div class="page-heading"><div><h2>Ganti Password</h2><p>Perbarui password untuk menjaga keamanan akun.</p></div></div>
    <?php if ($forced): ?><div class="alert alert-warning"><strong>Password sementara terdeteksi.</strong> Anda wajib membuat password pribadi sebelum menggunakan fitur KAMELA.</div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <div class="row justify-content-center"><div class="col-lg-6"><div class="surface-card p-4"><div class="mb-4"><div class="fw-bold"><?= esc($user['nama_lengkap']) ?></div><div class="small text-muted">@<?= esc($user['username']) ?></div></div><form method="post" action="<?= base_url('account/password') ?>"><?= csrf_field() ?><div class="mb-3"><label class="form-label fw-semibold">Password Saat Ini</label><input type="password" required class="form-control" name="current_password" autocomplete="current-password"></div><div class="mb-3"><label class="form-label fw-semibold">Password Baru</label><input type="password" required minlength="8" class="form-control" name="password" autocomplete="new-password"><div class="form-text">Minimal 8 karakter dan harus berbeda dari password saat ini.</div></div><div class="mb-4"><label class="form-label fw-semibold">Konfirmasi Password Baru</label><input type="password" required minlength="8" class="form-control" name="password_confirmation" autocomplete="new-password"></div><button class="btn btn-primary w-100"><i class="bi bi-shield-check me-1"></i>Simpan Password Baru</button></form></div></div></div>
</div>
</body></html>
