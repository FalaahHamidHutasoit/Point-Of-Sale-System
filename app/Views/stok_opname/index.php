<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<div class="main-content">
    <div class="page-heading">
        <div>
            <div class="text-primary fw-bold small mb-1">KONTROL PERSEDIAAN</div>
            <h2>Stock Opname</h2>
            <p>Bandingkan stok sistem dengan hitungan fisik melalui proses yang tercatat dan dapat diaudit.</p>
        </div>
        <span class="badge badge-soft-primary px-3 py-2"><i class="bi bi-clipboard-check me-1"></i>Admin Only</span>
    </div>

    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="surface-card p-4 mb-4">
        <form method="post" action="<?= base_url('stok/opname/create') ?>" class="row g-2 align-items-end">
            <?= csrf_field() ?>
            <div class="col-lg-9">
                <label class="form-label small fw-semibold">Catatan sesi baru</label>
                <input type="text" name="catatan" class="form-control" maxlength="255" placeholder="Contoh: Opname bulanan September 2026">
                <small class="text-muted">Saat dibuat, sistem menyimpan snapshot stok seluruh barang. Hanya satu sesi DRAFT boleh aktif.</small>
            </div>
            <div class="col-lg-3 d-grid"><button class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Mulai Stock Opname</button></div>
        </form>
    </div>

    <div class="surface-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>No. Opname</th><th>Tanggal</th><th>Pembuat</th><th>Status</th><th>Catatan</th><th>Finalisasi / Batal</th><th></th></tr></thead>
                <tbody>
                <?php if ($opname): ?>
                    <?php foreach ($opname as $row): ?>
                        <?php $badge = $row['status'] === 'FINALIZED' ? 'badge-soft-success' : ($row['status'] === 'CANCELLED' ? 'badge-soft-danger' : 'badge-soft-warning'); ?>
                        <tr>
                            <td><strong><?= esc($row['no_opname']) ?></strong></td>
                            <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td><?= esc($row['pembuat'] ?: '-') ?></td>
                            <td><span class="badge <?= $badge ?>"><?= esc($row['status']) ?></span></td>
                            <td><?= esc($row['catatan'] ?: '-') ?></td>
                            <td class="small text-muted">
                                <?php if ($row['status'] === 'FINALIZED'): ?><?= esc($row['finalizer'] ?: '-') ?> · <?= date('d M Y H:i', strtotime($row['finalized_at'])) ?>
                                <?php elseif ($row['status'] === 'CANCELLED'): ?><?= esc($row['canceller'] ?: '-') ?> · <?= date('d M Y H:i', strtotime($row['cancelled_at'])) ?>
                                <?php else: ?>Belum final<?php endif; ?>
                            </td>
                            <td class="text-end"><a href="<?= base_url('stok/opname/' . $row['id_opname']) ?>" class="btn btn-sm btn-outline-primary table-action" title="Buka detail"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">Belum ada sesi stock opname.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= view('components/pagination', ['pager' => $pager ?? null, 'group' => 'opname', 'label' => 'stock opname']) ?>
    </div>
</div>
<?= view('layout/footer') ?>
