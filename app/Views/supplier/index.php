<?= view('layout/header', ['title' => 'Data Supplier']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="page-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <h3 class="fw-bold mb-0">Data Supplier</h3>
            </div>

            <p class="text-muted mb-0">
                Kelola data supplier yang bekerja sama dengan toko
            </p>
        </div>

        <a href="<?= base_url('supplier/tambah') ?>" class="btn btn-primary btn-add">
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Supplier
        </a>
    </div>


    <!-- FLASH MESSAGE -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success custom-alert">
            <div class="alert-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>

            <div>
                <strong>Berhasil!</strong>
                <div><?= session()->getFlashdata('success') ?></div>
            </div>
        </div>
    <?php endif; ?>


    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger custom-alert">
            <div class="alert-icon">
                <i class="bi bi-exclamation-circle-fill"></i>
            </div>

            <div>
                <strong>Gagal!</strong>
                <div><?= session()->getFlashdata('error') ?></div>
            </div>
        </div>
    <?php endif; ?>


    <!-- SUMMARY -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="summary-card">

                <div class="summary-icon">
                    <i class="bi bi-truck"></i>
                </div>

                <div>
                    <small class="text-muted">Total Supplier</small>

                    <h4 class="fw-bold mb-0">
                        <?= count($supplier ?? []) ?>
                    </h4>
                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="summary-card">

                <div class="summary-icon blue">
                    <i class="bi bi-telephone"></i>
                </div>

                <div>
                    <small class="text-muted">Data Kontak</small>

                    <h4 class="fw-bold mb-0">
                        <?= count(array_filter($supplier ?? [], function ($row) {
                            return !empty($row['no_telp']);
                        })) ?>
                    </h4>
                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="summary-card">

                <div class="summary-icon green">
                    <i class="bi bi-building"></i>
                </div>

                <div>
                    <small class="text-muted">Data Alamat</small>

                    <h4 class="fw-bold mb-0">
                        <?= count(array_filter($supplier ?? [], function ($row) {
                            return !empty($row['alamat']);
                        })) ?>
                    </h4>
                </div>

            </div>

        </div>

    </div>


    <!-- MAIN CARD -->
    <div class="card supplier-card border-0">

        <!-- CARD HEADER -->
        <div class="card-header-custom">

            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-list-ul me-2"></i>
                    Daftar Supplier
                </h5>

                <small class="text-muted">
                    Daftar supplier yang tersimpan dalam sistem
                </small>
            </div>

            <span class="supplier-count">
                <?= count($supplier ?? []) ?> Supplier
            </span>

        </div>


        <div class="card-body p-0">

            <!-- SEARCH -->
            <div class="search-wrapper">

                <form
                    action="<?= base_url('supplier') ?>"
                    method="get"
                >

                    <div class="search-box">

                        <i class="bi bi-search search-icon"></i>

                        <input
                            type="text"
                            name="keyword"
                            class="form-control"
                            placeholder="Cari nama supplier, nomor telepon, atau alamat..."
                            value="<?= esc($keyword ?? '') ?>"
                        >

                        <?php if (!empty($keyword)): ?>

                            <a
                                href="<?= base_url('supplier') ?>"
                                class="btn btn-light reset-btn"
                                title="Reset pencarian"
                            >
                                <i class="bi bi-x-lg"></i>
                            </a>

                        <?php endif; ?>

                        <button
                            class="btn btn-primary search-btn"
                            type="submit"
                        >
                            <i class="bi bi-search me-1"></i>
                            Cari
                        </button>

                    </div>

                </form>

            </div>


            <!-- TABLE -->
            <div class="table-responsive">

                <table class="table supplier-table align-middle mb-0">

                    <thead>

                        <tr>

                            <th width="70">
                                No
                            </th>

                            <th>
                                Supplier
                            </th>

                            <th width="190">
                                No. Telepon
                            </th>

                            <th>
                                Alamat
                            </th>

                            <th width="150" class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (!empty($supplier)): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($supplier as $row): ?>

                                <tr>

                                    <!-- NO -->
                                    <td>

                                        <span class="number-badge">
                                            <?= $no++ ?>
                                        </span>

                                    </td>


                                    <!-- SUPPLIER -->
                                    <td>

                                        <div class="supplier-info">

                                            <div class="supplier-avatar">
                                                <i class="bi bi-building"></i>
                                            </div>

                                            <div>

                                                <div class="supplier-name">
                                                    <?= esc($row['nama_supplier']) ?>
                                                </div>

                                                <small class="text-muted">
                                                    ID Supplier #<?= esc($row['id_supplier']) ?>
                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- TELEPON -->
                                    <td>

                                        <?php if (!empty($row['no_telp'])): ?>

                                            <div class="contact-info">

                                                <div class="contact-icon">
                                                    <i class="bi bi-telephone"></i>
                                                </div>

                                                <span>
                                                    <?= esc($row['no_telp']) ?>
                                                </span>

                                            </div>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                <i class="bi bi-dash-lg"></i>
                                                Belum ada
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ALAMAT -->
                                    <td>

                                        <?php if (!empty($row['alamat'])): ?>

                                            <div class="address-info">

                                                <i class="bi bi-geo-alt"></i>

                                                <span>
                                                    <?= esc($row['alamat']) ?>
                                                </span>

                                            </div>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                <i class="bi bi-dash-lg"></i>
                                                Belum ada alamat
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- AKSI -->
                                    <td>

                                        <div class="action-buttons">

                                            <a
                                                href="<?= base_url('supplier/edit/' . $row['id_supplier']) ?>"
                                                class="action-btn edit-btn"
                                                title="Edit supplier"
                                            >
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <form method="post" action="<?= base_url('supplier/hapus/' . $row['id_supplier']) ?>" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">
                                                <?= csrf_field() ?>
                                                <button class="action-btn delete-btn border-0" title="Hapus supplier"><i class="bi bi-trash"></i></button>
                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                        <?php else: ?>

                            <!-- EMPTY STATE -->
                            <tr>

                                <td
                                    colspan="5"
                                    class="empty-state"
                                >

                                    <div class="empty-icon">
                                        <i class="bi bi-truck"></i>
                                    </div>

                                    <h6 class="fw-bold mt-3 mb-1">
                                        Belum Ada Supplier
                                    </h6>

                                    <p class="text-muted mb-3">
                                        Belum ada data supplier yang tersimpan.
                                    </p>

                                    <a
                                        href="<?= base_url('supplier/tambah') ?>"
                                        class="btn btn-primary btn-sm"
                                    >
                                        <i class="bi bi-plus-lg me-1"></i>
                                        Tambah Supplier
                                    </a>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

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
   ADD BUTTON
================================ */

.btn-add {
    border-radius: 10px;

    padding: 10px 17px;

    font-weight: 600;

    box-shadow:
        0 5px 15px rgba(13, 110, 253, .18);
}


/* ================================
   ALERT
================================ */

.custom-alert {
    border: none;
    border-radius: 12px;

    display: flex;
    align-items: center;

    gap: 12px;

    padding: 14px 16px;

    box-shadow:
        0 4px 14px rgba(0,0,0,.05);
}

.custom-alert .alert-icon {
    font-size: 22px;
}


/* ================================
   SUMMARY CARD
================================ */

.summary-card {
    background: #fff;

    border-radius: 14px;

    padding: 18px;

    display: flex;
    align-items: center;

    gap: 14px;

    border: 1px solid #edf0f4;

    box-shadow:
        0 5px 18px rgba(0,0,0,.04);

    transition: .2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);

    box-shadow:
        0 8px 22px rgba(0,0,0,.07);
}


