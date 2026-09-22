<?php

namespace App\Controllers;

class Management extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $allowedPeriods = [30, 60, 90];
        $period = (int) ($this->request->getGet('period') ?? 30);
        if (!in_array($period, $allowedPeriods, true)) {
            $period = 30;
        }

        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-' . ($period - 1) . ' days'));
        $previousEnd = date('Y-m-d', strtotime('-' . $period . ' days'));
        $previousStart = date('Y-m-d', strtotime('-' . (($period * 2) - 1) . ' days'));

        $current = $this->salesSummary($db, $startDate, $endDate);
        $previous = $this->salesSummary($db, $previousStart, $previousEnd);

        $current['margin'] = $current['omzet'] > 0
            ? ($current['laba_kotor'] / $current['omzet']) * 100
            : 0.0;
        $previous['margin'] = $previous['omzet'] > 0
            ? ($previous['laba_kotor'] / $previous['omzet']) * 100
            : 0.0;

        $changes = [
            'omzet' => $this->percentageChange($current['omzet'], $previous['omzet']),
            'laba_kotor' => $this->percentageChange($current['laba_kotor'], $previous['laba_kotor']),
            'transaksi' => $this->percentageChange($current['transaksi'], $previous['transaksi']),
            'rata_rata' => $this->percentageChange($current['rata_rata'], $previous['rata_rata']),
        ];

        $inventory = $db->table('barang')
            ->select('COUNT(*) AS total_sku, COALESCE(SUM(stok * harga_beli), 0) AS nilai_stok, SUM(CASE WHEN stok <= 0 THEN 1 ELSE 0 END) AS stok_habis, SUM(CASE WHEN stok > 0 AND stok <= 5 THEN 1 ELSE 0 END) AS stok_menipis')
            ->get()->getRowArray() ?? [];

        $inventory = [
            'total_sku' => (int) ($inventory['total_sku'] ?? 0),
            'nilai_stok' => (float) ($inventory['nilai_stok'] ?? 0),
            'stok_habis' => (int) ($inventory['stok_habis'] ?? 0),
            'stok_menipis' => (int) ($inventory['stok_menipis'] ?? 0),
        ];

        $topProducts = $db->query(
            "SELECT b.id_barang, b.kode_barang, b.nama_barang, b.satuan,
                    SUM(dp.qty) AS qty_terjual,
                    SUM(dp.subtotal) AS omzet,
                    SUM(dp.subtotal - (dp.harga_modal * dp.qty)) AS laba_kotor
             FROM detail_penjualan dp
             INNER JOIN penjualan p ON p.id_penjualan = dp.id_penjualan
             INNER JOIN barang b ON b.id_barang = dp.id_barang
             WHERE DATE(p.tanggal) BETWEEN ? AND ?
             GROUP BY b.id_barang, b.kode_barang, b.nama_barang, b.satuan
             ORDER BY laba_kotor DESC, qty_terjual DESC
             LIMIT 8",
            [$startDate, $endDate]
        )->getResultArray();

        $categorySales = $db->query(
            "SELECT k.nama_kategori,
                    SUM(dp.qty) AS qty_terjual,
                    SUM(dp.subtotal) AS omzet,
                    SUM(dp.subtotal - (dp.harga_modal * dp.qty)) AS laba_kotor
             FROM detail_penjualan dp
             INNER JOIN penjualan p ON p.id_penjualan = dp.id_penjualan
             INNER JOIN barang b ON b.id_barang = dp.id_barang
             INNER JOIN kategori k ON k.id_kategori = b.id_kategori
             WHERE DATE(p.tanggal) BETWEEN ? AND ?
             GROUP BY k.id_kategori, k.nama_kategori
             ORDER BY omzet DESC
             LIMIT 6",
            [$startDate, $endDate]
        )->getResultArray();

        $paymentMix = $db->query(
            "SELECT metode_pembayaran, COUNT(*) AS transaksi, COALESCE(SUM(total),0) AS omzet
             FROM penjualan
             WHERE DATE(tanggal) BETWEEN ? AND ?
             GROUP BY metode_pembayaran
             ORDER BY omzet DESC",
            [$startDate, $endDate]
        )->getResultArray();

        $dailyRows = $db->query(
            "SELECT DATE(tanggal) AS tanggal, COALESCE(SUM(total),0) AS omzet, COUNT(*) AS transaksi
             FROM penjualan
             WHERE DATE(tanggal) BETWEEN ? AND ?
             GROUP BY DATE(tanggal)
             ORDER BY tanggal ASC",
            [$startDate, $endDate]
        )->getResultArray();

        $dailyMap = [];
        foreach ($dailyRows as $row) {
            $dailyMap[$row['tanggal']] = [
                'omzet' => (float) $row['omzet'],
                'transaksi' => (int) $row['transaksi'],
            ];
        }

        $chartLabels = [];
        $chartRevenue = [];
        $chartTransactions = [];
        $cursor = new \DateTimeImmutable($startDate);
        $last = new \DateTimeImmutable($endDate);
        while ($cursor <= $last) {
            $date = $cursor->format('Y-m-d');
            $chartLabels[] = $cursor->format('d M');
            $chartRevenue[] = $dailyMap[$date]['omzet'] ?? 0;
            $chartTransactions[] = $dailyMap[$date]['transaksi'] ?? 0;
            $cursor = $cursor->modify('+1 day');
        }

        $slowMoving = $db->query(
            "SELECT b.id_barang, b.kode_barang, b.nama_barang, b.satuan, b.stok, b.harga_beli,
                    k.nama_kategori,
                    (b.stok * b.harga_beli) AS nilai_stok
             FROM barang b
             LEFT JOIN kategori k ON k.id_kategori = b.id_kategori
             LEFT JOIN (
                 SELECT dp.id_barang, SUM(dp.qty) AS qty_terjual
                 FROM detail_penjualan dp
                 INNER JOIN penjualan p ON p.id_penjualan = dp.id_penjualan
                 WHERE DATE(p.tanggal) BETWEEN ? AND ?
                 GROUP BY dp.id_barang
             ) s ON s.id_barang = b.id_barang
             WHERE b.stok > 0 AND COALESCE(s.qty_terjual, 0) = 0
             ORDER BY nilai_stok DESC, b.stok DESC
             LIMIT 8",
            [$startDate, $endDate]
        )->getResultArray();

        $slowCapitalRow = $db->query(
            "SELECT COALESCE(SUM(b.stok * b.harga_beli),0) AS nilai
             FROM barang b
             LEFT JOIN (
                 SELECT dp.id_barang, SUM(dp.qty) AS qty_terjual
                 FROM detail_penjualan dp
                 INNER JOIN penjualan p ON p.id_penjualan = dp.id_penjualan
                 WHERE DATE(p.tanggal) BETWEEN ? AND ?
                 GROUP BY dp.id_barang
             ) s ON s.id_barang = b.id_barang
             WHERE b.stok > 0 AND COALESCE(s.qty_terjual, 0) = 0",
            [$startDate, $endDate]
        )->getRowArray();
        $slowCapital = (float) ($slowCapitalRow['nilai'] ?? 0);

        $openPoRows = $db->query(
            "SELECT dp.id_barang,
                    SUM(GREATEST(dp.qty - dp.qty_diterima, 0)) AS qty_open
             FROM detail_pembelian dp
             INNER JOIN pembelian p ON p.id_pembelian = dp.id_pembelian
             WHERE p.status IN ('DIORDER','SEBAGIAN')
             GROUP BY dp.id_barang"
        )->getResultArray();
        $openPoByProduct = [];
        foreach ($openPoRows as $row) {
            $openPoByProduct[(int) $row['id_barang']] = (int) $row['qty_open'];
        }

        $salesByProduct = $db->query(
            "SELECT b.id_barang, b.kode_barang, b.nama_barang, b.satuan, b.stok,
                    COALESCE(SUM(CASE WHEN p.id_penjualan IS NOT NULL THEN dp.qty ELSE 0 END),0) AS qty_terjual
             FROM barang b
             LEFT JOIN detail_penjualan dp ON dp.id_barang = b.id_barang
             LEFT JOIN penjualan p ON p.id_penjualan = dp.id_penjualan
                 AND DATE(p.tanggal) BETWEEN ? AND ?
             GROUP BY b.id_barang, b.kode_barang, b.nama_barang, b.satuan, b.stok
             ORDER BY qty_terjual DESC",
            [$startDate, $endDate]
        )->getResultArray();

        $restockRecommendations = [];
        foreach ($salesByProduct as $row) {
            $qtySold = (int) $row['qty_terjual'];
            if ($qtySold <= 0) {
                continue;
            }

            $averageDaily = $qtySold / $period;
            // Heuristik transparan Phase 13: target 14 hari kebutuhan + 3 hari safety stock.
            $targetStock = (int) ceil($averageDaily * 17);
            $currentStock = (int) $row['stok'];
            $openQty = $openPoByProduct[(int) $row['id_barang']] ?? 0;
            $suggested = max(0, $targetStock - $currentStock - $openQty);

            if ($suggested <= 0) {
                continue;
            }

            $restockRecommendations[] = [
                'id_barang' => (int) $row['id_barang'],
                'kode_barang' => $row['kode_barang'],
                'nama_barang' => $row['nama_barang'],
                'satuan' => $row['satuan'],
                'stok' => $currentStock,
                'qty_terjual' => $qtySold,
                'rata_harian' => $averageDaily,
                'qty_po_terbuka' => $openQty,
                'target_stok' => $targetStock,
                'saran_pesan' => $suggested,
            ];
        }
        usort($restockRecommendations, static fn(array $a, array $b): int => $b['saran_pesan'] <=> $a['saran_pesan']);
        $restockRecommendations = array_slice($restockRecommendations, 0, 10);

        $overduePos = $db->query(
            "SELECT p.id_pembelian, p.no_pembelian, p.tanggal, p.tanggal_target, p.status, p.total,
                    s.nama_supplier,
                    COALESCE(SUM(GREATEST(dp.qty - dp.qty_diterima,0)),0) AS qty_belum_diterima
             FROM pembelian p
             INNER JOIN supplier s ON s.id_supplier = p.id_supplier
             INNER JOIN detail_pembelian dp ON dp.id_pembelian = p.id_pembelian
             WHERE p.status IN ('DIORDER','SEBAGIAN')
               AND p.tanggal_target IS NOT NULL
               AND p.tanggal_target < CURDATE()
             GROUP BY p.id_pembelian, p.no_pembelian, p.tanggal, p.tanggal_target, p.status, p.total, s.nama_supplier
             ORDER BY p.tanggal_target ASC
             LIMIT 8"
        )->getResultArray();

        $activePoSummary = $db->query(
            "SELECT COUNT(*) AS jumlah, COALESCE(SUM(total),0) AS nilai
             FROM pembelian
             WHERE status IN ('DIORDER','SEBAGIAN')"
        )->getRowArray() ?? [];

        $recommendations = $this->buildRecommendations(
            $period,
            $changes,
            $inventory,
            $restockRecommendations,
            $slowMoving,
            $slowCapital,
            $overduePos
        );

        return view('management/index', [
            'title' => 'Pusat Keputusan',
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'previousStart' => $previousStart,
            'previousEnd' => $previousEnd,
            'current' => $current,
            'previous' => $previous,
            'changes' => $changes,
            'inventory' => $inventory,
            'topProducts' => $topProducts,
            'categorySales' => $categorySales,
            'paymentMix' => $paymentMix,
            'chartLabels' => $chartLabels,
            'chartRevenue' => $chartRevenue,
            'chartTransactions' => $chartTransactions,
            'slowMoving' => $slowMoving,
            'slowCapital' => $slowCapital,
            'restockRecommendations' => $restockRecommendations,
            'overduePos' => $overduePos,
            'activePoSummary' => [
                'jumlah' => (int) ($activePoSummary['jumlah'] ?? 0),
                'nilai' => (float) ($activePoSummary['nilai'] ?? 0),
            ],
            'recommendations' => $recommendations,
        ]);
    }

    private function salesSummary($db, string $startDate, string $endDate): array
    {
        $sales = $db->query(
            "SELECT COUNT(*) AS transaksi,
                    COALESCE(SUM(total),0) AS omzet,
                    COALESCE(AVG(total),0) AS rata_rata
             FROM penjualan
             WHERE DATE(tanggal) BETWEEN ? AND ?",
            [$startDate, $endDate]
        )->getRowArray() ?? [];

        $profit = $db->query(
            "SELECT COALESCE(SUM(dp.subtotal - (dp.harga_modal * dp.qty)),0) AS laba_kotor,
                    COALESCE(SUM(dp.harga_modal * dp.qty),0) AS hpp
             FROM detail_penjualan dp
             INNER JOIN penjualan p ON p.id_penjualan = dp.id_penjualan
             WHERE DATE(p.tanggal) BETWEEN ? AND ?",
            [$startDate, $endDate]
        )->getRowArray() ?? [];

        return [
            'transaksi' => (int) ($sales['transaksi'] ?? 0),
            'omzet' => (float) ($sales['omzet'] ?? 0),
            'rata_rata' => (float) ($sales['rata_rata'] ?? 0),
            'laba_kotor' => (float) ($profit['laba_kotor'] ?? 0),
            'hpp' => (float) ($profit['hpp'] ?? 0),
        ];
    }

    private function percentageChange(float|int $current, float|int $previous): ?float
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if (abs($previous) < 0.000001) {
            return abs($current) < 0.000001 ? 0.0 : null;
        }

        return (($current - $previous) / abs($previous)) * 100;
    }

    private function buildRecommendations(
        int $period,
        array $changes,
        array $inventory,
        array $restockRecommendations,
        array $slowMoving,
        float $slowCapital,
        array $overduePos
    ): array {
        $items = [];

        if ($changes['omzet'] !== null) {
            $direction = $changes['omzet'] > 0 ? 'naik' : ($changes['omzet'] < 0 ? 'turun' : 'tetap');
            $items[] = [
                'tone' => $changes['omzet'] < 0 ? 'warning' : 'info',
                'icon' => $changes['omzet'] < 0 ? 'bi-graph-down-arrow' : 'bi-graph-up-arrow',
                'title' => 'Perubahan omzet',
                'text' => 'Omzet ' . $direction . ' ' . number_format(abs((float) $changes['omzet']), 1, ',', '.') . '% dibanding periode ' . $period . ' hari sebelumnya. Gunakan tren produk dan kategori di bawah untuk melihat sumber perubahannya.',
                'url' => base_url('laporan/penjualan'),
                'action' => 'Buka laporan penjualan',
            ];
        }

        if (!empty($overduePos)) {
            $items[] = [
                'tone' => 'danger',
                'icon' => 'bi-truck-flatbed',
                'title' => 'PO melewati target penerimaan',
                'text' => count($overduePos) . ' purchase order aktif telah melewati tanggal target. Prioritaskan konfirmasi status pengiriman dengan supplier.',
                'url' => base_url('pembelian'),
                'action' => 'Tinjau purchase order',
            ];
        }

        if (!empty($restockRecommendations)) {
            $items[] = [
                'tone' => 'warning',
                'icon' => 'bi-box-arrow-in-down',
                'title' => 'Kebutuhan restock terindikasi',
                'text' => count($restockRecommendations) . ' SKU memiliki proyeksi persediaan di bawah target 17 hari berdasarkan laju penjualan periode terpilih dan PO yang masih terbuka.',
                'url' => base_url('pembelian'),
                'action' => 'Tinjau pengadaan',
            ];
        }

        if (!empty($slowMoving)) {
            $items[] = [
                'tone' => 'secondary',
                'icon' => 'bi-hourglass-split',
                'title' => 'Persediaan tanpa penjualan',
                'text' => count($slowMoving) . ' SKU pada daftar prioritas tidak mencatat penjualan selama ' . $period . ' hari. Nilai modal pada seluruh stok tanpa penjualan periode ini sekitar Rp ' . number_format($slowCapital, 0, ',', '.') . '.',
                'url' => base_url('laporan/barang'),
                'action' => 'Tinjau persediaan',
            ];
        }

        if (($inventory['stok_habis'] ?? 0) > 0) {
            $items[] = [
                'tone' => 'danger',
                'icon' => 'bi-exclamation-octagon',
                'title' => 'Stok habis',
                'text' => number_format((int) $inventory['stok_habis']) . ' SKU saat ini memiliki stok nol atau negatif dan perlu ditinjau sebelum menerima transaksi berikutnya.',
                'url' => base_url('barang'),
                'action' => 'Lihat barang',
            ];
        }

        if (empty($items)) {
            $items[] = [
                'tone' => 'success',
                'icon' => 'bi-check2-circle',
                'title' => 'Tidak ada alert utama',
                'text' => 'Tidak ditemukan indikator operasional utama yang memerlukan perhatian pada periode yang dipilih.',
                'url' => base_url('dashboard'),
                'action' => 'Kembali ke dashboard',
            ];
        }

        return $items;
    }
}
