# KAMELA Phase 15 — Demo & Project Readiness

Phase 15 adalah finalisasi demo KAMELA setelah seluruh fitur inti lolos manual smoke test. Fase ini sengaja **tidak mengubah core logic yang sudah stabil**. Fokusnya adalah membuat fresh clone langsung siap dipresentasikan dengan satu database canonical yang lengkap dan data demo yang bermakna.

## Hasil utama

`KAMELA_FULL_RESET.sql` sekarang menjadi satu-satunya baseline database fresh install. File ini membuat schema lengkap dan mengisi seluruh tabel aplikasi dengan data demo, termasuk skenario yang sebelumnya hanya muncul setelah aplikasi dipakai manual.

Data yang sudah tersedia setelah import:

- 5 akun role aktif: Admin, Kasir, Gudang, Purchasing, Manager.
- 1 akun pegawai nonaktif untuk demonstrasi lifecycle akun.
- 7 kategori, 50 barang, 25 customer, 10 supplier.
- 290 transaksi penjualan termasuk data current-period dan previous-period.
- Riwayat pembayaran Tunai, QRIS demo, Transfer demo.
- Payment status PAID, EXPIRED, CANCELLED, FAILED.
- 34 Purchase Order dengan status DRAFT, DIORDER, SEBAGIAN, DITERIMA, DIBATALKAN.
- Goods Receiving historis dan partial receiving.
- Mutasi MASUK, KELUAR, PENYESUAIAN.
- Stock Opname: dua FINALIZED dan satu CANCELLED.
- Audit trail siap dilihat tanpa harus membuat aktivitas terlebih dahulu.
- Produk low-stock yang benar-benar memiliki recent sales sehingga Pusat Keputusan menghasilkan rekomendasi restock.
- PO aktif yang melewati tanggal target sehingga indikator overdue langsung muncul.
- Slow-moving inventory tetap tersedia untuk analisis manager.

## Skenario demo yang sengaja disiapkan

| Skenario | Data |
|---|---|
| PO menunggu barang | `PO-DEMO-OPEN` |
| PO melewati target | `PO-DEMO-OVERDUE` |
| PO diterima sebagian | `PO-DEMO-PARTIAL` |
| PO draft | `PO-DEMO-DRAFT` |
| PO dibatalkan | `PO-DEMO-CANCELLED` |
| Low stock + demand | KML005, KML012, KML027, KML039 |
| Stock opname selesai | 2 histori FINALIZED |
| Stock opname dibatalkan | 1 histori CANCELLED |
| Akun nonaktif | `kasir.demo` |

Tidak ada Stock Opname berstatus DRAFT yang ditinggalkan oleh seed, sehingga Gudang tetap bisa langsung membuat opname baru saat praktik.

## Akun demo

```text
admin      / admin123
kasir      / kasir123
gudang     / gudang123
purchasing / purchasing123
manager    / manager123
```

## Fresh setup di kampus

```text
1. Clone repository KAMELA.
2. Siapkan database MySQL `pos_ci4`.
3. Import `KAMELA_FULL_RESET.sql`.
4. Buat `.env` lokal dari `.env.development.example` bila `.env` belum ada.
5. Pastikan database.default.* dan app.baseURL sesuai laptop.
6. Jalankan `php spark serve`.
```

`KAMELA_FULL_RESET.sql` juga memiliki `CREATE DATABASE IF NOT EXISTS pos_ci4`, jadi import melalui MySQL/phpMyAdmin dapat langsung menggunakan file ini sebagai baseline tunggal. File ini bersifat destructive: tabel KAMELA lama akan di-drop dan dibuat ulang.

## Catatan decision support

Rekomendasi restock **bukan tabel tersendiri**. KAMELA menghitungnya dari recent sales + stok saat ini + open PO menggunakan rule Phase 13. Karena itu Phase 15 menyiapkan data input yang sengaja menghasilkan recommendation pada fresh import.

PO overdue juga **bukan data hasil yang disimpan terpisah**. Pusat Keputusan mendeteksinya dari PO berstatus `DIORDER/SEBAGIAN` yang `tanggal_target < CURDATE()`.

Dengan begitu data demo tetap mengikuti cara kerja aplikasi sebenarnya, bukan hasil dashboard yang di-hardcode.
