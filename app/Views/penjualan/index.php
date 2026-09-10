<?= view('layout/header', ['title' => 'Transaksi Penjualan']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="mb-4">
        <h3 class="fw-bold mb-1">
            <i class="bi bi-cart3"></i>
            Transaksi Penjualan
        </h3>

        <p class="text-muted mb-0">
            Buat transaksi penjualan baru
        </p>
    </div>

    <!-- ALERT ERROR -->
    <?php if (session()->getFlashdata('error')): ?>

        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i>
            <?= session()->getFlashdata('error') ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>
        </div>

    <?php endif; ?>


    <form
        action="<?= base_url('penjualan/simpan') ?>"
        method="post"
        id="formPenjualan"
    >

        <?= csrf_field() ?>


        <div class="row g-4">

            <!-- =====================================
                 BAGIAN KIRI
                 ===================================== -->
            <div class="col-lg-7">

                <!-- CUSTOMER -->
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-person"></i>
                            Customer
                        </h5>

                        <label class="form-label">
                            Pilih Customer
                        </label>

                        <select
                            name="id_customer"
                            class="form-select"
                        >

                            <option value="">
                                -- Customer Umum --
                            </option>

                            <?php foreach ($customer as $c): ?>

                                <option
                                    value="<?= $c['id_customer'] ?>"
                                >
                                    <?= esc($c['nama_customer']) ?>

                                    <?php if (!empty($c['no_telp'])): ?>
                                        - <?= esc($c['no_telp']) ?>
                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <small class="text-muted">
                            Kosongkan jika pembeli adalah customer umum.
                        </small>

                    </div>

                </div>


                <!-- TAMBAH BARANG -->
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-box-seam"></i>
                            Tambah Barang
                        </h5>

                        <div class="row g-3">

                            <!-- BARANG -->
                            <div class="col-md-8">

                                <label class="form-label">
                                    Barang
                                </label>

                                <select
                                    id="selectBarang"
                                    class="form-select"
                                >

                                    <option value="">
                                        -- Pilih Barang --
                                    </option>

                                    <?php foreach ($barang as $b): ?>

                                        <option
                                            value="<?= $b['id_barang'] ?>"
                                            data-harga="<?= $b['harga_jual'] ?>"
                                            data-stok="<?= $b['stok'] ?>"
                                        >
                                            <?= esc($b['kode_barang']) ?>
                                            -
                                            <?= esc($b['nama_barang']) ?>
                                            | Rp <?= number_format($b['harga_jual'], 0, ',', '.') ?>
                                            | Stok: <?= $b['stok'] ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- QTY -->
                            <div class="col-md-4">

                                <label class="form-label">
                                    Jumlah
                                </label>

                                <input
                                    type="number"
                                    id="inputQty"
                                    class="form-control"
                                    value="1"
                                    min="1"
                                >

                            </div>

                        </div>


                        <div class="mt-3">

                            <button
                                type="button"
                                class="btn btn-primary"
                                id="btnTambah"
                            >
                                <i class="bi bi-plus-lg"></i>
                                Tambah ke Keranjang
                            </button>

                        </div>

                    </div>

                </div>


                <!-- KERANJANG -->
                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-cart"></i>
                                Keranjang
                            </h5>

                            <span
                                class="badge bg-primary"
                                id="jumlahItem"
                            >
                                0 Item
                            </span>

                        </div>


                        <div class="table-responsive">

                            <table class="table table-hover align-middle">

                                <thead class="table-light">

                                    <tr>

                                        <th>No</th>
                                        <th>Barang</th>
                                        <th>Harga</th>
                                        <th width="100">Qty</th>
                                        <th>Subtotal</th>
                                        <th width="60">Aksi</th>

                                    </tr>

                                </thead>


                                <tbody id="keranjangBody">

                                    <tr id="emptyCart">

                                        <td
                                            colspan="6"
                                            class="text-center text-muted py-5"
                                        >

                                            <i class="bi bi-cart-x fs-1 d-block mb-2"></i>

                                            Keranjang masih kosong.

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================
                 BAGIAN KANAN
                 ===================================== -->
            <div class="col-lg-5">

                <div
                    class="card border-0 shadow-sm sticky-top"
                    style="top: 20px;"
                >

                    <div class="card-body">

                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-receipt"></i>
                            Ringkasan Transaksi
                        </h5>


                        <!-- TOTAL -->
                        <div class="summary-box mb-4">

                            <div class="text-muted">
                                Total Belanja
                            </div>

                            <div
                                class="fs-2 fw-bold text-primary"
                                id="totalText"
                            >
                                Rp 0
                            </div>

                        </div>


                        <!-- BAYAR -->
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Uang Bayar
                            </label>

                            <input
                                type="number"
                                name="bayar"
                                id="inputBayar"
                                class="form-control form-control-lg"
                                min="0"
                                placeholder="Masukkan uang pembayaran"
                                required
                            >

                        </div>


                        <!-- KEMBALIAN -->
                        <div class="summary-box mb-4">

                            <div class="text-muted">
                                Kembalian
                            </div>

                            <div
                                class="fs-3 fw-bold text-success"
                                id="kembalianText"
                            >
                                Rp 0
                            </div>

                        </div>


                        <!-- TOMBOL -->
                        <button
                            type="submit"
                            class="btn btn-success btn-lg w-100"
                            id="btnSimpan"
                            disabled
                        >
                            <i class="bi bi-check-circle"></i>
                            Simpan Transaksi
                        </button>


                        <a
                            href="<?= base_url('dashboard') ?>"
                            class="btn btn-outline-secondary w-100 mt-2"
                        >
                            Batal

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>


<style>

    .summary-box {
        background: #f8f9fa;
        padding: 18px;
        border-radius: 12px;
    }

    #keranjangBody td {
        vertical-align: middle;
    }

    .sticky-top {
        z-index: 1;
    }

