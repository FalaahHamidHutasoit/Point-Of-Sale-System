<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<?php $role = (string) session()->get('role'); ?>
<div class="main-content">
    <div class="page-heading">
        <div><div class="text-primary fw-bold small mb-1">BUSINESS OVERVIEW</div><h2>Dashboard</h2><p>Ringkasan operasional dan penjualan untuk membantu pengambilan keputusan.</p></div>
        <div class="d-flex gap-2">
            <?php if (in_array($role, ['admin','manager'], true)): ?><a class="btn btn-outline-dark" href="<?= base_url('management') ?>"><i class="bi bi-compass me-1"></i> Pusat Keputusan</a><?php endif; ?>
            <?php if (in_array($role, ['admin','purchasing'], true)): ?><a class="btn btn-outline-primary" href="<?= base_url('pembelian/tambah') ?>"><i class="bi bi-clipboard2-plus me-1"></i> Buat PO</a><?php elseif ($role === 'gudang'): ?><a class="btn btn-outline-primary" href="<?= base_url('pembelian') ?>"><i class="bi bi-box-arrow-in-down me-1"></i> Terima Barang</a><?php endif; ?>
            <?php if (in_array($role, ['admin','kasir'], true)): ?><a class="btn btn-primary" href="<?= base_url('penjualan') ?>"><i class="bi bi-plus-lg me-1"></i> Transaksi</a><?php endif; ?>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="row g-3 mb-4">
        <?php $cards = $role === 'gudang' ? [
            ['Total Barang',number_format($totalBarang).' SKU','bi-box-seam','primary'],
            ['Stok Menipis',number_format(count($stokMenipis)),'bi-exclamation-triangle','danger'],
            ['PO Menunggu',number_format($poMenunggu),'bi-clipboard2-check','warning'],
            ['Penerimaan Bulan Ini',number_format($penerimaanBulanIni),'bi-box-arrow-in-down','success'],
        ] : ($role === 'purchasing' ? [
            ['PO Aktif',number_format($poMenunggu),'bi-clipboard2-check','primary'],
            ['Stok Menipis',number_format(count($stokMenipis)),'bi-exclamation-triangle','danger'],
            ['Nilai PO Bulan Ini','Rp '.number_format($pembelianBulanIni,0,',','.'),'bi-cash-stack','warning'],
            ['Penerimaan Bulan Ini',number_format($penerimaanBulanIni),'bi-box-arrow-in-down','success'],
        ] : [
            ['Omzet Hari Ini','Rp '.number_format($omzetHariIni,0,',','.'),'bi-cash-stack','primary'],
            ['Transaksi Hari Ini',number_format($transaksiHariIni),'bi-receipt-cutoff','success'],
            ['Omzet Bulan Ini','Rp '.number_format($omzetBulanIni,0,',','.'),'bi-graph-up-arrow','warning'],
            ['Stok Menipis',number_format(count($stokMenipis)),'bi-exclamation-triangle','danger'],
        ]); ?>        <?php foreach ($cards as [$label,$value,$icon,$tone]): ?>
            <div class="col-xl-3 col-md-6"><div class="surface-card metric-card"><div class="metric-icon tone-<?= $tone ?>"><i class="bi <?= $icon ?>"></i></div><div><span><?= esc($label) ?></span><strong><?= esc($value) ?></strong></div></div></div>
        <?php endforeach; ?>
    </div>

    <?php if (in_array($role, ['admin','manager'], true)): ?>