.summary-icon {
    width: 46px;
    height: 46px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: #fff3cd;

    color: #856404;

    font-size: 20px;
}

.summary-icon.blue {
    background: #e7f1ff;
    color: #0d6efd;
}

.summary-icon.green {
    background: #e8f7ee;
    color: #198754;
}


/* ================================
   MAIN CARD
================================ */

.supplier-card {
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

    display: flex;
    align-items: center;
    justify-content: space-between;

    border-bottom: 1px solid #edf0f4;
}

.card-header-custom h5 {
    font-size: 16px;
}


.supplier-count {
    background: #f0f5ff;

    color: #0d6efd;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;
}


/* ================================
   SEARCH
================================ */

.search-wrapper {
    padding: 18px 22px;

    border-bottom: 1px solid #edf0f4;

    background: #fafbfc;
}


.search-box {
    position: relative;

    display: flex;
    align-items: center;
}


.search-box .form-control {
    height: 45px;

    border-radius: 10px;

    padding-left: 42px;
    padding-right: 105px;

    border: 1px solid #dfe3e8;

    font-size: 14px;

    box-shadow: none;
}

.search-box .form-control:focus {
    border-color: #86b7fe;

    box-shadow:
        0 0 0 .2rem rgba(13,110,253,.08);
}


.search-icon {
    position: absolute;

    left: 15px;

    color: #8b95a1;

    z-index: 3;
}