</style>


<script>

    // ==========================================
    // DATA KERANJANG
    // ==========================================

    let keranjang = [];


    // ==========================================
    // FORMAT RUPIAH
    // ==========================================

    function formatRupiah(angka)
    {
        return 'Rp ' + Number(angka).toLocaleString('id-ID');
    }


    // ==========================================
    // TAMBAH BARANG
    // ==========================================

    document
        .getElementById('btnTambah')
        .addEventListener('click', function()
        {

            const select = document.getElementById('selectBarang');

            const idBarang = select.value;

            const qty = parseInt(
                document.getElementById('inputQty').value
            );


            if (!idBarang)
            {
                alert('Silakan pilih barang terlebih dahulu.');
                return;
            }


            if (!qty || qty <= 0)
            {
                alert('Jumlah barang harus lebih dari 0.');
                return;
            }


            const option =
                select.options[select.selectedIndex];


            const namaBarang =
                option.text.split('|')[0].trim();


            const harga =
                parseFloat(option.dataset.harga);


            const stok =
                parseInt(option.dataset.stok);


            // Cek apakah barang sudah ada
            const existing =
                keranjang.find(
                    item => item.id_barang == idBarang
                );


            if (existing)
            {

                if (existing.qty + qty > stok)
                {
                    alert(
                        'Jumlah melebihi stok yang tersedia.\n' +
                        'Stok tersedia: ' + stok
                    );

                    return;
                }


                existing.qty += qty;

            }
            else
            {

                if (qty > stok)
                {
                    alert(
                        'Jumlah melebihi stok yang tersedia.\n' +
                        'Stok tersedia: ' + stok
                    );

                    return;
                }


                keranjang.push({

                    id_barang: idBarang,

                    nama_barang: namaBarang,

                    harga: harga,

                    qty: qty,

                    stok: stok

                });

            }


            renderKeranjang();


            // Reset pilihan
            select.value = '';

            document.getElementById('inputQty').value = 1;

        });


    // ==========================================
    // RENDER KERANJANG
    // ==========================================

    function renderKeranjang()
    {

        const body =
            document.getElementById('keranjangBody');


        body.innerHTML = '';


        if (keranjang.length === 0)
        {

            body.innerHTML = `

                <tr>

                    <td
                        colspan="6"
                        class="text-center text-muted py-5"
                    >

                        <i class="bi bi-cart-x fs-1 d-block mb-2"></i>

                        Keranjang masih kosong.

                    </td>

                </tr>

            `;

        }


        let total = 0;


        keranjang.forEach(
            (item, index) =>
            {

                const subtotal =
                    item.harga * item.qty;


                total += subtotal;


                body.innerHTML += `

                    <tr>

                        <td>
                            ${index + 1}
                        </td>

                        <td>
                            <strong>
                                ${item.nama_barang}
                            </strong>
                        </td>

                        <td>
                            ${formatRupiah(item.harga)}
                        </td>

                        <td>

                            <input
                                type="number"
                                class="form-control form-control-sm"
                                value="${item.qty}"
                                min="1"
                                max="${item.stok}"
                                onchange="ubahQty(${index}, this.value)"
                            >

                        </td>

                        <td>
                            <strong>
                                ${formatRupiah(subtotal)}
                            </strong>
                        </td>

                        <td>

                            <button
                                type="button"
                                class="btn btn-sm btn-danger"
                                onclick="hapusItem(${index})"
                            >

                                <i class="bi bi-trash"></i>

                            </button>

                        </td>

                    </tr>

                    <input
                        type="hidden"
                        name="id_barang[]"
                        value="${item.id_barang}"
                    >

                    <input
                        type="hidden"
                        name="qty[]"
                        value="${item.qty}"
                    >

                `;

            }
        );


        document.getElementById('totalText')
            .innerText = formatRupiah(total);


        document.getElementById('jumlahItem')
            .innerText =
            keranjang.length + ' Item';


        hitungKembalian();

    }


    // ==========================================
    // UBAH QTY
    // ==========================================

    function ubahQty(index, value)
    {

        const qty =
            parseInt(value);


        if (
            !qty ||
            qty <= 0 ||
            qty > keranjang[index].stok
        )
        {

            alert(
                'Jumlah tidak valid. Stok tersedia: ' +
                keranjang[index].stok
            );

            renderKeranjang();

            return;

        }


        keranjang[index].qty = qty;

        renderKeranjang();

    }


    // ==========================================
    // HAPUS ITEM
    // ==========================================

    function hapusItem(index)
    {

        if (
            confirm(
                'Yakin ingin menghapus barang dari keranjang?'
            )
        )
        {

            keranjang.splice(index, 1);

            renderKeranjang();

        }

    }


    // ==========================================
    // HITUNG KEMBALIAN
    // ==========================================

    document
        .getElementById('inputBayar')
        .addEventListener(
            'input',
            hitungKembalian
        );


    function hitungKembalian()
    {

        let total = 0;


        keranjang.forEach(
            item =>
            {
                total +=
                    item.harga * item.qty;
            }
        );


        const bayar =
            parseFloat(
                document.getElementById('inputBayar').value
            ) || 0;


        const kembalian =
            bayar - total;


        if (kembalian >= 0)
        {

            document.getElementById('kembalianText')
                .innerText =
                formatRupiah(kembalian);

        }
        else
        {

            document.getElementById('kembalianText')
                .innerText =
                'Kurang ' +
                formatRupiah(Math.abs(kembalian));

        }


        // Aktifkan tombol simpan
        document.getElementById('btnSimpan')
            .disabled =
            keranjang.length === 0 ||
            bayar < total;

    }


    // ==========================================
    // VALIDASI SEBELUM SUBMIT
    // ==========================================

    document
        .getElementById('formPenjualan')
        .addEventListener(
            'submit',
            function(e)
            {

                if (keranjang.length === 0)
                {

                    e.preventDefault();

                    alert(
                        'Keranjang masih kosong.'
                    );

                    return;

                }


                let total = 0;


                keranjang.forEach(
                    item =>
                    {
                        total +=
                            item.harga * item.qty;
                    }
                );


                const bayar =
                    parseFloat(
                        document.getElementById('inputBayar').value
                    ) || 0;


                if (bayar < total)
                {

                    e.preventDefault();

                    alert(
                        'Uang pembayaran kurang.'
                    );

                    return;

                }

            }
        );

</script>

<?= view('layout/footer') ?>