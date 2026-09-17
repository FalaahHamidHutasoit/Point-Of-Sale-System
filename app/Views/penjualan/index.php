<?= view('layout/header', ['title' => 'Transaksi Penjualan']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">

        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="page-icon">
                    <i class="bi bi-cart3"></i>
                </div>

                <div>
                    <h3 class="fw-bold mb-0">
                        Transaksi Penjualan
                    </h3>

                    <p class="text-muted mb-0">
                        Buat transaksi penjualan baru
                    </p>
                </div>
            </div>
        </div>

        <a href="<?= base_url('penjualan/riwayat') ?>"
           class="btn btn-outline-primary">

            <i class="bi bi-clock-history me-1"></i>
            Riwayat Transaksi

        </a>

    </div>


    <!-- ALERT ERROR -->
    <?php if (session()->getFlashdata('error')): ?>

        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4">

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

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


            <!-- =====================================================
                 BAGIAN KIRI
            ====================================================== -->

            <div class="col-lg-7">


                <!-- CUSTOMER -->
                <div class="card pos-card border-0 shadow-sm mb-4">

                    <div class="card-body p-4">

                        <div class="section-heading mb-3">

                            <div class="section-icon customer-icon">
                                <i class="bi bi-person"></i>
                            </div>

                            <div>
                                <h5 class="fw-bold mb-0">
                                    Customer
                                </h5>

                                <small class="text-muted">
                                    Pilih customer transaksi
                                </small>
                            </div>

                        </div>


                        <label class="form-label fw-semibold">
                            Customer
                        </label>

                        <select
                            name="id_customer"
                            class="form-select form-select-lg"
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

                        <div class="form-hint mt-2">

                            <i class="bi bi-info-circle me-1"></i>

                            Kosongkan jika pembeli adalah customer umum.

                        </div>

                    </div>

                </div>


                <!-- TAMBAH BARANG -->
                <div class="card pos-card border-0 shadow-sm mb-4">

                    <div class="card-body p-4">

                        <div class="section-heading mb-4">

                            <div class="section-icon product-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>

                            <div>
                                <h5 class="fw-bold mb-0">
                                    Tambah Barang
                                </h5>

                                <small class="text-muted">
                                    Pilih produk dan tentukan jumlah
                                </small>
                            </div>

                        </div>


                        <div class="row g-3">


                            <!-- BARANG -->
                            <div class="col-md-8">

                                <label class="form-label fw-semibold">
                                    Barang
                                </label>

                                <select
                                    id="selectBarang"
                                    class="form-select form-select-lg"
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

                                <label class="form-label fw-semibold">
                                    Jumlah
                                </label>

                                <input
                                    type="number"
                                    id="inputQty"
                                    class="form-control form-control-lg"
                                    value="1"
                                    min="1"
                                >

                            </div>

                        </div>


                        <button
                            type="button"
                            class="btn btn-primary mt-3"
                            id="btnTambah"
                        >

                            <i class="bi bi-cart-plus me-1"></i>

                            Tambah ke Keranjang

                        </button>

                    </div>

                </div>


                <!-- KERANJANG -->
                <div class="card pos-card border-0 shadow-sm">

                    <div class="card-body p-0">


                        <!-- CART HEADER -->
                        <div class="cart-header p-4">

                            <div class="d-flex justify-content-between align-items-center">

                                <div class="section-heading mb-0">

                                    <div class="section-icon cart-icon">
                                        <i class="bi bi-cart3"></i>
                                    </div>

                                    <div>
                                        <h5 class="fw-bold mb-0">
                                            Keranjang
                                        </h5>

                                        <small class="text-muted">
                                            Daftar barang yang dibeli
                                        </small>
                                    </div>

                                </div>


                                <span
                                    class="item-badge"
                                    id="jumlahItem"
                                >
                                    0 Item
                                </span>

                            </div>

                        </div>


                        <!-- CART TABLE -->
                        <div class="table-responsive">

                            <table class="table cart-table align-middle mb-0">

                                <thead>

                                    <tr>

                                        <th width="55">
                                            #
                                        </th>

                                        <th>
                                            Barang
                                        </th>

                                        <th>
                                            Harga
                                        </th>

                                        <th width="110">
                                            Qty
                                        </th>

                                        <th>
                                            Subtotal
                                        </th>

                                        <th width="60">
                                        </th>

                                    </tr>

                                </thead>


                                <tbody id="keranjangBody">

                                    <tr id="emptyCart">

                                        <td
                                            colspan="6"
                                            class="empty-cart"
                                        >

                                            <div class="empty-cart-icon">
                                                <i class="bi bi-cart-x"></i>
                                            </div>

                                            <div class="fw-semibold mb-1">
                                                Keranjang masih kosong
                                            </div>

                                            <small class="text-muted">
                                                Tambahkan barang untuk memulai transaksi.
                                            </small>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 BAGIAN KANAN
            ====================================================== -->

            <div class="col-lg-5">


                <div
                    class="card summary-card border-0 shadow-sm sticky-top"
                    style="top: 20px;"
                >

                    <div class="card-body p-4">


                        <!-- HEADER -->
                        <div class="summary-heading mb-4">

                            <div class="section-icon summary-icon">
                                <i class="bi bi-receipt"></i>
                            </div>

                            <div>
                                <h5 class="fw-bold mb-0">
                                    Ringkasan Transaksi
                                </h5>

                                <small class="text-muted">
                                    Detail pembayaran
                                </small>
                            </div>

                        </div>


                        <!-- TOTAL -->
                        <div class="total-box mb-4">

                            <div class="total-label">
                                Total Belanja
                            </div>

                            <div
                                class="total-value"
                                id="totalText"
                            >
                                Rp 0
                            </div>

                        </div>


                        <!-- BAYAR -->
                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                <i class="bi bi-cash-stack me-1"></i>

                                Uang Bayar

                            </label>

                            <div class="input-group input-group-lg">

                                <span class="input-group-text">
                                    Rp
                                </span>

                                <input
                                    type="number"
                                    name="bayar"
                                    id="inputBayar"
                                    class="form-control"
                                    min="0"
                                    placeholder="0"
                                    required
                                >

                            </div>

                            <small class="text-muted mt-1 d-block">
                                Masukkan nominal uang yang diterima.
                            </small>

                        </div>


                        <!-- KEMBALIAN -->
                        <div class="change-box mb-4">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted d-block">
                                        Kembalian
                                    </small>

                                    <strong id="kembalianText">
                                        Rp 0
                                    </strong>

                                </div>

                                <div class="change-icon">
                                    <i class="bi bi-wallet2"></i>
                                </div>

                            </div>

                        </div>


                        <!-- BUTTON SIMPAN -->
                        <button
                            type="submit"
                            class="btn btn-success btn-lg w-100"
                            id="btnSimpan"
                            disabled
                        >

                            <i class="bi bi-check-circle me-1"></i>

                            Simpan Transaksi

                        </button>


                        <a
                            href="<?= base_url('dashboard') ?>"
                            class="btn btn-light border w-100 mt-2"
                        >

                            <i class="bi bi-arrow-left me-1"></i>

                            Kembali

                        </a>


                        <!-- INFO -->
                        <div class="transaction-info mt-4">

                            <i class="bi bi-shield-check"></i>

                            <span>
                                Pastikan jumlah pembayaran sudah benar
                                sebelum menyimpan transaksi.
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>


<style>

/* =====================================================
   GENERAL
===================================================== */

.pos-card,
.summary-card {
    border-radius: 16px;
    overflow: hidden;
}

.page-header {
    min-height: 55px;
}

.page-icon {
    width: 46px;
    height: 46px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #eaf2ff;
    color: #0d6efd;

    border-radius: 12px;

    font-size: 22px;
}


/* =====================================================
   SECTION
===================================================== */

.section-heading,
.summary-heading {
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-icon {
    width: 42px;
    height: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 11px;

    font-size: 19px;

    flex-shrink: 0;
}

.customer-icon {
    background: #eaf2ff;
    color: #0d6efd;
}

.product-icon {
    background: #eaf8f1;
    color: #198754;
}

.cart-icon {
    background: #fff4e5;
    color: #fd7e14;
}

.summary-icon {
    background: #f0eaff;
    color: #6f42c1;
}


/* =====================================================
   FORM
===================================================== */

.form-select,
.form-control {
    border-radius: 10px;
}

.form-select-lg,
.form-control-lg {
    min-height: 48px;
}

.form-hint {
    font-size: 12px;
    color: #6c757d;
}


/* =====================================================
   CART
===================================================== */

.cart-header {
    border-bottom: 1px solid #edf0f2;
}

.item-badge {
    padding: 7px 12px;

    background: #eaf2ff;

    color: #0d6efd;

    border-radius: 50px;

    font-size: 12px;
    font-weight: 600;
}

.cart-table thead th {
    background: #f8f9fa;

    color: #6c757d;

    font-size: 12px;

    text-transform: uppercase;

    letter-spacing: .3px;

    padding: 13px 12px;

    border-bottom: 1px solid #e9ecef;
}

.cart-table tbody td {
    padding: 14px 12px;

    border-color: #edf0f2;
}

.cart-table tbody tr:last-child td {
    border-bottom: none;
}


/* =====================================================
   EMPTY CART
===================================================== */

.empty-cart {
    text-align: center;

    padding: 50px 20px !important;
}

.empty-cart-icon {
    width: 60px;
    height: 60px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin: 0 auto 12px;

    background: #f1f3f5;

    color: #adb5bd;

    border-radius: 16px;

    font-size: 26px;
}


/* =====================================================
   SUMMARY
===================================================== */

.summary-card {
    z-index: 1;
}

.total-box {
    padding: 20px;

    background: #f5f8ff;

    border: 1px solid #e2eaff;

    border-radius: 14px;
}

.total-label {
    color: #6c757d;

    font-size: 13px;

    margin-bottom: 4px;
}

.total-value {
    color: #0d6efd;

    font-size: 30px;

    font-weight: 700;
}


/* =====================================================
   CHANGE
===================================================== */

.change-box {
    padding: 17px 18px;

    background: #f1fdf6;

    border: 1px solid #d6f1e0;

    border-radius: 13px;
}

#kembalianText {
    color: #198754;

    font-size: 23px;
}

.change-icon {
    width: 40px;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #198754;

    color: white;

    border-radius: 11px;

    font-size: 18px;
}


/* =====================================================
   INFO
===================================================== */

.transaction-info {
    display: flex;

    gap: 8px;

    padding: 12px 14px;

    background: #f8f9fa;

    border-radius: 10px;

    color: #6c757d;

    font-size: 11px;

    line-height: 1.5;
}

.transaction-info i {
    font-size: 15px;
    color: #198754;
}


/* =====================================================
   BUTTON
===================================================== */

#btnTambah {
    border-radius: 10px;

    padding: 10px 16px;
}

