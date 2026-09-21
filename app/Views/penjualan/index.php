<?= view('layout/header', ['title' => 'Transaksi Penjualan']) ?>
<?= view('layout/sidebar') ?>

<div class="main-content pos-page">

    <!-- HEADER -->
    <div class="pos-topbar">
        <div>
            <div class="eyebrow">AREA KASIR</div>
            <div class="d-flex align-items-center gap-3">
                <div class="pos-title-icon">
                    <i class="bi bi-cart3"></i>
                </div>
                <div>
                    <h3 class="pos-title mb-0">Transaksi Penjualan</h3>
                    <p class="pos-subtitle mb-0">Buat transaksi baru dengan cepat dan mudah</p>
                </div>
            </div>
        </div>

        <a href="<?= base_url('penjualan/riwayat') ?>" class="history-btn">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Transaksi</span>
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm pos-alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('penjualan/simpan') ?>" method="post" id="formPenjualan">
        <?= csrf_field() ?>

        <div class="pos-layout">

            <!-- KATALOG PRODUK -->
            <section class="catalog-panel">

                <div class="catalog-toolbar">
                    <div class="search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchBarang" placeholder="Cari barang..." autocomplete="off">
                    </div>

                    <div class="product-count">
                        <i class="bi bi-box-seam"></i>
                        <span id="productCount"><?= count($barang) ?></span> produk
                    </div>
                </div>

                <?php
                    $kategoriList = [];
                    foreach ($barang as $b) {
                        if (!empty($b['nama_kategori']) && !in_array($b['nama_kategori'], $kategoriList)) {
                            $kategoriList[] = $b['nama_kategori'];
                        }
                    }
                ?>

                <div class="category-tabs" id="categoryTabs">
                    <button type="button" class="category-btn active" data-category="all">
                        Semua
                    </button>

                    <?php foreach ($kategoriList as $kategori): ?>
                        <button
                            type="button"
                            class="category-btn"
                            data-category="<?= esc($kategori, 'attr') ?>"
                        >
                            <?= esc($kategori) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="product-grid" id="productGrid">

                    <?php foreach ($barang as $b): ?>
                        <?php
                            $namaKategori = $b['nama_kategori'] ?? 'Produk';
                            $stok = (int) $b['stok'];
                            $stokClass = $stok <= 5 ? 'low' : '';
                        ?>

                        <div
                            class="product-card"
                            data-id="<?= $b['id_barang'] ?>"
                            data-name="<?= esc(strtolower($b['nama_barang']), 'attr') ?>"
                            data-category="<?= esc($namaKategori, 'attr') ?>"
                            data-price="<?= $b['harga_jual'] ?>"
                            data-stock="<?= $stok ?>"
                        >
                            <div class="product-visual">
                                <div class="product-icon">
                                    <i class="bi bi-box-seam"></i>
                                </div>

                                <span class="stock-badge <?= $stokClass ?>">
                                    Stok <?= $stok ?>
                                </span>
                            </div>

                            <div class="product-info">
                                <div class="product-code">
                                    <?= esc($b['kode_barang']) ?>
                                </div>

                                <div class="product-name">
                                    <?= esc($b['nama_barang']) ?>
                                </div>

                                <div class="product-bottom">
                                    <div class="product-price">
                                        Rp <?= number_format($b['harga_jual'], 0, ',', '.') ?>
                                    </div>

                                    <div class="product-unit">
                                        <?= esc($b['satuan'] ?? 'pcs') ?>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="add-product-btn">
                                <i class="bi bi-cart-plus"></i>
                                Tambah
                            </button>
                        </div>
                    <?php endforeach; ?>

                    <div class="no-product" id="noProduct" style="display:none;">
                        <div class="no-product-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <strong>Barang tidak ditemukan</strong>
                        <span>Coba gunakan kata kunci pencarian lain.</span>
                    </div>

                </div>
            </section>


            <!-- CHECKOUT -->
            <aside class="checkout-panel">

                <div class="checkout-header">
                    <div>
                        <div class="checkout-title">
                            <i class="bi bi-cart3"></i>
                            Keranjang
                        </div>
                        <small>Transaksi barang</small>
                    </div>

                    <button type="button" class="clear-cart-btn" id="btnKosongkan">
                        Kosongkan
                    </button>
                </div>


                <!-- CUSTOMER -->
                <div class="checkout-section">
                    <label class="checkout-label">
                        <i class="bi bi-person"></i>
                        Customer
                    </label>

                    <select name="id_customer" class="form-select checkout-input">
                        <option value="">Customer Umum</option>

                        <?php foreach ($customer as $c): ?>
                            <option value="<?= $c['id_customer'] ?>">
                                <?= esc($c['nama_customer']) ?>
                                <?php if (!empty($c['no_telp'])): ?>
                                    - <?= esc($c['no_telp']) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <!-- CART -->
                <div class="cart-area">
                    <div class="cart-empty" id="emptyCart">
                        <div class="cart-empty-icon">
                            <i class="bi bi-cart-x"></i>
                        </div>
                        <strong>Keranjang masih kosong</strong>
                        <span>Pilih produk dari katalog untuk mulai menjual.</span>
                    </div>

                    <div class="cart-items" id="cartItems"></div>
                </div>


                <!-- SUMMARY -->
                <div class="summary-area">
                    <div class="summary-row">
                        <span>Jumlah item</span>
                        <strong id="jumlahItem">0 Item</strong>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong id="subtotalText">Rp 0</strong>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-total">
                        <span>Total</span>
                        <strong id="totalText">Rp 0</strong>
                    </div>
                </div>


                <!-- PAYMENT -->
                <div class="payment-area">

                    <label class="checkout-label">
                        <i class="bi bi-credit-card"></i>
                        Metode pembayaran
                    </label>

                    <select class="form-select checkout-input" name="metode_pembayaran" id="metodePembayaran">
                        <option value="Tunai">Tunai</option>
                        <option value="QRIS">QRIS</option>
                        <option value="Transfer">Transfer</option>
                    </select>

                    <label class="checkout-label mt-3">
                        <i class="bi bi-cash-stack"></i>
                        Uang diterima
                    </label>

                    <div class="money-input">
                        <span>Rp</span>
                        <input
                            type="number"
                            name="bayar"
                            id="inputBayar"
                            min="0"
                            placeholder="Masukkan nominal tunai"
                            required
                        >
                    </div>

                    <div class="change-row">
                        <div>
                            <small>Kembalian</small>
                            <strong id="kembalianText">Rp 0</strong>
                        </div>
                        <div class="change-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="confirm-btn"
                        id="btnSimpan"
                        disabled
                    >
                        <i class="bi bi-check-circle"></i>
                        Konfirmasi transaksi
                    </button>

                    <div class="payment-note">
                        <i class="bi bi-shield-check"></i>
                        Pastikan barang dan nominal pembayaran sudah benar.
                    </div>

                </div>

            </aside>

        </div>
    </form>
