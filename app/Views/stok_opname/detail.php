<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<?php
$isDraft = $header['status'] === 'DRAFT';
$filled = 0;
$diffCount = 0;
foreach ($details as $d) {
    if ($d['stok_fisik'] !== null) $filled++;
    if ((int) ($d['selisih'] ?? 0) !== 0) $diffCount++;
}
?>
<div class="main-content">
    <div class="page-heading">
        <div>
            <div class="text-primary fw-bold small mb-1">STOCK OPNAME</div>
            <h2><?= esc($header['no_opname']) ?></h2>
            <p>Snapshot <?= date('d M Y H:i', strtotime($header['tanggal'])) ?> · <?= esc($header['catatan'] ?: 'Tanpa catatan') ?></p>
        </div>
        <a href="<?= base_url('stok/opname') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="surface-card p-3"><small class="text-muted">Status</small><div class="fw-bold mt-1"><?= esc($header['status']) ?></div></div></div>
        <div class="col-md-3"><div class="surface-card p-3"><small class="text-muted">Barang dihitung</small><div class="fw-bold mt-1"><?= $filled ?> / <?= count($details) ?></div></div></div>
        <div class="col-md-3"><div class="surface-card p-3"><small class="text-muted">Ada selisih</small><div class="fw-bold mt-1"><?= $diffCount ?> barang</div></div></div>
        <div class="col-md-3"><div class="surface-card p-3"><small class="text-muted">Pembuat</small><div class="fw-bold mt-1"><?= esc($header['pembuat'] ?: '-') ?></div></div></div>
    </div>

    <?php if ($isDraft): ?>
        <div class="alert alert-warning small"><strong>Penting:</strong> jangan finalisasi jika ada transaksi penjualan/restock setelah sesi ini dibuat. KAMELA akan memeriksa perubahan stok dan menolak finalisasi jika snapshot sudah tidak cocok.</div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('stok/opname/' . $header['id_opname'] . '/save') ?>">
        <?= csrf_field() ?>
        <div class="surface-card overflow-hidden">
            <div class="table-responsive" style="max-height:65vh">
                <table class="table table-hover align-middle mb-0">
                    <thead class="sticky-top"><tr><th>Kode</th><th>Barang</th><th>Kategori</th><th>Stok Snapshot</th><th>Stok Sekarang</th><th style="width:160px">Stok Fisik</th><th>Selisih</th></tr></thead>
                    <tbody>
                    <?php foreach ($details as $d): ?>
                        <?php $selisih = $d['stok_fisik'] === null ? null : ((int) $d['stok_fisik'] - (int) $d['stok_sistem']); ?>
                        <tr>
                            <td><span class="font-monospace small"><?= esc($d['kode_barang']) ?></span></td>
                            <td><strong><?= esc($d['nama_barang']) ?></strong><small class="d-block text-muted"><?= esc($d['satuan']) ?></small></td>
                            <td><?= esc($d['nama_kategori'] ?: '-') ?></td>
                            <td><strong><?= (int) $d['stok_sistem'] ?></strong></td>
                            <td class="<?= (int) $d['stok_sekarang'] !== (int) $d['stok_sistem'] ? 'text-danger fw-bold' : '' ?>"><?= (int) $d['stok_sekarang'] ?></td>
                            <td>
                                <?php if ($isDraft): ?>
                                    <input type="number" min="0" step="1" name="stok_fisik[<?= (int) $d['id_detail_opname'] ?>]" class="form-control form-control-sm" value="<?= $d['stok_fisik'] === null ? '' : (int) $d['stok_fisik'] ?>" placeholder="Hitung fisik">
                                <?php else: ?>
                                    <strong><?= $d['stok_fisik'] === null ? '-' : (int) $d['stok_fisik'] ?></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($selisih === null): ?><span class="text-muted">-</span>
                                <?php elseif ($selisih === 0): ?><span class="badge badge-soft-success">0</span>
                                <?php elseif ($selisih > 0): ?><span class="badge badge-soft-primary">+<?= $selisih ?></span>
                                <?php else: ?><span class="badge badge-soft-danger"><?= $selisih ?></span><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($isDraft): ?>
                <div class="p-3 border-top d-flex justify-content-end"><button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Hitungan</button></div>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($isDraft): ?>
        <div class="row g-3 mt-1">
            <div class="col-lg-7">
                <div class="surface-card p-4 border-success-subtle">
                    <h5 class="fw-bold">Finalisasi Opname</h5>
                    <p class="small text-muted">Semua barang harus sudah dihitung. Finalisasi akan mengubah stok hanya sebesar selisih dan membuat Mutasi Stok PENYESUAIAN.</p>
                    <form method="post" action="<?= base_url('stok/opname/' . $header['id_opname'] . '/finalize') ?>" class="d-flex gap-2">
                        <?= csrf_field() ?>
                        <input name="confirmation" class="form-control" placeholder="Ketik FINALISASI" required autocomplete="off">
                        <button class="btn btn-success text-nowrap">Finalisasi</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="surface-card p-4 border-danger-subtle">
                    <h5 class="fw-bold">Batalkan Sesi</h5>
                    <p class="small text-muted">Membatalkan draft tidak mengubah stok dan histori sesi tetap tersimpan.</p>
                    <form method="post" action="<?= base_url('stok/opname/' . $header['id_opname'] . '/cancel') ?>" class="d-flex gap-2">
                        <?= csrf_field() ?>
                        <input name="confirmation" class="form-control" placeholder="Ketik BATALKAN" required autocomplete="off">
                        <button class="btn btn-outline-danger text-nowrap">Batalkan</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= view('layout/footer') ?>