#btnSimpan {
    border-radius: 11px;

    padding: 12px;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 991px) {

    .summary-card {
        position: static !important;
    }

}


@media (max-width: 768px) {

    .main-content {
        padding: 20px !important;
    }

    .page-header {
        align-items: flex-start !important;

        flex-direction: column;

        gap: 15px;
    }

    .page-header > a {
        width: 100%;
    }

    .cart-table {
        min-width: 700px;
    }

    .total-value {
        font-size: 25px;
    }

}

</style>


<script>

/* =====================================================
   DATA KERANJANG
===================================================== */

let keranjang = [];


/* =====================================================
   FORMAT RUPIAH
===================================================== */

function formatRupiah(angka)
{
    return 'Rp ' + Number(angka).toLocaleString('id-ID');
}


/* =====================================================
   TAMBAH BARANG
===================================================== */

document
    .getElementById('btnTambah')
    .addEventListener('click', function()
    {

        const select =
            document.getElementById('selectBarang');

        const idBarang =
            select.value;

        const qty =
            parseInt(
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


        select.value = '';

        document.getElementById('inputQty').value = 1;

    });


/* =====================================================
   RENDER KERANJANG
===================================================== */

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
                    class="empty-cart"
                >

                    <div class="empty-cart-icon">
                        <i class="bi bi-cart-x"></i>
                    </div>

                    <div class="fw-semibold mb-1">
                        Keranjang masih kosong
                    </div>

                    <small class="text-muted">
                        Tambahkan barang untuk memulai transaksi.
                    </small>

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

                    <td class="text-muted">
                        ${index + 1}
                    </td>


                    <td>

                        <div class="fw-semibold">
                            ${item.nama_barang}
                        </div>

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


                    <td class="fw-bold">
                        ${formatRupiah(subtotal)}
                    </td>


                    <td>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            onclick="hapusItem(${index})"
                            title="Hapus barang"
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
        .innerText =
        formatRupiah(total);


    document.getElementById('jumlahItem')
        .innerText =
        keranjang.length + ' Item';


    hitungKembalian();

}


/* =====================================================
   UBAH QTY
===================================================== */

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


/* =====================================================
   HAPUS ITEM
===================================================== */

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


/* =====================================================
   HITUNG KEMBALIAN
===================================================== */

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


    document.getElementById('btnSimpan')
        .disabled =
        keranjang.length === 0 ||
        bayar < total;

}


/* =====================================================
   VALIDASI SUBMIT
===================================================== */

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