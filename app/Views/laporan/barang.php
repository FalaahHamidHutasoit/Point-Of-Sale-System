<?= view('layout/header', ['title' => 'Laporan Barang']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-box-seam me-2 text-primary"></i>
                Laporan Barang
            </h2>

            <p class="text-muted mb-0">
                Informasi lengkap mengenai barang dan kondisi stok.
            </p>
        </div>

        <button onclick="window.print()"
                class="btn btn-dark px-4">

            <i class="bi bi-printer me-2"></i>
            Cetak Laporan

        </button>

    </div>


    <!-- SUMMARY -->
    <?php
        $totalBarang = (int) ($stats['total'] ?? 0);
        $stokTersedia = (int) ($stats['tersedia'] ?? 0);
        $stokMenipis = (int) ($stats['menipis'] ?? 0);
        $stokHabis = (int) ($stats['habis'] ?? 0);
    ?>

    <div class="row g-3 mb-4">

        <!-- TOTAL BARANG -->
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1 small">
                                Total Barang
                            </p>

                            <h3 class="fw-bold mb-0">
                                <?= $totalBarang ?>
                            </h3>

                        </div>

                        <div class="summary-icon bg-primary bg-opacity-10 text-primary">

                            <i class="bi bi-box-seam"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- TERSEDIA -->
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1 small">
                                Stok Tersedia
                            </p>

                            <h3 class="fw-bold mb-0">
                                <?= $stokTersedia ?>
                            </h3>

                        </div>

                        <div class="summary-icon bg-success bg-opacity-10 text-success">

                            <i class="bi bi-check-circle"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- MENIPIS -->
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1 small">
                                Stok Menipis
                            </p>

                            <h3 class="fw-bold mb-0">
                                <?= $stokMenipis ?>
                            </h3>

                        </div>

                        <div class="summary-icon bg-warning bg-opacity-10 text-warning">

                            <i class="bi bi-exclamation-triangle"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- HABIS -->
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1 small">
                                Stok Habis
                            </p>

                            <h3 class="fw-bold mb-0">
                                <?= $stokHabis ?>
                            </h3>

                        </div>

                        <div class="summary-icon bg-danger bg-opacity-10 text-danger">

                            <i class="bi bi-x-circle"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- FILTER -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-3">

                <div class="filter-icon me-3">

                    <i class="bi bi-funnel"></i>

                </div>

                <div>

                    <h5 class="fw-bold mb-0">
                        Filter Data
                    </h5>

                    <small class="text-muted">
                        Cari barang berdasarkan kode, nama, atau kategori.
                    </small>

                </div>

            </div>


            <form method="get"
                  action="<?= base_url('laporan/barang') ?>">

                <div class="row g-2">

                    <div class="col-md-10">

                        <div class="input-group">

                            <span class="input-group-text bg-light">

                                <i class="bi bi-search"></i>

                            </span>

                            <input
                                type="text"
                                name="keyword"
                                class="form-control"
                                placeholder="Cari kode barang, nama barang, atau kategori..."
                                value="<?= esc($keyword) ?>"
                            >

                        </div>

                    </div>

                    <div class="col-md-2">

                        <button type="submit"
                                class="btn btn-primary w-100">

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

        <div class="card-header bg-white border-0 p-4 pb-2">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="fw-bold mb-1">
                        Data Barang
                    </h5>

                    <small class="text-muted">
                        Daftar barang berdasarkan hasil pencarian.
                    </small>

                </div>

                <span class="badge bg-primary rounded-pill px-3 py-2">

                    <?= $totalBarang ?> Data

                </span>

            </div>

        </div>


        <div class="card-body p-4 pt-3">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>

                        <tr>

                            <th class="text-center">
                                No
                            </th>

                            <th>
                                Kode Barang
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

                            <th class="text-center">
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (!empty($barang)): ?>

                            <?php $no = isset($pager) ? (($pager->getCurrentPage('laporan_barang') - 1) * $pager->getPerPage('laporan_barang')) + 1 : 1; ?>

                            <?php foreach ($barang as $item): ?>

                                <tr>

                                    <!-- NO -->
                                    <td class="text-center text-muted">

                                        <?= $no++ ?>

                                    </td>


                                    <!-- KODE -->
                                    <td>

                                        <span class="badge bg-light text-dark border">

                                            <i class="bi bi-upc-scan me-1"></i>

                                            <?= esc($item['kode_barang']) ?>

                                        </span>

                                    </td>


                                    <!-- NAMA -->
                                    <td>

                                        <div class="d-flex align-items-center">

                                            <div class="product-icon me-2">

                                                <i class="bi bi-box"></i>

                                            </div>

                                            <span class="fw-semibold">

                                                <?= esc($item['nama_barang']) ?>

                                            </span>

                                        </div>

                                    </td>


                                    <!-- KATEGORI -->
                                    <td>

                                        <span class="badge bg-primary bg-opacity-10 text-primary">

                                            <i class="bi bi-tag me-1"></i>

                                            <?= esc($item['nama_kategori']) ?>

                                        </span>

                                    </td>


                                    <!-- HARGA BELI -->
                                    <td>

                                        Rp <?= number_format(
                                            $item['harga_beli'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </td>


                                    <!-- HARGA JUAL -->
                                    <td class="fw-semibold">

                                        Rp <?= number_format(
                                            $item['harga_jual'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </td>


                                    <!-- STOK -->
                                    <td class="text-center">

                                        <span class="fw-bold">

                                            <?= esc($item['stok']) ?>

                                        </span>

                                    </td>


                                    <!-- SATUAN -->
                                    <td>

                                        <?= esc(ucfirst($item['satuan'])) ?>

                                    </td>


                                    <!-- STATUS -->
                                    <td class="text-center">

                                        <?php if ($item['stok'] <= 0): ?>

                                            <span class="badge rounded-pill bg-danger px-3">

                                                <i class="bi bi-x-circle me-1"></i>
                                                Habis

                                            </span>

                                        <?php elseif ($item['stok'] <= 5): ?>

                                            <span class="badge rounded-pill bg-warning text-dark px-3">

                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Menipis

                                            </span>

                                        <?php else: ?>

                                            <span class="badge rounded-pill bg-success px-3">

                                                <i class="bi bi-check-circle me-1"></i>
                                                Tersedia

                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="9"
                                    class="text-center py-5">

                                    <div class="empty-icon mb-3">

                                        <i class="bi bi-inbox"></i>

                                    </div>

                                    <h6 class="fw-bold">
                                        Data Tidak Ditemukan
                                    </h6>

                                    <p class="text-muted mb-0">

                                        Tidak ada barang yang sesuai dengan pencarian.

                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <?= view('components/pagination', ['pager' => $pager ?? null, 'group' => 'laporan_barang', 'label' => 'barang']) ?>

        </div>

    </div>

</div>


<style>

.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 22px;
}

.filter-icon {
    width: 42px;
    height: 42px;

    border-radius: 10px;

    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 18px;
}

.product-icon {
    width: 34px;
    height: 34px;

    border-radius: 8px;

    background: #f1f3f5;

    display: flex;
    align-items: center;
    justify-content: center;

    color: #6c757d;
}

.empty-icon {
    width: 60px;
    height: 60px;

    margin: auto;

    border-radius: 15px;

    background: #f1f3f5;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 28px;
    color: #6c757d;
}


/* PRINT */

@media print {

    .sidebar,
    .btn,
    form,
    .filter-icon,
    .summary-icon,
    .product-icon {
        display: none !important;
    }

    .main-content {

        margin-left: 0 !important;
        padding: 10px !important;

    }

    body {

        background: white !important;

    }

    .card {

        box-shadow: none !important;
        border: none !important;

    }

    .card-header {

        padding-left: 0 !important;
        padding-right: 0 !important;

    }

    .table {

        font-size: 12px;

    }

    .table th,
    .table td {

        padding: 6px;

    }

    h2 {

        font-size: 22px;

    }

}

</style>


<?= view('layout/footer') ?>