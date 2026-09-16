<?= view('layout/header', ['title' => 'Edit Barang']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                Edit Barang
            </h2>

            <p class="text-muted mb-0">
                Perbarui informasi barang yang tersedia.
            </p>
        </div>

        <a href="<?= base_url('/barang') ?>"
           class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>

    </div>


    <!-- ERROR -->
    <?php if (session()->getFlashdata('error')) : ?>

        <div class="alert alert-danger border-0 shadow-sm">

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            <?= session()->getFlashdata('error') ?>

        </div>

    <?php endif; ?>


    <!-- FORM CARD -->
    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-4">

                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">

                    <i class="bi bi-pencil-square fs-4"></i>

                </div>

                <div>

                    <h5 class="fw-bold mb-1">
                        Informasi Barang
                    </h5>

                    <small class="text-muted">
                        Silakan ubah data barang sesuai kebutuhan.
                    </small>

                </div>

            </div>


            <form action="<?= base_url('/barang/update/' . $barang['id_barang']) ?>"
                  method="post">

                <?= csrf_field() ?>


                <div class="row g-4">

                    <!-- KODE BARANG -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Kode Barang
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">
                                <i class="bi bi-upc-scan"></i>
                            </span>

                            <input type="text"
                                   name="kode_barang"
                                   class="form-control"
                                   placeholder="Contoh: BRG001"
                                   value="<?= old('kode_barang', $barang['kode_barang']) ?>"
                                   required>

                        </div>

                        <small class="text-muted">
                            Gunakan kode barang yang unik.
                        </small>

                    </div>


                    <!-- KATEGORI -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Kategori
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">
                                <i class="bi bi-tags"></i>
                            </span>

                            <select name="id_kategori"
                                    class="form-select"
                                    required>

                                <?php foreach ($kategori as $kat) : ?>

                                    <option value="<?= $kat['id_kategori'] ?>"
                                        <?= $barang['id_kategori'] == $kat['id_kategori'] ? 'selected' : '' ?>>

                                        <?= esc($kat['nama_kategori']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>


                    <!-- NAMA BARANG -->
                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Nama Barang
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">
                                <i class="bi bi-box-seam"></i>
                            </span>

                            <input type="text"
                                   name="nama_barang"
                                   class="form-control"
                                   placeholder="Masukkan nama barang"
                                   value="<?= old('nama_barang', $barang['nama_barang']) ?>"
                                   required>

                        </div>

                    </div>


                    <!-- HARGA BELI -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Harga Beli
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">
                                Rp
                            </span>

                            <input type="number"
                                   name="harga_beli"
                                   class="form-control"
                                   min="0"
                                   placeholder="0"
                                   value="<?= old('harga_beli', $barang['harga_beli']) ?>"
                                   required>

                        </div>

                    </div>


                    <!-- HARGA JUAL -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Harga Jual
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">
                                Rp
                            </span>

                            <input type="number"
                                   name="harga_jual"
                                   class="form-control"
                                   min="0"
                                   placeholder="0"
                                   value="<?= old('harga_jual', $barang['harga_jual']) ?>"
                                   required>

                        </div>

                    </div>


                    <!-- STOK -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Stok
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">
                                <i class="bi bi-stack"></i>
                            </span>

                            <input type="number"
                                   name="stok"
                                   class="form-control"
                                   min="0"
                                   placeholder="0"
                                   value="<?= old('stok', $barang['stok']) ?>"
                                   required>

                        </div>

                        <small class="text-muted">
                            Jumlah stok barang saat ini.
                        </small>

                    </div>


                    <!-- SATUAN -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Satuan
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">
                                <i class="bi bi-rulers"></i>
                            </span>

                            <?php
                            $satuan = [
                                'pcs',
                                'box',
                                'pack',
                                'kg',
                                'liter',
                                'botol'
                            ];
                            ?>

                            <select name="satuan"
                                    class="form-select"
                                    required>

                                <?php foreach ($satuan as $s) : ?>

                                    <option value="<?= $s ?>"
                                        <?= $barang['satuan'] == $s ? 'selected' : '' ?>>

                                        <?= ucfirst($s) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                </div>


                <!-- ACTION -->
                <div class="border-top mt-4 pt-4 d-flex justify-content-end gap-2">

                    <a href="<?= base_url('/barang') ?>"
                       class="btn btn-light border">

                        <i class="bi bi-x-lg me-1"></i>
                        Batal

                    </a>

                    <button type="submit"
                            class="btn btn-primary px-4">

                        <i class="bi bi-save me-1"></i>
                        Update Barang

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>