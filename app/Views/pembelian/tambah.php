<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<div class="main-content">
    <div class="page-heading"><div><div class="text-primary fw-bold small mb-1">PERSEDIAAN</div><h2>Pembelian Baru</h2><p>Hubungkan supplier dengan barang masuk. Setelah disimpan, stok otomatis bertambah.</p></div><a class="btn btn-light border" href="<?= base_url('pembelian') ?>"><i class="bi bi-arrow-left me-1"></i>Kembali</a></div>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <form action="<?= base_url('pembelian/simpan') ?>" method="post" id="purchaseForm">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-xl-8"><div class="surface-card p-4"><h6 class="fw-bold mb-3">Daftar Barang Masuk</h6><div id="purchaseItems"></div><button type="button" class="btn btn-outline-primary mt-2" id="addItem"><i class="bi bi-plus-lg me-1"></i>Tambah Barang</button></div></div>
            <div class="col-xl-4"><div class="surface-card p-4 sticky-top" style="top:20px"><h6 class="fw-bold mb-3">Informasi Pembelian</h6><label class="form-label small fw-semibold">Supplier</label><select class="form-select mb-3" name="id_supplier" required><option value="">Pilih supplier</option><?php foreach ($supplier as $s): ?><option value="<?= $s['id_supplier'] ?>" <?= old('id_supplier')==$s['id_supplier']?'selected':'' ?>><?= esc($s['nama_supplier']) ?></option><?php endforeach; ?></select><label class="form-label small fw-semibold">Catatan</label><textarea class="form-control mb-4" rows="3" name="catatan" placeholder="Opsional"><?= old('catatan') ?></textarea><div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3"><span class="text-muted small">Estimasi total</span><strong id="purchaseTotal" class="fs-5">Rp 0</strong></div><button class="btn btn-primary w-100 py-2"><i class="bi bi-check-circle me-1"></i>Simpan & Tambah Stok</button><div class="small text-muted mt-3"><i class="bi bi-info-circle me-1"></i>Setiap barang masuk otomatis tercatat pada Mutasi Stok.</div></div></div>
        </div>
    </form>
</div>
<template id="itemTemplate"><div class="purchase-row border rounded-3 p-3 mb-2 bg-light-subtle"><div class="row g-2 align-items-end"><div class="col-lg-5"><label class="form-label small fw-semibold">Barang</label><select class="form-select item-product" name="id_barang[]" required><option value="">Pilih barang</option><?php foreach ($barang as $b): ?><option value="<?= $b['id_barang'] ?>" data-cost="<?= $b['harga_beli'] ?>"><?= esc($b['kode_barang'].' - '.$b['nama_barang']) ?></option><?php endforeach; ?></select></div><div class="col-lg-2"><label class="form-label small fw-semibold">Qty</label><input type="number" min="1" value="1" class="form-control item-qty" name="qty[]" required></div><div class="col-lg-3"><label class="form-label small fw-semibold">Harga Beli</label><input type="number" min="0" class="form-control item-cost" name="harga_beli[]" required></div><div class="col-lg-1"><label class="form-label small fw-semibold">Subtotal</label><div class="small fw-bold item-subtotal text-nowrap">Rp 0</div></div><div class="col-lg-1 text-end"><button type="button" class="btn btn-outline-danger remove-item"><i class="bi bi-trash"></i></button></div></div></div></template>
<script>
const wrap=document.getElementById('purchaseItems'),tpl=document.getElementById('itemTemplate');
const rupiah=n=>'Rp '+new Intl.NumberFormat('id-ID').format(n||0);
function recalc(){let total=0;wrap.querySelectorAll('.purchase-row').forEach(r=>{const q=Number(r.querySelector('.item-qty').value)||0,c=Number(r.querySelector('.item-cost').value)||0,s=q*c;total+=s;r.querySelector('.item-subtotal').textContent=rupiah(s)});document.getElementById('purchaseTotal').textContent=rupiah(total)}
function add(){const n=tpl.content.cloneNode(true);wrap.appendChild(n);bind();recalc()}
function bind(){wrap.querySelectorAll('.purchase-row').forEach(r=>{if(r.dataset.bound)return;r.dataset.bound=1;const p=r.querySelector('.item-product'),c=r.querySelector('.item-cost');p.addEventListener('change',()=>{const o=p.selectedOptions[0];if(o&&o.dataset.cost)c.value=o.dataset.cost;recalc()});r.querySelectorAll('input').forEach(i=>i.addEventListener('input',recalc));r.querySelector('.remove-item').addEventListener('click',()=>{if(wrap.children.length>1)r.remove();recalc()})})}
document.getElementById('addItem').addEventListener('click',add);add();
</script>
<?= view('layout/footer') ?>
