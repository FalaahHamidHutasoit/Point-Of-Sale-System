<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<?php
$role = (string) session()->get('role');
$statusMap = [
    'DRAFT' => ['Draft', 'secondary'],
    'DIORDER' => ['Diorder', 'primary'],
    'SEBAGIAN' => ['Diterima Sebagian', 'warning'],
    'DITERIMA' => ['Selesai', 'success'],
    'DIBATALKAN' => ['Dibatalkan', 'danger'],
];
?>
<div class="main-content">
    <div class="page-heading">
        <div>
            <div class="text-primary fw-bold small mb-1">PROCUREMENT</div>
            <h2>Purchase Order</h2>
            <p>Pisahkan proses pemesanan supplier dari penerimaan fisik barang di gudang.</p>
        </div>
        <?php if (in_array($role, ['admin','purchasing'], true)): ?>
            <a href="<?= base_url('pembelian/tambah') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Purchase Order</a>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="surface-card p-3 mb-3">
        <form class="row g-2" method="get">
            <div class="col-md-7"><input class="form-control" name="keyword" value="<?= esc($keyword) ?>" placeholder="Cari nomor PO, supplier, atau purchasing..."></div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">Semua status</option>
                    <?php foreach ($statusMap as $key => [$label]): ?><option value="<?= $key ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Cari</button></div>
        </form>
    </div>

    <div class="surface-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>No. PO</th><th>Tanggal</th><th>Supplier</th><th>Purchasing</th><th>Status</th><th class="text-end">Nilai PO</th><th class="text-center">Aksi</th></tr></thead>
                <tbody>
                <?php if ($pembelian): foreach ($pembelian as $row): $meta = $statusMap[$row['status']] ?? [$row['status'],'secondary']; ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($row['no_pembelian']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?><?php if (!empty($row['tanggal_target'])): ?><small class="d-block text-muted">Target <?= date('d M Y', strtotime($row['tanggal_target'])) ?></small><?php endif; ?></td>
                        <td><?= esc($row['nama_supplier']) ?></td>
                        <td><?= esc($row['nama_lengkap']) ?></td>
                        <td><span class="badge text-bg-<?= esc($meta[1]) ?>"><?= esc($meta[0]) ?></span></td>
                        <td class="text-end fw-semibold">Rp <?= number_format($row['total'],0,',','.') ?></td>
                        <td class="text-center"><a href="<?= base_url('pembelian/'.$row['id_pembelian']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-clipboard2-x fs-2 d-block mb-2"></i>Belum ada purchase order.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= view('layout/footer') ?>
