<?= view('layout/header', ['title' => 'Tambah Kategori']) ?>

<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="page-header mb-4">

        <div class="d-flex align-items-center gap-3">

            <div class="page-icon">
                <i class="bi bi-tags-fill"></i>
            </div>

            <div>
                <h2 class="fw-bold mb-1">
                    Tambah Kategori
                </h2>

                <p class="text-muted mb-0">
                    Tambahkan kategori barang baru ke dalam sistem
                </p>
            </div>

        </div>

        <a href="<?= base_url('/kategori') ?>"
           class="btn btn-light border btn-action">

            <i class="bi bi-arrow-left me-1"></i>
            Kembali

        </a>

    </div>


    <!-- ALERT ERROR -->
    <?php if (session()->getFlashdata('error')) : ?>

        <div class="alert alert-danger alert-dismissible fade show custom-alert"
             role="alert">

            <div class="d-flex align-items-center">

                <div class="alert-icon error-icon">
                    <i class="bi bi-exclamation-lg"></i>
                </div>

                <div>
                    <strong>Gagal menyimpan!</strong>

                    <div>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                </div>

            </div>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <!-- FORM CARD -->
    <div class="row">

        <div class="col-lg-8">

            <div class="card category-form-card border-0">

                <div class="card-body p-4 p-lg-5">

                    <div class="form-title mb-4">

                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-pencil-square text-primary me-2"></i>
                            Informasi Kategori
                        </h5>

                        <small class="text-muted">
                            Isi data kategori dengan lengkap
                        </small>

                    </div>


                    <form action="<?= base_url('/kategori/simpan') ?>"
                          method="post">

                        <?= csrf_field() ?>


                        <!-- NAMA KATEGORI -->
                        <div class="mb-4">

                            <label for="nama_kategori"
                                   class="form-label fw-semibold">

                                Nama Kategori

                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group custom-input">

                                <span class="input-group-text">
                                    <i class="bi bi-tag"></i>
                                </span>

                                <input type="text"
                                       id="nama_kategori"
                                       name="nama_kategori"
                                       class="form-control"
                                       placeholder="Contoh: Makanan"
                                       value="<?= old('nama_kategori') ?>"
                                       autocomplete="off"
                                       required>

                            </div>

                            <div class="form-text">
                                Contoh: Makanan, Minuman, Sembako, Snack, dan lainnya.
                            </div>

                        </div>


                        <!-- BUTTON -->
                        <div class="d-flex justify-content-end gap-2 pt-2">

                            <a href="<?= base_url('/kategori') ?>"
                               class="btn btn-light border btn-form">

                                <i class="bi bi-x-lg me-1"></i>
                                Batal

                            </a>

                            <button type="submit"
                                    class="btn btn-primary btn-form">

                                <i class="bi bi-check-lg me-1"></i>
                                Simpan Kategori

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        <!-- INFO CARD -->
        <div class="col-lg-4 mt-4 mt-lg-0">

            <div class="card info-card border-0">

                <div class="card-body p-4">

                    <div class="info-icon mb-3">
                        <i class="bi bi-lightbulb"></i>
                    </div>

                    <h6 class="fw-bold">
                        Informasi
                    </h6>

                    <p class="text-muted small mb-3">
                        Kategori digunakan untuk mengelompokkan barang
                        agar data produk lebih mudah dikelola.
                    </p>

                    <div class="info-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <span>
                            Gunakan nama kategori yang jelas
                        </span>

                    </div>

                    <div class="info-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <span>
                            Hindari nama kategori yang sama
                        </span>

                    </div>

                    <div class="info-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <span>
                            Pastikan nama mudah dikenali
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

/* ================================
   HEADER
================================ */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

}

.page-icon {

    width: 48px;
    height: 48px;

    border-radius: 12px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #eaf2ff;

    color: #0d6efd;

    font-size: 21px;

}

.btn-action {

    border-radius: 10px;

    padding: 9px 15px;

    font-weight: 500;

}


/* ================================
   ALERT
================================ */

.custom-alert {

    border: none;

    border-radius: 12px;

    padding: 14px 18px;

}

.alert-icon {

    width: 36px;
    height: 36px;

    border-radius: 10px;

    display: flex;

    align-items: center;
    justify-content: center;

    margin-right: 12px;

}

.error-icon {

    background: #f8d7da;

    color: #dc3545;

}


/* ================================
   FORM CARD
================================ */

.category-form-card {

    border-radius: 16px;

    box-shadow:
        0 5px 20px rgba(0, 0, 0, 0.05);

}

.form-title {

    padding-bottom: 15px;

    border-bottom: 1px solid #f1f5f9;

}


/* ================================
   INPUT
================================ */

.custom-input {

    border-radius: 10px;

    overflow: hidden;

}

.custom-input .input-group-text {

    background: #f8fafc;

    border: 1px solid #dee2e6;

    border-right: none;

    color: #64748b;

}

.custom-input .form-control {

    border-left: none;

    padding: 11px 13px;

    box-shadow: none;

}

.custom-input .form-control:focus {

    border-color: #86b7fe;

    box-shadow: none;

}

.custom-input:focus-within .input-group-text {

    border-color: #86b7fe;

    color: #0d6efd;

}


/* ================================
   FORM TEXT
================================ */

.form-text {

    font-size: 12px;

    margin-top: 7px;

    color: #94a3b8;

}


/* ================================
   BUTTON
================================ */

.btn-form {

    border-radius: 10px;

    padding: 10px 17px;

    font-weight: 500;

}


/* ================================
   INFO CARD
================================ */

.info-card {

    border-radius: 16px;

    background: #f8fafc;

    box-shadow:
        0 5px 20px rgba(0, 0, 0, 0.03);

}

.info-icon {

    width: 42px;
    height: 42px;

    border-radius: 11px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #fff3cd;

    color: #997404;

    font-size: 19px;

}

.info-item {

    display: flex;

    align-items: flex-start;

    gap: 9px;

    font-size: 13px;

    color: #64748b;

    margin-bottom: 12px;

}

.info-item i {

    color: #198754;

    margin-top: 2px;

}


/* ================================
   RESPONSIVE
================================ */

@media (max-width: 768px) {

    .page-header {

        align-items: flex-start;

        gap: 15px;

    }

    .page-header .btn-action {

        display: none;

    }

}

</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>