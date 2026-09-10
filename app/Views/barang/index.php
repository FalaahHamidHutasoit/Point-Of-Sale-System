<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?= esc($title) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body>

<div class="container py-5">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                <i class="bi bi-box-seam"></i>
                Data Barang
            </h2>

            <p class="text-muted mb-0">
                Kelola data barang dan stok
            </p>

        </div>

        <div>

            <a href="<?= base_url('/dashboard') ?>"
               class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>
                Dashboard

            </a>

            <a href="<?= base_url('/barang/tambah') ?>"
               class="btn btn-primary">

                <i class="bi bi-plus-lg"></i>
                Tambah Barang

            </a>

        </div>

    </div>


    <!-- FLASH SUCCESS -->
    <?php if (session()->getFlashdata('success')) : ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i class="bi bi-check-circle"></i>

            <?= session()->getFlashdata('success') ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <!-- FLASH ERROR -->
    <?php if (session()->getFlashdata('error')) : ?>

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="bi bi-exclamation-circle"></i>

            <?= session()->getFlashdata('error') ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <!-- SEARCH -->
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="get"
                  action="<?= base_url('/barang') ?>">

                <div class="row g-2">

                    <div class="col-md-10">

                        <input type="text"
                               name="keyword"
                               class="form-control"
                               placeholder="Cari kode, nama barang, atau kategori..."
                               value="<?= esc($keyword ?? '') ?>">

                    </div>

                    <div class="col-md-2">

                        <button type="submit"
                                class="btn btn-dark w-100">

                            <i class="bi bi-search"></i>
                            Cari

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- TABLE -->
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
                            <th>Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (!empty($barang)) : ?>

                        <?php $no = 1; ?>

                        <?php foreach ($barang as $row) : ?>

                            <tr>

                                <td>
                                    <?= $no++ ?>
                                </td>

                                <td>
                                    <span class="badge text-bg-secondary">
                                        <?= esc($row['kode_barang']) ?>
                                    </span>
                                </td>

                                <td class="fw-semibold">
                                    <?= esc($row['nama_barang']) ?>
                                </td>

                                <td>
                                    <?= esc($row['nama_kategori']) ?>
                                </td>

                                <td>
                                    Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?>
                                </td>

                                <td>
                                    Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?>
                                </td>

                                <td>

                                    <?php if ($row['stok'] <= 5) : ?>

                                        <span class="badge text-bg-danger">
                                            <?= esc($row['stok']) ?>
                                        </span>

                                    <?php else : ?>

                                        <span class="badge text-bg-success">
                                            <?= esc($row['stok']) ?>
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= esc($row['satuan']) ?>
                                </td>

                                <td>

                                    <a href="<?= base_url('/barang/edit/' . $row['id_barang']) ?>"
                                       class="btn btn-warning btn-sm">

                                        <i class="bi bi-pencil"></i>

                                    </a>

                                    <a href="<?= base_url('/barang/hapus/' . $row['id_barang']) ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Yakin ingin menghapus barang ini?')">

                                        <i class="bi bi-trash"></i>

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else : ?>

                        <tr>

                            <td colspan="9"
                                class="text-center text-muted py-4">

                                Belum ada data barang.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>