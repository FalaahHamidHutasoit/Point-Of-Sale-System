<?= view('layout/header', ['title' => 'Data Customer']) ?>

<?= view('layout/sidebar') ?>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-people-fill"></i>
                Data Customer
            </h2>

            <p class="text-muted mb-0">
                Kelola data customer
            </p>
        </div>

        <a href="<?= base_url('/customer/tambah') ?>"
           class="btn btn-primary">

            <i class="bi bi-plus-lg"></i>
            Tambah Customer

        </a>

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


    <!-- SEARCH -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form method="get"
                  action="<?= base_url('/customer') ?>">

                <div class="row g-2">

                    <div class="col-md-10">

                        <input type="text"
                               name="keyword"
                               class="form-control"
                               placeholder="Cari nama, nomor telepon, atau alamat..."
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

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th width="70">No</th>
                            <th>Nama Customer</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th width="180">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (!empty($customer)) : ?>

                        <?php $no = 1; ?>

                        <?php foreach ($customer as $row) : ?>

                            <tr>

                                <td>
                                    <?= $no++ ?>
                                </td>

                                <td class="fw-semibold">
                                    <?= esc($row['nama_customer']) ?>
                                </td>

                                <td>
                                    <?= esc($row['no_telp'] ?: '-') ?>
                                </td>

                                <td>
                                    <?= esc($row['alamat'] ?: '-') ?>
                                </td>

                                <td>

                                    <a href="<?= base_url('/customer/edit/' . $row['id_customer']) ?>"
                                       class="btn btn-warning btn-sm">

                                        <i class="bi bi-pencil"></i>
                                        Edit

                                    </a>

                                    <a href="<?= base_url('/customer/hapus/' . $row['id_customer']) ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Yakin ingin menghapus customer ini?')">

                                        <i class="bi bi-trash"></i>
                                        Hapus

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else : ?>

                        <tr>

                            <td colspan="5"
                                class="text-center text-muted py-4">

                                Belum ada data customer.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>