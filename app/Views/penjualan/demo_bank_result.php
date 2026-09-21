<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>KAMELA Bank Demo</title>
<style>
body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#edf3ef;margin:0;display:grid;place-items:center;min-height:100vh;color:#17211b}
.card{background:#fff;width:min(92vw,430px);padding:30px;border-radius:20px;box-shadow:0 14px 45px rgba(0,0,0,.09);text-align:center}
.icon{width:72px;height:72px;margin:auto;border-radius:50%;display:grid;place-items:center;font-size:42px;font-weight:900}.ok{background:#e8f6ee;color:#198754}.bad{background:#fff0ef;color:#b42318}.amount{font-size:30px;font-weight:850}.muted{color:#68746d}.brand{font-weight:850;letter-spacing:.1em;font-size:13px}
</style>
</head>
<body>
<div class="card">
  <div class="brand">KAMELA BANK DEMO</div>
  <div class="icon <?= $ok?'ok':'bad' ?>"><?= $ok?'✓':'×' ?></div>
  <h2><?= $ok?'Transfer Berhasil':'Transfer Gagal' ?></h2>
  <?php if ($ok && $payment): ?><div class="amount">Rp <?= number_format((float)$payment['amount'],0,',','.') ?></div><?php endif; ?>
  <p><?= esc($message) ?></p>
  <?php if ($ok && $payment): ?><p class="muted">VA <?= esc($payment['payment_reference'] ?? '-') ?><br>Silakan lihat layar kasir. Status akan diperbarui otomatis.</p><?php endif; ?>
  <p class="muted">Simulasi saja — tidak ada uang sungguhan yang dipindahkan.</p>
</div>
</body>
</html>