</div>



<!-- Phase 7: QR Payment Simulation -->
<div class="modal fade" id="qrisDemoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
    <div class="modal-header"><div><h5 class="modal-title fw-bold">QR Payment Demo</h5><small class="text-muted">Simulasi dynamic QR — bukan QRIS sungguhan</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body text-center p-4">
      <div class="fw-bold fs-3 mb-1" id="qrisDemoAmount">Rp 0</div>
      <div class="text-muted small mb-3">Scan dari HP, lalu buka link hasil scan.</div>
      <div id="qrisDemoQr" class="d-inline-block bg-white p-3 border rounded-3"></div>
      <div class="mt-3"><span class="badge text-bg-warning" id="qrisDemoStatus">MENUNGGU PEMBAYARAN</span></div>
      <div class="small text-muted mt-2">Sisa waktu <strong id="qrisDemoCountdown">05:00</strong> • token sekali pakai</div>
      <div class="small text-muted mt-1">Ref: <span class="font-monospace" id="qrisDemoReference">-</span></div>
      <button type="button" class="btn btn-outline-danger btn-sm mt-3" id="qrisDemoCancelBtn" onclick="cancelActiveDemoPayment('QRIS')">
        <i class="bi bi-x-circle me-1"></i>Batalkan Pembayaran
      </button>
      <div class="alert alert-warning text-start small mt-3 mb-0 d-none" id="qrisNetworkWarning">Kamu membuka KAMELA lewat localhost. HP tidak bisa membuka alamat localhost milik laptop. Untuk scan lintas perangkat, jalankan server pada jaringan LAN dan buka KAMELA memakai IP laptop, misalnya <b>http://192.168.x.x:8080</b>.</div>
    </div>
  </div></div>
</div>

<!-- Phase 8: Transfer / Virtual Account Demo -->
<div class="modal fade" id="transferDemoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
    <div class="modal-header">
      <div><h5 class="modal-title fw-bold">Transfer Demo</h5><small class="text-muted">Virtual Account + KAMELA Bank Simulator</small></div>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body p-4">
      <div class="text-center mb-3">
        <div class="text-muted small">Total Transfer</div>
        <div class="fw-bold fs-3" id="transferDemoAmount">Rp 0</div>
      </div>
      <div class="border rounded-3 p-3 mb-3">
        <div class="small text-muted">KAMELA Virtual Account Demo</div>
        <div class="fw-bold fs-4 font-monospace" id="transferDemoVa">-</div>
      </div>
      <div class="text-center">
        <div class="small text-muted mb-2">Scan untuk membuka <b>KAMELA Bank Demo</b> di HP.</div>
        <div id="transferDemoQr" class="d-inline-block bg-white p-3 border rounded-3"></div>
        <div class="small text-muted mt-2">QR ini hanya membuka bank simulator, bukan langsung membayar.</div>
        <div class="mt-3"><span class="badge text-bg-warning" id="transferDemoStatus">MENUNGGU TRANSFER</span></div>
        <div class="small text-muted mt-2">Sisa waktu <strong id="transferDemoCountdown">10:00</strong></div>
        <button type="button" class="btn btn-outline-danger btn-sm mt-3" id="transferDemoCancelBtn" onclick="cancelActiveDemoPayment('TRANSFER')">
          <i class="bi bi-x-circle me-1"></i>Batalkan Transfer
        </button>
      </div>
      <div class="alert alert-warning small mt-3 mb-0 d-none" id="transferNetworkWarning">KAMELA sedang dibuka lewat localhost. HP tidak dapat membuka alamat localhost milik laptop. Gunakan IP LAN laptop seperti <b>http://192.168.x.x:8080</b>.</div>
    </div>
  </div></div>
