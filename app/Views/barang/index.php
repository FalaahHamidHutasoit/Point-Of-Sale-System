<?= view('layout/header', ['title' => 'Data Barang']) ?>

<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="page-header mb-4">

        <div class="d-flex align-items-center gap-3">

            <div class="page-icon">
                <i class="bi bi-box-seam-fill"></i>
            </div>

            <div>
                <h2 class="fw-bold mb-1">
                    Data Barang
                </h2>

                <p class="text-muted mb-0">
                    Kelola data barang, harga, dan stok
                </p>
            </div>

        </div>

        <div class="d-flex gap-2">

            <a href="<?= base_url('/dashboard') ?>"
               class="btn btn-light border btn-action">

                <i class="bi bi-arrow-left me-1"></i>
                Dashboard

            </a>

            <a href="<?= base_url('/barang/tambah') ?>"
               class="btn btn-primary btn-action">

                <i class="bi bi-plus-lg me-1"></i>
                Tambah Barang

            </a>

        </div>

    </div>


    <!-- FLASH SUCCESS -->
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


    <!-- FLASH ERROR -->
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


    <!-- SEARCH CARD -->
    <div class="card search-card border-0 mb-4">

        <div class="card-body p-3">

            <form method="get"
                  action="<?= base_url('/barang') ?>">

                <div class="row g-2 align-items-center">

                    <div class="col-md">

                        <div class="search-wrapper">

                            <i class="bi bi-search search-icon"></i>

                            <input type="text"
                                   name="keyword"
                                   class="form-control search-input"
                                   placeholder="Cari kode, nama barang, atau kategori..."
                                   value="<?= esc($keyword ?? '') ?>">

                        </div>

                    </div>

                    <div class="col-md-auto">

                        <button type="submit"
                                class="btn btn-dark search-btn">

                            <i class="bi bi-search me-1"></i>
                            Cari

                        </button>

                    </div>

                    <?php if (!empty($keyword)) : ?>

                        <div class="col-md-auto">

                            <a href="<?= base_url('/barang') ?>"
                               class="btn btn-light border search-btn">

                                <i class="bi bi-x-lg me-1"></i>
                                Reset

                            </a>

                        </div>

                    <?php endif; ?>

                </div>

            </form>

        </div>

    </div>


    <!-- TABLE CARD -->
    <div class="card barang-card border-0">

        <!-- CARD HEADER -->
        <div class="card-header bg-white border-0 px-4 pt-4 pb-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="fw-bold mb-1">

                        <i class="bi bi-boxes text-primary me-2"></i>

                        Daftar Barang

                    </h5>

                    <small class="text-muted">
                        Data produk dan persediaan barang
                    </small>

                </div>

                <div class="barang-count">

                    <i class="bi bi-box-seam me-1"></i>

                    <?= !empty($barang) ? count($barang) : 0 ?>
                    Barang

                </div>

            </div>

        </div>


        <!-- TABLE -->
        <div class="card-body px-4 pb-4">

            <div class="table-responsive">

                <table class="table barang-table align-middle mb-0">

                    <thead>

                        <tr>

                            <th width="60">
                                #
                            </th>

                            <th>
                                Kode
                            </th>

                            <th>
                                Nama Barang
                            </th>

                            <th>
                                Kategori
                            </th>

                            <th>
                                Harga Beli
                            </th>

                            <th>
                                Harga Jual
                            </th>

                            <th class="text-center">
                                Stok
                            </th>

                            <th>
                                Satuan
                            </th>

                            <th width="100" class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (!empty($barang)) : ?>

                        <?php $no = 1; ?>

                        <?php foreach ($barang as $row) : ?>

                            <tr>

                                <!-- NOMOR -->
                                <td>

                                    <span class="number-badge">
                                        <?= $no++ ?>
                                    </span>

                                </td>


                                <!-- KODE -->
                                <td>

                                    <span class="kode-badge">
                                        <?= esc($row['kode_barang']) ?>
                                    </span>

                                </td>


                                <!-- NAMA -->
                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        <div class="product-icon">
                                            <i class="bi bi-box-seam"></i>
                                        </div>

                                        <div>

                                            <div class="product-name">
                                                <?= esc($row['nama_barang']) ?>
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- KATEGORI -->
                                <td>

                                    <span class="category-badge">

                                        <i class="bi bi-tag me-1"></i>

                                        <?= esc($row['nama_kategori']) ?>

                                    </span>

                                </td>


                                <!-- HARGA BELI -->
                                <td>

                                    <div class="price-text">

                                        Rp <?= number_format(
                                            $row['harga_beli'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </div>

                                </td>


                                <!-- HARGA JUAL -->
                                <td>

                                    <div class="price-jual">

                                        Rp <?= number_format(
                                            $row['harga_jual'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </div>

                                </td>


                                <!-- STOK -->
                                <td class="text-center">

                                    <?php if ($row['stok'] <= 5) : ?>

                                        <div class="stock-badge stock-low">

                                            <i class="bi bi-exclamation-circle-fill"></i>

                                            <?= esc($row['stok']) ?>

                                        </div>

                                        <small class="stock-status low-text">
                                            Menipis
                                        </small>

                                    <?php else : ?>

                                        <div class="stock-badge stock-safe">

                                            <i class="bi bi-check-circle-fill"></i>

                                            <?= esc($row['stok']) ?>

                                        </div>

                                        <small class="stock-status safe-text">
                                            Aman
                                        </small>

                                    <?php endif; ?>

                                </td>


                                <!-- SATUAN -->
                                <td>

                                    <span class="text-muted">
                                        <?= esc($row['satuan']) ?>
                                    </span>

                                </td>


                                <!-- AKSI -->
                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-2">

                                        <a href="<?= base_url('/barang/edit/' . $row['id_barang']) ?>"
                                           class="btn btn-outline-warning action-btn"
                                           title="Edit barang">

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <form method="post" action="<?= base_url('/barang/hapus/' . $row['id_barang']) ?>" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus barang ini?')">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-outline-danger action-btn" title="Hapus barang"><i class="bi bi-trash"></i></button>
                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>


                    <?php else : ?>

                        <!-- EMPTY STATE -->
                        <tr>

                            <td colspan="9">

                                <div class="empty-state">

                                    <div class="empty-icon">

                                        <i class="bi bi-box-seam"></i>

                                    </div>

                                    <h6 class="fw-bold mt-3 mb-1">
                                        Belum Ada Data Barang
                                    </h6>

                                    <p class="text-muted mb-3">

                                        <?= !empty($keyword)
                                            ? 'Barang yang kamu cari tidak ditemukan.'
                                            : 'Belum ada barang yang ditambahkan ke sistem.'
                                        ?>

                                    </p>

                                    <?php if (empty($keyword)) : ?>

                                        <a href="<?= base_url('/barang/tambah') ?>"
                                           class="btn btn-primary btn-sm">

                                            <i class="bi bi-plus-lg me-1"></i>
                                            Tambah Barang

                                        </a>

                                    <?php else : ?>

                                        <a href="<?= base_url('/barang') ?>"
                                           class="btn btn-light border btn-sm">

                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            Tampilkan Semua

                                        </a>

                                    <?php endif; ?>

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

.success-icon {

    background: #d1e7dd;

    color: #198754;

}

.error-icon {

    background: #f8d7da;

    color: #dc3545;

}


/* ================================
   SEARCH
================================ */

.search-card {

    border-radius: 14px;

    background: #ffffff;

    box-shadow:
        0 4px 18px rgba(0, 0, 0, 0.04);

}

.search-wrapper {

    position: relative;

}

.search-icon {

    position: absolute;

    left: 14px;

    top: 50%;

    transform: translateY(-50%);

    color: #94a3b8;

    z-index: 2;

}

.search-input {

    padding-left: 42px;

    height: 44px;

    border-radius: 10px;

    border: 1px solid #e2e8f0;

    box-shadow: none;

}

.search-input:focus {

    border-color: #86b7fe;

    box-shadow: none;

}

.search-btn {

    height: 44px;

    border-radius: 10px;

    padding-left: 18px;

    padding-right: 18px;

}


/* ================================
   TABLE CARD
================================ */

.barang-card {

    border-radius: 16px;

    box-shadow:
        0 5px 20px rgba(0, 0, 0, 0.05);

}

.barang-count {

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

.barang-table {

    min-width: 1100px;

}

.barang-table thead th {

    background: #f8fafc;

    color: #64748b;

    font-size: 11px;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    font-weight: 700;

    border-bottom: 1px solid #e2e8f0;

    padding: 14px 10px;

    white-space: nowrap;

}

.barang-table tbody td {

    padding: 15px 10px;

    border-bottom: 1px solid #f1f5f9;

}

.barang-table tbody tr {

    transition: 0.2s;

}

.barang-table tbody tr:hover {

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

    font-size: 12px;

    font-weight: 600;

}


/* ================================
   PRODUCT
================================ */

.product-icon {

    width: 36px;
    height: 36px;

    border-radius: 9px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #f1f5f9;

    color: #64748b;

}

.product-name {

    font-size: 13px;

    font-weight: 600;

    color: #1e293b;

}


/* ================================
   KODE
================================ */

.kode-badge {

    display: inline-block;

    padding: 6px 9px;

    border-radius: 7px;

    background: #f1f5f9;

    color: #475569;

    font-size: 11px;

    font-weight: 600;

    letter-spacing: 0.3px;

}


/* ================================
   CATEGORY
================================ */

.category-badge {

    display: inline-block;

    padding: 6px 9px;

    border-radius: 8px;

    background: #eaf2ff;

    color: #0d6efd;

    font-size: 11px;

    font-weight: 600;

}


/* ================================
   PRICE
================================ */

.price-text {

    color: #64748b;

    font-size: 12px;

    white-space: nowrap;

}

.price-jual {

    color: #198754;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;

}


/* ================================
   STOCK
================================ */

.stock-badge {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding: 5px 9px;

    border-radius: 8px;

    font-size: 12px;

    font-weight: 700;

}

.stock-low {

    background: #f8d7da;

    color: #dc3545;

}

.stock-safe {

    background: #d1e7dd;

    color: #198754;

}

.stock-status {

    display: block;

    font-size: 10px;

    margin-top: 3px;

}

.low-text {

    color: #dc3545;

}

.safe-text {

    color: #198754;

}


/* ================================
   ACTION
================================ */

.action-btn {

    width: 34px;
    height: 34px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border-radius: 8px;

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

}

</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>