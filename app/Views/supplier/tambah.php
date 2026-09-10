<?= view('layout/header', ['title' => 'Tambah Supplier']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <div class="mb-4">
        <h3 class="fw-bold mb-1">Tambah Supplier</h3>
        <p class="text-muted mb-0">Tambahkan supplier baru</p>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>

        <div class="alert alert-danger">

            <ul class="mb-0">

                <?php foreach (session()->getFlashdata('errors') as $error): ?>

                    <li><?= esc($error) ?></li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <form action="<?= base_url('supplier/simpan') ?>" method="post">

                <?= csrf_field() ?>

                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Nama Supplier
                    </label>

                    <input
                        type="text"
                        name="nama_supplier"
                        class="form-control"
                        placeholder="Masukkan nama supplier"
                        value="<?= old('nama_supplier') ?>"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        No. Telepon
                    </label>

                    <input
                        type="text"
                        name="no_telp"
                        class="form-control"
                        placeholder="Contoh: 081234567890"
                        value="<?= old('no_telp') ?>"
                    >

                </div>

                <div class="mb-4">

                    <label class="form-label fw-semibold">
                        Alamat
                    </label>

                    <textarea
                        name="alamat"
                        class="form-control"
                        rows="4"
                        placeholder="Masukkan alamat supplier"
                    ><?= old('alamat') ?></textarea>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="<?= base_url('supplier') ?>"
                        class="btn btn-secondary"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Kembali
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-save"></i>
                        Simpan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>