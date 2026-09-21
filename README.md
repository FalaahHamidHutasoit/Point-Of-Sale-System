# KAMELA — Point of Sale, Inventory & Management Information System

KAMELA adalah sistem **Point of Sale (POS) + Inventory Management** berbasis **CodeIgniter 4 + MySQL/MariaDB** yang dikembangkan untuk tugas Sistem Informasi Manajemen. Versi final ini menggabungkan sistem transaksi, pembelian supplier, persediaan, informasi manajerial, kontrol stok, audit aktivitas, backup/recovery, dan hardening keamanan dalam satu project.

Dokumen ini adalah dokumentasi tunggal project. README apply per fase, docs security per fase, dan SQL upgrade terpisah sudah digabung agar anggota tim yang baru clone tidak perlu mengikuti sejarah pengembangan dari awal.

---

## 1. Quick Start untuk Device Baru

### Requirement

- PHP 8.1+ (project saat ini kompatibel dengan PHP 8.4)
- MySQL/MariaDB
- Composer
- Extension PHP `intl`, `mbstring`, dan `mysqli`

### Setup

```bash
git clone <repository-kalian>
cd Point-Of-Sale-System
composer install
```

Salin environment development:

**Windows CMD**

```cmd
copy .env.example .env
```

**PowerShell**

```powershell
Copy-Item .env.example .env
```

Pastikan konfigurasi database di `.env` sesuai device masing-masing. Default project menggunakan:

```text
database : pos_ci4
host     : localhost
user     : root
password : kosong
port     : 3306
```

Lalu buka phpMyAdmin dan **import satu file saja**:

```text
KAMELA_FULL_RESET.sql
```

File tersebut sudah mencakup struktur database final sekaligus dataset demo. Tidak perlu lagi menjalankan SQL Phase 2, Phase 3, Phase 5, atau reset data lain secara terpisah.

Jalankan aplikasi:

```bash
php spark serve
```

Umumnya aplikasi tersedia di:

```text
http://localhost:8080
```

### Login demo

```text
Admin
username : admin
password : admin123

Kasir
username : kasir
password : kasir123
```

> Akun demo hanya untuk development/presentasi. Ganti kredensial sebelum deployment sungguhan.

---

## 2. Perhatian Tentang SQL Reset

`KAMELA_FULL_RESET.sql` adalah **FULL RESET**. File ini melakukan `DROP TABLE` terhadap tabel aplikasi KAMELA lalu membuat ulang struktur final dan mengisi dataset demo.

Gunakan untuk:

- laptop anggota kelompok yang baru clone;
- device presentasi;
- environment testing;
- reset data demo yang sudah berantakan.

Jangan import ke database yang berisi data sungguhan tanpa backup.

Dataset demo setelah import:

| Data | Jumlah |
|---|---:|
| Kategori | 7 |
| Barang | 50 |
| Customer | 25 |
| Supplier | 10 |
| Histori pembelian | 29 |
| Histori penjualan | 282 |
| Mutasi stok | 1277 |
| Periode histori | Jan 2024 – 21 Sep 2026 |

Kode barang demo menggunakan `KML001` sampai `KML050`.

---

## 3. Gambaran Besar Sistem

```text
Supplier
   ↓
Pembelian / Restock
   ↓
Mutasi Stok MASUK
   ↓
Persediaan Barang
   ↓
Penjualan
   ↓
Mutasi Stok KELUAR
   ↓
Riwayat & Laporan
   ↓
Dashboard Manajemen
```

Kontrol tambahan:

```text
Stock Opname ───────→ Mutasi PENYESUAIAN
Audit Trail ────────→ Jejak aktivitas pengguna
Backup & Recovery ──→ Pemulihan data
Security Layer ─────→ Auth, role, CSRF, session, headers, integrity
```

Tujuan KAMELA bukan hanya mencatat transaksi, tetapi mengubah data operasional menjadi informasi yang dapat dipakai untuk monitoring dan pengambilan keputusan.

---

## 4. Role Pengguna

### Admin

Admin memiliki akses ke:

- Dashboard manajemen
- Penjualan dan riwayat penjualan
- Pembelian / restock
- Mutasi stok
- Stock opname
- Barang
- Kategori
- Supplier
- Customer
- Laporan penjualan
- Laporan persediaan
- Audit aktivitas
- Backup & recovery

### Kasir

