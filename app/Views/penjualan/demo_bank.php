<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>KAMELA Bank Demo</title>
<style>
*{box-sizing:border-box}
body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#edf3ef;margin:0;color:#17211b}
.phone{width:min(100%,440px);min-height:100vh;margin:auto;background:#f7faf8;box-shadow:0 0 45px rgba(0,0,0,.08)}
.top{background:#146c43;color:white;padding:22px 22px 28px;border-radius:0 0 26px 26px}
.brand{font-size:13px;font-weight:800;letter-spacing:.13em;opacity:.92}
.top h1{font-size:24px;margin:10px 0 4px}
.balance{margin-top:22px;background:rgba(255,255,255,.14);padding:16px;border-radius:16px}
.balance small{opacity:.8}.balance strong{display:block;font-size:25px;margin-top:4px}
.content{padding:20px}.card{background:white;border-radius:18px;padding:20px;box-shadow:0 8px 28px rgba(34,63,46,.08);margin-bottom:16px}
.row{display:flex;justify-content:space-between;gap:18px;padding:11px 0;border-bottom:1px solid #edf1ee}.row:last-child{border-bottom:0}.row span{color:#68746d}.row strong{text-align:right}
.amount{font-size:30px;font-weight:850;margin:10px 0 4px}
label{display:block;font-size:13px;font-weight:700;margin:16px 0 7px}
input{width:100%;font:inherit;font-size:18px;padding:13px 14px;border:1px solid #ced8d1;border-radius:12px}
button{width:100%;border:0;border-radius:13px;padding:14px;font-size:16px;font-weight:800;background:#198754;color:white;margin-top:16px}
.note{font-size:12px;color:#758079;text-align:center;margin:14px 4px}.alert{padding:12px 14px;border-radius:12px;background:#fff1f0;color:#b42318;margin-bottom:14px;font-size:14px}.bad{color:#b42318;font-weight:700}.ok{color:#198754;font-weight:700}.status{font-size:13px;font-weight:800}
</style>
</head>
<body>
<div class="phone">
  <div class="top">
    <div class="brand">KAMELA BANK DEMO</div>
    <h1>Transfer Virtual Account</h1>
    <div style="opacity:.82">Simulasi mobile banking untuk demo sistem.</div>
    <div class="balance"><small>Saldo Demo</small><strong>Rp 5.000.000</strong></div>
  </div>

  <div class="content">
    <?php if (!$payment): ?>
      <div class="card"><p class="bad"><?= esc($error ?: 'Instruksi transfer tidak ditemukan.') ?></p></div>
    <?php else: ?>
      <?php if ($error): ?><div class="alert"><?= esc($error) ?></div><?php endif; ?>

      <div class="card">
        <div class="status <?= $payment['status']==='PENDING'?'':'bad' ?>">STATUS: <?= esc($payment['status']) ?></div>
        <div class="amount">Rp <?= number_format((float)$payment['amount'],0,',','.') ?></div>
        <div class="row"><span>Penerima</span><strong>KAMELA STORE</strong></div>
        <div class="row"><span>Virtual Account</span><strong><?= esc($payment['payment_reference']) ?></strong></div>
        <div class="row"><span>Metode</span><strong>Transfer Demo</strong></div>
        <div class="row"><span>Berlaku sampai</span><strong><?= esc($payment['expires_at']) ?> WIB</strong></div>
        <?php if ($payment['status'] === 'PENDING'): ?><div class="row"><span>Sisa waktu</span><strong id="bankCountdown">--:--</strong></div><?php endif; ?>
      </div>

      <?php if ($payment['status'] === 'PENDING'): ?>
      <div class="card">
        <form method="post" action="<?= site_url('demo-bank/pay/' . $payment['token'] . '/confirm') ?>">
          <?= csrf_field() ?>
          <label for="amount">Nominal transfer</label>
          <input id="amount" type="number" name="amount" min="0" step="1" value="<?= esc((string)(int)$payment['amount']) ?>" required>
          <button type="submit">Transfer Sekarang</button>
        </form>
        <div class="note">Tidak ada uang sungguhan yang dipindahkan. Tombol ini mensimulasikan konfirmasi dari sistem bank.</div>
      </div>
      <?php elseif ($payment['status'] === 'PAID'): ?>
      <div class="card"><p class="ok">✓ Transfer ini sudah berhasil.</p><p class="note">Kamu bisa kembali ke perangkat kasir.</p></div>
      <?php elseif ($payment['status'] === 'CANCELLED'): ?>
      <div class="card"><p class="bad">Instruksi transfer sudah dibatalkan oleh kasir.</p></div>
      <?php else: ?>
      <div class="card"><p class="bad">Instruksi transfer sudah <?= esc(strtolower($payment['status'])) ?>.</p></div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php if (!empty($payment) && $payment['status'] === 'PENDING'): ?>
<script>
(() => {
  const target = document.getElementById('bankCountdown');
  const expiresAt = <?= json_encode(strtotime($payment['expires_at']) * 1000) ?>;
  const tick = () => {
    const sec = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
    const m = String(Math.floor(sec / 60)).padStart(2, '0');
    const s = String(sec % 60).padStart(2, '0');
    target.textContent = `${m}:${s}`;
    if (sec <= 0) location.reload();
  };
  tick(); setInterval(tick, 1000);
})();
</script>
<?php endif; ?>
</body>
</html>
