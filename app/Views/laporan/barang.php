<?= view('layout/header') ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-box-seam"></i> Laporan Barang
            </h3>
            <p class="text-muted mb-0">
                Daftar seluruh barang yang tersedia
            </p>
        </div>

        <button onclick="window.print()" class="btn btn-dark">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>

    <!-- FILTER -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <form method="get" action="<?= base_url('laporan/barang') ?>">

                <div class="row g-2">

                    <div class="col-md-10">
                        <input
                            type="text"
                            name="keyword"
                            class="form-control"
                            placeholder="Cari kode barang, nama barang, atau kategori..."
                            value="<?= esc($keyword) ?>"
                        >
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>

    <!-- TABEL -->
    <div class="card shadow-sm border-0">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">

                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($barang)): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($barang as $item): ?>

                                <tr>

                                    <td><?= $no++ ?></td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= esc($item['kode_barang']) ?>
                                        </span>
                                    </td>

                                    <td class="fw-semibold">
                                        <?= esc($item['nama_barang']) ?>
                                    </td>

                                    <td>
                                        <?= esc($item['nama_kategori']) ?>
                                    </td>

                                    <td>
                                        Rp <?= number_format($item['harga_beli'], 0, ',', '.') ?>
                                    </td>

                                    <td>
                                        Rp <?= number_format($item['harga_jual'], 0, ',', '.') ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= esc($item['stok']) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= esc($item['satuan']) ?>
                                    </td>

                                    <td>

                                        <?php if ($item['stok'] <= 0): ?>

                                            <span class="badge bg-danger">
                                                Habis
                                            </span>

                                        <?php elseif ($item['stok'] <= 5): ?>

                                            <span class="badge bg-warning text-dark">
                                                Stok Menipis
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-success">
                                                Tersedia
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="9" class="text-center py-4">

                                    <i class="bi bi-inbox fs-2 text-muted"></i>

                                    <p class="text-muted mb-0 mt-2">
                                        Data barang tidak ditemukan.
                                    </p>

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

@media print {

    .sidebar,
    .btn,
    form,
    .sidebar-link,
    button {
        display: none !important;
    }

    .main-content {
        margin-left: 0 !important;
        padding: 10px !important;
    }

    .card {
        box-shadow: none !important;
        border: none !important;
    }

    body {
        background: white !important;
    }

}

</style>

<?= view('layout/footer') ?>