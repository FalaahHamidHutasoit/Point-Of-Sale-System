<?= view('layout/header', ['title' => 'Edit Supplier']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <div class="d-flex align-items-center gap-2 mb-2">

                <div class="page-icon">
                    <i class="bi bi-pencil-square"></i>
                </div>

                <h3 class="fw-bold mb-0">
                    Edit Supplier
                </h3>

            </div>

            <p class="text-muted mb-0">
                Perbarui informasi supplier yang tersimpan di sistem
            </p>
        </div>

        <a
            href="<?= base_url('supplier') ?>"
            class="btn btn-light btn-back"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>

    </div>


    <!-- ERROR -->
    <?php if (session()->getFlashdata('errors')): ?>

        <div class="alert alert-danger custom-alert">

            <div class="alert-icon">
                <i class="bi bi-exclamation-circle-fill"></i>
            </div>

            <div>

                <strong>
                    Data belum dapat diperbarui
                </strong>

                <ul class="mb-0 mt-1 ps-3">

                    <?php foreach (session()->getFlashdata('errors') as $error): ?>

                        <li>
                            <?= esc($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        </div>

    <?php endif; ?>


    <div class="row g-4">

        <!-- FORM -->
        <div class="col-lg-8">

            <div class="card form-card border-0">

                <div class="card-header-custom">

                    <div>

                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-building me-2"></i>
                            Informasi Supplier
                        </h5>

                        <small class="text-muted">
                            Ubah informasi supplier sesuai data terbaru
                        </small>

                    </div>

                    <span class="supplier-id">
                        ID #<?= esc($supplier['id_supplier']) ?>
                    </span>

                </div>


                <div class="card-body p-4">

                    <form
                        action="<?= base_url('supplier/update/' . $supplier['id_supplier']) ?>"
                        method="post"
                    >

                        <?= csrf_field() ?>


                        <!-- NAMA -->
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Nama Supplier
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group-custom">

                                <span class="input-icon">
                                    <i class="bi bi-building"></i>
                                </span>

                                <input
                                    type="text"
                                    name="nama_supplier"
                                    class="form-control"
                                    placeholder="Masukkan nama supplier"
                                    value="<?= old('nama_supplier', $supplier['nama_supplier']) ?>"
                                    required
                                    autofocus
                                >

                            </div>

                            <small class="text-muted">
                                Pastikan nama supplier sesuai dengan data terbaru.
                            </small>

                        </div>


                        <!-- TELEPON -->
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                No. Telepon
                            </label>

                            <div class="input-group-custom">

                                <span class="input-icon">
                                    <i class="bi bi-telephone"></i>
                                </span>

                                <input
                                    type="text"
                                    name="no_telp"
                                    class="form-control"
                                    placeholder="Contoh: 081234567890"
                                    value="<?= old('no_telp', $supplier['no_telp']) ?>"
                                >

                            </div>

                            <small class="text-muted">
                                Perbarui nomor telepon jika terdapat perubahan.
                            </small>

                        </div>


                        <!-- ALAMAT -->
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Alamat
                            </label>

                            <div class="textarea-wrapper">

                                <span class="textarea-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </span>

                                <textarea
                                    name="alamat"
                                    class="form-control"
                                    rows="5"
                                    placeholder="Masukkan alamat lengkap supplier"
                                ><?= old('alamat', $supplier['alamat']) ?></textarea>

                            </div>

                            <small class="text-muted">
                                Perbarui alamat supplier jika diperlukan.
                            </small>

                        </div>


                        <!-- BUTTON -->
                        <div class="form-actions">

                            <a
                                href="<?= base_url('supplier') ?>"
                                class="btn btn-light btn-cancel"
                            >
                                <i class="bi bi-x-lg me-1"></i>
                                Batal
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary btn-save"
                            >
                                <i class="bi bi-check-lg me-1"></i>
                                Simpan Perubahan
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        <!-- SIDE INFO -->
        <div class="col-lg-4">

            <div class="current-data-card mb-4">

                <div class="current-icon">
                    <i class="bi bi-person-vcard"></i>
                </div>

                <small class="text-muted d-block mb-1">
                    Supplier yang sedang diedit
                </small>

                <h5 class="fw-bold mb-1">
                    <?= esc($supplier['nama_supplier']) ?>
                </h5>

                <span class="supplier-id-badge">
                    ID Supplier #<?= esc($supplier['id_supplier']) ?>
                </span>

            </div>


            <div class="info-card">

                <div class="info-card-icon">
                    <i class="bi bi-info-circle"></i>
                </div>

                <h6 class="fw-bold mb-2">
                    Informasi
                </h6>

                <p class="text-muted small mb-3">
                    Pastikan perubahan data supplier sudah sesuai
                    sebelum menyimpan perubahan.
                </p>

                <div class="info-item">

                    <i class="bi bi-check-circle-fill"></i>

                    <span>
                        Nama supplier wajib diisi.
                    </span>

                </div>

                <div class="info-item">

                    <i class="bi bi-check-circle-fill"></i>

                    <span>
                        Nomor telepon dapat diperbarui.
                    </span>

                </div>

                <div class="info-item">

                    <i class="bi bi-check-circle-fill"></i>

                    <span>
                        Alamat dapat diperbarui.
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>


<?= view('layout/footer') ?>


<style>

/* ================================
   PAGE ICON
================================ */

.page-icon {
    width: 42px;
    height: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: linear-gradient(
        135deg,
        #0d6efd,
        #3d8bfd
    );

    color: #fff;

    border-radius: 12px;

    font-size: 19px;

    box-shadow:
        0 6px 16px rgba(13, 110, 253, .20);
}


/* ================================
   BACK BUTTON
================================ */

.btn-back {
    border: 1px solid #e1e5ea;

    color: #495057;

    border-radius: 10px;

    padding: 9px 15px;

    font-weight: 500;
}

.btn-back:hover {
    background: #f1f3f5;

    color: #212529;
}


/* ================================
   ALERT
================================ */

.custom-alert {
    border: none;

    border-radius: 12px;

    display: flex;

    align-items: flex-start;

    gap: 12px;

    padding: 15px 17px;

    box-shadow:
        0 4px 14px rgba(0,0,0,.05);
}

.custom-alert .alert-icon {
    font-size: 21px;

    margin-top: 1px;
}


/* ================================
   FORM CARD
================================ */

.form-card {
    border-radius: 16px;

    background: #fff;

    box-shadow:
        0 6px 24px rgba(0,0,0,.05);

    overflow: hidden;
}


/* ================================
   CARD HEADER
================================ */

.card-header-custom {
    padding: 20px 22px;

    border-bottom: 1px solid #edf0f4;

    background: #fff;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;
}

.card-header-custom h5 {
    font-size: 16px;
}


/* ================================
   SUPPLIER ID
================================ */

.supplier-id {
    background: #f1f3f5;

    color: #6c757d;

    padding: 7px 11px;

    border-radius: 8px;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;
}


/* ================================
   INPUT
================================ */

.input-group-custom {
    position: relative;

    display: flex;

    align-items: center;
}


.input-icon {
    position: absolute;

    left: 14px;

    width: 20px;

    color: #8b95a1;

    z-index: 3;

    text-align: center;
}


.input-group-custom .form-control {
    height: 46px;

    padding-left: 44px;

    border: 1px solid #dfe3e8;

    border-radius: 10px;

    font-size: 14px;

    box-shadow: none;
}


.input-group-custom .form-control:focus {
    border-color: #86b7fe;

    box-shadow:
        0 0 0 .2rem rgba(13,110,253,.08);
}


/* ================================
   TEXTAREA
================================ */

.textarea-wrapper {
    position: relative;
}


.textarea-icon {
    position: absolute;

    left: 14px;

    top: 13px;

    color: #8b95a1;

    z-index: 3;
}


.textarea-wrapper textarea {
    padding-left: 44px;

    border: 1px solid #dfe3e8;

    border-radius: 10px;

    font-size: 14px;

    resize: vertical;

    box-shadow: none;
}


.textarea-wrapper textarea:focus {
    border-color: #86b7fe;

    box-shadow:
        0 0 0 .2rem rgba(13,110,253,.08);
}


/* ================================
   LABEL
================================ */

.form-label {
    font-size: 14px;

    color: #343a40;

    margin-bottom: 8px;
}


/* ================================
   FORM ACTION
================================ */

.form-actions {
    display: flex;

    justify-content: flex-end;

    gap: 10px;

    padding-top: 8px;

    border-top: 1px solid #edf0f4;
}


.btn-cancel {
    border: 1px solid #dee2e6;

    border-radius: 10px;

    padding: 10px 16px;

    font-weight: 500;
}


.btn-save {
    border-radius: 10px;

    padding: 10px 18px;

    font-weight: 600;

    box-shadow:
        0 5px 15px rgba(13,110,253,.18);
}


/* ================================
   CURRENT DATA
================================ */

.current-data-card {
    padding: 22px;

    border-radius: 16px;

    background: linear-gradient(
        135deg,
        #fff8e1,
        #fff3cd
    );

    border: 1px solid #ffe69c;
}


.current-icon {
    width: 45px;
    height: 45px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #fff;

    color: #856404;

    border-radius: 11px;

    font-size: 20px;

    margin-bottom: 15px;

    box-shadow:
        0 4px 12px rgba(133,100,4,.08);
}


.supplier-id-badge {
    display: inline-block;

    background: rgba(255,255,255,.75);

    color: #856404;

    padding: 5px 9px;

    border-radius: 7px;

    font-size: 11px;

    font-weight: 600;
}


/* ================================
   INFO CARD
================================ */

.info-card {
    padding: 22px;

    background: #f8f9fb;

    border: 1px solid #edf0f4;

    border-radius: 16px;
}


.info-card-icon {
    width: 42px;
    height: 42px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #e7f1ff;

    color: #0d6efd;

    border-radius: 11px;

    font-size: 19px;

    margin-bottom: 15px;
}


.info-item {
    display: flex;

    align-items: flex-start;

    gap: 9px;

    font-size: 13px;

    color: #495057;

    margin-bottom: 10px;
}


.info-item:last-child {
    margin-bottom: 0;
}


.info-item i {
    color: #198754;

    margin-top: 2px;
}


/* ================================
   RESPONSIVE
================================ */

@media (max-width: 768px) {

    .main-content {
        padding: 20px;
    }

    .d-flex.justify-content-between {
        align-items: flex-start !important;

        gap: 15px;
    }

    .btn-back {
        white-space: nowrap;
    }

    .form-card .card-body {
        padding: 20px !important;
    }

    .card-header-custom {
        align-items: flex-start;

        flex-direction: column;
    }

    .form-actions {
        justify-content: stretch;
    }

    .form-actions .btn {
        flex: 1;
    }

}

</style>