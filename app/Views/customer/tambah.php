<?= view('layout/header', ['title' => 'Tambah Customer']) ?>

<?= view('layout/sidebar') ?>

<div class="main-content">

    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h3 class="fw-bold mb-4">
                        <i class="bi bi-person-plus"></i>
                        Tambah Customer
                    </h3>


                    <?php if (session()->getFlashdata('error')) : ?>

                        <div class="alert alert-danger">

                            <?= session()->getFlashdata('error') ?>

                        </div>

                    <?php endif; ?>


                    <form action="<?= base_url('/customer/simpan') ?>"
                          method="post">

                        <?= csrf_field() ?>


                        <div class="mb-3">

                            <label class="form-label">
                                Nama Customer
                            </label>

                            <input type="text"
                                   name="nama_customer"
                                   class="form-control"
                                   placeholder="Masukkan nama customer"
                                   value="<?= old('nama_customer') ?>"
                                   required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Nomor Telepon
                            </label>

                            <input type="text"
                                   name="no_telp"
                                   class="form-control"
                                   placeholder="Contoh: 081234567890"
                                   value="<?= old('no_telp') ?>">

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Alamat
                            </label>

                            <textarea name="alamat"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Masukkan alamat customer"><?= old('alamat') ?></textarea>

                        </div>


                        <div class="mt-4">

                            <a href="<?= base_url('/customer') ?>"
                               class="btn btn-secondary">

                                Kembali

                            </a>

                            <button type="submit"
                                    class="btn btn-primary">

                                <i class="bi bi-save"></i>
                                Simpan Customer

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>