</div>

<script src="<?= base_url('assets/js/kamela-qr-core.js') ?>"></script>

<style>
#qrisDemoQr svg,#transferDemoQr svg{display:block;width:260px;height:260px;max-width:70vw;}

/* =========================================================
   POS SALES - MODERN UI
========================================================= */

.pos-page {
    background: #f8f9fa;
    min-height: 100vh;
}

/* HEADER */
.pos-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
}

.eyebrow {
    color: #6c757d;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    margin-bottom: 6px;
}

.pos-title-icon {
    width: 46px;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 13px;
    background: #eaf2ff;
    color: #0d6efd;
    font-size: 21px;
}

.pos-title {
    color: #212529;
    font-size: 25px;
    font-weight: 700;
}

.pos-subtitle {
    color: #6c757d;
    font-size: 13px;
    margin-top: 3px;
}

.history-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 16px;
    border: 1px solid #dfe7e2;
    border-radius: 11px;
    background: #fff;
    color: #344054;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: .2s;
}

.history-btn:hover {
    background: #f4f8ff;
    color: #0d6efd;
    border-color: #cfe2ff;
}

.pos-alert {
    border-radius: 12px;
}


/* MAIN GRID */
.pos-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 390px;
    gap: 22px;
    align-items: start;
}


/* CATALOG */
.catalog-panel {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 5px 20px rgba(13, 110, 253, .04);
}

.catalog-toolbar {
    display: flex;
    align-items: center;
    gap: 14px;
}

.search-wrap {
    position: relative;
    flex: 1;
}

.search-wrap i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #87958f;
    font-size: 17px;
}

.search-wrap input {
    width: 100%;
    height: 48px;
    padding: 0 16px 0 44px;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    outline: none;
    color: #212529;
    font-size: 13px;
    background: #fff;
}

.search-wrap input:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, .08);
}

.product-count {
    height: 48px;
    padding: 0 15px;
    display: flex;
    align-items: center;
    gap: 7px;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    color: #6c757d;
    background: #fff;
    white-space: nowrap;
    font-size: 12px;
    font-weight: 600;
}

.product-count i {
    color: #0d6efd;
}


/* CATEGORY */
.category-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin: 18px 0;
}

.category-btn {
    border: 0;
    background: #f1f3f5;
    color: #6c757d;
    border-radius: 50px;
    padding: 9px 16px;
    font-size: 12px;
    font-weight: 600;
    transition: .2s;
}

.category-btn:hover {
    background: #eaf2ff;
    color: #0d6efd;
}

.category-btn.active {
    background: #0d6efd;
    color: #fff;
}


/* PRODUCT GRID */
.product-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 13px;
}

.product-card {
    min-width: 0;
    padding: 12px;
    border: 1px solid #e2e6ea;
    border-radius: 14px;
    background: #fff;
    transition: transform .2s, box-shadow .2s, border-color .2s;
}

.product-card:hover {
    transform: translateY(-2px);
    border-color: #cfe2ff;
    box-shadow: 0 7px 20px rgba(13, 110, 253, .08);
}