Kasir difokuskan ke kegiatan operasional:

- Dashboard
- Transaksi penjualan
- Riwayat penjualan

Akses tidak hanya disembunyikan dari sidebar. Route dan aksi sensitif juga memiliki pemeriksaan role di server.

---

## 5. Alur Master Barang & Persediaan

### Mengapa stok tidak diedit bebas?

Pada desain lama, stok dapat berubah dari halaman edit barang tanpa diketahui penyebabnya. Di KAMELA:

- **Edit Barang** hanya untuk data master seperti nama, kategori, harga, dan satuan.
- Stok bertambah melalui **Pembelian / Restock**.
- Stok berkurang melalui **Penjualan**.
- Selisih fisik diselesaikan melalui **Stock Opname**.
- Semua perubahan stok menghasilkan record di `mutasi_stok`.

Karena itu sistem dapat menjawab dua pertanyaan:

1. stok sekarang berapa;
2. mengapa stok berubah menjadi angka tersebut.

---

## 6. Alur Pembelian / Restock

1. Admin memilih supplier.
2. Admin memilih barang.
3. Admin memasukkan qty dan harga beli.
4. Server memvalidasi input dan menghitung subtotal/total.
5. Header disimpan ke `pembelian`.
6. Item disimpan ke `detail_pembelian`.
7. Stok barang bertambah.
8. Harga beli terbaru diperbarui.
9. `mutasi_stok` dibuat dengan tipe `MASUK`.
10. Seluruh proses memakai database transaction.
11. Row barang dikunci ketika perlu agar update stok bersamaan tidak menghasilkan lost update.

Jika satu langkah gagal, transaksi database di-rollback sehingga data tidak dibiarkan setengah tersimpan.

---

## 7. Alur Penjualan

1. Admin/Kasir membuka Penjualan.
2. Barang dimasukkan ke keranjang.
3. Customer dapat dipilih atau transaksi dilakukan sebagai pelanggan umum.
4. Metode pembayaran: **Tunai, QRIS, Transfer**.
5. Server membaca harga barang dari database; nilai dari browser tidak dipercaya.
6. Barang duplikat dalam request digabung sebelum validasi stok.
7. Server memvalidasi stok aktual.
8. Untuk transaksi tunai, pembayaran kurang ditolak.
9. Header disimpan ke `penjualan`.
10. Detail disimpan ke `detail_penjualan`.
11. Harga jual **dan harga modal saat transaksi** disimpan sebagai histori.
12. Stok dikurangi.
13. `mutasi_stok` dibuat dengan tipe `KELUAR`.
14. Nota/detail transaksi ditampilkan.

Harga transaksi lama tidak berubah walaupun harga master barang diubah di kemudian hari.

---

## 8. Dashboard Sebagai Sistem Informasi Manajemen

Dashboard merangkum data operasional menjadi informasi manajerial, misalnya:

- omzet hari ini;
- jumlah transaksi hari ini;
- omzet bulan berjalan;
- stok menipis;
- tren omzet 7 hari;
- nilai pembelian bulan berjalan;
- produk terlaris;
- transaksi terbaru.

Contoh keputusan sederhana:

> Produk yang penjualannya tinggi dan stoknya mulai menipis dapat menjadi prioritas restock.

---

## 9. AJAX Table / Live Search

Tabel pencarian dan filter menggunakan progressive AJAX melalui `public/assets/js/ajax-table.js`.

Perilaku utamanya:

- pencarian berubah tanpa full-page reload;
- input search memiliki debounce;
- filter select/tanggal memperbarui tabel otomatis;
- pagination tetap AJAX;
- URL ikut diperbarui sehingga Back/Forward browser tetap masuk akal;
- jika JavaScript/AJAX gagal, form masih dapat bekerja dengan navigasi biasa.

AJAX adalah peningkatan UX, bukan pengganti validasi server.

---

# FOUNDATION & SECURITY PHASE 1–6

## 10. Phase 1 — Authentication & Session Hardening

Fokus Phase 1 adalah siklus login sampai logout.

Kontrol yang diterapkan:

- password diverifikasi menggunakan `password_verify`;
- pesan kegagalan login dibuat generik;
- login rate limiting / temporary lockout;
- session ID diregenerasi setelah login;
- session ID diregenerasi periodik;
- idle timeout;
- absolute session timeout;
- fingerprint session berbasis User-Agent;
- password hash dapat direhash otomatis bila standar PHP berubah;
- logout menggunakan POST + CSRF;
- route setup user lama dihapus;
- auto-routing tetap OFF.

