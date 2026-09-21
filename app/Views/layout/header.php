<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'KAMELA') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= base_url('assets/js/ajax-table.js') ?>" defer></script>
    <style>
        :root{--brand:#2563eb;--brand-dark:#1d4ed8;--ink:#18212f;--muted:#6b7280;--border:#e5e7eb;--surface:#fff;--bg:#f6f8fc;--sidebar:#111827;--success:#0f9f6e;--danger:#dc2626;--warning:#d97706}
        *{font-family:'Inter',sans-serif;box-sizing:border-box}
        body{margin:0;background:var(--bg);color:var(--ink)}
        .main-content{margin-left:268px;padding:28px;min-height:100vh}
        .page-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:22px}
        .page-heading h1,.page-heading h2,.page-heading h3{margin:0;font-weight:800;letter-spacing:-.025em}
        .page-heading p{margin:5px 0 0;color:var(--muted);font-size:13px}
        .surface-card{background:#fff;border:1px solid var(--border);border-radius:16px;box-shadow:0 5px 18px rgba(15,23,42,.035)}
        .btn-primary{background:var(--brand);border-color:var(--brand);font-weight:600}
        .btn-primary:hover{background:var(--brand-dark);border-color:var(--brand-dark)}
        .form-control,.form-select{border-color:#d9dee7;border-radius:10px;min-height:42px}
        .form-control:focus,.form-select:focus{border-color:#9dbbf9;box-shadow:0 0 0 .2rem rgba(37,99,235,.1)}
        .table>thead>tr>th{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#667085;background:#f8fafc;border-bottom-color:#e5e7eb}
        .table>tbody>tr>td{vertical-align:middle;border-color:#eef1f5}
        .badge-soft-success{background:#eafaf4;color:#087956}.badge-soft-danger{background:#fff0f0;color:#b42318}.badge-soft-warning{background:#fff8e7;color:#a35b00}.badge-soft-primary{background:#eef4ff;color:#1d4ed8}
        @media(max-width:991.98px){.main-content{margin-left:0;padding:20px;padding-top:82px}.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.mobile-topbar{display:flex!important}}
    </style>
</head>
<body>
