<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-tags"></i>
                Data Kategori
            </h2>

            <p class="text-muted mb-0">
                Kelola kategori barang
            </p>
        </div>

        <div>
            <a href="<?= base_url('/dashboard') ?>"
               class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Dashboard
            </a>

            <a href="<?= base_url('/kategori/tambah') ?>"
               class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Tambah Kategori
            </a>
        </div>

    </div>


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


    <div class="card shadow-sm border-0">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">

                        <tr>
                            <th width="80">No</th>
                            <th>Nama Kategori</th>
                            <th width="200">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if (!empty($kategori)) : ?>

                        <?php $no = 1; ?>

                        <?php foreach ($kategori as $row) : ?>

                            <tr>

                                <td><?= $no++ ?></td>

                                <td>
                                    <?= esc($row['nama_kategori']) ?>
                                </td>

                                <td>

                                    <a href="<?= base_url('/kategori/edit/' . $row['id_kategori']) ?>"
                                       class="btn btn-warning btn-sm">

                                        <i class="bi bi-pencil"></i>
                                        Edit

                                    </a>

                                    <a href="<?= base_url('/kategori/hapus/' . $row['id_kategori']) ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Yakin ingin menghapus kategori ini?')">

                                        <i class="bi bi-trash"></i>
                                        Hapus

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else : ?>

                        <tr>

                            <td colspan="3"
                                class="text-center text-muted py-4">

                                Belum ada data kategori.

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