### Test minimum

- login Admin dan Kasir berhasil;
- password salah ditolak tanpa membocorkan apakah username ada;
- kegagalan berulang memicu rate limit;
- logout membuat route protected tidak dapat dibuka lagi;
- session lama tidak tetap valid setelah proses autentikasi baru.

---

## 11. Phase 2 — Authorization & Data Integrity

Fokus Phase 2 adalah memastikan pengguna hanya dapat melakukan aksi yang berhak dilakukan dan master data tidak merusak histori transaksi.

Kontrol utama:

- sensitive POST memiliki pemeriksaan role di controller sebagai defense-in-depth;
- Kasir tidak dapat melakukan aksi Admin hanya dengan mengetik URL/request manual;
- kategori duplikat ditolak di aplikasi dan database;
- kategori yang dipakai barang tidak dapat dihapus;
- barang yang masih memiliki stok/histori tidak dapat dihapus;
- customer yang telah dipakai transaksi tidak dapat dihapus;
- supplier yang telah dipakai pembelian tidak dapat dihapus;
- FK customer pada penjualan menggunakan `ON DELETE RESTRICT`;
- penjualan/pembelian menggunakan transaction;
- stock mutation menggunakan row-level locking untuk mencegah overselling/lost update.

### Test minimum

- Kasir mencoba endpoint Admin → ditolak;
- dua transaksi bersamaan terhadap stok terakhir tidak boleh menghasilkan stok negatif;
- error di tengah transaksi harus rollback header, detail, stok, dan mutasi sekaligus.

---

## 12. Phase 3 — Audit Trail & Accountability

KAMELA menyimpan aktivitas penting ke `audit_logs`.

Informasi yang dapat dicatat:

- siapa pelaku;
- role;
- action;
- entity yang terpengaruh;
- snapshot sebelum/sesudah untuk perubahan penting;
- status `SUCCESS`, `FAILED`, atau `BLOCKED`;
- IP address;
- User-Agent;
- HTTP method;
- request URI;
- timestamp.

Contoh event:

- login berhasil/gagal;
- login diblokir rate limit;
- logout/session invalid;
- unauthorized access;
- tambah/edit/hapus master;
- delete yang ditolak karena integritas data;
- penjualan;
- pembelian;
- backup/restore;
- stock opname.

Field seperti password, token, secret, API key, cookie, dan CSRF tidak boleh disalin mentah ke snapshot audit.

Audit merupakan histori read-only dari UI aplikasi; tidak disediakan endpoint edit/delete audit normal.

---

## 13. Phase 4 — Backup & Recovery Safety

Menu Admin **Backup & Recovery** membuat snapshot database aplikasi ke:

```text
writable/backups/
```

Folder tersebut berada di luar `public` dan tidak boleh menjadi web-accessible.

Karakteristik backup:

- format JSON internal KAMELA, bukan arbitrary SQL execution;
- checksum SHA-256;
- file corrupt/diubah ditolak;
- restore hanya mengizinkan tabel whitelist;
- restore meminta password Admin;
- restore meminta frasa konfirmasi `RESTORE KAMELA`;
- sebelum restore dibuat safety backup otomatis;
- restore berjalan menggunakan database transaction;
- aktivitas backup/restore tercatat di audit log.

Backup aplikasi bukan pengganti backup server/off-site. Production tetap perlu backup hosting/database terjadwal.

---

## 14. Phase 5 — Controlled Stock Opname

Stock Opname digunakan untuk menyelaraskan stok sistem dengan stok fisik tanpa mengedit stok master secara sembarangan.

Alurnya:

```text
Buat Draft
   ↓
Snapshot stok sistem
   ↓
Input stok fisik
   ↓
Review selisih
   ↓
Finalisasi
   ↓
Mutasi PENYESUAIAN
```

Prinsip integritas:

- hanya satu sesi `DRAFT` aktif;
- input stok fisik tidak langsung mengubah master stok;
- semua barang harus dihitung sebelum finalisasi;
- finalisasi mengunci row barang;
- jika stok berubah setelah snapshot karena penjualan/restock lain, finalisasi ditolak;
- finalisasi bersifat atomic;
- opname `FINALIZED` tidak dapat diedit lagi;
- sesi dapat dibatalkan sebelum finalisasi;
- hasil penyesuaian tercatat di `mutasi_stok` dan audit log.

