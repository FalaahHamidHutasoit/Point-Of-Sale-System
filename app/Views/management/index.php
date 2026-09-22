<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/sidebar') ?>
<?php
$formatChange = static function (?float $value): string {
    if ($value === null) return 'Periode sebelumnya 0';
    if (abs($value) < 0.05) return '0,0% vs periode sebelumnya';
    return ($value > 0 ? '+' : '') . number_format($value, 1, ',', '.') . '% vs periode sebelumnya';
};
$changeClass = static function (?float $value): string {
    if ($value === null || abs($value) < 0.05) return 'neutral';
    return $value > 0 ? 'up' : 'down';
};
?>
<div class="main-content">
    <div class="page-heading align-items-start">
        <div>
            <div class="text-primary fw-bold small mb-1">MANAGERIAL DECISION SUPPORT</div>
            <h2>Pusat Keputusan</h2>
            <p>Ringkasan penjualan, profitabilitas, persediaan, dan procurement untuk mendukung keputusan manajemen.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <form method="get" action="<?= base_url('management') ?>" class="period-form">
                <select name="period" class="form-select" onchange="this.form.submit()" aria-label="Periode analisis">
                    <?php foreach ([30,60,90] as $days): ?>
                        <option value="<?= $days ?>" <?= $period === $days ? 'selected' : '' ?>><?= $days ?> hari terakhir</option>
                    <?php endforeach; ?>
                </select>
            </form>
            <button type="button" class="btn btn-outline-dark" onclick="window.print()"><i class="bi bi-printer me-1"></i> Cetak</button>
        </div>
    </div>

    <div class="analysis-period mb-4">
        <i class="bi bi-calendar3"></i>
        <span>Periode aktif <strong><?= date('d M Y', strtotime($startDate)) ?> – <?= date('d M Y', strtotime($endDate)) ?></strong></span>
        <span class="sep">•</span>
        <span>Pembanding <?= date('d M Y', strtotime($previousStart)) ?> – <?= date('d M Y', strtotime($previousEnd)) ?></span>
    </div>

    <div class="row g-3 mb-4">
        <?php $kpis = [
            ['Omzet','Rp '.number_format($current['omzet'],0,',','.'),$changes['omzet'],'bi-cash-stack'],
            ['Laba Kotor','Rp '.number_format($current['laba_kotor'],0,',','.'),$changes['laba_kotor'],'bi-piggy-bank'],
            ['Transaksi',number_format($current['transaksi']),$changes['transaksi'],'bi-receipt-cutoff'],
            ['Rata-rata Transaksi','Rp '.number_format($current['rata_rata'],0,',','.'),$changes['rata_rata'],'bi-calculator'],
        ]; ?>
        <?php foreach ($kpis as [$label,$value,$change,$icon]): ?>
            <div class="col-xl-3 col-md-6">
                <div class="surface-card decision-kpi h-100">
                    <div class="decision-icon"><i class="bi <?= $icon ?>"></i></div>
                    <div class="flex-grow-1">
                        <span><?= esc($label) ?></span>
                        <strong><?= esc($value) ?></strong>
                        <small class="delta <?= $changeClass($change) ?>"><?= esc($formatChange($change)) ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="surface-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div><h5 class="fw-bold mb-1">Tren Kinerja Penjualan</h5><small class="text-muted">Omzet harian pada periode analisis.</small></div>
                    <span class="badge text-bg-light border px-3 py-2">Margin kotor <?= number_format($current['margin'],1,',','.') ?>%</span>
                </div>
                <canvas id="managementSalesChart" height="105"></canvas>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="surface-card p-4 h-100">
                <h5 class="fw-bold mb-1">Posisi Persediaan</h5>
                <small class="text-muted">Nilai berdasarkan harga beli master saat ini.</small>
                <div class="summary-list mt-3">
                    <div><span>Nilai stok</span><strong>Rp <?= number_format($inventory['nilai_stok'],0,',','.') ?></strong></div>
                    <div><span>Total SKU</span><strong><?= number_format($inventory['total_sku']) ?></strong></div>
                    <div><span>Stok menipis</span><strong class="text-warning"><?= number_format($inventory['stok_menipis']) ?></strong></div>
                    <div><span>Stok habis</span><strong class="text-danger"><?= number_format($inventory['stok_habis']) ?></strong></div>
                    <div><span>PO aktif</span><strong><?= number_format($activePoSummary['jumlah']) ?></strong></div>
                    <div><span>Nilai PO aktif</span><strong>Rp <?= number_format($activePoSummary['nilai'],0,',','.') ?></strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="surface-card p-4 mb-4 decision-panel">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div><div class="section-kicker">ACTION CENTER</div><h5 class="fw-bold mb-1">Indikator untuk Ditindaklanjuti</h5><small class="text-muted">Rekomendasi berbasis aturan transparan, bukan prediksi otomatis.</small></div>
        </div>
        <div class="row g-3">
            <?php foreach ($recommendations as $item): ?>
                <div class="col-xl-6">
                    <div class="decision-alert tone-<?= esc($item['tone']) ?>">
                        <div class="alert-icon"><i class="bi <?= esc($item['icon']) ?>"></i></div>
                        <div class="flex-grow-1">
                            <strong><?= esc($item['title']) ?></strong>
                            <p><?= esc($item['text']) ?></p>
                            <a href="<?= esc($item['url']) ?>"><?= esc($item['action']) ?> <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="surface-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3"><div><h5 class="fw-bold mb-1">Produk Kontributor Laba</h5><small class="text-muted">Diurutkan berdasarkan laba kotor historis pada periode aktif.</small></div></div>
                <div class="table-responsive">
                    <table class="table align-middle management-table mb-0">
                        <thead><tr><th>Produk</th><th class="text-end">Qty</th><th class="text-end">Omzet</th><th class="text-end">Laba Kotor</th></tr></thead>
                        <tbody>
                        <?php if ($topProducts): foreach ($topProducts as $row): ?>
                            <tr><td><strong><?= esc($row['nama_barang']) ?></strong><small><?= esc($row['kode_barang']) ?></small></td><td class="text-end"><?= number_format($row['qty_terjual']) ?> <?= esc($row['satuan']) ?></td><td class="text-end">Rp <?= number_format($row['omzet'],0,',','.') ?></td><td class="text-end fw-bold">Rp <?= number_format($row['laba_kotor'],0,',','.') ?></td></tr>
                        <?php endforeach; else: ?><tr><td colspan="4" class="text-center text-muted py-4">Belum ada penjualan pada periode ini.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="surface-card p-4 h-100">
                <h5 class="fw-bold mb-1">Kontribusi Kategori</h5><small class="text-muted">Kategori berdasarkan omzet pada periode aktif.</small>
                <div class="category-list mt-3">
                    <?php if ($categorySales): $maxCategory = max(array_map(static fn($r)=>(float)$r['omzet'],$categorySales)); foreach ($categorySales as $row): $pct = $maxCategory > 0 ? ((float)$row['omzet']/$maxCategory)*100 : 0; ?>
                        <div class="category-row"><div class="d-flex justify-content-between gap-3"><strong><?= esc($row['nama_kategori']) ?></strong><span>Rp <?= number_format($row['omzet'],0,',','.') ?></span></div><div class="mini-bar"><span style="width:<?= number_format($pct,2,'.','') ?>%"></span></div><small><?= number_format($row['qty_terjual']) ?> unit · laba Rp <?= number_format($row['laba_kotor'],0,',','.') ?></small></div>
                    <?php endforeach; else: ?><div class="text-muted small py-4">Belum ada data kategori.</div><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="surface-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3"><div><h5 class="fw-bold mb-1">Rekomendasi Restock</h5><small class="text-muted">Target persediaan = estimasi kebutuhan 14 hari + safety 3 hari, dikurangi stok dan PO terbuka.</small></div><span class="badge text-bg-light border"><?= count($restockRecommendations) ?> SKU</span></div>
                <div class="table-responsive">
                    <table class="table align-middle management-table mb-0">
                        <thead><tr><th>Produk</th><th class="text-end">Terjual</th><th class="text-end">Stok</th><th class="text-end">PO Terbuka</th><th class="text-end">Saran Pesan</th></tr></thead>
                        <tbody>
                        <?php if ($restockRecommendations): foreach ($restockRecommendations as $row): ?>
                            <tr><td><strong><?= esc($row['nama_barang']) ?></strong><small><?= esc($row['kode_barang']) ?></small></td><td class="text-end"><?= number_format($row['qty_terjual']) ?></td><td class="text-end"><?= number_format($row['stok']) ?></td><td class="text-end"><?= number_format($row['qty_po_terbuka']) ?></td><td class="text-end"><span class="badge text-bg-warning"><?= number_format($row['saran_pesan']) ?> <?= esc($row['satuan']) ?></span></td></tr>
                        <?php endforeach; else: ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada SKU yang memerlukan restock berdasarkan aturan periode ini.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="surface-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3"><div><h5 class="fw-bold mb-1">Metode Pembayaran</h5><small class="text-muted">Komposisi transaksi pada periode aktif.</small></div></div>
                <div class="payment-list">
                <?php if ($paymentMix): foreach ($paymentMix as $row): ?>
                    <div><span><strong><?= esc($row['metode_pembayaran']) ?></strong><small><?= number_format($row['transaksi']) ?> transaksi</small></span><strong>Rp <?= number_format($row['omzet'],0,',','.') ?></strong></div>
                <?php endforeach; else: ?><div class="text-muted small py-4">Belum ada transaksi.</div><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-6">
            <div class="surface-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3"><div><h5 class="fw-bold mb-1">PO Melewati Target</h5><small class="text-muted">PO aktif dengan tanggal target sebelum hari ini.</small></div><span class="badge <?= $overduePos ? 'text-bg-danger' : 'text-bg-success' ?>"><?= count($overduePos) ?></span></div>
                <div class="table-responsive"><table class="table align-middle management-table mb-0"><thead><tr><th>PO / Supplier</th><th>Target</th><th class="text-end">Sisa Qty</th></tr></thead><tbody>
                <?php if ($overduePos): foreach ($overduePos as $row): ?>
                    <tr><td><a href="<?= base_url('pembelian/'.$row['id_pembelian']) ?>" class="fw-bold text-decoration-none"><?= esc($row['no_pembelian']) ?></a><small><?= esc($row['nama_supplier']) ?> · <?= esc($row['status']) ?></small></td><td><?= date('d M Y',strtotime($row['tanggal_target'])) ?></td><td class="text-end fw-bold text-danger"><?= number_format($row['qty_belum_diterima']) ?></td></tr>
                <?php endforeach; else: ?><tr><td colspan="3" class="text-center text-muted py-4">Tidak ada PO aktif yang melewati target.</td></tr><?php endif; ?>
                </tbody></table></div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="surface-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3"><div><h5 class="fw-bold mb-1">Slow Moving / Tidak Terjual</h5><small class="text-muted">Stok tersedia tanpa penjualan selama <?= $period ?> hari.</small></div><span class="badge text-bg-light border">Modal Rp <?= number_format($slowCapital,0,',','.') ?></span></div>
                <div class="table-responsive"><table class="table align-middle management-table mb-0"><thead><tr><th>Produk</th><th class="text-end">Stok</th><th class="text-end">Nilai Stok</th></tr></thead><tbody>
                <?php if ($slowMoving): foreach ($slowMoving as $row): ?>
                    <tr><td><strong><?= esc($row['nama_barang']) ?></strong><small><?= esc($row['nama_kategori'] ?: '-') ?></small></td><td class="text-end"><?= number_format($row['stok']) ?> <?= esc($row['satuan']) ?></td><td class="text-end">Rp <?= number_format($row['nilai_stok'],0,',','.') ?></td></tr>
                <?php endforeach; else: ?><tr><td colspan="3" class="text-center text-muted py-4">Semua stok aktif mencatat penjualan pada periode ini.</td></tr><?php endif; ?>
                </tbody></table></div>
            </div>
        </div>
    </div>
