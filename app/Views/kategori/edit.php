<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Kategori</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4">

                    <h3 class="fw-bold mb-4">
                        Edit Kategori
                    </h3>


                    <?php if (session()->getFlashdata('error')) : ?>

                        <div class="alert alert-danger">

                            <?= session()->getFlashdata('error') ?>

                        </div>

                    <?php endif; ?>


                    <form action="<?= base_url('/kategori/update/' . $kategori['id_kategori']) ?>"
                          method="post">

                        <?= csrf_field() ?>


                        <div class="mb-3">

                            <label class="form-label">
                                Nama Kategori
                            </label>

                            <input type="text"
                                   name="nama_kategori"
                                   class="form-control"
                                   value="<?= esc($kategori['nama_kategori']) ?>"
                                   required>

                        </div>


                        <div class="d-flex gap-2">

                            <a href="<?= base_url('/kategori') ?>"
                               class="btn btn-secondary">

                                Kembali

                            </a>

                            <button type="submit"
                                    class="btn btn-primary">

                                Update

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>