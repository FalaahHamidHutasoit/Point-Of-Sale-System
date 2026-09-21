<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>KAMELA Payment Demo</title>
<style>body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f4f7f5;margin:0;display:grid;place-items:center;min-height:100vh;color:#17211b}.card{background:#fff;width:min(92vw,430px);padding:28px;border-radius:18px;box-shadow:0 14px 45px rgba(0,0,0,.08);text-align:center}.brand{font-weight:800;letter-spacing:.08em}.amount{font-size:34px;font-weight:800;margin:18px 0}.muted{color:#68746d}.spinner{width:34px;height:34px;border:4px solid #dfe7e2;border-top-color:#198754;border-radius:50%;animation:s .8s linear infinite;margin:20px auto}@keyframes s{to{transform:rotate(360deg)}}.bad{color:#b42318;font-weight:700}</style></head><body><div class="card">
<div class="brand">KAMELA</div><h2>Payment Demo</h2>
<?php if (!$payment): ?><p class="bad">QR pembayaran tidak ditemukan.</p>
<?php elseif ($payment['status'] === 'PENDING'): ?>
<div class="amount">Rp <?= number_format((float)$payment['amount'],0,',','.') ?></div><p class="muted">Ref: <?= esc($payment['payment_reference'] ?? '-') ?></p><div class="spinner"></div><p>Memvalidasi token dan mengonfirmasi pembayaran demo…</p><p class="muted">Berlaku sampai <?= esc($payment['expires_at']) ?> WIB • Tidak ada uang sungguhan yang dipindahkan.</p>
<form id="confirmForm" method="post" action="<?= site_url('payment/demo/' . $payment['token'] . '/confirm') ?>"><?= csrf_field() ?></form><script>setTimeout(()=>document.getElementById('confirmForm').submit(),500);</script>
<?php elseif ($payment['status'] === 'PAID'): ?><h2>✓ Sudah dibayar</h2><p class="muted">Token sekali pakai ini sudah digunakan dan tidak dapat dipakai lagi.</p>
<?php elseif ($payment['status'] === 'CANCELLED'): ?><p class="bad">Pembayaran ini sudah dibatalkan oleh kasir.</p>
<?php else: ?><p class="bad">QR ini sudah <?= esc(strtolower($payment['status'])) ?>.</p><?php endif; ?>
</div></body></html>