Tabel:

- `stock_opname`
- `stock_opname_detail`

---

## 15. Phase 6 — Production Hardening

Phase ini tidak menambah fitur bisnis. Fokusnya adalah deployment hygiene.

Kontrol yang diterapkan antara lain:

- global CSRF;
- Content Security Policy;
- `X-Frame-Options`;
- `X-Content-Type-Options`;
- `Referrer-Policy`;
- `Permissions-Policy`;
- cross-origin related headers;
- response authenticated menggunakan `Cache-Control: no-store`;
- HSTS ketika berjalan melalui HTTPS;
- auto routing OFF;
- `.env` dan runtime files di-ignore Git;
- `.htaccess` melindungi dotfile/config/SQL bila document root salah konfigurasi;
- template environment production tersedia di `.env.production.example`;
- production exception tidak seharusnya menampilkan stack trace/credential ke user.

### Production configuration

Jangan memakai `.env.example` development untuk server publik. Gunakan `.env.production.example` sebagai template, salin menjadi `.env` hanya di server, lalu isi credential production di sana.

---

## 16. Struktur Database Final

| Tabel | Fungsi |
|---|---|
| `users` | User login dan role |
| `kategori` | Master kategori |
| `barang` | Master barang + stok terakhir |
| `customer` | Master pelanggan |
| `supplier` | Master pemasok |
| `penjualan` | Header penjualan |
| `detail_penjualan` | Qty + harga historis + harga modal historis |
| `pembelian` | Header pembelian/restock |
| `detail_pembelian` | Detail barang masuk |
| `mutasi_stok` | Ledger perubahan stok |
| `audit_logs` | Accountability aktivitas aplikasi |
| `stock_opname` | Header sesi stok opname |
| `stock_opname_detail` | Snapshot dan stok fisik per barang |

Backup tidak mempunyai tabel khusus karena disimpan sebagai file JSON di `writable/backups`.

---

## 17. Invariant / Aturan Penting Sistem

Anggota tim sebaiknya memahami aturan berikut sebelum mengubah source:

1. Jangan mengubah stok secara arbitrary dari Edit Barang.
2. Stok masuk normal harus berasal dari Pembelian.
3. Stok keluar normal harus berasal dari Penjualan.
4. Koreksi stok harus melalui Stock Opname/PENYESUAIAN.
5. Harga/total transaksi dihitung kembali oleh server.
6. Aksi browser tidak boleh dianggap cukup sebagai validasi keamanan.
7. Master yang sudah menjadi bagian histori tidak boleh dihapus sembarangan.
8. Penjualan/pembelian harus tetap atomic.
9. Audit log tidak boleh menyimpan credential sensitif.
10. Backup harus berada di luar public web root.

---

## 18. Checklist Demo / Regression Test

Sebelum presentasi atau setelah merge besar, minimal jalankan:

### Authentication

- [ ] Admin login.
- [ ] Kasir login.
- [ ] Password salah ditolak.
- [ ] Logout melalui tombol/form berhasil.
- [ ] Setelah logout, `/barang` mengarah ke login.

### Authorization

- [ ] Admin dapat membuka master/persediaan/kontrol sistem.
- [ ] Kasir hanya melihat area operasional yang diizinkan.
- [ ] Kasir mengetik `/supplier` langsung → ditolak/redirect.

### Barang

- [ ] Catat stok barang.
- [ ] Edit nama/harga.
- [ ] Stok tidak berubah.

### Pembelian

- [ ] Restock +5 unit.
- [ ] Stok bertambah tepat 5.
- [ ] Detail pembelian tersimpan.
- [ ] Mutasi `MASUK` menunjukkan stok sebelum/sesudah.

### Penjualan

- [ ] Jual 2 unit.
- [ ] Stok berkurang tepat 2.
- [ ] Mutasi `KELUAR` tercatat.
- [ ] Jual melebihi stok → ditolak.
- [ ] Tunai kurang → ditolak.
- [ ] QRIS/Transfer mengikuti total.

### Historical price

- [ ] Lakukan penjualan.
- [ ] Ubah harga master barang.
- [ ] Buka transaksi lama.
- [ ] Harga transaksi lama tidak ikut berubah.

### Audit