.product-visual {
    position: relative;
    height: 112px;
    border-radius: 11px;
    background: linear-gradient(135deg, #f8f9fa, #f4f8ff);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 11px;
}

.product-icon {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 17px;
    background: #eaf2ff;
    color: #0d6efd;
    font-size: 26px;
}

.stock-badge {
    position: absolute;
    right: 8px;
    bottom: 8px;
    padding: 5px 8px;
    border-radius: 50px;
    background: #e7f1ff;
    color: #0d6efd;
    font-size: 10px;
    font-weight: 700;
}

.stock-badge.low {
    background: #fff3cd;
    color: #856404;
}

.product-code {
    color: #adb5bd;
    font-size: 9px;
    font-weight: 600;
    margin-bottom: 3px;
}

.product-name {
    color: #263c35;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.45;
    min-height: 35px;
}

.product-bottom {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 8px;
    margin-top: 5px;
}

.product-price {
    color: #0d6efd;
    font-size: 13px;
    font-weight: 700;
}

.product-unit {
    color: #adb5bd;
    font-size: 9px;
}

.add-product-btn {
    width: 100%;
    border: 0;
    margin-top: 11px;
    padding: 9px 8px;
    border-radius: 9px;
    background: #0d6efd;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    transition: .2s;
}

.add-product-btn:hover {
    background: #0b5ed7;
}

.add-product-btn i {
    margin-right: 4px;
}

.product-card.out-of-stock {
    opacity: .55;
}

.product-card.out-of-stock .add-product-btn {
    background: #adb8b3;
    cursor: not-allowed;
}

.no-product {
    grid-column: 1 / -1;
    padding: 55px 20px;
    text-align: center;
    color: #74837d;
}

.no-product-icon {
    width: 58px;
    height: 58px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    border-radius: 16px;
    background: #f8f9fa;
    color: #adb5bd;
    font-size: 22px;
}

.no-product strong,
.no-product span {
    display: block;
}

.no-product strong {
    color: #495057;
    font-size: 14px;
    margin-bottom: 3px;
}

.no-product span {
    font-size: 12px;
}


/* CHECKOUT */
.checkout-panel {
    position: sticky;
    top: 20px;
    background: #fff;
    border: 1px solid #e4ebe7;
    border-radius: 18px;
    box-shadow: 0 7px 25px rgba(35, 62, 53, .06);
    overflow: hidden;
}

.checkout-header {
    padding: 20px 20px 17px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #edf1ef;
}

.checkout-title {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #212529;
    font-size: 18px;
    font-weight: 700;
}

.checkout-title i {
    color: #0d6efd;
}

.checkout-header small {
    color: #6c757d;
    font-size: 11px;
    margin-left: 28px;
}

.clear-cart-btn {
    border: 0;
    background: transparent;
    color: #df6256;
    font-size: 10px;
    font-weight: 700;
    padding: 7px;
}

.clear-cart-btn:hover {
    color: #bb3e32;
}

.checkout-section {
    padding: 16px 20px 12px;
}

.checkout-label {
    display: block;
    color: #344054;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 7px;
}

.checkout-label i {
    color: #0d6efd;
    margin-right: 4px;
}

.checkout-input {
    min-height: 44px;
    border-color: #dee2e6;
    border-radius: 10px;
    font-size: 12px;
    color: #343a40;
    box-shadow: none !important;
}


/* CART AREA */
.cart-area {
    padding: 0 20px;
}

.cart-empty {
    min-height: 180px;
    padding: 25px 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    border: 1px dashed #d8e1dd;
    border-radius: 14px;
    color: #6c757d;
}

.cart-empty-icon {
    width: 55px;
    height: 55px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f8f9fa;
    color: #adb5bd;
    font-size: 24px;
    margin-bottom: 11px;
}

.cart-empty strong {
    color: #495057;
    font-size: 13px;
    margin-bottom: 4px;
}

.cart-empty span {
    font-size: 11px;
}

.cart-items {
    max-height: 290px;
    overflow-y: auto;
}

.cart-item {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 10px;
    padding: 12px 0;
    border-bottom: 1px solid #edf1ef;
}

.cart-item:last-child {
    border-bottom: 0;
}

.cart-item-name {
    color: #343a40;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
}

.cart-item-price {
    color: #6c757d;
    font-size: 10px;
    margin-top: 3px;
}

.cart-item-total {
    color: #0d6efd;
    font-size: 12px;
    font-weight: 700;
    text-align: right;
}

.cart-item-controls {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 7px;
}

.qty-btn {
    width: 25px;
    height: 25px;
    padding: 0;
    border: 1px solid #dce5e0;
    border-radius: 7px;
    background: #fff;
    color: #6c757d;
    font-size: 12px;
}

.qty-value {
    min-width: 28px;
    text-align: center;
    color: #343a40;
    font-size: 11px;
    font-weight: 700;
}

.remove-btn {
    width: 25px;
    height: 25px;
    padding: 0;
    border: 0;
    border-radius: 7px;
    background: #fff1ef;
    color: #d85b50;
    font-size: 11px;
    margin-left: 3px;
}


/* SUMMARY */
.summary-area {
    margin: 13px 20px 0;
    padding: 15px 0;
    border-top: 1px solid #e9efec;
}

.summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 0;
    color: #6c757d;
    font-size: 11px;
}

.summary-row strong {
    color: #495057;
    font-size: 11px;
}

.summary-divider {
    border-top: 1px dashed #dce4e0;
    margin: 8px 0;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 2px;
}

.summary-total span {
    color: #212529;
    font-size: 15px;
    font-weight: 700;
}

.summary-total strong {
    color: #0d6efd;
    font-size: 22px;
    font-weight: 800;
}


/* PAYMENT */
.payment-area {
    padding: 0 20px 20px;
}

.money-input {
    display: flex;
    align-items: center;
    height: 45px;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    overflow: hidden;
}

.money-input span {
    padding-left: 13px;
    color: #6d7c76;
    font-size: 12px;
    font-weight: 700;
}

.money-input input {
    width: 100%;
    height: 100%;
    border: 0;
    outline: none;
    padding: 0 12px 0 7px;
    color: #343a40;
    font-size: 12px;
}

