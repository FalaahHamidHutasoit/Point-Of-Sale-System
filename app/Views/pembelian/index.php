<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<div class="main-content">
    <div class="page-heading">
        <div><div class="text-primary fw-bold small mb-1">PERSEDIAAN</div><h2>Pembelian & Restock</h2><p>Catat barang masuk dari supplier agar stok bertambah dan memiliki histori yang jelas.</p></div>
        <a href="<?= base_url('pembelian/tambah') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Pembelian Baru</a>
    </div>
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <div class="surface-card p-3 mb-3"><form class="row g-2" method="get"><div class="col-md-10"><input class="form-control" name="keyword" value="<?= esc($keyword) ?>" placeholder="Cari nomor pembelian, supplier, atau petugas..."></div><div class="col-md-2 d-grid"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Cari</button></div></form></div>
    <div class="surface-card overflow-hidden"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>No. Pembelian</th><th>Tanggal</th><th>Supplier</th><th>Petugas</th><th class="text-end">Total</th><th class="text-center">Aksi</th></tr></thead><tbody>
        <?php if ($pembelian): foreach ($pembelian as $row): ?><tr><td class="fw-semibold"><?= esc($row['no_pembelian']) ?></td><td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td><td><?= esc($row['nama_supplier']) ?></td><td><?= esc($row['nama_lengkap']) ?></td><td class="text-end fw-semibold">Rp <?= number_format($row['total'],0,',','.') ?></td><td class="text-center"><a href="<?= base_url('pembelian/'.$row['id_pembelian']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td></tr><?php endforeach; else: ?><tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-bag-x fs-2 d-block mb-2"></i>Belum ada transaksi pembelian.</td></tr><?php endif; ?>
    </tbody></table></div></div>
</div>
<?= view('layout/footer') ?>
