<?= view('layout/header') ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <div class="receipt-wrapper">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">

            <div>
                <h3 class="fw-bold mb-1">
                    <i class="bi bi-receipt"></i>
                    Transaksi Berhasil
                </h3>

                <p class="text-muted mb-0">
                    Nota transaksi berhasil dibuat.
                </p>
            </div>

            <div>
                <button onclick="window.print()" class="btn btn-dark">
                    <i class="bi bi-printer"></i>
                    Cetak Nota
                </button>

                <a href="<?= base_url('penjualan') ?>"
                   class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Transaksi Baru
                </a>
            </div>

        </div>


        <!-- NOTA -->
        <div class="card border-0 shadow-sm nota">

            <div class="card-body p-4">

                <!-- IDENTITAS TOKO -->
                <div class="text-center mb-4">

                    <h2 class="fw-bold mb-1">
                        POS SYSTEM
                    </h2>

                    <p class="text-muted mb-1">
                        Sistem Informasi Penjualan
                    </p>

                    <small class="text-muted">
                        Nota Penjualan
                    </small>

                </div>


                <hr>


                <!-- INFORMASI TRANSAKSI -->
                <div class="row mb-4">

                    <div class="col-md-6">

                        <table class="table table-sm table-borderless mb-0">

                            <tr>
                                <td width="130">No. Transaksi</td>
                                <td>:</td>
                                <td class="fw-bold">
                                    <?= esc($penjualan['no_transaksi']) ?>
                                </td>
                            </tr>

                            <tr>
                                <td>Tanggal</td>
                                <td>:</td>
                                <td>
                                    <?= date('d-m-Y H:i', strtotime($penjualan['tanggal'])) ?>
                                </td>
                            </tr>

                        </table>

                    </div>


                    <div class="col-md-6">

                        <table class="table table-sm table-borderless mb-0">

                            <tr>
                                <td width="130">Customer</td>
                                <td>:</td>
                                <td>
                                    <?= esc($penjualan['nama_customer'] ?? 'Umum') ?>
                                </td>
                            </tr>

                            <tr>
                                <td>Kasir</td>
                                <td>:</td>
                                <td>
                                    <?= esc($penjualan['nama_lengkap']) ?>
                                </td>
                            </tr>

                        </table>

                    </div>

                </div>


                <!-- DETAIL BARANG -->
                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead class="table-dark">

                            <tr>

                                <th>No</th>

                                <th>Barang</th>

                                <th class="text-center">
                                    Qty
                                </th>

                                <th class="text-end">
                                    Harga
                                </th>

                                <th class="text-end">
                                    Subtotal
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php $no = 1; ?>

                            <?php foreach ($detail as $item): ?>

                                <tr>

                                    <td>
                                        <?= $no++ ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= esc($item['nama_barang']) ?>
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            <?= esc($item['kode_barang']) ?>
                                        </small>
                                    </td>

                                    <td class="text-center">
                                        <?= esc($item['qty']) ?>
                                    </td>

                                    <td class="text-end">
                                        Rp <?= number_format(
                                            $item['harga'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td class="text-end fw-semibold">
                                        Rp <?= number_format(
                                            $item['subtotal'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <hr>


                <!-- TOTAL -->
                <div class="row justify-content-end">

                    <div class="col-md-5">

                        <table class="table table-borderless">

                            <tr>

                                <td class="fw-semibold">
                                    Total
                                </td>

                                <td class="text-end fw-bold fs-5">
                                    Rp <?= number_format(
                                        $penjualan['total'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    Bayar
                                </td>

                                <td class="text-end">
                                    Rp <?= number_format(
                                        $penjualan['bayar'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    Kembalian
                                </td>

                                <td class="text-end fw-bold">
                                    Rp <?= number_format(
                                        $penjualan['kembalian'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                            </tr>

                        </table>

                    </div>

                </div>


                <hr>


                <!-- FOOTER NOTA -->
                <div class="text-center mt-4">

                    <h6 class="fw-bold">
                        Terima Kasih
                    </h6>

                    <p class="text-muted mb-0">
                        Barang yang sudah dibeli tidak dapat dikembalikan.
                    </p>

                    <small class="text-muted">
                        Simpan nota ini sebagai bukti transaksi.
                    </small>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

.nota {
    max-width: 900px;
    margin: auto;
}

.table th {
    white-space: nowrap;
}

@media print {

    @page {
        size: A4;
        margin: 15mm;
    }

    body {
        background: white !important;
    }

    .sidebar,
    .no-print {
        display: none !important;
    }

    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }

    .nota {
        max-width: 100% !important;
        box-shadow: none !important;
        border: none !important;
    }

    .card-body {
        padding: 0 !important;
    }

}

</style>


<?= view('layout/footer') ?>