<div class="row g-3 mb-4">
        <div class="col-xl-8"><div class="surface-card p-4 h-100"><div class="d-flex justify-content-between align-items-center mb-3"><div><h6 class="fw-bold mb-1">Tren Penjualan 7 Hari</h6><small class="text-muted">Omzet harian berdasarkan transaksi tersimpan</small></div><span class="badge badge-soft-primary px-3 py-2"><?= number_format($transaksiBulanIni) ?> transaksi bulan ini</span></div><canvas id="salesChart" height="105"></canvas></div></div>
        <div class="col-xl-4"><div class="surface-card p-4 h-100"><h6 class="fw-bold mb-1">Ringkasan Bulan Ini</h6><small class="text-muted">Arus aktivitas operasional</small><div class="summary-list mt-4"><div><span>Penjualan</span><strong>Rp <?= number_format($omzetBulanIni,0,',','.') ?></strong></div><div><span>Pembelian / Restock</span><strong>Rp <?= number_format($pembelianBulanIni,0,',','.') ?></strong></div><div><span>Total barang</span><strong><?= number_format($totalBarang) ?> SKU</strong></div><div><span>Total customer</span><strong><?= number_format($totalCustomer) ?></strong></div></div></div></div>
    </div>
    <?php endif; ?>

    <?php if (in_array($role, ['admin','manager','kasir'], true)): ?>
    <div class="row g-3">
        <div class="col-xl-5"><div class="surface-card p-4 h-100"><div class="d-flex justify-content-between mb-3"><div><h6 class="fw-bold mb-1">Produk Terlaris</h6><small class="text-muted">Berdasarkan qty bulan berjalan</small></div></div><?php if ($produkTerlaris): ?><div class="list-group list-group-flush"><?php foreach ($produkTerlaris as $i=>$p): ?><div class="list-group-item px-0 d-flex align-items-center gap-3"><div class="rank"><?= $i+1 ?></div><div class="flex-grow-1"><strong class="d-block small"><?= esc($p['nama_barang']) ?></strong><small class="text-muted"><?= number_format($p['qty_terjual']) ?> <?= esc($p['satuan']) ?> terjual</small></div><strong class="small">Rp <?= number_format($p['omzet'],0,',','.') ?></strong></div><?php endforeach; ?></div><?php else: ?><div class="text-muted small py-4">Belum ada penjualan bulan ini.</div><?php endif; ?></div></div>
        <div class="col-xl-7"><div class="surface-card p-4 h-100"><div class="d-flex justify-content-between align-items-center mb-3"><div><h6 class="fw-bold mb-1">Transaksi Terbaru</h6><small class="text-muted">Aktivitas penjualan terkini</small></div><a href="<?= base_url('penjualan/riwayat') ?>" class="small text-decoration-none">Lihat semua</a></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Transaksi</th><th>Customer</th><th>Kasir</th><th class="text-end">Total</th></tr></thead><tbody><?php foreach ($transaksiTerbaru as $trx): ?><tr><td><a class="fw-semibold text-decoration-none" href="<?= base_url('penjualan/sukses/'.$trx['id_penjualan']) ?>"><?= esc($trx['no_transaksi']) ?></a><small class="d-block text-muted"><?= date('d M H:i', strtotime($trx['tanggal'])) ?></small></td><td><?= esc($trx['nama_customer'] ?: 'Umum') ?></td><td><?= esc($trx['nama_lengkap']) ?></td><td class="text-end fw-semibold">Rp <?= number_format($trx['total'],0,',','.') ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
    </div>
    <?php endif; ?>

    <?php if ($stokMenipis && in_array($role, ['admin','gudang','purchasing','manager'], true)): ?><div class="surface-card p-4 mt-3"><div class="d-flex justify-content-between align-items-center mb-3"><div><h6 class="fw-bold mb-1">Perlu Restock</h6><small class="text-muted">Barang dengan stok ≤ 5</small></div><?php if (in_array($role, ['admin','purchasing'], true)): ?><a href="<?= base_url('pembelian/tambah') ?>" class="btn btn-sm btn-outline-primary">Buat PO</a><?php endif; ?></div><div class="row g-2"><?php foreach ($stokMenipis as $b): ?><div class="col-md-4 col-xl-2"><div class="stock-chip"><strong><?= esc($b['nama_barang']) ?></strong><span><?= esc($b['stok']) ?> <?= esc($b['satuan']) ?> tersisa</span></div></div><?php endforeach; ?></div></div><?php endif; ?>
</div>
<style>
.metric-card{padding:19px;display:flex;align-items:center;gap:14px}.metric-icon{width:46px;height:46px;border-radius:13px;display:grid;place-items:center;font-size:19px}.tone-primary{background:#eef4ff;color:#2563eb}.tone-success{background:#eafaf4;color:#07855d}.tone-warning{background:#fff7e8;color:#c26a00}.tone-danger{background:#fff0f0;color:#dc2626}.metric-card span{display:block;color:#6b7280;font-size:11px;font-weight:600}.metric-card strong{display:block;font-size:20px;margin-top:4px;letter-spacing:-.02em}.summary-list>div{display:flex;justify-content:space-between;gap:15px;padding:13px 0;border-bottom:1px solid #eef1f5;font-size:12px}.summary-list span{color:#6b7280}.rank{width:30px;height:30px;border-radius:9px;background:#f1f5f9;display:grid;place-items:center;font-size:11px;font-weight:800}.stock-chip{padding:12px;border:1px solid #fee2e2;background:#fffafa;border-radius:12px}.stock-chip strong,.stock-chip span{display:block}.stock-chip strong{font-size:11px}.stock-chip span{font-size:10px;color:#b42318;margin-top:4px}
</style>
<?php if (in_array($role, ['admin','manager'], true)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('salesChart'),{type:'line',data:{labels:<?= json_encode($chartLabels) ?>,datasets:[{label:'Omzet',data:<?= json_encode($chartValues) ?>,borderColor:'#2563eb',backgroundColor:'rgba(37,99,235,.08)',fill:true,tension:.38,borderWidth:2,pointRadius:3}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{callback:v=>'Rp '+new Intl.NumberFormat('id-ID',{notation:'compact'}).format(v)}}}}});
</script>
<?php endif; ?>
<?= view('layout/footer') ?>
