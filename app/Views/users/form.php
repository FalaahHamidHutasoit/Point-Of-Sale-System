<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<?php $isEdit = $mode === 'edit'; ?>
<div class="main-content">
    <div class="page-heading"><div><h2><?= $isEdit ? 'Edit Pegawai' : 'Tambah Pegawai' ?></h2><p><?= $isEdit ? 'Perbarui identitas dan kewenangan pegawai.' : 'Buat identitas pegawai sekaligus akun akses KAMELA.' ?></p></div><a class="btn btn-light border" href="<?= base_url('users') ?>"><i class="bi bi-arrow-left me-1"></i>Kembali</a></div>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <form method="post" action="<?= $isEdit ? base_url('users/update/' . $user['id_user']) : base_url('users/simpan') ?>">
        <?= csrf_field() ?>
        <div class="row g-4">
            <div class="col-lg-7"><div class="surface-card p-4"><h5 class="fw-bold mb-3">Profil Pegawai</h5><div class="row g-3">
                <div class="col-md-5"><label class="form-label fw-semibold">Kode Pegawai</label><input class="form-control" name="kode_pegawai" maxlength="20" value="<?= esc(old('kode_pegawai', $user['kode_pegawai'] ?? '')) ?>" placeholder="Otomatis jika kosong"><div class="form-text">Contoh: KSR001, GDG002.</div></div>
                <div class="col-md-7"><label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label><input required class="form-control" name="nama_lengkap" maxlength="100" value="<?= esc(old('nama_lengkap', $user['nama_lengkap'] ?? '')) ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control" name="email" maxlength="120" value="<?= esc(old('email', $user['email'] ?? '')) ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">No. Telepon</label><input class="form-control" name="no_telp" maxlength="20" value="<?= esc(old('no_telp', $user['no_telp'] ?? '')) ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Masuk</label><input type="date" class="form-control" name="tanggal_masuk" value="<?= esc(old('tanggal_masuk', $user['tanggal_masuk'] ?? date('Y-m-d'))) ?>"></div>
            </div></div></div>
            <div class="col-lg-5"><div class="surface-card p-4"><h5 class="fw-bold mb-3">Akun & Hak Akses</h5><div class="row g-3">
                <div class="col-12"><label class="form-label fw-semibold">Username <span class="text-danger">*</span></label><input required class="form-control" name="username" maxlength="50" value="<?= esc(old('username', $user['username'] ?? '')) ?>"><div class="form-text">Huruf kecil, angka, titik, garis bawah, atau strip.</div></div>
                <div class="col-12"><label class="form-label fw-semibold">Role <span class="text-danger">*</span></label><select required class="form-select" name="role"><?php foreach ($roleLabels as $key => $label): ?><option value="<?= esc($key) ?>" <?= old('role', $user['role'] ?? 'kasir') === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
                <?php if (!$isEdit): ?>
                    <div class="col-12"><label class="form-label fw-semibold">Password Sementara <span class="text-danger">*</span></label><input required type="password" class="form-control" name="password" minlength="8" autocomplete="new-password"><div class="form-text">Minimal 8 karakter. Pegawai wajib menggantinya saat login pertama.</div></div>
                    <div class="col-12"><label class="form-label fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label><input required type="password" class="form-control" name="password_confirmation" minlength="8" autocomplete="new-password"></div>
                <?php else: ?>
                    <div class="col-12"><div class="alert alert-light border mb-0 small"><i class="bi bi-shield-lock me-1"></i>Password tidak diedit dari halaman profil. Gunakan menu <strong>Reset Password</strong> agar tercatat di audit trail.</div></div>
                <?php endif; ?>
            </div></div></div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-light border" href="<?= base_url('users') ?>">Batal</a><button class="btn btn-primary px-4"><i class="bi bi-check2-circle me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Buat Pegawai' ?></button></div>
    </form>
</div>
</body></html>
