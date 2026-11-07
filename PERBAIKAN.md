# Catatan Perbaikan Project E-Commerce

Semua perubahan sudah diuji: **38 test otomatis (234 assertion)** lolos di SQLite **dan** MariaDB/MySQL, termasuk
uji upgrade dari `db_ecommerce.sql` lama beserta data bermasalah. Jalankan sendiri dengan `php artisan test`.

## Cara memasang
1. Timpa file project Anda dengan isi zip ini (`.env`, `vendor/`, `node_modules/` dan gambar di `storage/` tidak ikut, jadi aman).
2. `php artisan migrate`   (menjalankan migrasi baru `2026_11_03_000001_improve_ecommerce_schema`)
3. Aset frontend sudah di-build ulang di `public/build`. Jika mengubah view lagi: `npm run build`.
4. Di `.env`, disarankan: `APP_TIMEZONE=Asia/Jakarta` (sekarang `UTC`, jam pesanan selisih 7 jam) dan `APP_DEBUG=false` saat produksi.
5. Agar langganan kedaluwarsa otomatis: jalankan scheduler (`php artisan schedule:work`, atau cron `schedule:run` tiap menit).
   Pengecekan diskon sudah memeriksa tanggal berakhir, jadi diskon tidak bergantung pada scheduler.

## Bug & celah keamanan yang diperbaiki
| # | Masalah sebelumnya | Perbaikan |
|---|---|---|
| 1 | Keranjang (`add/remove/destroy`) bisa dimanipulasi user lain lewat ID | Semua dibatasi ke keranjang milik sendiri (404 jika bukan) |
| 2 | Faktur & "pesanan diterima" bisa diakses/diubah untuk pesanan orang lain | Dibatasi ke pemilik pesanan |
| 3 | Logout lewat GET (rawan CSRF) | Logout kini POST + invalidate session |
| 4 | Login tanpa pembatasan percobaan | Maks. 5x/menit per email+IP, plus opsi "Ingat saya" |
| 5 | Bukti pembayaran tersimpan publik & nama file `time()` bisa bentrok | Disimpan privat (nama acak), dibuka lewat route berotorisasi (pemilik/admin). Bukti lama tetap terbaca |
| 6 | Checkout tanpa transaksi: data setengah jadi jika gagal, stok tidak dicek | `OrderService` memakai DB transaction + row lock, validasi stok, rollback + hapus file jika gagal |
| 7 | Stok bisa minus saat admin kirim | Stok dicek ulang saat kirim; pesanan tidak bisa dikirim/ditolak dua kali |
| 8 | `start_date` == `end_date` pada langganan (Carbon mutable) | Dihitung benar (30 hari); data lama diperbaiki oleh migrasi |
| 9 | Kolom `subscriptions_type` enum `weekly/monthly/yearly`, tapi kode menyimpan `basic/premium/eksklusif` | Diubah jadi string |
| 10 | User yang pernah berlangganan (atau ditolak) tidak bisa berlangganan lagi | Hanya diblokir bila masih ada pembayaran *pending* |
| 11 | Diskon member memakai langganan pertama (bisa sudah kedaluwarsa) & crash bila tidak ada baris | Memakai langganan **aktif** terbaru; satu sumber kebenaran di `config/shop.php` |
| 12 | Paket ditentukan dari harga (`== 199000`), pesanan langganan dikenali dari alamat NULL | Kolom `orders.type` dan `orders.subscription_plan` |
| 13 | Gambar katalog & keranjang salah path (`storage/x.jpg`) | Accessor `Product::image_url` + placeholder |
| 14 | Hapus produk merusak riwayat pesanan (error null) | Soft delete; riwayat tetap tampil; keranjang produk itu dibersihkan |
| 15 | Produk baru tanpa diskon/gambar → error SQL; slug bisa kembar; nama file gambar dari klien | Default diskon 0, slug unik, gambar nama acak, validasi harga/stok/diskon 0-100 |
| 16 | `route('home')` tidak ada pada halaman langganan; paket tak valid menghasilkan harga 0 | Dirapikan, paket divalidasi dari config |
| 17 | Halaman 404 dikirim dengan status 200 | Status 404 yang benar |
| 18 | Karakter `z` nyasar di awal migrasi products | Dihapus |
| 19 | Nomor urut admin langganan selalu "1"; paginasi pesanan admin bentrok | Diperbaiki |
| 20 | Total di checkout versi mobile tersembunyi | Selalu tampil, lengkap dengan diskon member |

## Fitur baru
- **Katalog**: pencarian, filter kategori, urutan, 12 produk/halaman (sebelumnya 4), label stok habis/diskon.
- **Halaman detail produk** `/produk/{slug}` + produk serupa.
- **Ulasan & rating** (hanya pembeli yang pesanannya sudah sampai; satu ulasan per produk per pembeli).
- **Dashboard admin** berisi pendapatan, pesanan/langganan menunggu, jumlah pelanggan, stok menipis, pesanan terbaru.
- Badge jumlah item keranjang & badge member yang akurat di navbar.
- Command `subscriptions:expire` (terjadwal harian).
- Validasi lebih ketat: kode pos 5 digit, format telepon, nomor telepon unik saat ubah profil.

## Struktur kode baru
`app/Services/OrderService.php` (logika pesanan), `app/Http/Requests/CheckoutRequest.php`, `config/shop.php` (paket, per_page,
ambang stok), `app/Exceptions/OrderException.php`, `Admin/PaymentReceiptController`, `Customer/ReviewController`,
`tests/Feature/*`.

## Belum dikerjakan (saran lanjutan)
- Foreign key antar tabel (kolom `user_id`/`order_id` bertipe `unsignedInteger`, sedangkan `id` bertipe bigint; perlu migrasi penyamaan tipe + pembersihan data yatim).
- Bukti pembayaran lama (sebelum perbaikan) masih berada di disk publik; bisa dipindah ke `storage/app/private`.
- Fitur notifikasi (model `Notification` sudah ada tetapi belum dipakai), verifikasi email, dan lupa password.
- Payment gateway otomatis (Midtrans/Xendit) menggantikan verifikasi manual.
