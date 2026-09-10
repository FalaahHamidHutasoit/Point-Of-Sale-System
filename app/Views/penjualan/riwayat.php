<?= view('layout/header', ['title' => 'Riwayat Transaksi']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-clock-history"></i>
                Riwayat Transaksi
            </h3>

            <p class="text-muted mb-0">
                Daftar seluruh transaksi penjualan
            </p>
        </div>

        <a
            href="<?= base_url('penjualan') ?>"
            class="btn btn-primary"
        >
            <i class="bi bi-plus-lg"></i>
            Transaksi Baru
        </a>

    </div>


    <?php if (session()->getFlashdata('success')): ?>

        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            <?= session()->getFlashdata('success') ?>
        </div>

    <?php endif; ?>


    <!-- SEARCH -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form
                action="<?= base_url('penjualan/riwayat') ?>"
                method="get"
            >

                <div class="input-group">

                    <input
                        type="text"
                        name="keyword"
                        class="form-control"
                        placeholder="Cari nomor transaksi atau customer..."
                        value="<?= esc($keyword ?? '') ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-dark"
                    >
                        <i class="bi bi-search"></i>
                        Cari
                    </button>

                    <?php if (!empty($keyword)): ?>

                        <a
                            href="<?= base_url('penjualan/riwayat') ?>"
                            class="btn btn-outline-secondary"
                        >
                            Reset
                        </a>

                    <?php endif; ?>

                </div>

            </form>

        </div>

    </div>


    <!-- TABLE -->

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th width="60">
                                No
                            </th>

                            <th>
                                No. Transaksi
                            </th>

                            <th>
                                Tanggal
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Kasir
                            </th>

                            <th class="text-end">
                                Total
                            </th>

                            <th width="100">
                                Aksi
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($penjualan)): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($penjualan as $row): ?>

                                <tr>

                                    <td>
                                        <?= $no++ ?>
                                    </td>

                                    <td>

                                        <span class="fw-semibold">
                                            <?= esc($row['no_transaksi']) ?>
                                        </span>

                                    </td>

                                    <td>

                                        <?= date(
                                            'd/m/Y H:i',
                                            strtotime($row['tanggal'])
                                        ) ?>

                                    </td>

                                    <td>

                                        <?= esc(
                                            $row['nama_customer']
                                            ?? 'Customer Umum'
                                        ) ?>

                                    </td>

                                    <td>

                                        <?= esc(
                                            $row['nama_lengkap']
                                        ) ?>

                                    </td>

                                    <td class="text-end">

                                        <strong>
                                            Rp <?= number_format(
                                                $row['total'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>
                                        </strong>

                                    </td>

                                    <td>

                                        <a
                                            href="<?= base_url(
                                                'penjualan/sukses/' .
                                                $row['id_penjualan']
                                            ) ?>"
                                            class="btn btn-sm btn-info text-white"
                                        >
                                            <i class="bi bi-eye"></i>
                                            Detail
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center text-muted py-5"
                                >

                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>

                                    Belum ada transaksi.

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