<?= view('layout/header', ['title' => 'Penjualan']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Transaksi Penjualan</h3>
            <p class="text-muted mb-0">
                Kelola transaksi penjualan dan pembayaran pelanggan
            </p>
        </div>
    </div>

    <!-- FLASH MESSAGE -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form id="formPenjualan"
          action="<?= base_url('penjualan/simpan') ?>"
          method="post">

        <?= csrf_field() ?>

        <div class="row g-4">

            <!-- ===================================================== -->
            <!-- PRODUK -->
            <!-- ===================================================== -->
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>
                                <h5 class="fw-bold mb-1">
                                    <i class="bi bi-box-seam me-2"></i>
                                    Pilih Barang
                                </h5>

                                <small class="text-muted">
                                    Klik barang untuk memasukkannya ke keranjang
                                </small>
                            </div>

                            <span class="badge bg-primary">
                                <?= count($barang) ?> Barang
                            </span>

                        </div>


                        <!-- SEARCH -->
                        <div class="input-group mb-4">

                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>

                            <input type="text"
                                   id="searchBarang"
                                   class="form-control"
                                   placeholder="Cari nama barang...">

                        </div>


                        <!-- LIST BARANG -->
                        <div class="row g-3"
                             id="produkContainer">

                            <?php if (!empty($barang)): ?>

                                <?php foreach ($barang as $b): ?>

                                    <div class="col-md-4 produk-item"
                                         data-nama="<?= strtolower(esc($b['nama_barang'])) ?>">

                                        <div class="card border h-100 product-card"
                                             onclick="tambahProduk(<?= (int) $b['id_barang'] ?>)"
                                             style="cursor:pointer;">

                                            <div class="card-body">

                                                <div class="d-flex justify-content-between align-items-start mb-2">

                                                    <div class="bg-primary bg-opacity-10 rounded p-2">

                                                        <i class="bi bi-box-seam text-primary fs-5"></i>

                                                    </div>

                                                    <span class="badge bg-light text-dark border">
                                                        Stok <?= (int) $b['stok'] ?>
                                                    </span>

                                                </div>


                                                <h6 class="fw-bold mb-1">
                                                    <?= esc($b['nama_barang']) ?>
                                                </h6>


                                                <?php if (!empty($b['kode_barang'])): ?>

                                                    <small class="text-muted d-block mb-2">
                                                        <?= esc($b['kode_barang']) ?>
                                                    </small>

                                                <?php endif; ?>


                                                <?php if (!empty($b['nama_kategori'])): ?>

                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary mb-2">
                                                        <?= esc($b['nama_kategori']) ?>
                                                    </span>

                                                <?php endif; ?>


                                                <div class="fw-bold text-primary mt-2">

                                                    Rp <?= number_format(
                                                        (float) $b['harga_jual'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) ?>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <div class="col-12">

                                    <div class="text-center py-5 text-muted">

                                        <i class="bi bi-box-seam fs-1 d-block mb-3"></i>

                                        <p class="mb-0">
                                            Belum ada barang yang tersedia.
                                        </p>

                                    </div>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- KERANJANG -->
            <!-- ===================================================== -->
            <div class="col-lg-4">

                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>

                                <h5 class="fw-bold mb-1">
                                    <i class="bi bi-cart3 me-2"></i>
                                    Keranjang
                                </h5>

                                <small class="text-muted">
                                    Barang yang dipilih
                                </small>

                            </div>

                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="kosongkanKeranjang()">

                                <i class="bi bi-trash"></i>

                            </button>

                        </div>


                        <!-- CART -->
                        <div id="keranjangContainer">

                            <div class="text-center text-muted py-5">

                                <i class="bi bi-cart-x fs-1 d-block mb-3"></i>

                                <p class="mb-0">
                                    Keranjang masih kosong
                                </p>

                            </div>

                        </div>


                        <!-- TOTAL -->
                        <div class="border-top pt-3 mt-3">

                            <div class="d-flex justify-content-between align-items-center">

                                <span class="text-muted">
                                    Total
                                </span>

                                <h4 class="fw-bold text-primary mb-0"
                                    id="totalText">

                                    Rp 0

                                </h4>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- PEMBAYARAN -->
                <!-- ================================================= -->

                <div class="card border-0 shadow-sm mt-4">

                    <div class="card-body">

                        <h5 class="fw-bold mb-3">

                            <i class="bi bi-credit-card me-2"></i>

                            Pembayaran

                        </h5>


                        <!-- CUSTOMER -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Customer

                            </label>

                            <select name="id_customer"
                                    id="id_customer"
                                    class="form-select">

                                <option value="">
                                    Customer Umum
                                </option>

                                <?php foreach ($customer as $c): ?>

                                    <option value="<?= (int) $c['id_customer'] ?>">

                                        <?= esc($c['nama_customer']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- METODE -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Metode Pembayaran

                            </label>

                            <select name="metode_pembayaran"
                                    id="metode_pembayaran"
                                    class="form-select">

                                <option value="Tunai">
                                    Tunai
                                </option>

                                <option value="QRIS">
                                    QRIS
                                </option>

                                <option value="Transfer">
                                    Transfer
                                </option>

                            </select>

                        </div>


                        <!-- NOMINAL BAYAR -->

                        <div id="nominalPaymentBox">

                            <div class="mb-3">

                                <label class="form-label fw-semibold">

                                    Nominal Bayar

                                </label>

                                <input type="number"
                                       name="bayar"
                                       id="inputBayar"
                                       class="form-control"
                                       min="0"
                                       placeholder="Masukkan nominal pembayaran">

                            </div>


                            <!-- KEMBALIAN -->

                            <div class="d-flex justify-content-between align-items-center
                                        bg-light rounded p-3 mb-3"
                                 id="changeRow">

                                <span class="text-muted">

                                    Kembalian

                                </span>

                                <strong id="kembalianText">

                                    Rp 0

                                </strong>

                            </div>

                        </div>


                        <!-- ================================================= -->
                        <!-- QRIS -->
                        <!-- ================================================= -->

                        <div id="qrisPaymentBox"
                             class="d-none">

                            <div class="border rounded-4 p-3 text-center bg-light">

                                <div class="mb-2">

                                    <span class="badge bg-primary">

                                        <i class="bi bi-qr-code me-1"></i>

                                        QRIS DEMO

                                    </span>

                                </div>


                                <h6 class="fw-bold mb-1">

                                    Scan QRIS

                                </h6>


                                <p class="text-muted small mb-3">

                                    Simulasi pembayaran untuk keperluan demo sistem.

                                </p>


                                <!-- QR -->
                                <div id="qrisCanvas"
                                     class="d-flex justify-content-center mb-3">
                                </div>


                                <!-- TOTAL QRIS -->

                                <div class="mb-3">

                                    <small class="text-muted d-block">

                                        Total Pembayaran

                                    </small>

                                    <h4 class="fw-bold text-primary mb-0"
                                        id="qrisAmount">

                                        Rp 0

                                    </h4>

                                </div>


                                <!-- STATUS -->

                                <div id="qrisStatus"
                                     class="alert alert-warning mb-0">

                                    <div class="fw-semibold"
                                         id="qrisStatusTitle">

                                        Menunggu pembayaran...

                                    </div>

                                    <small id="qrisStatusDescription">

                                        Pembayaran otomatis diproses.

                                    </small>

                                </div>


                                <!-- COUNTDOWN -->

                                <div class="mt-3">

                                    <small class="text-muted">

                                        Simulasi diproses dalam

                                    </small>

                                    <div class="fw-bold"
                                         id="qrisCountdown">

                                        4 detik

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- CATATAN -->

                        <div class="small text-muted mb-3"
                             id="paymentNote">

                            <i class="bi bi-info-circle me-1"></i>

                            Masukkan nominal pembayaran pelanggan.

                        </div>


                        <!-- SUBMIT -->

                        <button type="submit"
                                id="btnSimpan"
                                class="btn btn-primary w-100">

                            <i class="bi bi-check-circle me-1"></i>

                            Simpan Transaksi

                        </button>

                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- HIDDEN CART INPUT -->
        <!-- ========================================================= -->

        <div id="hiddenInputs"></div>

    </form>

</div>


<!-- =============================================================== -->
<!-- STYLE -->
<!-- =============================================================== -->

<style>

.product-card {
    transition: all 0.2s ease;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,.08);
    border-color: #0d6efd !important;
}

.cart-item {
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 10px;
}

.qty-control {
    display: flex;
    align-items: center;
    gap: 6px;
}

.qty-control button {
    width: 30px;
    height: 30px;
    padding: 0;
}

#qrisCanvas img {
    border-radius: 10px;
}

</style>


<!-- =============================================================== -->
<!-- QR CODE LIBRARY -->
<!-- =============================================================== -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>


<script>

const produkData = <?= json_encode($barang) ?>;

let keranjang = [];

let qrisTimer = null;
let qrisCountdownTimer = null;
let qrisReference = '';


/* ===============================================================
   FORMAT RUPIAH
=============================================================== */

function formatRupiah(angka)
{
    angka = Number(angka) || 0;

    return 'Rp ' + angka.toLocaleString('id-ID');
}


/* ===============================================================
   CARI PRODUK
=============================================================== */

function getProduct(id)
{
    return produkData.find(function(item) {

        return Number(item.id_barang) === Number(id);

    });
}


/* ===============================================================
   TOTAL KERANJANG
=============================================================== */

function getTotalKeranjang()
{
    return keranjang.reduce(function(total, item) {

        return total + (
            Number(item.harga_jual) * Number(item.qty)
        );

    }, 0);
}


/* ===============================================================
   TAMBAH PRODUK
=============================================================== */

function tambahProduk(id)
{
    const produk = getProduct(id);

    if (!produk) {
        return;
    }

    const stok = Number(produk.stok);

    const existing = keranjang.find(function(item) {

        return Number(item.id_barang) === Number(id);

    });


    if (existing) {

        if (Number(existing.qty) >= stok) {

            alert('Jumlah barang sudah mencapai stok yang tersedia.');

            return;
        }

        existing.qty++;

    } else {

        keranjang.push({

            id_barang: Number(produk.id_barang),

            nama_barang: produk.nama_barang,

            harga_jual: Number(produk.harga_jual),

            stok: stok,

            qty: 1

        });

    }


    renderKeranjang();

}


/* ===============================================================
   TAMBAH QTY
=============================================================== */

function tambahQty(id)
{
    const item = keranjang.find(function(item) {

        return Number(item.id_barang) === Number(id);

    });

    if (!item) {
        return;
    }


    if (Number(item.qty) >= Number(item.stok)) {

        alert('Jumlah melebihi stok yang tersedia.');

        return;
    }


    item.qty++;

    renderKeranjang();
}


/* ===============================================================
   KURANG QTY
=============================================================== */

function kurangQty(id)
{
    const item = keranjang.find(function(item) {

        return Number(item.id_barang) === Number(id);

    });

    if (!item) {
        return;
    }


    item.qty--;


    if (item.qty <= 0) {

        keranjang = keranjang.filter(function(row) {

            return Number(row.id_barang) !== Number(id);

        });

    }


    renderKeranjang();
}


/* ===============================================================
   HAPUS PRODUK
=============================================================== */

function hapusProduk(id)
{
    keranjang = keranjang.filter(function(item) {

        return Number(item.id_barang) !== Number(id);

    });

    renderKeranjang();
}


/* ===============================================================
   KOSONGKAN KERANJANG
=============================================================== */

function kosongkanKeranjang()
{
    if (keranjang.length === 0) {
        return;
    }


    if (!confirm('Kosongkan semua barang dari keranjang?')) {

        return;
    }


    keranjang = [];

    renderKeranjang();
}


/* ===============================================================
   ESCAPE HTML
=============================================================== */

function escapeHtml(text)
{
    const div = document.createElement('div');

    div.textContent = text ?? '';

    return div.innerHTML;
}


/* ===============================================================
   RENDER KERANJANG
=============================================================== */

function renderKeranjang()
{
    const container = document.getElementById('keranjangContainer');

    if (keranjang.length === 0) {

        container.innerHTML = `

            <div class="text-center text-muted py-5">

                <i class="bi bi-cart-x fs-1 d-block mb-3"></i>

                <p class="mb-0">
                    Keranjang masih kosong
                </p>

            </div>

        `;

    } else {

        let html = '';


        keranjang.forEach(function(item) {

            const subtotal =
                Number(item.harga_jual) *
                Number(item.qty);


            html += `

                <div class="cart-item">

                    <div class="d-flex justify-content-between">

                        <div class="pe-2">

                            <div class="fw-semibold">

                                ${escapeHtml(item.nama_barang)}

                            </div>

                            <small class="text-muted">

                                ${formatRupiah(item.harga_jual)}

                            </small>

                        </div>


                        <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                onclick="hapusProduk(${item.id_barang})">

                            <i class="bi bi-trash"></i>

                        </button>

                    </div>


                    <div class="d-flex justify-content-between align-items-center mt-2">

                        <div class="qty-control">

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    onclick="kurangQty(${item.id_barang})">

                                <i class="bi bi-dash"></i>

                            </button>


                            <span class="fw-semibold px-2">

                                ${item.qty}

                            </span>


                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    onclick="tambahQty(${item.id_barang})">

                                <i class="bi bi-plus"></i>

                            </button>

                        </div>


                        <strong>

                            ${formatRupiah(subtotal)}

                        </strong>

                    </div>

                </div>

            `;

        });


        container.innerHTML = html;

    }


    updateTotal();

    renderHiddenInputs();

    updatePaymentUI();
}


/* ===============================================================
   UPDATE TOTAL
=============================================================== */

function updateTotal()
{
    const total = getTotalKeranjang();

    document.getElementById('totalText').innerText =
        formatRupiah(total);

    document.getElementById('qrisAmount').innerText =
        formatRupiah(total);

    hitungKembalian();
}


/* ===============================================================
   HIDDEN INPUT
=============================================================== */

function renderHiddenInputs()
{
    const container =
        document.getElementById('hiddenInputs');

    container.innerHTML = '';


    keranjang.forEach(function(item) {

        const inputId = document.createElement('input');

        inputId.type = 'hidden';

        inputId.name = 'id_barang[]';

        inputId.value = item.id_barang;


        const inputQty = document.createElement('input');

        inputQty.type = 'hidden';

        inputQty.name = 'qty[]';

        inputQty.value = item.qty;


        container.appendChild(inputId);

        container.appendChild(inputQty);

    });
}


/* ===============================================================
   QRIS REFERENCE
=============================================================== */

function createQrisReference()
{
    const now = new Date();

    return 'QRIS-DEMO-' +
        now.getFullYear() +
        String(now.getMonth() + 1).padStart(2, '0') +
        String(now.getDate()).padStart(2, '0') +
        '-' +
        String(now.getHours()).padStart(2, '0') +
        String(now.getMinutes()).padStart(2, '0') +
        String(now.getSeconds()).padStart(2, '0');
}


/* ===============================================================
   STOP QRIS SIMULATION
=============================================================== */

function stopQrisSimulation()
{
    if (qrisTimer) {

        clearTimeout(qrisTimer);

        qrisTimer = null;

    }


    if (qrisCountdownTimer) {

        clearInterval(qrisCountdownTimer);

        qrisCountdownTimer = null;

    }
}


/* ===============================================================
   STATUS QRIS
=============================================================== */

function setQrisStatus(type, title, description)
{
    const box = document.getElementById('qrisStatus');

    const titleElement =
        document.getElementById('qrisStatusTitle');

    const descriptionElement =
        document.getElementById('qrisStatusDescription');


    box.className = 'alert mb-0 alert-' + type;

    titleElement.innerText = title;

    descriptionElement.innerText = description;
}


/* ===============================================================
   GENERATE QRIS
=============================================================== */

function generateQRIS()
{
    stopQrisSimulation();


    const total = getTotalKeranjang();

    const qrContainer =
        document.getElementById('qrisCanvas');


    qrContainer.innerHTML = '';


    if (total <= 0) {

        setQrisStatus(
            'secondary',
            'Keranjang kosong',
            'Silakan pilih barang terlebih dahulu.'
        );

        document.getElementById('qrisCountdown').innerText =
            '-';

        return;
    }


    qrisReference = createQrisReference();


    const qrData = JSON.stringify({

        jenis: 'QRIS-DEMO',

        toko: 'SISTEM SALE',

        transaksi: qrisReference,

        total: total,

        keterangan: 'SIMULASI - BUKAN PEMBAYARAN NYATA'

    });


    new QRCode(qrContainer, {

        text: qrData,

        width: 190,

        height: 190,

        colorDark: '#000000',

        colorLight: '#ffffff',

        correctLevel: QRCode.CorrectLevel.H

    });


    setQrisStatus(

        'warning',

        'Menunggu pembayaran...',

        'Pembayaran otomatis diproses dalam 4 detik.'

    );


    let remaining = 4;


    document.getElementById('qrisCountdown').innerText =
        remaining + ' detik';


    qrisCountdownTimer = setInterval(function() {

        remaining--;


        if (remaining <= 0) {

            clearInterval(qrisCountdownTimer);

            qrisCountdownTimer = null;

            document.getElementById('qrisCountdown').innerText =
                'Memproses...';

            return;

        }


        document.getElementById('qrisCountdown').innerText =
            remaining + ' detik';

    }, 1000);


    qrisTimer = setTimeout(function() {

        if (
            document.getElementById('metode_pembayaran').value !== 'QRIS' ||
            keranjang.length === 0
        ) {

            return;

        }


        setQrisStatus(

            'success',

            'Pembayaran berhasil',

            'Pembayaran diterima. Transaksi akan disimpan otomatis.'

        );


        document.getElementById('qrisCountdown').innerText =
            'Berhasil';


        const inputBayar =
            document.getElementById('inputBayar');

        inputBayar.value = total;


        setTimeout(function() {

            const metode =
                document.getElementById('metode_pembayaran').value;


            if (
                metode === 'QRIS' &&
                keranjang.length > 0
            ) {

                document.getElementById('formPenjualan').submit();

            }

        }, 700);


    }, 4000);

}


/* ===============================================================
   HITUNG KEMBALIAN
=============================================================== */

function hitungKembalian()
{
    const total = getTotalKeranjang();

    const bayar =
        Number(document.getElementById('inputBayar').value) || 0;


    const kembalian =
        Math.max(0, bayar - total);


    document.getElementById('kembalianText').innerText =
        formatRupiah(kembalian);
}


/* ===============================================================
   UPDATE PAYMENT UI
=============================================================== */

function updatePaymentUI()
{
    const metode =
        document.getElementById('metode_pembayaran').value;

    const total =
        getTotalKeranjang();


    const qrisBox =
        document.getElementById('qrisPaymentBox');

    const nominalBox =
        document.getElementById('nominalPaymentBox');

    const changeRow =
        document.getElementById('changeRow');

    const btnSimpan =
        document.getElementById('btnSimpan');

    const inputBayar =
        document.getElementById('inputBayar');

    const paymentNote =
        document.getElementById('paymentNote');


    /* =========================================================
       QRIS
    ========================================================= */

    if (metode === 'QRIS') {

        qrisBox.classList.remove('d-none');

        nominalBox.style.display = 'none';

        changeRow.style.display = 'none';

        btnSimpan.style.display = 'none';


        inputBayar.value = total;

        inputBayar.readOnly = true;

        inputBayar.required = false;


        paymentNote.innerHTML = `

            <i class="bi bi-info-circle me-1"></i>

            QRIS menggunakan simulasi pembayaran otomatis.

        `;


        document.getElementById('qrisAmount').innerText =
            formatRupiah(total);


        generateQRIS();

        return;

    }


    /* =========================================================
       NON QRIS
    ========================================================= */

    stopQrisSimulation();


    qrisBox.classList.add('d-none');

    nominalBox.style.display = 'block';

    changeRow.style.display = 'flex';

    btnSimpan.style.display = '';


    inputBayar.readOnly = false;

    inputBayar.required = true;


    if (metode === 'Transfer') {

        inputBayar.value = total;

        inputBayar.readOnly = true;

        inputBayar.required = false;


        paymentNote.innerHTML = `

            <i class="bi bi-info-circle me-1"></i>

            Pembayaran transfer dianggap lunas sesuai total transaksi.

        `;

    } else {

        paymentNote.innerHTML = `

            <i class="bi bi-info-circle me-1"></i>

            Masukkan nominal pembayaran pelanggan.

        `;

    }


    hitungKembalian();

}


/* ===============================================================
   SEARCH BARANG
=============================================================== */

document.getElementById('searchBarang')
    .addEventListener('input', function() {

        const keyword =
            this.value.toLowerCase().trim();


        document
            .querySelectorAll('.produk-item')
            .forEach(function(item) {

                const nama =
                    item.dataset.nama || '';


                if (nama.includes(keyword)) {

                    item.style.display = '';

                } else {

                    item.style.display = 'none';

                }

            });

    });


/* ===============================================================
   METODE PEMBAYARAN
=============================================================== */

document.getElementById('metode_pembayaran')
    .addEventListener('change', function() {

        updatePaymentUI();

    });


/* ===============================================================
   INPUT BAYAR
=============================================================== */

document.getElementById('inputBayar')
    .addEventListener('input', function() {

        hitungKembalian();

    });


/* ===============================================================
   VALIDASI FORM
=============================================================== */

document.getElementById('formPenjualan')
    .addEventListener('submit', function(event) {

        if (keranjang.length === 0) {

            event.preventDefault();

            alert('Keranjang masih kosong.');

            return;

        }


        const total = getTotalKeranjang();

        const metode =
            document.getElementById('metode_pembayaran').value;

        const bayar =
            Number(document.getElementById('inputBayar').value) || 0;


        if (
            metode === 'Tunai' &&
            bayar < total
        ) {

            event.preventDefault();

            alert(
                'Pembayaran kurang.\n\n' +
                'Total: ' + formatRupiah(total) + '\n' +
                'Bayar: ' + formatRupiah(bayar)
            );

            return;

        }

    });


/* ===============================================================
   INITIALIZE
=============================================================== */

renderKeranjang();

updatePaymentUI();

</script>