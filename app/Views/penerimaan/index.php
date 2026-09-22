<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<div class="main-content">
    <div class="page-heading">
        <div><div class="text-success fw-bold small mb-1">WAREHOUSE</div><h2>Penerimaan Barang</h2><p>Dokumen barang yang benar-benar tiba di gudang dan telah menambah stok.</p></div>
        <a class="btn btn-outline-primary" href="<?= base_url('pembelian') ?>"><i class="bi bi-clipboard2-check me-1"></i>Lihat Purchase Order</a>
    </div>
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <div class="surface-card p-3 mb-3"><form class="row g-2" method="get"><div class="col-md-10"><input class="form-control" name="keyword" value="<?= esc($keyword) ?>" placeholder="Cari no. penerimaan, PO, supplier, atau surat jalan..."></div><div class="col-md-2 d-grid"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Cari</button></div></form></div>
    <div class="surface-card overflow-hidden"><div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead><tr><th>No. Penerimaan</th><th>Tanggal</th><th>Purchase Order</th><th>Supplier</th><th>Petugas Gudang</th><th>Surat Jalan</th><th class="text-center">Aksi</th></tr></thead><tbody>
    <?php if ($rows): foreach($rows as $r): ?><tr><td class="fw-semibold"><?= esc($r['no_penerimaan']) ?></td><td><?= date('d M Y H:i',strtotime($r['tanggal'])) ?></td><td><a class="text-decoration-none fw-semibold" href="<?= base_url('pembelian/'.$r['id_pembelian']) ?>"><?= esc($r['no_pembelian']) ?></a></td><td><?= esc($r['nama_supplier']) ?></td><td><?= esc($r['nama_lengkap']) ?></td><td><?= esc($r['no_surat_jalan'] ?: '-') ?></td><td class="text-center"><a class="btn btn-sm btn-outline-primary table-action" title="Lihat detail" href="<?= base_url('penerimaan/'.$r['id_penerimaan']) ?>"><i class="bi bi-eye"></i></a></td></tr><?php endforeach; else: ?><tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-box-arrow-in-down fs-2 d-block mb-2"></i>Belum ada penerimaan barang.</td></tr><?php endif; ?>
    </tbody></table></div><?= view('components/pagination', ['pager' => $pager ?? null, 'group' => 'penerimaan', 'label' => 'penerimaan']) ?></div>
</div>
<?= view('layout/footer') ?>
