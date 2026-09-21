# Alur NexaPOS

Dokumen ini dibuat agar anggota tim dapat menjelaskan sistem tanpa harus membaca seluruh source code.

## 1. Gambaran Besar

```text
ADMIN
  ├─ Master Data
  │    ├─ Kategori
  │    ├─ Barang
  │    ├─ Supplier
  │    └─ Customer
  │
  ├─ Pembelian / Restock
  │    └─ Supplier → Barang masuk → Stok bertambah → Mutasi MASUK
  │
  ├─ Penjualan
  │    └─ Barang terjual → Stok berkurang → Mutasi KELUAR
  │
  └─ Laporan & Dashboard
       └─ Informasi untuk pengambilan keputusan

KASIR
  ├─ Penjualan
  └─ Riwayat Penjualan
```

## 2. Mengapa Stok Tidak Boleh Diedit Bebas?

Pada sistem lama, perubahan stok dapat terjadi dari halaman edit barang sehingga angka stok berubah tanpa diketahui penyebabnya.

Pada NexaPOS:

- Form **Edit Barang** hanya mengubah data master: nama, kategori, harga, satuan.
- Stok masuk dicatat melalui **Pembelian / Restock**.
- Stok keluar terjadi melalui **Penjualan**.
- Setiap perubahan disimpan ke **Mutasi Stok**.

Dengan demikian sistem dapat menjawab bukan hanya "stok sekarang berapa", tetapi juga "kenapa stok menjadi sebanyak itu".

## 3. Alur Pembelian / Restock

1. Admin membuka **Pembelian / Restock**.
2. Admin memilih supplier.
3. Admin memilih satu atau lebih barang.
4. Admin memasukkan qty dan harga beli.
5. Server menghitung subtotal dan total.
6. Sistem menyimpan header `pembelian`.
7. Sistem menyimpan `detail_pembelian`.
8. Stok barang bertambah.
9. Sistem membuat `mutasi_stok` bertipe `MASUK`.
10. Seluruh proses berada dalam satu database transaction. Jika salah satu gagal, semuanya dibatalkan.

## 4. Alur Penjualan

1. Admin/Kasir membuka **Penjualan**.
2. Produk dipilih ke keranjang.
3. Customer dapat dipilih atau menggunakan Customer Umum.
4. Kasir memilih metode pembayaran: Tunai, QRIS, atau Transfer.
5. Server membaca harga barang langsung dari database. Harga dari browser tidak dipercaya.
6. Server menggabungkan qty apabila barang yang sama dikirim lebih dari sekali.
7. Server memeriksa stok kembali.
8. Sistem menyimpan `penjualan` dan `detail_penjualan`.
9. Harga jual dan harga modal pada saat transaksi disimpan sebagai histori.
10. Stok berkurang.
11. Sistem membuat `mutasi_stok` bertipe `KELUAR`.
12. Nota transaksi ditampilkan.

## 5. Dashboard

Dashboard dirancang sebagai bagian SIM, bukan hanya halaman menu. Informasi yang ditampilkan:

- Omzet hari ini
- Jumlah transaksi hari ini
- Omzet bulan berjalan
- Stok menipis
- Grafik omzet 7 hari
- Nilai pembelian/restock bulan berjalan
- Produk terlaris
- Transaksi terbaru

Data operasional dari penjualan dan persediaan diringkas menjadi informasi yang lebih mudah digunakan untuk mengambil keputusan.

## 6. Tabel Utama

| Tabel | Fungsi |
|---|---|
| `barang` | Master barang dan stok saat ini |
| `kategori` | Kategori barang |
| `supplier` | Master pemasok |
| `customer` | Master pelanggan |
| `penjualan` | Header transaksi penjualan |
| `detail_penjualan` | Barang, qty, harga historis, harga modal, subtotal |
| `pembelian` | Header transaksi pembelian/restock |
| `detail_pembelian` | Barang masuk, qty, harga beli, subtotal |
| `mutasi_stok` | Audit trail semua perubahan stok |
| `users` | Login dan role Admin/Kasir |

## 7. Checklist Test Manual Sebelum Merge

### Login & role
- Login Admin → menu master/persediaan/laporan tampil.
- Login Kasir → menu admin tidak tampil.
- Admin dan Kasir sama-sama dapat membuka Penjualan dan Riwayat Penjualan.

### Pembelian
- Buat restock 5 unit suatu barang.
- Pastikan stok bertambah tepat 5.
- Pastikan transaksi muncul di Pembelian.
- Pastikan Mutasi Stok menampilkan `MASUK`, stok sebelum, dan stok sesudah.

### Penjualan
- Jual 2 unit barang yang baru direstock.
- Pastikan stok berkurang tepat 2.
- Pastikan Mutasi Stok menampilkan `KELUAR`.
- Coba jual lebih banyak dari stok → transaksi harus ditolak.
- Coba pembayaran tunai kurang → transaksi harus ditolak.
- Coba QRIS/Transfer → nominal pembayaran mengikuti total transaksi.

### Histori harga
- Jual satu barang.
- Ubah harga jual barang tersebut.
- Buka kembali transaksi lama.
- Nilai transaksi lama harus tetap menggunakan harga pada saat transaksi.

### Edit barang
- Catat stok barang.
- Edit nama/harga barang.
- Simpan.
- Stok harus tetap sama.

### Keamanan dasar
- Setelah logout, akses URL `/barang` secara langsung → harus diarahkan ke login.
- Kasir membuka `/supplier` langsung → harus ditolak/diarahkan dashboard.
- Tombol hapus menggunakan form POST, bukan link GET.

## 8. Upgrade Database

Jalankan `database_upgrade_sim.sql` **sekali** sebelum menguji fitur Pembelian, Mutasi Stok, metode pembayaran, dan harga modal historis.