.search-btn {
    position: absolute;

    right: 5px;

    height: 35px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 600;
}


.reset-btn {
    position: absolute;

    right: 80px;

    z-index: 4;

    width: 32px;
    height: 32px;

    padding: 0;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 8px;

    color: #6c757d;
}


/* ================================
   TABLE
================================ */

.supplier-table {
    font-size: 14px;
}


.supplier-table thead th {
    background: #f8f9fb;

    color: #6c757d;

    font-size: 12px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .4px;

    padding: 14px 18px;

    border-bottom: 1px solid #e9ecef;
}


.supplier-table tbody td {
    padding: 15px 18px;

    border-bottom: 1px solid #f0f1f3;
}


.supplier-table tbody tr {
    transition: .15s ease;
}


.supplier-table tbody tr:hover {
    background: #fafcff;
}


/* ================================
   NUMBER
================================ */

.number-badge {
    width: 28px;
    height: 28px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border-radius: 8px;

    background: #f1f3f5;

    color: #495057;

    font-size: 12px;

    font-weight: 600;
}


/* ================================
   SUPPLIER INFO
================================ */

.supplier-info {
    display: flex;

    align-items: center;

    gap: 12px;
}


.supplier-avatar {
    width: 40px;
    height: 40px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 10px;

    background: #fff3cd;

    color: #856404;

    font-size: 17px;

    flex-shrink: 0;
}


.supplier-name {
    font-weight: 600;

    color: #212529;

    margin-bottom: 2px;
}


/* ================================
   CONTACT
================================ */

.contact-info {
    display: flex;

    align-items: center;

    gap: 8px;

    color: #495057;
}


.contact-icon {
    width: 28px;
    height: 28px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 7px;

    background: #e7f1ff;

    color: #0d6efd;

    font-size: 13px;
}


/* ================================
   ADDRESS
================================ */

.address-info {
    display: flex;

    align-items: flex-start;

    gap: 8px;

    color: #495057;

    line-height: 1.5;
}

.address-info i {
    color: #6c757d;

    margin-top: 2px;

    flex-shrink: 0;
}


/* ================================
   ACTION
================================ */

.action-buttons {
    display: flex;

    justify-content: center;

    gap: 7px;
}


.action-btn {
    width: 34px;
    height: 34px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border-radius: 9px;

    text-decoration: none;

    transition: .2s ease;

    font-size: 14px;
}


.edit-btn {
    color: #0d6efd;

    background: #e7f1ff;
}


.edit-btn:hover {
    background: #0d6efd;

    color: #fff;

    transform: translateY(-1px);
}


.delete-btn {
    color: #dc3545;

    background: #fdebed;
}


.delete-btn:hover {
    background: #dc3545;

    color: #fff;

    transform: translateY(-1px);
}


/* ================================
   EMPTY STATE
================================ */

.empty-state {
    padding: 55px 20px !important;

    text-align: center;
}


.empty-icon {
    width: 65px;
    height: 65px;

    margin: 0 auto;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 16px;

    background: #f1f3f5;

    color: #adb5bd;

    font-size: 27px;
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

    .btn-add {
        white-space: nowrap;
    }

    .summary-card {
        padding: 15px;
    }

    .card-header-custom {
        padding: 17px;
    }

    .search-wrapper {
        padding: 15px;
    }

    .supplier-table thead th,
    .supplier-table tbody td {
        padding: 12px;
    }

}

</style>