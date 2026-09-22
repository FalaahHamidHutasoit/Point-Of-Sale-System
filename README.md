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
copy .env.development.example .env
```

**PowerShell**

```powershell
Copy-Item .env.development.example .env
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
Admin      : admin / admin123
Kasir      : kasir / kasir123
Gudang     : gudang / gudang123
Purchasing : purchasing / purchasing123
Manager    : manager / manager123
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
| PO historis diterima | 29 |
| PO demo aktif menunggu penerimaan | 1 |
| Histori penjualan | 282 |
| Mutasi stok | 1277 |
| Periode histori | Jan 2024 – 21 Sep 2026 |

Kode barang demo menggunakan `KML001` sampai `KML050`.

---

## 3. Gambaran Besar Sistem

```text
Supplier
   ↓
Purchase Order oleh Purchasing
   ↓
Barang dikirim supplier
   ↓
Goods Receiving oleh Gudang
   ↓
Mutasi Stok MASUK
   ↓
Persediaan Barang
   ↓
Penjualan oleh Kasir
   ↓
Tunai / Dynamic QR Demo / Transfer Demo
   ↓
Mutasi Stok KELUAR
   ↓
Riwayat, Laporan & Audit
   ↓
Dashboard + Pusat Keputusan Manager
```

Kontrol tambahan:

```text
Stock Opname ───────→ Mutasi PENYESUAIAN
Audit Trail ────────→ Jejak aktivitas pengguna
Backup & Recovery ──→ Snapshot schema aplikasi saat ini
Security Layer ─────→ Auth, role, CSRF, session, headers, integrity
```

Tujuan KAMELA bukan hanya mencatat transaksi, tetapi mengubah data operasional menjadi informasi yang dapat dipakai untuk monitoring dan pengambilan keputusan.

## 4. Role Pengguna

### Admin

Admin memiliki full control termasuk transaksi, master data, laporan, audit, backup/recovery, serta **Pegawai & Akun**.

### Kasir

Kasir difokuskan ke penjualan, pembayaran, riwayat transaksi, dan customer.

### Gudang

Gudang menangani barang, kategori, penerimaan fisik dari Purchase Order, mutasi stok, stock opname, dan laporan persediaan. Gudang tidak membuat pesanan ke supplier.

### Purchasing

Purchasing menangani supplier dan Purchase Order: membuat draft/PO, mengirim pesanan, membatalkan PO yang belum diterima, serta memonitor histori penerimaan. Purchasing tidak mengubah stok secara langsung.

### Manager

Manager memiliki akses monitoring read-only ke dashboard, histori penjualan/pembelian, barang, mutasi stok, laporan, dan audit.

Setiap pegawai dapat mengganti password miliknya sendiri. Akses tidak hanya disembunyikan dari sidebar; route dan aksi sensitif juga memiliki pemeriksaan role di server.

---

## 5. Alur Master Barang & Persediaan

### Mengapa stok tidak diedit bebas?

Pada desain lama, stok dapat berubah dari halaman edit barang tanpa diketahui penyebabnya. Di KAMELA:

- **Edit Barang** hanya untuk data master seperti nama, kategori, harga, dan satuan.
- Stok bertambah hanya melalui **Penerimaan Barang (Goods Receiving)** atas Purchase Order yang valid.
- Stok berkurang melalui **Penjualan**.
- Selisih fisik diselesaikan melalui **Stock Opname**.
- Semua perubahan stok menghasilkan record di `mutasi_stok`.

Karena itu sistem dapat menjawab dua pertanyaan:

1. stok sekarang berapa;
2. mengapa stok berubah menjadi angka tersebut.

---

## 6. Alur Procurement / Purchase Order / Goods Receiving

KAMELA memisahkan **pemesanan barang** dari **barang yang benar-benar diterima**.

