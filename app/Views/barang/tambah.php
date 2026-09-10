<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Tambah Barang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4">

                    <h3 class="fw-bold mb-4">
                        Tambah Barang
                    </h3>


                    <?php if (session()->getFlashdata('error')) : ?>

                        <div class="alert alert-danger">

                            <?= session()->getFlashdata('error') ?>

                        </div>

                    <?php endif; ?>


                    <form action="<?= base_url('/barang/simpan') ?>"
                          method="post">

                        <?= csrf_field() ?>


                        <div class="row">

                            <!-- KODE -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Kode Barang
                                </label>

                                <input type="text"
                                       name="kode_barang"
                                       class="form-control"
                                       placeholder="Contoh: BRG001"
                                       value="<?= old('kode_barang') ?>"
                                       required>

                            </div>


                            <!-- KATEGORI -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Kategori
                                </label>

                                <select name="id_kategori"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        -- Pilih Kategori --
                                    </option>

                                    <?php foreach ($kategori as $kat) : ?>

                                        <option value="<?= $kat['id_kategori'] ?>"
                                            <?= old('id_kategori') == $kat['id_kategori'] ? 'selected' : '' ?>>

                                            <?= esc($kat['nama_kategori']) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- NAMA -->
                            <div class="col-md-12 mb-3">

                                <label class="form-label">
                                    Nama Barang
                                </label>

                                <input type="text"
                                       name="nama_barang"
                                       class="form-control"
                                       placeholder="Contoh: Indomie Goreng"
                                       value="<?= old('nama_barang') ?>"
                                       required>

                            </div>


                            <!-- HARGA BELI -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Harga Beli
                                </label>

                                <input type="number"
                                       name="harga_beli"
                                       class="form-control"
                                       min="0"
                                       value="<?= old('harga_beli', 0) ?>"
                                       required>

                            </div>


                            <!-- HARGA JUAL -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Harga Jual
                                </label>

                                <input type="number"
                                       name="harga_jual"
                                       class="form-control"
                                       min="0"
                                       value="<?= old('harga_jual', 0) ?>"
                                       required>

                            </div>


                            <!-- STOK -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Stok
                                </label>

                                <input type="number"
                                       name="stok"
                                       class="form-control"
                                       min="0"
                                       value="<?= old('stok', 0) ?>"
                                       required>

                            </div>


                            <!-- SATUAN -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Satuan
                                </label>

                                <select name="satuan"
                                        class="form-select"
                                        required>

                                    <option value="pcs">Pcs</option>
                                    <option value="box">Box</option>
                                    <option value="pack">Pack</option>
                                    <option value="kg">Kg</option>
                                    <option value="liter">Liter</option>
                                    <option value="botol">Botol</option>

                                </select>

                            </div>

                        </div>


                        <div class="mt-3">

                            <a href="<?= base_url('/barang') ?>"
                               class="btn btn-secondary">

                                Kembali

                            </a>

                            <button type="submit"
                                    class="btn btn-primary">

                                <i class="bi bi-save"></i>
                                Simpan Barang

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