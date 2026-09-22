<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<div class="main-content">
    <div class="page-heading"><div><div class="text-success fw-bold small mb-1">GOODS RECEIVING</div><h2>Terima Barang</h2><p><?= esc($pembelian['no_pembelian']) ?> · <?= esc($pembelian['nama_supplier']) ?></p></div><a class="btn btn-light border" href="<?= base_url('pembelian/'.$pembelian['id_pembelian']) ?>"><i class="bi bi-arrow-left me-1"></i>Kembali</a></div>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <form method="post" action="<?= base_url('penerimaan/simpan/'.$pembelian['id_pembelian']) ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-xl-8"><div class="surface-card overflow-hidden"><div class="p-4 border-bottom"><h6 class="fw-bold mb-1">Cocokkan Barang Fisik</h6><small class="text-muted">Isi hanya qty yang benar-benar diterima hari ini. Sistem mendukung penerimaan sebagian.</small></div><div class="table-responsive"><table class="table mb-0 align-middle"><thead><tr><th>Barang</th><th class="text-end">Dipesan</th><th class="text-end">Sudah Diterima</th><th class="text-end">Sisa</th><th style="width:150px">Terima Sekarang</th></tr></thead><tbody>
                <?php foreach($detail as $d): $remaining=(int)$d['qty']-(int)$d['qty_diterima']; ?><tr><td><strong><?= esc($d['nama_barang']) ?></strong><small class="d-block text-muted"><?= esc($d['kode_barang']) ?> · Rp <?= number_format($d['harga_beli'],0,',','.') ?></small></td><td class="text-end"><?= number_format($d['qty']) ?> <?= esc($d['satuan']) ?></td><td class="text-end"><?= number_format($d['qty_diterima']) ?></td><td class="text-end fw-semibold <?= $remaining>0?'text-warning':'text-success' ?>"><?= number_format($remaining) ?></td><td><?php if($remaining>0): ?><input class="form-control" type="number" min="0" max="<?= $remaining ?>" value="0" name="qty_terima[<?= $d['id_detail_pembelian'] ?>]"><?php else: ?><span class="badge text-bg-success">Lengkap</span><?php endif; ?></td></tr><?php endforeach; ?>
            </tbody></table></div></div></div>
            <div class="col-xl-4"><div class="surface-card p-4 sticky-top" style="top:20px"><h6 class="fw-bold mb-3">Dokumen Penerimaan</h6><label class="form-label small fw-semibold">No. Surat Jalan</label><input class="form-control mb-3" name="no_surat_jalan" maxlength="60" value="<?= esc(old('no_surat_jalan')) ?>" placeholder="Contoh: SJ-INV-2209"><label class="form-label small fw-semibold">Catatan Gudang</label><textarea class="form-control mb-4" name="catatan" rows="3" maxlength="255" placeholder="Kondisi barang, selisih, dsb."><?= esc(old('catatan')) ?></textarea><div class="alert alert-light border small"><i class="bi bi-info-circle me-1"></i>Setelah disimpan, stok bertambah sesuai qty yang diterima dan Mutasi Stok tercatat otomatis.</div><button class="btn btn-success w-100 py-2"><i class="bi bi-box-arrow-in-down me-1"></i>Konfirmasi Penerimaan</button></div></div>
        </div>
    </form>
</div>
<?= view('layout/footer') ?>
