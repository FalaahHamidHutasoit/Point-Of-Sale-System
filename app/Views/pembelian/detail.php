<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<?php
$role = (string) session()->get('role');
$statusMap = [
    'DRAFT' => ['Draft','secondary'],
    'DIORDER' => ['Diorder','primary'],
    'SEBAGIAN' => ['Diterima Sebagian','warning'],
    'DITERIMA' => ['Selesai','success'],
    'DIBATALKAN' => ['Dibatalkan','danger'],
];
$meta = $statusMap[$pembelian['status']] ?? [$pembelian['status'],'secondary'];
$progress = $orderedQty > 0 ? min(100, round(($receivedQty / $orderedQty) * 100)) : 0;
?>
<div class="main-content">
    <div class="page-heading">
        <div>
            <div class="text-primary fw-bold small mb-1">PURCHASE ORDER</div>
            <h2><?= esc($pembelian['no_pembelian']) ?></h2>
            <p><?= date('d F Y H:i', strtotime($pembelian['tanggal'])) ?> · <?= esc($pembelian['nama_supplier']) ?></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (in_array($role, ['admin','gudang'], true) && in_array($pembelian['status'], ['DIORDER','SEBAGIAN'], true)): ?>
                <a class="btn btn-success" href="<?= base_url('penerimaan/tambah/'.$pembelian['id_pembelian']) ?>"><i class="bi bi-box-arrow-in-down me-1"></i>Terima Barang</a>
            <?php endif; ?>
            <a class="btn btn-light border" href="<?= base_url('pembelian') ?>"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="surface-card overflow-hidden">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <div><h6 class="fw-bold mb-1">Detail Pesanan</h6><small class="text-muted">Progress penerimaan dihitung per qty.</small></div>
                    <span class="badge text-bg-<?= esc($meta[1]) ?> px-3 py-2"><?= esc($meta[0]) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead><tr><th>Barang</th><th class="text-end">Dipesan</th><th class="text-end">Diterima</th><th class="text-end">Sisa</th><th class="text-end">Harga Beli</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                        <?php foreach($detail as $d): $remaining=(int)$d['qty']-(int)$d['qty_diterima']; ?>
                            <tr>
                                <td><strong><?= esc($d['nama_barang']) ?></strong><small class="d-block text-muted"><?= esc($d['kode_barang']) ?></small></td>
                                <td class="text-end"><?= number_format($d['qty']) ?> <?= esc($d['satuan']) ?></td>
                                <td class="text-end text-success fw-semibold"><?= number_format($d['qty_diterima']) ?></td>
                                <td class="text-end <?= $remaining>0?'text-warning fw-semibold':'text-muted' ?>"><?= number_format($remaining) ?></td>
                                <td class="text-end">Rp <?= number_format($d['harga_beli'],0,',','.') ?></td>
                                <td class="text-end fw-semibold">Rp <?= number_format($d['subtotal'],0,',','.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot><tr><th colspan="5" class="text-end">Total PO</th><th class="text-end">Rp <?= number_format($pembelian['total'],0,',','.') ?></th></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="surface-card p-4 mb-3">
                <h6 class="fw-bold">Informasi PO</h6><hr>
                <div class="small text-muted">Supplier</div><div class="fw-semibold mb-3"><?= esc($pembelian['nama_supplier']) ?></div>
                <div class="small text-muted">Dibuat oleh</div><div class="fw-semibold mb-3"><?= esc($pembelian['nama_lengkap']) ?></div>
                <div class="small text-muted">Target penerimaan</div><div class="fw-semibold mb-3"><?= $pembelian['tanggal_target'] ? date('d F Y', strtotime($pembelian['tanggal_target'])) : '-' ?></div>
                <div class="small text-muted">Catatan</div><div><?= esc($pembelian['catatan'] ?: '-') ?></div>
            </div>
            <div class="surface-card p-4">
                <div class="d-flex justify-content-between"><span class="small text-muted">Progress penerimaan</span><strong><?= $progress ?>%</strong></div>
                <div class="progress mt-2 mb-3" style="height:9px"><div class="progress-bar" style="width:<?= $progress ?>%"></div></div>
                <div class="d-flex justify-content-between small"><span><?= number_format($receivedQty) ?> diterima</span><span><?= number_format(max(0,$orderedQty-$receivedQty)) ?> tersisa</span></div>
                <?php if (in_array($role, ['admin','purchasing'], true) && $pembelian['status'] === 'DRAFT'): ?>
                    <form action="<?= base_url('pembelian/'.$pembelian['id_pembelian'].'/kirim') ?>" method="post" class="mt-3"><?= csrf_field() ?><button class="btn btn-primary w-100"><i class="bi bi-send-check me-1"></i>Kirim PO ke Supplier</button></form>
                <?php endif; ?>
                <?php if (in_array($role, ['admin','purchasing'], true) && in_array($pembelian['status'], ['DRAFT','DIORDER'], true) && $receivedQty === 0): ?>
                    <form action="<?= base_url('pembelian/'.$pembelian['id_pembelian'].'/batalkan') ?>" method="post" class="mt-2" onsubmit="return confirm('Batalkan purchase order ini?')"><?= csrf_field() ?><button class="btn btn-outline-danger w-100"><i class="bi bi-x-circle me-1"></i>Batalkan PO</button></form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="surface-card overflow-hidden">
        <div class="p-4 border-bottom"><h6 class="fw-bold mb-1">Riwayat Penerimaan</h6><small class="text-muted">Satu PO dapat diterima beberapa kali (partial receiving).</small></div>
        <div class="table-responsive"><table class="table mb-0"><thead><tr><th>No. Penerimaan</th><th>Tanggal</th><th>Petugas Gudang</th><th>Surat Jalan</th><th class="text-center">Aksi</th></tr></thead><tbody>
        <?php if ($penerimaan): foreach($penerimaan as $r): ?>
            <tr><td class="fw-semibold"><?= esc($r['no_penerimaan']) ?></td><td><?= date('d M Y H:i',strtotime($r['tanggal'])) ?></td><td><?= esc($r['nama_lengkap']) ?></td><td><?= esc($r['no_surat_jalan'] ?: '-') ?></td><td class="text-center"><a class="btn btn-sm btn-outline-primary" href="<?= base_url('penerimaan/'.$r['id_penerimaan']) ?>"><i class="bi bi-eye"></i></a></td></tr>
        <?php endforeach; else: ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada barang yang diterima untuk PO ini.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
</div>
<?= view('layout/footer') ?>