1. Purchasing memilih supplier dan membuat Purchase Order (PO).
2. PO dapat disimpan sebagai `DRAFT`, kemudian dikirim menjadi `DIORDER`.
3. Pembuatan PO **tidak mengubah stok**.
4. Supplier mengirim barang secara fisik.
5. Gudang membuka PO dan mencatat **Penerimaan Barang** beserta nomor surat jalan bila ada.
6. Gudang dapat menerima seluruh qty atau hanya sebagian.
7. Server mengunci dan memvalidasi PO/detail terkait; penerimaan melebihi sisa qty ditolak.
8. Hanya qty yang benar-benar diterima yang menambah stok dan memperbarui harga beli terbaru.
9. Setiap penerimaan menghasilkan `mutasi_stok` tipe `MASUK`.
10. PO menjadi `SEBAGIAN` bila masih ada sisa, atau `DITERIMA` bila seluruh qty terpenuhi.
11. PO yang belum memiliki penerimaan dapat dibatalkan sesuai hak akses.
12. Seluruh perubahan penting dicatat ke Audit Trail dan operasi kritis berjalan dalam database transaction.

Pemisahan ini membuat histori procurement lebih realistis: **pesanan belum tentu sama dengan penerimaan fisik**.

## 7. Alur Penjualan & Pembayaran

1. Admin/Kasir membuka Penjualan dan memasukkan barang ke keranjang.
2. Customer dapat dipilih atau transaksi dilakukan sebagai pelanggan umum.
3. Server selalu membaca harga dan stok aktual dari database; nilai sensitif dari browser tidak dipercaya.
4. Barang duplikat dalam request digabung sebelum validasi stok.
5. Harga jual **dan harga modal saat transaksi** disimpan sebagai histori.

### Tunai

```text
Keranjang → validasi stok → validasi uang bayar → simpan penjualan
→ kurangi stok → mutasi KELUAR → struk
```

Pembayaran kurang ditolak. Endpoint simpan penjualan langsung hanya menerima **Tunai**.

### Dynamic QR Payment Demo

```text
Keranjang → generate payment PENDING + token unik → tampilkan QR
→ HP membuka link bertoken → server konfirmasi payment
→ re-check stok + row lock → PAID → baru buat penjualan
→ stok berkurang → mutasi KELUAR → kasir mendeteksi status lewat polling
```

QR demo mempunyai expiry, dapat dibatalkan saat masih `PENDING`, dan token terminal tidak dapat dipakai ulang.

### Transfer / Virtual Account Demo

```text
Keranjang → generate VA/reference PENDING → Bank Demo di HP
→ validasi nominal → konfirmasi transfer
→ re-check stok + row lock → PAID → buat penjualan
→ stok berkurang → mutasi KELUAR
```

QRIS/Transfer **tidak boleh** disimpan langsung melalui endpoint Tunai. Keduanya wajib melewati lifecycle payment demo agar status, audit, expiry, cancel, dan replay protection tetap berlaku.

> QR dan Transfer pada KAMELA adalah **simulasi pembayaran**, bukan integrasi bank/payment gateway dan tidak memindahkan uang sungguhan.

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

## 13. Phase 4 + Phase 14 — Backup & Recovery Safety

Menu Admin **Backup & Recovery** membuat snapshot database aplikasi ke:

```text
writable/backups/
```

Folder tersebut berada di luar `public` dan tidak boleh menjadi web-accessible.

Karakteristik backup saat ini:

- format internal `KAMELA_BACKUP_V2`, bukan arbitrary SQL execution;
- checksum SHA-256;
- file corrupt/diubah ditolak;
- restore hanya mengizinkan whitelist tabel KAMELA;
- snapshot mencakup data core, `demo_payments`, procurement/goods receiving, stock opname, dan audit;
- restore menolak snapshot yang kehilangan tabel yang ada pada schema KAMELA saat ini;
- restore meminta password Admin dan frasa `RESTORE KAMELA`;
- sebelum restore dibuat safety backup otomatis;
- restore berjalan dalam database transaction;
- aktivitas backup/restore tercatat di Audit Trail.

Backup `KAMELA_BACKUP_V1` dari versi sebelum Phase 14 sengaja tidak diterima oleh service V2 karena snapshot lama belum mencakup tabel pembayaran demo dan penerimaan barang. Setelah memasang Phase 14, buat backup baru sebelum menguji restore.