.money-input:focus-within {
    border-color: #86b7fe;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, .08);
}

.change-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 13px 14px;
    margin-top: 12px;
    border: 1px solid #dceee5;
    border-radius: 11px;
    background: #f8fbff;
}

.change-row small {
    display: block;
    color: #6c757d;
    font-size: 10px;
    margin-bottom: 2px;
}

.change-row strong {
    display: block;
    color: #0d6efd;
    font-size: 17px;
}

.change-icon {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #0d6efd;
    color: #fff;
}

.confirm-btn {
    width: 100%;
    border: 0;
    margin-top: 13px;
    min-height: 47px;
    border-radius: 11px;
    background: #0d6efd;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    transition: .2s;
}

.confirm-btn:not(:disabled):hover {
    background: #0b5ed7;
    transform: translateY(-1px);
}

.confirm-btn:disabled {
    background: #b8c5c0;
    cursor: not-allowed;
}

.confirm-btn i {
    margin-right: 5px;
}

.payment-note {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    margin-top: 11px;
    padding: 9px 10px;
    border-radius: 9px;
    background: #f6f8f7;
    color: #6c757d;
    font-size: 9px;
    line-height: 1.5;
}

.payment-note i {
    color: #0d6efd;
    font-size: 13px;
}


/* RESPONSIVE */
@media (max-width: 1200px) {
    .pos-layout {
        grid-template-columns: minmax(0, 1fr) 350px;
    }

    .product-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 991px) {
    .pos-layout {
        grid-template-columns: 1fr;
    }

    .checkout-panel {
        position: static;
    }

    .product-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .pos-page {
        padding-bottom: 25px;
    }

    .pos-topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .history-btn {
        width: 100%;
        justify-content: center;
    }

    .catalog-panel {
        padding: 15px;
    }

    .catalog-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 480px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
}
</style>


<script>
/* =========================================================
   DATA
========================================================= */

