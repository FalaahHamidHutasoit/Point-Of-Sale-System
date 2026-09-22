# KAMELA — Final Demo Checklist

Gunakan checklist ini sesudah fresh import.

## 1. Login & Role
- Admin, Kasir, Gudang, Purchasing, Manager dapat login.
- `kasir.demo` tampil sebagai akun nonaktif.
- Role tidak dapat membuka modul yang bukan kewenangannya.

## 2. Purchasing & Goods Receiving
- Daftar PO menampilkan DRAFT, DIORDER, SEBAGIAN, DITERIMA, DIBATALKAN.
- `PO-DEMO-OVERDUE` melewati target.
- `PO-DEMO-PARTIAL` memiliki histori Goods Receiving dan masih menyisakan qty belum diterima.
- Membuat PO baru tidak langsung menaikkan stok.
- Gudang dapat menerima barang dan stok baru bertambah saat receiving.

## 3. Inventory
- Mutasi menampilkan MASUK, KELUAR, PENYESUAIAN.
- Stock Opname memiliki histori FINALIZED dan CANCELLED.
- Tidak ada DRAFT opname bawaan yang menghalangi praktik membuat opname baru.

## 4. Penjualan & Payment
- Histori penjualan sudah terisi.
- Tunai, QRIS, Transfer terlihat pada histori.
- Payment attempt memiliki PAID, EXPIRED, CANCELLED, FAILED.
- Buat satu transaksi baru untuk mendemokan pembayaran real-time.

## 5. Manager / SIM
- Pusat Keputusan memiliki omzet, laba kotor, transaksi, rata-rata transaksi.
- Ada data pembanding periode sebelumnya.
- KML005/KML012/KML027/KML039 muncul sebagai low-stock candidates dan recent demand.
- Restock recommendation tampil.
- PO overdue tampil.
- Slow-moving inventory tampil.

## 6. Audit & Backup
- Audit Trail tidak kosong setelah fresh import.
- Create Backup menghasilkan KAMELA_BACKUP_V2 JSON.
- Restore tetap dilakukan dari UI Admin, bukan manual coding/phpMyAdmin.
