<?= view('layout/header', ['title' => 'Edit Customer']) ?>

<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex align-items-center mb-4">

        <div class="page-icon me-3">
            <i class="bi bi-pencil-square"></i>
        </div>

        <div>
            <h2 class="fw-bold mb-1">
                Edit Customer
            </h2>

            <p class="text-muted mb-0">
                Perbarui informasi customer yang tersimpan
            </p>
        </div>

    </div>


    <!-- ERROR -->
    <?php if (session()->getFlashdata('error')) : ?>

        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm"
             role="alert">

            <i class="bi bi-exclamation-circle-fill me-2"></i>

            <?= session()->getFlashdata('error') ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <div class="row g-4">

        <!-- FORM -->
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-0 px-4 pt-4">

                    <h5 class="fw-bold mb-1">
                        Informasi Customer
                    </h5>

                    <small class="text-muted">
                        Silakan perbarui data customer di bawah ini.
                    </small>

                </div>


                <div class="card-body p-4">

                    <form action="<?= base_url('/customer/update/' . $customer['id_customer']) ?>"
                          method="post">

                        <?= csrf_field() ?>


                        <!-- NAMA -->
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Nama Customer
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>

                                <input type="text"
                                       name="nama_customer"
                                       class="form-control"
                                       placeholder="Masukkan nama customer"
                                       value="<?= old('nama_customer', $customer['nama_customer']) ?>"
                                       required>

                            </div>

                        </div>


                        <!-- TELEPON -->
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Nomor Telepon
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>

                                <input type="text"
                                       name="no_telp"
                                       class="form-control"
                                       placeholder="Contoh: 081234567890"
                                       value="<?= old('no_telp', $customer['no_telp']) ?>">

                            </div>

                            <small class="text-muted">
                                Nomor telepon bersifat opsional.
                            </small>

                        </div>


                        <!-- ALAMAT -->
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Alamat
                            </label>

                            <div class="input-group">

                                <span class="input-group-text align-items-start pt-3">
                                    <i class="bi bi-geo-alt"></i>
                                </span>

                                <textarea name="alamat"
                                          class="form-control"
                                          rows="4"
                                          placeholder="Masukkan alamat customer..."><?= old('alamat', $customer['alamat']) ?></textarea>

                            </div>

                        </div>


                        <!-- BUTTON -->
                        <div class="d-flex justify-content-between align-items-center pt-2">

                            <a href="<?= base_url('/customer') ?>"
                               class="btn btn-light border">

                                <i class="bi bi-arrow-left me-1"></i>
                                Kembali

                            </a>

                            <button type="submit"
                                    class="btn btn-primary px-4">

                                <i class="bi bi-save me-1"></i>
                                Update Customer

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        <!-- INFO -->
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <div class="info-icon mb-3">
                        <i class="bi bi-person-vcard"></i>
                    </div>

                    <h5 class="fw-bold">
                        Customer Terdaftar
                    </h5>

                    <p class="text-muted small mb-4">
                        Data customer yang tersimpan dapat digunakan
                        saat melakukan transaksi penjualan.
                    </p>


                    <div class="customer-info">

                        <div class="info-row">

                            <span class="text-muted">
                                ID Customer
                            </span>

                            <span class="fw-semibold">
                                #<?= esc($customer['id_customer']) ?>
                            </span>

                        </div>


                        <div class="info-row">

                            <span class="text-muted">
                                Status
                            </span>

                            <span class="badge bg-success-subtle text-success">
                                Aktif
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <div class="card border-0 shadow-sm mt-3">

                <div class="card-body p-4">

                    <div class="d-flex align-items-start">

                        <i class="bi bi-info-circle text-primary fs-5 me-3"></i>

                        <div>

                            <h6 class="fw-bold mb-1">
                                Catatan
                            </h6>

                            <small class="text-muted">
                                Pastikan nama customer sudah benar sebelum
                                menyimpan perubahan.
                            </small>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

/* PAGE ICON */
.page-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #eaf2ff;
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}


/* INPUT */
.input-group-text {
    background: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
}

.form-control {
    border-color: #dee2e6;
}

.form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .1);
}


/* BUTTON */
.btn {
    border-radius: 9px;
}


/* INFO */
.info-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: #eaf2ff;
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}


/* INFO ROW */
.customer-info {
    border-top: 1px solid #eee;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 13px 0;
    border-bottom: 1px solid #eee;
}

.info-row:last-child {
    border-bottom: none;
}


/* BADGE */
.badge {
    border-radius: 8px;
    padding: 6px 10px;
}

</style>


<?= view('layout/footer') ?>