const produkData = <?= json_encode(
    array_values($barang),
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

let keranjang = [];


/* =========================================================
   HELPER
========================================================= */

function formatRupiah(angka) {
    return 'Rp ' + Number(angka).toLocaleString('id-ID');
}

function getProduct(id) {
    return produkData.find(item => String(item.id_barang) === String(id));
}


/* =========================================================
   TAMBAH PRODUK
========================================================= */

document.querySelectorAll('.add-product-btn').forEach(button => {

    button.addEventListener('click', function () {

        const card = this.closest('.product-card');

        const idBarang = card.dataset.id;
        const namaBarang = card.querySelector('.product-name').innerText;
        const harga = parseFloat(card.dataset.price);
        const stok = parseInt(card.dataset.stock);

        if (!idBarang || stok <= 0) {
            alert('Barang sedang tidak tersedia.');
            return;
        }

        const existing = keranjang.find(
            item => String(item.id_barang) === String(idBarang)
        );

        if (existing) {

            if (existing.qty + 1 > stok) {
                alert(
                    'Jumlah melebihi stok yang tersedia.\n' +
                    'Stok tersedia: ' + stok
                );
                return;
            }

            existing.qty++;

        } else {

            keranjang.push({
                id_barang: idBarang,
                nama_barang: namaBarang,
                harga: harga,
                qty: 1,
                stok: stok
            });
        }

        renderKeranjang();
    });
});


/* =========================================================
   RENDER KERANJANG
========================================================= */

function renderKeranjang() {

    const container = document.getElementById('cartItems');
    const empty = document.getElementById('emptyCart');

    container.innerHTML = '';

    if (keranjang.length === 0) {

        empty.style.display = 'flex';

    } else {

        empty.style.display = 'none';

        keranjang.forEach((item, index) => {

            const subtotal = item.harga * item.qty;

            const row = document.createElement('div');
            row.className = 'cart-item';

            const left = document.createElement('div');

            const name = document.createElement('div');
            name.className = 'cart-item-name';
            name.textContent = item.nama_barang;

            const price = document.createElement('div');
            price.className = 'cart-item-price';
            price.textContent = formatRupiah(item.harga) + ' / item';

            const controls = document.createElement('div');
            controls.className = 'cart-item-controls';

            const minus = document.createElement('button');
            minus.type = 'button';
            minus.className = 'qty-btn';
            minus.innerHTML = '<i class="bi bi-dash"></i>';
            minus.onclick = () => ubahQty(index, item.qty - 1);

            const qty = document.createElement('span');
            qty.className = 'qty-value';
            qty.textContent = item.qty;

            const plus = document.createElement('button');
            plus.type = 'button';
            plus.className = 'qty-btn';
            plus.innerHTML = '<i class="bi bi-plus"></i>';
            plus.onclick = () => ubahQty(index, item.qty + 1);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'remove-btn';
            remove.innerHTML = '<i class="bi bi-trash3"></i>';
            remove.title = 'Hapus barang';
            remove.onclick = () => hapusItem(index);

            controls.append(minus, qty, plus, remove);
            left.append(name, price, controls);

            const right = document.createElement('div');

            const total = document.createElement('div');
            total.className = 'cart-item-total';
            total.textContent = formatRupiah(subtotal);

            right.appendChild(total);

            row.append(left, right);
            container.appendChild(row);

            const hiddenId = document.createElement('input');
            hiddenId.type = 'hidden';
            hiddenId.name = 'id_barang[]';
            hiddenId.value = item.id_barang;

            const hiddenQty = document.createElement('input');
            hiddenQty.type = 'hidden';
            hiddenQty.name = 'qty[]';
            hiddenQty.value = item.qty;

            container.append(hiddenId, hiddenQty);
        });
    }

    let total = 0;
    let totalQty = 0;

    keranjang.forEach(item => {
        total += item.harga * item.qty;
        totalQty += item.qty;
    });

    document.getElementById('subtotalText').innerText = formatRupiah(total);
    document.getElementById('totalText').innerText = formatRupiah(total);
    document.getElementById('jumlahItem').innerText =
        totalQty + ' Item';

    hitungKembalian();
}


/* =========================================================
   UBAH QTY
========================================================= */

function ubahQty(index, value) {

    const qty = parseInt(value);

    if (!qty || qty <= 0) {
        hapusItem(index);
        return;
    }

    if (qty > keranjang[index].stok) {
        alert(
            'Jumlah melebihi stok yang tersedia.\n' +
            'Stok tersedia: ' + keranjang[index].stok
        );
        return;
    }

    keranjang[index].qty = qty;
    renderKeranjang();
}


/* =========================================================
   HAPUS ITEM
========================================================= */

function hapusItem(index) {
    keranjang.splice(index, 1);
    renderKeranjang();
}


/* =========================================================
   KOSONGKAN KERANJANG
========================================================= */

document.getElementById('btnKosongkan').addEventListener('click', function () {

    if (keranjang.length === 0) {
        return;
    }

    if (confirm('Yakin ingin mengosongkan keranjang?')) {
        keranjang = [];
        renderKeranjang();
    }
});


/* =========================================================
   HITUNG KEMBALIAN
========================================================= */

document.getElementById('inputBayar').addEventListener(
    'input',
    hitungKembalian
);

const metodePembayaran = document.getElementById('metodePembayaran');
metodePembayaran.addEventListener('change', function () {
    const bayarInput = document.getElementById('inputBayar');
    if (this.value !== 'Tunai') {
        let total = 0;
        keranjang.forEach(item => total += item.harga * item.qty);
        bayarInput.value = total || '';
        bayarInput.readOnly = true;
        bayarInput.placeholder = 'Nominal mengikuti total';
    } else {
        bayarInput.readOnly = false;
        bayarInput.value = '';
        bayarInput.placeholder = 'Masukkan nominal tunai';
    }
    hitungKembalian();
});

function hitungKembalian() {

    let total = 0;

    keranjang.forEach(item => {
        total += item.harga * item.qty;
    });

    const bayar =
        parseFloat(document.getElementById('inputBayar').value) || 0;

    const kembalian = bayar - total;

    const kembalianText =
        document.getElementById('kembalianText');

    if (kembalian >= 0) {

        kembalianText.innerText =
            formatRupiah(kembalian);

    } else {

        kembalianText.innerText =
            'Kurang ' + formatRupiah(Math.abs(kembalian));
    }

    document.getElementById('btnSimpan').disabled =
        keranjang.length === 0 || bayar < total;
}


/* =========================================================
   SEARCH PRODUK
========================================================= */

const searchInput = document.getElementById('searchBarang');
const productCards = document.querySelectorAll('.product-card');
const noProduct = document.getElementById('noProduct');
const productCount = document.getElementById('productCount');

let activeCategory = 'all';

function filterProducts() {

    const keyword = searchInput.value.toLowerCase().trim();
    let visible = 0;

    productCards.forEach(card => {

        const name = card.dataset.name || '';
        const category = card.dataset.category || '';

        const matchName = name.includes(keyword);
        const matchCategory =
            activeCategory === 'all' ||
            category === activeCategory;

        if (matchName && matchCategory) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    productCount.innerText = visible;
    noProduct.style.display = visible === 0 ? 'block' : 'none';
}

searchInput.addEventListener('input', filterProducts);


/* =========================================================
   FILTER KATEGORI
========================================================= */

document.querySelectorAll('.category-btn').forEach(button => {

    button.addEventListener('click', function () {

        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        this.classList.add('active');
        activeCategory = this.dataset.category;

        filterProducts();
    });
});


/* =========================================================
   VALIDASI SUBMIT + PHASE 7 QR PAYMENT DEMO
========================================================= */

let qrisPollTimer = null;
let transferPollTimer = null;
let qrisCountdownTimer = null;
let transferCountdownTimer = null;
let activeQrisPayment = null;
let activeTransferPayment = null;

function drawKamelaQr(text, targetId = 'qrisDemoQr') {
    const target = document.getElementById(targetId);
    target.innerHTML = '';
    const qr = new KamelaQRCodeCore(-1, KamelaQRErrorCorrectLevel.M);
    qr.addData(text);
    qr.make();
    const count = qr.getModuleCount();
    const quiet = 4;
    const size = count + quiet * 2;
    let cells = '';
    for (let r = 0; r < count; r++) {
        for (let c = 0; c < count; c++) {
            if (qr.isDark(r, c)) cells += `<rect x="${c+quiet}" y="${r+quiet}" width="1" height="1"/>`;
        }
    }
    target.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${size} ${size}" shape-rendering="crispEdges"><rect width="100%" height="100%" fill="white"/><g fill="black">${cells}</g></svg>`;
}

function updateCsrfToken(name, hash) {
    if (!name || !hash) return;
    document.querySelectorAll(`input[name="${name}"]`).forEach(input => input.value = hash);
}

function formatCountdown(seconds) {
    seconds = Math.max(0, Number(seconds) || 0);
    const min = Math.floor(seconds / 60).toString().padStart(2, '0');
    const sec = Math.floor(seconds % 60).toString().padStart(2, '0');
    return `${min}:${sec}`;
}

function stopPaymentCountdown(kind) {
    const key = kind === 'QRIS' ? 'qris' : 'transfer';
    const timer = key === 'qris' ? qrisCountdownTimer : transferCountdownTimer;
    if (timer) clearInterval(timer);
    if (key === 'qris') qrisCountdownTimer = null;
    else transferCountdownTimer = null;
}

function startPaymentCountdown(kind, seconds) {
    stopPaymentCountdown(kind);
    const id = kind === 'QRIS' ? 'qrisDemoCountdown' : 'transferDemoCountdown';
    const target = document.getElementById(id);
    const deadline = Date.now() + Math.max(0, Number(seconds) || 0) * 1000;
    const tick = () => {
        const remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
        target.innerText = formatCountdown(remaining);
        if (remaining <= 0) stopPaymentCountdown(kind);
    };
    tick();
    const timer = setInterval(tick, 1000);
    if (kind === 'QRIS') qrisCountdownTimer = timer;
    else transferCountdownTimer = timer;
}

function stopPaymentPolling(kind) {
    if (kind === 'QRIS' && qrisPollTimer) { clearInterval(qrisPollTimer); qrisPollTimer = null; }
    if (kind === 'TRANSFER' && transferPollTimer) { clearInterval(transferPollTimer); transferPollTimer = null; }
}

function markPaymentFinished(kind, status, successUrl = null) {
    const isQris = kind === 'QRIS';
    const badge = document.getElementById(isQris ? 'qrisDemoStatus' : 'transferDemoStatus');
    const cancelBtn = document.getElementById(isQris ? 'qrisDemoCancelBtn' : 'transferDemoCancelBtn');
    stopPaymentPolling(kind);
    stopPaymentCountdown(kind);
    cancelBtn.disabled = true;

    if (status === 'PAID') {
        badge.className = 'badge text-bg-success';
        badge.innerText = isQris ? '✓ PEMBAYARAN BERHASIL' : '✓ TRANSFER BERHASIL';
        if (successUrl) setTimeout(() => { window.location.href = successUrl; }, 900);
        return;
    }

    if (status === 'CANCELLED') {
        badge.className = 'badge text-bg-secondary';
        badge.innerText = 'DIBATALKAN';
    } else {
        badge.className = 'badge text-bg-danger';
        badge.innerText = status;
    }
    document.getElementById('btnSimpan').disabled = false;
}

async function cancelActiveDemoPayment(kind) {
    const state = kind === 'QRIS' ? activeQrisPayment : activeTransferPayment;
    if (!state || !state.cancel_path) return;
    const button = document.getElementById(kind === 'QRIS' ? 'qrisDemoCancelBtn' : 'transferDemoCancelBtn');
    if (!confirm('Batalkan pembayaran ini? Stok tidak akan berubah.')) return;

    button.disabled = true;
    const body = new FormData();
    body.append(state.csrf_name, state.csrf_hash);
    try {
        const response = await fetch(window.location.origin + state.cancel_path, {
            method: 'POST', body, headers: {'X-Requested-With':'XMLHttpRequest'}
        });
        const data = await response.json();
        if (data.csrf_name && data.csrf_hash) {
            updateCsrfToken(data.csrf_name, data.csrf_hash);
            state.csrf_name = data.csrf_name;
            state.csrf_hash = data.csrf_hash;
        }
        if (!response.ok || !data.ok) throw new Error(data.message || 'Gagal membatalkan pembayaran.');
        markPaymentFinished(kind, data.status || 'CANCELLED');
    } catch (err) {
        alert(err.message || 'Gagal membatalkan pembayaran.');
        button.disabled = false;
    }
}

async function mulaiQrisDemo(form) {
    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Membuat QR...';
    try {
        const response = await fetch('<?= site_url('penjualan/qris-demo/prepare') ?>', {
            method: 'POST', body: new FormData(form), headers: {'X-Requested-With':'XMLHttpRequest'}
        });
        const data = await response.json();
        if (!response.ok || !data.ok) throw new Error(data.message || 'Gagal membuat QR.');
        updateCsrfToken(data.csrf_name, data.csrf_hash);
        activeQrisPayment = data;

        const confirmUrl = window.location.origin + data.confirm_path;
        document.getElementById('qrisDemoAmount').innerText = formatRupiah(data.amount);
        document.getElementById('qrisDemoReference').innerText = data.payment_reference || '-';
        document.getElementById('qrisDemoStatus').className = 'badge text-bg-warning';
        document.getElementById('qrisDemoStatus').innerText = 'MENUNGGU PEMBAYARAN';
        document.getElementById('qrisDemoCancelBtn').disabled = false;
        document.getElementById('qrisNetworkWarning').classList.toggle('d-none', !['localhost','127.0.0.1'].includes(window.location.hostname));
        drawKamelaQr(confirmUrl);
        startPaymentCountdown('QRIS', data.expires_in || 300);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('qrisDemoModal')).show();

        stopPaymentPolling('QRIS');
        qrisPollTimer = setInterval(async () => {
            try {
                const r = await fetch(window.location.origin + data.status_path, {headers:{'X-Requested-With':'XMLHttpRequest'}});
                const st = await r.json();
                if (!st.ok) return;
                if (typeof st.remaining_seconds !== 'undefined') {
                    document.getElementById('qrisDemoCountdown').innerText = formatCountdown(st.remaining_seconds);
                }
                if (['PAID','EXPIRED','FAILED','CANCELLED'].includes(st.status)) {
                    markPaymentFinished('QRIS', st.status, st.success_url);
                }
            } catch (_) {}
        }, 1000);
    } catch (err) {
        alert(err.message || 'Gagal membuat QR pembayaran demo.');
        btn.disabled = false;
    } finally {
        btn.innerHTML = oldHtml;
    }
}

async function mulaiTransferDemo(form) {
    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Membuat VA...';

    try {
        const response = await fetch('<?= site_url('penjualan/transfer-demo/prepare') ?>', {
            method: 'POST', body: new FormData(form), headers: {'X-Requested-With':'XMLHttpRequest'}
        });
        const data = await response.json();
        if (!response.ok || !data.ok) throw new Error(data.message || 'Gagal membuat transfer demo.');
        updateCsrfToken(data.csrf_name, data.csrf_hash);
        activeTransferPayment = data;

        const bankUrl = window.location.origin + data.bank_path;
        document.getElementById('transferDemoAmount').innerText = formatRupiah(data.amount);
        document.getElementById('transferDemoVa').innerText = data.virtual_account;
        document.getElementById('transferDemoStatus').className = 'badge text-bg-warning';
        document.getElementById('transferDemoStatus').innerText = 'MENUNGGU TRANSFER';
        document.getElementById('transferDemoCancelBtn').disabled = false;
        document.getElementById('transferNetworkWarning').classList.toggle('d-none', !['localhost','127.0.0.1'].includes(window.location.hostname));
        drawKamelaQr(bankUrl, 'transferDemoQr');
        startPaymentCountdown('TRANSFER', data.expires_in || 600);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('transferDemoModal')).show();

        stopPaymentPolling('TRANSFER');
        transferPollTimer = setInterval(async () => {
            try {
                const r = await fetch(window.location.origin + data.status_path, {headers:{'X-Requested-With':'XMLHttpRequest'}});
                const st = await r.json();
                if (!st.ok) return;
                if (typeof st.remaining_seconds !== 'undefined') {
                    document.getElementById('transferDemoCountdown').innerText = formatCountdown(st.remaining_seconds);
                }
                if (['PAID','EXPIRED','FAILED','CANCELLED'].includes(st.status)) {
                    markPaymentFinished('TRANSFER', st.status, st.success_url);
                }
            } catch (_) {}
        }, 1000);
    } catch (err) {
        alert(err.message || 'Gagal membuat transfer demo.');
        btn.disabled = false;
    } finally {
        btn.innerHTML = oldHtml;
    }
}

document.getElementById('formPenjualan').addEventListener('submit', function (e) {
    if (keranjang.length === 0) { e.preventDefault(); alert('Keranjang masih kosong.'); return; }
    let total = 0; keranjang.forEach(item => total += item.harga * item.qty);
    const metode = document.getElementById('metodePembayaran').value;
    const bayar = parseFloat(document.getElementById('inputBayar').value) || 0;

    if (metode === 'QRIS') {
        e.preventDefault();
        mulaiQrisDemo(this);
        return;
    }

    if (metode === 'Transfer') {
        e.preventDefault();
        mulaiTransferDemo(this);
        return;
    }

    if (bayar < total) { e.preventDefault(); alert('Uang pembayaran kurang.'); }
});

/* Initial state */
renderKeranjang();
</script>

<?= view('layout/footer') ?>