Backup aplikasi bukan pengganti backup server/off-site. Production tetap perlu backup hosting/database terjadwal.

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

Jangan memakai `.env.development.example` development untuk server publik. Gunakan `.env.production.example` sebagai template, salin menjadi `.env` hanya di server, lalu isi credential production di sana.

---

## 16. Struktur Database Final

Tabel aplikasi yang menjadi schema KAMELA saat ini:

```text
users
kategori
customer
supplier
barang
penjualan
detail_penjualan
pembelian
detail_pembelian
penerimaan_barang
detail_penerimaan_barang
mutasi_stok
demo_payments
stock_opname
stock_opname_detail
audit_logs
```

`KAMELA_FULL_RESET.sql` adalah canonical fresh-install schema + demo seed. File dump legacy `pos_ci4.sql` tidak digunakan lagi dan sebaiknya tidak berada di repository final.

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

### Authentication & akun

- [ ] Admin, Kasir, Gudang, Purchasing, dan Manager dapat login.
- [ ] Password salah ditolak dan rate limit tetap bekerja.
- [ ] Akun nonaktif tidak dapat login; user aktif yang dinonaktifkan terputus pada request berikutnya.
- [ ] Password sementara memaksa change-password.
- [ ] Logout POST berhasil dan halaman protected kembali meminta login.

### Authorization

- [ ] Kasir tidak dapat membuka Stock Opname/PO/Pegawai lewat URL langsung.
- [ ] Gudang tidak dapat membuat penjualan atau membuat PO.
- [ ] Purchasing tidak dapat mencatat Goods Receiving.
- [ ] Manager hanya dapat monitoring/read-only.
- [ ] Admin mempunyai full control.

### Procurement & inventory

- [ ] Membuat PO tidak mengubah stok.
- [ ] Partial receiving hanya menambah stok sesuai qty yang diterima dan status PO menjadi `SEBAGIAN`.
- [ ] Receiving sisanya membuat status `DITERIMA`.
- [ ] Over-receive ditolak.
- [ ] Mutasi `MASUK` mencatat stok sebelum/sesudah.
- [ ] Edit master barang tidak mengubah stok.

### Penjualan & pembayaran

- [ ] Tunai kurang ditolak; tunai valid mengurangi stok tepat sekali.
- [ ] POST simpan penjualan dengan metode QRIS/Transfer secara langsung ditolak.
- [ ] QR: `PENDING → PAID`; scan ulang setelah terminal tidak membuat penjualan kedua.
- [ ] QR expired/cancelled tidak mengubah stok.
- [ ] Transfer: nominal salah ditolak; nominal benar menjadi `PAID` dan stok berkurang sekali.
- [ ] Payment cancelled/expired/replayed memiliki status dan audit yang sesuai.
- [ ] Jual melebihi stok ditolak.

### Historical data & stock opname

- [ ] Ubah harga master setelah transaksi; harga/harga modal historis transaksi lama tetap sama.
- [ ] Input stok fisik opname tidak langsung mengubah master stok.
- [ ] Finalisasi opname menghasilkan `PENYESUAIAN` dan opname final tidak dapat diedit ulang.
- [ ] Finalisasi ditolak bila stok berubah sejak snapshot.

### Audit, backup & decision support

- [ ] Audit menampilkan actor/action/status/timestamp untuk aksi penting.
- [ ] Buat backup V2 dan download berhasil.
- [ ] Backup V1/korup/parsial ditolak untuk restore current schema.
- [ ] Restore V2 diuji hanya pada database demo/copy terlebih dahulu.
- [ ] Pusat Keputusan Manager menampilkan KPI, stok rendah, PO terlambat, slow-moving, dan saran restock.

### UX

- [ ] Search/filter/pagination AJAX tetap bekerja dan fallback navigasi normal tetap tersedia.
- [ ] Tanggal/jam transaksi menggunakan Asia/Jakarta.
- [ ] Public payment page tidak menyimpan respons bertoken ke cache browser/proxy.

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

