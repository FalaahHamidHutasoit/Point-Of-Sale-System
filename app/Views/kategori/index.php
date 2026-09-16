<?= view('layout/header', ['title' => 'Data Kategori']) ?>

<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="page-header mb-4">

        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="page-icon">
                    <i class="bi bi-tags-fill"></i>
                </div>

                <div>
                    <h2 class="fw-bold mb-0">Data Kategori</h2>
                    <p class="text-muted mb-0">
                        Kelola kategori barang dalam sistem
                    </p>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">

            <a href="<?= base_url('/dashboard') ?>"
               class="btn btn-light border btn-action">

                <i class="bi bi-arrow-left"></i>
                Dashboard

            </a>

            <a href="<?= base_url('/kategori/tambah') ?>"
               class="btn btn-primary btn-action">

                <i class="bi bi-plus-lg"></i>
                Tambah Kategori

            </a>

        </div>

    </div>


    <!-- ALERT SUCCESS -->
    <?php if (session()->getFlashdata('success')) : ?>

        <div class="alert alert-success alert-dismissible fade show custom-alert"
             role="alert">

            <div class="d-flex align-items-center">

                <div class="alert-icon success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>

                <div>
                    <strong>Berhasil!</strong>
                    <div>
                        <?= session()->getFlashdata('success') ?>
                    </div>
                </div>

            </div>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <!-- ALERT ERROR -->
    <?php if (session()->getFlashdata('error')) : ?>

        <div class="alert alert-danger alert-dismissible fade show custom-alert"
             role="alert">

            <div class="d-flex align-items-center">

                <div class="alert-icon error-icon">
                    <i class="bi bi-exclamation-lg"></i>
                </div>

                <div>
                    <strong>Terjadi kesalahan!</strong>
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


    <!-- CARD -->
    <div class="card category-card border-0">

        <!-- CARD HEADER -->
        <div class="card-header bg-white border-0 px-4 pt-4 pb-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-list-ul me-2 text-primary"></i>
                        Daftar Kategori
                    </h5>

                    <small class="text-muted">
                        Daftar kategori barang yang tersedia
                    </small>

                </div>

                <div class="category-count">

                    <i class="bi bi-tags me-1"></i>

                    <?= !empty($kategori) ? count($kategori) : 0 ?>
                    Kategori

                </div>

            </div>

        </div>


        <!-- TABLE -->
        <div class="card-body px-4 pb-4">

            <div class="table-responsive">

                <table class="table category-table align-middle mb-0">

                    <thead>

                        <tr>

                            <th width="80">
                                #
                            </th>

                            <th>
                                Nama Kategori
                            </th>

                            <th width="180" class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (!empty($kategori)) : ?>

                        <?php $no = 1; ?>

                        <?php foreach ($kategori as $row) : ?>

                            <tr>

                                <!-- NOMOR -->
                                <td>

                                    <span class="number-badge">
                                        <?= $no++ ?>
                                    </span>

                                </td>


                                <!-- NAMA KATEGORI -->
                                <td>

                                    <div class="d-flex align-items-center gap-3">

                                        <div class="category-icon">
                                            <i class="bi bi-tag-fill"></i>
                                        </div>

                                        <div>

                                            <div class="fw-semibold category-name">
                                                <?= esc($row['nama_kategori']) ?>
                                            </div>

                                            <small class="text-muted">
                                                ID: <?= esc($row['id_kategori']) ?>
                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <!-- AKSI -->
                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-2">

                                        <a href="<?= base_url('/kategori/edit/' . $row['id_kategori']) ?>"
                                           class="btn btn-outline-warning btn-sm action-btn"
                                           title="Edit kategori">

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <a href="<?= base_url('/kategori/hapus/' . $row['id_kategori']) ?>"
                                           class="btn btn-outline-danger btn-sm action-btn"
                                           title="Hapus kategori"
                                           onclick="return confirm('Yakin ingin menghapus kategori ini?')">

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>


                    <?php else : ?>

                        <!-- EMPTY STATE -->
                        <tr>

                            <td colspan="3">

                                <div class="empty-state">

                                    <div class="empty-icon">
                                        <i class="bi bi-tags"></i>
                                    </div>

                                    <h6 class="fw-bold mt-3 mb-1">
                                        Belum Ada Kategori
                                    </h6>

                                    <p class="text-muted mb-3">
                                        Belum ada kategori barang yang ditambahkan.
                                    </p>

                                    <a href="<?= base_url('/kategori/tambah') ?>"
                                       class="btn btn-primary btn-sm">

                                        <i class="bi bi-plus-lg me-1"></i>
                                        Tambah Kategori

                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<style>

/* ================================
   PAGE HEADER
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


/* ================================
   BUTTON
================================ */

.btn-action {
    border-radius: 10px;
    padding: 9px 15px;
    font-weight: 500;
}

.btn-primary {
    border-radius: 10px;
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

.success-icon {
    background: #d1e7dd;
    color: #198754;
}

.error-icon {
    background: #f8d7da;
    color: #dc3545;
}


/* ================================
   CARD
================================ */

.category-card {
    border-radius: 16px;

    box-shadow:
        0 5px 20px rgba(0, 0, 0, 0.05);
}


/* ================================
   CATEGORY COUNT
================================ */

.category-count {
    background: #f1f5f9;
    color: #475569;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 13px;
    font-weight: 600;
}


/* ================================
   TABLE
================================ */

.category-table thead th {

    background: #f8fafc;

    color: #64748b;

    font-size: 12px;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    font-weight: 700;

    border-bottom: 1px solid #e2e8f0;

    padding: 14px 12px;
}

.category-table tbody td {

    padding: 16px 12px;

    border-bottom: 1px solid #f1f5f9;
}

.category-table tbody tr {

    transition: 0.2s;
}

.category-table tbody tr:hover {

    background: #f8fafc;
}


/* ================================
   NUMBER
================================ */

.number-badge {

    width: 30px;
    height: 30px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border-radius: 8px;

    background: #f1f5f9;

    color: #64748b;

    font-size: 13px;

    font-weight: 600;
}


/* ================================
   CATEGORY
================================ */

.category-icon {

    width: 38px;
    height: 38px;

    border-radius: 10px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #eaf2ff;

    color: #0d6efd;
}

.category-name {

    color: #1e293b;

    font-size: 14px;
}


/* ================================
   ACTION BUTTON
================================ */

.action-btn {

    width: 36px;
    height: 36px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border-radius: 9px;

    transition: 0.2s;
}

.action-btn:hover {

    transform: translateY(-2px);

}


/* ================================
   EMPTY STATE
================================ */

.empty-state {

    text-align: center;

    padding: 55px 20px;
}

.empty-icon {

    width: 70px;
    height: 70px;

    margin: auto;

    border-radius: 18px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #f1f5f9;

    color: #94a3b8;

    font-size: 30px;
}


/* ================================
   RESPONSIVE
================================ */

@media (max-width: 768px) {

    .page-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }

    .page-header > div:last-child {

        width: 100%;

    }

    .page-header .btn {

        flex: 1;

    }

    .category-card {

        border-radius: 12px;

    }

}

</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>