- [ ] Edit satu barang.
- [ ] Buka Audit Aktivitas.
- [ ] Event memiliki actor, action, waktu, dan before/after yang sesuai.

### Backup

- [ ] Create backup.
- [ ] Download berhasil.
- [ ] Restore hanya setelah password + konfirmasi.
- [ ] File backup corrupt ditolak.

### Stock opname

- [ ] Buat draft.
- [ ] Input stok fisik tidak langsung mengubah stok master.
- [ ] Finalisasi menghasilkan PENYESUAIAN.
- [ ] Opname final tidak dapat diedit ulang.

### AJAX

- [ ] Search tabel berubah tanpa full-page reload.
- [ ] Filter dan pagination masih berfungsi.

---

## 19. Production Checklist

Sebelum aplikasi dibuka ke internet:

### Environment

- [ ] `CI_ENVIRONMENT=production`
- [ ] `app.baseURL` menggunakan domain HTTPS final
- [ ] `app.forceGlobalSecureRequests=true`
- [ ] `cookie.secure=true`
- [ ] document root web server menunjuk ke folder `public`
- [ ] debug toolbar/stack trace tidak terlihat publik

### Secret & repository

- [ ] `.env` tidak dilacak Git
- [ ] credential DB production berbeda dari demo/local
- [ ] secret yang pernah ter-push sudah dirotasi
- [ ] session, logs, backup, dan debugbar tidak di-commit

### Database & recovery

- [ ] backup dibuat sebelum go-live
- [ ] prosedur restore pernah diuji pada staging/copy database
- [ ] DB user menggunakan privilege minimum yang diperlukan
- [ ] ada backup server/off-site terjadwal

### Security

- [ ] HTTPS valid
- [ ] cookie Secure + HttpOnly + SameSite
- [ ] security headers muncul
- [ ] `.env`, SQL, writable, dan config tidak dapat diakses URL publik
- [ ] CSRF aktif
- [ ] auto routing OFF

### Functional

- [ ] role Admin/Kasir
- [ ] penjualan
- [ ] pembelian
- [ ] mutasi stok
- [ ] audit
- [ ] backup/recovery
- [ ] stock opname
- [ ] AJAX search/filter

---

## 20. Risiko Residual

Fondasi ini tidak berarti aplikasi kebal terhadap semua serangan. Hal yang masih dapat dikembangkan:

- belum ada MFA/2FA;
- CSP masih perlu kompatibilitas dengan frontend existing yang memakai sebagian inline JS/CSS;
- session berbasis file kurang ideal untuk deployment multi-server;
- backup aplikasi masih manual dan harus dilengkapi backup otomatis/off-site;
- mitigasi DDoS membutuhkan CDN/WAF/infrastruktur, bukan hanya PHP;
- belum ada penetration test profesional;
- belum ada SAST/DAST otomatis di CI;
- dependencies Composer/CDN harus tetap diperbarui berkala.

---

## 21. File Penting

```text
KAMELA_FULL_RESET.sql       -> satu-satunya SQL setup/reset final
.env.example                -> template localhost/development
.env.production.example     -> template production HTTPS
app/Controllers/            -> business/application controllers
app/Services/AuditService.php
app/Services/BackupService.php
public/assets/js/ajax-table.js
writable/backups/           -> backup runtime, tidak di-commit
```

---

## 22. Catatan untuk Anggota Kelompok

Untuk anggota yang baru masuk project, urutan memahami KAMELA paling mudah adalah:

```text
Dashboard
→ Master Barang
→ Supplier
→ Pembelian / Restock
→ Mutasi Stok
→ Penjualan
→ Riwayat
→ Stock Opname
→ Audit Aktivitas
→ Backup & Recovery
```

Dengan urutan itu, hubungan antar subsistem terlihat jelas tanpa harus membaca source code per baris.

---

## 23. Ringkasan Fondasi Final

```text
Authentication & Session
        ↓
Authorization
        ↓
Transaction / Data Integrity
        ↓
Audit Trail
        ↓
Backup & Recovery
        ↓
Controlled Inventory / Stock Opname
        ↓
Production Hardening
```

Sesudah titik ini, pengembangan berikutnya dapat dianggap **fitur bisnis**, contohnya retur penjualan/pembelian, promo, barcode scanner, export PDF/Excel, profit analytics, restock recommendation, loyalty, dan multi-cabang.