- [ ] role Admin/Kasir/Gudang/Purchasing/Manager
- [ ] lifecycle akun pegawai
- [ ] PO + partial/full Goods Receiving
- [ ] penjualan Tunai/QR Demo/Transfer Demo
- [ ] mutasi stok + stock opname
- [ ] audit
- [ ] backup/recovery V2
- [ ] Pusat Keputusan Manager
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
.env.development.example    -> template localhost/development
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
→ Pegawai & Role
→ Supplier
→ Purchase Order
→ Penerimaan Barang
→ Barang & Mutasi Stok
→ Penjualan + Pembayaran
→ Stock Opname
→ Pusat Keputusan
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


## Phase 7 — Dynamic QR Payment Simulation

KAMELA dapat membuat QR unik sekali pakai untuk simulasi pembayaran. Saat QR dipindai dari HP dan link dibuka, halaman HP otomatis melakukan POST konfirmasi; laptop kasir melakukan polling status setiap 1 detik. Stok dan transaksi penjualan **baru dibuat setelah konfirmasi berhasil**. Token berlaku 5 menit dan tidak dapat dipakai dua kali.

> Ini **simulasi**, bukan integrasi QRIS bank/payment gateway dan tidak memindahkan uang sungguhan.

Agar HP dapat membuka QR lokal, jalankan server yang dapat diakses LAN (contoh `php spark serve --host 0.0.0.0`) dan buka KAMELA di browser laptop menggunakan IP LAN laptop, bukan `localhost`.

## Phase 8 — Transfer / Virtual Account Demo

Metode Transfer menggunakan Virtual Account demo unik. QR pada modal kasir hanya membuka **KAMELA Bank Demo** di HP; customer tetap harus mengonfirmasi nominal transfer di bank simulator. Setelah nominal cocok, payment berubah menjadi `PAID`, transaksi penjualan dibuat, stok berkurang, dan kasir menerima status berhasil lewat polling.

## Phase 9 — Payment Reliability & Audit UX

Phase 9 memperkuat QR/Transfer demo tanpa menambah kompleksitas alur demo utama:

- countdown terlihat pada QR (5 menit) dan Transfer (10 menit);
- kasir dapat membatalkan payment yang masih `PENDING`; pembatalan tidak mengurangi stok;
- status baru `CANCELLED` dan timestamp `cancelled_at`;
- replay/double payment tetap diblokir menggunakan row lock dan status terminal;
- QR demo baru memiliki `payment_reference` yang mudah dibaca;
- detail transaksi menampilkan metode, status, reference, dan waktu pembayaran;
- Riwayat Transaksi menampilkan badge payment serta tabel payment attempt terbaru (`PENDING`, `PAID`, `EXPIRED`, `FAILED`, `CANCELLED`);
- audit event pembayaran mencakup `QR_CREATED`, `TRANSFER_CREATED`, `PAYMENT_PAID`, `TRANSFER_CONFIRMED`, `PAYMENT_EXPIRED`, `PAYMENT_CANCELLED`, `PAYMENT_REPLAY_BLOCKED`, dan `PAYMENT_FAILED`.

## Phase 10 — Role Architecture & Access Control

Phase 10 awalnya memisahkan tanggung jawab menjadi Admin, Kasir, Gudang, dan Manager. Phase 12 kemudian menambahkan Purchasing agar proses pengadaan terpisah dari penerimaan gudang. Hak akses diterapkan pada sidebar, route, dan controller.

## Phase 11 — User & Employee Management

Admin sekarang dapat mengelola siklus hidup akun pegawai melalui menu **Pegawai & Akun**:

- membuat akun pegawai dengan kode pegawai, kontak, tanggal masuk, dan role;
- mengedit profil/role;
- mengaktifkan atau menonaktifkan akun tanpa hard delete;
- mereset password ke password sementara;
- memaksa pegawai mengganti password sementara saat login berikutnya;
- melindungi administrator aktif terakhir dari demote/deactivation;
- mencatat login terakhir dan perubahan akun ke Audit Trail;
- memutus sesi secara otomatis jika akun dinonaktifkan atau role berubah.