</div>

<style>
.period-form{min-width:170px}.analysis-period{display:flex;align-items:center;gap:9px;flex-wrap:wrap;font-size:12px;color:#64748b;background:#f8fafc;border:1px solid #e8edf4;border-radius:12px;padding:10px 14px}.analysis-period i{color:#2563eb}.analysis-period .sep{color:#cbd5e1}.decision-kpi{padding:18px;display:flex;gap:13px;align-items:flex-start}.decision-icon{width:43px;height:43px;border-radius:12px;background:#eef4ff;color:#2563eb;display:grid;place-items:center;font-size:18px;flex:0 0 auto}.decision-kpi span{display:block;color:#64748b;font-size:11px;font-weight:700}.decision-kpi strong{display:block;font-size:20px;margin-top:3px;letter-spacing:-.02em}.delta{display:inline-block;margin-top:6px;font-size:10px;font-weight:700}.delta.up{color:#07855d}.delta.down{color:#c2410c}.delta.neutral{color:#64748b}.summary-list>div{display:flex;justify-content:space-between;gap:14px;padding:11px 0;border-bottom:1px solid #eef1f5;font-size:12px}.summary-list span{color:#64748b}.section-kicker{font-size:10px;font-weight:800;color:#2563eb;letter-spacing:.12em;margin-bottom:3px}.decision-alert{height:100%;border:1px solid #e5e7eb;border-radius:14px;padding:15px;display:flex;gap:12px;background:#fff}.decision-alert .alert-icon{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;flex:0 0 auto;background:#f1f5f9}.decision-alert strong{display:block;font-size:13px}.decision-alert p{font-size:11px;color:#64748b;line-height:1.6;margin:5px 0 7px}.decision-alert a{font-size:11px;text-decoration:none;font-weight:700}.tone-danger .alert-icon{background:#fff1f2;color:#dc2626}.tone-warning .alert-icon{background:#fff7ed;color:#c2410c}.tone-info .alert-icon{background:#eff6ff;color:#2563eb}.tone-secondary .alert-icon{background:#f1f5f9;color:#475569}.tone-success .alert-icon{background:#ecfdf5;color:#047857}.management-table{font-size:11px}.management-table th{font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.04em;border-bottom-color:#e8edf4}.management-table td{border-bottom-color:#eef2f7}.management-table td small{display:block;color:#94a3b8;margin-top:2px}.category-row{padding:11px 0;border-bottom:1px solid #eef2f7}.category-row strong,.category-row span{font-size:11px}.category-row small{color:#94a3b8;font-size:10px}.mini-bar{height:6px;background:#eef2f7;border-radius:999px;overflow:hidden;margin:7px 0}.mini-bar span{display:block;height:100%;background:#2563eb;border-radius:999px}.payment-list>div{display:flex;justify-content:space-between;gap:15px;padding:13px 0;border-bottom:1px solid #eef2f7;font-size:11px}.payment-list span strong,.payment-list span small{display:block}.payment-list span small{color:#94a3b8;margin-top:2px}
@media print{.sidebar,.page-heading .btn,.period-form{display:none!important}.main-content{margin-left:0!important;padding:10px!important}.surface-card{box-shadow:none!important;break-inside:avoid}.decision-panel{break-inside:avoid}}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(() => {
    const canvas = document.getElementById('managementSalesChart');
    if (!canvas || typeof Chart === 'undefined') return;
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Omzet',
                data: <?= json_encode($chartRevenue) ?>,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,.08)',
                fill: true,
                tension: .32,
                borderWidth: 2,
                pointRadius: <?= $period <= 30 ? 2 : 0 ?>
            }]
        },
        options: {
            responsive: true,
            interaction: {mode:'index', intersect:false},
            plugins: {legend:{display:false}},
            scales: {
                x: {grid:{display:false}, ticks:{maxTicksLimit:12}},
                y: {beginAtZero:true, ticks:{callback:v=>'Rp '+new Intl.NumberFormat('id-ID',{notation:'compact'}).format(v)}}
            }
        }
    });
})();
</script>
<?= view('layout/footer') ?>
