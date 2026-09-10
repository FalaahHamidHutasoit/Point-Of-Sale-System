<?= view('layout/header', ['title' => 'Data Supplier']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Data Supplier</h3>
            <p class="text-muted mb-0">Kelola data supplier</p>
        </div>

        <a href="<?= base_url('supplier/tambah') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Supplier
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <form action="<?= base_url('supplier') ?>" method="get" class="mb-4">

                <div class="input-group">

                    <input
                        type="text"
                        name="keyword"
                        class="form-control"
                        placeholder="Cari nama, nomor telepon, atau alamat..."
                        value="<?= esc($keyword ?? '') ?>"
                    >

                    <button class="btn btn-dark" type="submit">
                        <i class="bi bi-search"></i> Cari
                    </button>

                    <?php if (!empty($keyword)): ?>
                        <a href="<?= base_url('supplier') ?>" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    <?php endif; ?>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>
                            <th width="60">No</th>
                            <th>Nama Supplier</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th width="180">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($supplier)): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($supplier as $row): ?>

                                <tr>

                                    <td><?= $no++ ?></td>

                                    <td class="fw-semibold">
                                        <?= esc($row['nama_supplier']) ?>
                                    </td>

                                    <td>
                                        <?= esc($row['no_telp'] ?: '-') ?>
                                    </td>

                                    <td>
                                        <?= esc($row['alamat'] ?: '-') ?>
                                    </td>

                                    <td>

                                        <a
                                            href="<?= base_url('supplier/edit/' . $row['id_supplier']) ?>"
                                            class="btn btn-sm btn-warning"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            Edit
                                        </a>

                                        <a
                                            href="<?= base_url('supplier/hapus/' . $row['id_supplier']) ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus supplier ini?')"
                                        >
                                            <i class="bi bi-trash"></i>
                                            Hapus
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Belum ada data supplier.
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