Setiap pegawai memiliki menu **Ganti Password** untuk mengelola password pribadinya. Akun nonaktif ditolak saat login dan data historis tetap dapat ditelusuri.


## Phase 12 — Procurement & Goods Receiving

KAMELA sekarang memisahkan Purchase Order dari penerimaan fisik barang. Role **Purchasing** membuat PO dan mengelola supplier, sedangkan **Gudang** mencatat Goods Receipt. Stok tidak lagi bertambah saat PO dibuat; stok hanya bertambah saat barang benar-benar diterima. Partial receiving, status PO, nomor surat jalan, mutasi stok, dan audit trail didukung.


## Phase 13 — Managerial Reports & Decision Support

Phase 13 menambahkan **Pusat Keputusan** (`/management`) untuk role `manager` dan `admin`. Fitur ini bersifat read-only dan tidak mengubah transaksi.

Informasi yang disediakan:

- perbandingan omzet, laba kotor, jumlah transaksi, dan rata-rata nilai transaksi terhadap periode sebelumnya;
- margin kotor berdasarkan `harga_modal` historis pada detail penjualan;
- tren omzet 30/60/90 hari;
- produk dan kategori yang paling berkontribusi terhadap laba/omzet;
- komposisi metode pembayaran;
- nilai persediaan, stok habis, stok menipis, dan PO aktif;
- PO yang melewati tanggal target penerimaan;
- slow-moving stock (stok tersedia tanpa penjualan pada periode terpilih);
- rekomendasi restock transparan menggunakan rata-rata penjualan harian, target kebutuhan 14 hari + safety stock 3 hari, serta memperhitungkan qty PO yang masih terbuka.

Phase ini **tidak membutuhkan perubahan schema database**. Setelah file di-overlay, cukup buka menu **Pusat Keputusan** menggunakan akun Manager/Admin.

> Rekomendasi Phase 13 adalah decision support berbasis aturan, bukan prediksi AI. Keputusan akhir tetap berada pada pengguna/manager.

## Phase 14 — Operational Hardening & QA

Phase 14 mengaudit integrasi antar-modul dan memperbaiki inkonsistensi yang ditemukan setelah Phase 10–13 digabung:

- endpoint simpan penjualan langsung hanya menerima Tunai; QR/Transfer wajib melewati lifecycle payment demo terverifikasi;
- public payment error tidak lagi menampilkan pesan exception internal;
- halaman payment bertoken menggunakan `no-store`/`no-cache`;
- Backup & Recovery dinaikkan ke `KAMELA_BACKUP_V2` dan mencakup payment + Goods Receiving;
- restore menolak snapshot lama/parsial yang tidak lengkap untuk schema saat ini;
- `KAMELA_FULL_RESET.sql` disinkronkan dengan role, user management, procurement, payment, dan schema final;
- dokumentasi dan checklist regression disinkronkan dengan alur bisnis aktual.

Phase 14 tidak menambah schema baru untuk database existing, sehingga **tidak ada SQL migration yang perlu diimport**. Fresh install tetap menggunakan satu file `KAMELA_FULL_RESET.sql`.

---

## Phase 15 — Demo & Project Readiness

Phase 15 menetapkan `KAMELA_FULL_RESET.sql` sebagai **canonical fresh-install database**. Seluruh tabel aplikasi sekarang memiliki data demo yang relevan setelah import, termasuk Goods Receiving, payment attempts, Audit Trail, dan Stock Opname.

Seed Phase 15 juga menyiapkan skenario manajerial yang dapat langsung ditunjukkan: recent-vs-previous sales, low-stock dengan demand aktual, rekomendasi restock, slow-moving inventory, PO aktif, PO sebagian diterima, dan PO melewati target.

Core business logic tidak diubah pada Phase 15 karena aplikasi sudah lolos manual smoke test setelah Phase 14. Tujuan fase ini adalah menjaga build yang stabil sekaligus membuat proses `clone -> import -> demo` sesingkat mungkin.

Lihat `README_PHASE15.md` dan `DEMO_CHECKLIST.md` untuk detail data presentasi.
