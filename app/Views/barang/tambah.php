<?= view('layout/header', ['title' => 'Tambah Barang']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                Tambah Barang
            </h2>

            <p class="text-muted mb-0">
                Tambahkan barang baru ke dalam data inventaris.
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

            <!-- FORM TITLE -->
            <div class="d-flex align-items-center mb-4">

                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">

                    <i class="bi bi-plus-circle fs-4"></i>

                </div>

                <div>

                    <h5 class="fw-bold mb-1">
                        Informasi Barang
                    </h5>

                    <small class="text-muted">
                        Isi data barang yang akan ditambahkan.
                    </small>

                </div>

            </div>


            <form action="<?= base_url('/barang/simpan') ?>"
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
                                   value="<?= old('kode_barang') ?>"
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
                                   placeholder="Contoh: Indomie Goreng"
                                   value="<?= old('nama_barang') ?>"
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
                                   value="<?= old('harga_beli', 0) ?>"
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
                                   value="<?= old('harga_jual', 0) ?>"
                                   required>

                        </div>

                    </div>


                    <!-- STOK -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Stok Awal
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
                                   value="<?= old('stok', 0) ?>"
                                   required>

                        </div>

                        <small class="text-muted">
                            Masukkan jumlah stok awal barang.
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

                            <select name="satuan"
                                    class="form-select"
                                    required>

                                <option value="pcs"
                                    <?= old('satuan') == 'pcs' ? 'selected' : '' ?>>
                                    Pcs
                                </option>

                                <option value="box"
                                    <?= old('satuan') == 'box' ? 'selected' : '' ?>>
                                    Box
                                </option>

                                <option value="pack"
                                    <?= old('satuan') == 'pack' ? 'selected' : '' ?>>
                                    Pack
                                </option>

                                <option value="kg"
                                    <?= old('satuan') == 'kg' ? 'selected' : '' ?>>
                                    Kg
                                </option>

                                <option value="liter"
                                    <?= old('satuan') == 'liter' ? 'selected' : '' ?>>
                                    Liter
                                </option>

                                <option value="botol"
                                    <?= old('satuan') == 'botol' ? 'selected' : '' ?>>
                                    Botol
                                </option>

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
                        Simpan Barang

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>