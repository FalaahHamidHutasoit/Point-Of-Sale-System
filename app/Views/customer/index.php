<?= view('layout/header', ['title' => 'Data Customer']) ?>

<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="page-icon">
                    <i class="bi bi-people-fill"></i>
                </div>

                <h2 class="fw-bold mb-0">Data Customer</h2>
            </div>

            <p class="text-muted mb-0">
                Kelola data customer atau member toko
            </p>
        </div>

        <a href="<?= base_url('/customer/tambah') ?>"
           class="btn btn-primary px-3">

            <i class="bi bi-plus-lg me-1"></i>
            Tambah Customer

        </a>

    </div>


    <!-- FLASH MESSAGE -->
    <?php if (session()->getFlashdata('success')) : ?>

        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm"
             role="alert">

            <i class="bi bi-check-circle-fill me-2"></i>

            <?= session()->getFlashdata('success') ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


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


    <!-- SUMMARY -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card summary-card border-0 shadow-sm h-100">

                <div class="card-body d-flex align-items-center">

                    <div class="summary-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div class="ms-3">

                        <small class="text-muted">
                            Total Customer
                        </small>

                        <h4 class="fw-bold mb-0">
                            <?= count($customer) ?>
                        </h4>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-8">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body d-flex align-items-center">

                    <div>
                        <div class="fw-semibold mb-1">
                            <i class="bi bi-info-circle me-1"></i>
                            Informasi Customer
                        </div>

                        <small class="text-muted">
                            Data customer digunakan untuk mencatat pelanggan
                            terdaftar. Pelanggan biasa dapat menggunakan
                            <strong>Customer Umum</strong> saat melakukan transaksi.
                        </small>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- SEARCH -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form method="get"
                  action="<?= base_url('/customer') ?>">

                <div class="row g-3 align-items-end">

                    <div class="col-md-10">

                        <label class="form-label fw-semibold">
                            <i class="bi bi-search me-1"></i>
                            Cari Customer
                        </label>

                        <input type="text"
                               name="keyword"
                               class="form-control"
                               placeholder="Cari nama, nomor telepon, atau alamat..."
                               value="<?= esc($keyword ?? '') ?>">

                    </div>

                    <div class="col-md-2">

                        <button type="submit"
                                class="btn btn-dark w-100">

                            <i class="bi bi-search me-1"></i>
                            Cari

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- TABLE -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h5 class="fw-bold mb-1">
                        Daftar Customer
                    </h5>

                    <small class="text-muted">
                        Menampilkan <?= count($customer) ?> data customer
                    </small>
                </div>

                <span class="badge bg-primary-subtle text-primary px-3 py-2">
                    <i class="bi bi-person-check me-1"></i>
                    Customer Terdaftar
                </span>

            </div>

        </div>

        <div class="card-body px-4">

            <div class="table-responsive">

                <table class="table table-hover align-middle custom-table">

                    <thead>

                        <tr>

                            <th width="70">No</th>
                            <th>Customer</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th width="160" class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (!empty($customer)) : ?>

                        <?php $no = 1; ?>

                        <?php foreach ($customer as $row) : ?>

                            <tr>

                                <!-- NO -->
                                <td>

                                    <span class="number-badge">
                                        <?= $no++ ?>
                                    </span>

                                </td>


                                <!-- CUSTOMER -->
                                <td>

                                    <div class="d-flex align-items-center">

                                        <div class="customer-avatar">
                                            <i class="bi bi-person-fill"></i>
                                        </div>

                                        <div class="ms-3">

                                            <div class="fw-semibold">
                                                <?= esc($row['nama_customer']) ?>
                                            </div>

                                            <small class="text-muted">
                                                ID Customer #<?= esc($row['id_customer']) ?>
                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <!-- TELEPON -->
                                <td>

                                    <?php if (!empty($row['no_telp'])) : ?>

                                        <span>
                                            <i class="bi bi-telephone me-1 text-primary"></i>
                                            <?= esc($row['no_telp']) ?>
                                        </span>

                                    <?php else : ?>

                                        <span class="text-muted">
                                            -
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ALAMAT -->
                                <td>

                                    <?php if (!empty($row['alamat'])) : ?>

                                        <span>
                                            <i class="bi bi-geo-alt me-1 text-danger"></i>
                                            <?= esc($row['alamat']) ?>
                                        </span>

                                    <?php else : ?>

                                        <span class="text-muted">
                                            -
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- AKSI -->
                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= base_url('/customer/edit/' . $row['id_customer']) ?>"
                                           class="btn btn-outline-warning btn-sm"
                                           title="Edit Customer">

                                            <i class="bi bi-pencil"></i>

                                        </a>

                                        <form method="post" action="<?= base_url('/customer/hapus/' . $row['id_customer']) ?>" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus customer ini?')">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-outline-danger btn-sm" title="Hapus Customer"><i class="bi bi-trash"></i></button>
                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else : ?>

                        <tr>

                            <td colspan="5">

                                <div class="empty-state">

                                    <div class="empty-icon">
                                        <i class="bi bi-people"></i>
                                    </div>

                                    <h6 class="fw-bold mt-3">
                                        Belum Ada Customer
                                    </h6>

                                    <p class="text-muted mb-3">
                                        Belum terdapat data customer yang tersimpan.
                                    </p>

                                    <a href="<?= base_url('/customer/tambah') ?>"
                                       class="btn btn-primary btn-sm">

                                        <i class="bi bi-plus-lg me-1"></i>
                                        Tambah Customer

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

/* PAGE ICON */
.page-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #eaf2ff;
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}


/* SUMMARY */
.summary-card {
    transition: .2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
}

.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
}


/* TABLE */
.custom-table {
    margin-bottom: 0;
}

.custom-table thead th {
    background: #f8f9fa;
    color: #6c757d;
    font-size: 13px;
    font-weight: 600;
    border-bottom: 1px solid #dee2e6;
    padding: 14px 12px;
}

.custom-table tbody td {
    padding: 15px 12px;
    border-bottom: 1px solid #f0f0f0;
}

.custom-table tbody tr:last-child td {
    border-bottom: none;
}


/* CUSTOMER AVATAR */
.customer-avatar {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #eaf2ff;
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}


/* NUMBER */
.number-badge {
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: #f1f3f5;
    color: #495057;
    font-size: 13px;
    font-weight: 600;
}


/* BUTTON */
.btn {
    border-radius: 9px;
}

.btn-sm {
    border-radius: 8px;
}


/* EMPTY STATE */
.empty-state {
    text-align: center;
    padding: 55px 20px;
}

.empty-icon {
    width: 70px;
    height: 70px;
    margin: auto;
    border-radius: 20px;
    background: #f1f3f5;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
}

</style>


<?= view('layout/footer') ?>