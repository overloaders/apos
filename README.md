<div align="center">
  <img src="public/favicon.svg" alt="APOS Logo" width="80">
  <h1 align="center">APOS - Aplikasi Point of Sale Supermarket</h1>
  <p align="center">
    Sistem POS lengkap untuk supermarket, toko kelontong, dan bisnis retail
    <br />
    </p>
    
    
    
  
</div>


---

Jika aplikasi ini bermanfaat, Anda dapat mendukung saya melalui:
    
<a href="https://saweria.co/overloaders" target="_blank">
  <img src="https://img.shields.io/badge/Support%20Me-Saweria-FF6600?style=for-the-badge&logo=ko-fi&logoColor=white" alt="Saweria">
</a>

**https://saweria.co/overloaders**

Setiap dukungan sangat berarti untuk pengembangan fitur-fitur selanjutnya. Terima kasih! 🙏

---

## 📋 Daftar Isi

- [Tentang](#tentang)
- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Syarat Sistem](#syarat-sistem)
- [Instalasi](#instalasi)
- [Cara Penggunaan](#cara-penggunaan)
- [Lisensi](#lisensi)
- [Dukungan](#dukungan)

---

## 📖 Tentang

**APOS** adalah aplikasi Point of Sale (POS) berbasis web yang dirancang khusus untuk supermarket, toko kelontong, dan bisnis retail. Dibangun menggunakan Laravel 11 dan Bootstrap 4.6 dengan AdminLTE 3.2, aplikasi ini menyediakan fitur lengkap mulai dari manajemen produk, stok, penjualan, pembelian, hingga laporan keuangan.

Aplikasi ini mencakup **40+ fitur** yang saling terintegrasi untuk mendukung operasional bisnis retail sehari-hari.

---

## ✨ Fitur

### 🛒 Manajemen Penjualan (POS)

| Fitur | Keterangan |
|-------|------------|
| **Kasir / POS** | Antarmuka kasir cepat dengan input barcode, pencarian produk, dan tampilan keranjang |
| **Riwayat Penjualan** | Daftar transaksi lengkap dengan filter tanggal, metode bayar, dan status |
| **Retur Penjualan** | Proses retur barang dengan pengembalian stok otomatis |
| **Cetak Struk** | Cetak struk thermal langsung dari browser |
| **Multi Payment** | Tunai, Kartu Debit/Kredit, Transfer Bank, E-Wallet, Gift Card |
| **Gift Card / Voucher** | Penerbitan, top-up, dan redeem gift card di kasir |
| **Diskon Member** | Diskon otomatis berdasarkan level membership |
| **Tukar Poin** | Penukaran poin member sebagai diskon |

### 📦 Manajemen Produk & Stok

| Fitur | Keterangan |
|-------|------------|
| **Master Produk** | CRUD produk dengan barcode, SKU, harga beli/jual, gambar |
| **Kategori Produk** | Pengelompokan produk per kategori |
| **Brand / Merek** | Manajemen merek produk |
| **Satuan Unit** | Konversi satuan produk |
| **Supplier** | Data pemasok dengan kontak lengkap |
| **Stok Masuk/Keluar** | Tracking stok dengan mutasi gudang |
| **Kartu Stok** | Riwayat mutasi per SKU dengan saldo berjalan |
| **Stok Minimal** | Peringatan stok menipis di dashboard |
| **Stok Awal** | Pengaturan stok awal produk baru |
| **Penyesuaian Stok** | Koreksi stok manual dengan catatan |
| **Mutasi Stok** | Pemindahan stok antar gudang |
| **Opname Stok** | Stock opname dengan selisih otomatis jadi beban |
| **History Harga** | Riwayat perubahan harga beli & jual per produk |
| **Print Barcode** | Cetak label barcode (single & multiple) |

### 📋 Manajemen Pembelian

| Fitur | Keterangan |
|-------|------------|
| **Purchase Order** | Pembuatan PO dengan status menunggu, diterima, atau dibatalkan |
| **Receiving Barang** | Penerimaan barang dari PO, update stok otomatis |
| **Retur Pembelian** | Retur barang ke supplier, kurangi stok otomatis |
| **Request Pembelian** | Staff bisa request pembelian, atasan approve/reject |
| **Hutang (Payables)** | Tracking status lunas/partial/hutang per PO, input pembayaran |
| **Laporan Pembayaran PO** | Rekap pembayaran PO per supplier/status dengan print & export |

### 👥 Manajemen Member

| Fitur | Keterangan |
|-------|------------|
| **Data Member** | CRUD member dengan kode, nama, level membership |
| **Level Member** | Tingkatan member dengan disken berbeda |
| **Poin Member** | Akumulasi poin dari transaksi, bisa ditukar |
| **Riwayat Transaksi Member** | Tracking semua transaksi per member |
| **Piutang Member** | Limit kredit, outstanding balance, pembayaran piutang |
| **Diskon Spesial Member** | Harga khusus member per produk |

### 💰 Manajemen Keuangan

| Fitur | Keterangan |
|-------|------------|
| **Kategori Pengeluaran** | Pengelompokan biaya operasional |
| **Pengeluaran / Biaya** | Catat pengeluaran harian dengan approval |
| **Target Penjualan** | Target per user dan periode dengan progress bar |
| **Shift Kasir** | Buka/tutup shift, tracking kasir per shift |
| **Mesin Kasir** | Multi cash register support |

### 📊 Laporan & Export

| Fitur | Keterangan |
|-------|------------|
| **Laporan Penjualan** | Rekap penjualan harian/bulanan dengan detail |
| **Laporan Pembelian** | Rekap pembelian per periode |
| **Laporan Stok** | Stok masuk/keluar/sisa |
| **Laporan Laba Rugi** | Profit & loss statement |
| **Laba per Produk** | Margin analysis per produk dengan warna |
| **Laporan PPN** | Pajak PPN 11% per transaksi |
| **Retur Pembelian** | Rekap retur ke supplier |
| **Receiving Barang** | Rekap penerimaan barang |
| **Stok Bergerak** | Moving stock report |
| **Export Excel** | Download Excel untuk semua laporan |
| **Cetak / Print** | Setiap laporan bisa dicetak atau disimpan sebagai PDF |

### ⚙️ Pengaturan Sistem

| Fitur | Keterangan |
|-------|------------|
| **Manajemen User** | CRUD user dengan role dan foto profil |
| **Role & Permission** | Kontrol akses berbasis role |
| **Profil User** | Edit profil, foto, dan password |
| **Pengaturan Perusahaan** | Nama toko, alamat, logo, header/footer struk |
| **Activity Logs** | Audit trail semua aktivitas user |
| **Gift Card** | Manajemen gift card/voucher |

---

## 🛠️ Teknologi

| Teknologi | Versi |
|-----------|-------|
| **Backend** | Laravel 11, PHP 8.2+ |
| **Frontend** | Bootstrap 4.6, AdminLTE 3.2, jQuery 3 |
| **Database** | MySQL / MariaDB |
| **Template Engine** | Blade |
| **JavaScript** | Vanilla JS, SweetAlert2 |
| **Barcode** | Code128 (SVG) |
| **Export** | PhpSpreadsheet (Excel), CSV fallback |
| **Icons** | Font Awesome 5 / 6 |

---

## 💻 Syarat Sistem

- PHP 8.2 atau lebih baru
- Composer 2.x
- MySQL 5.7+ / MariaDB 10.3+
- Ekstensi PHP: `fileinfo`, `gd`, `mbstring`, `pdo_mysql`, `xml`, `curl`, `zip`
- Node.js & NPM (untuk build asset)
- Web Server: Apache / Nginx

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/overloaders/apos.git
cd apos
```

### 2. Install Dependencies

```bash
composer install
npm install && npm run build
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apos
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Migrasi Database & Seed

```bash
php artisan migrate --seed
```

Perintah di atas akan:
- Membuat semua tabel database
- Mengisi data awal (roles, user admin default)

### 5. Storage Link

```bash
php artisan storage:link
```

### 6. Jalankan Aplikasi

```bash
php artisan serve
```

Akses di browser: `http://localhost:8000`

### 🔐 Login Default

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@admin.com | password |

> **Penting:** Segera ganti password setelah login pertama!

### 🐳 Alternatif dengan XAMPP / Laragon

1. Letakkan folder project di `htdocs` (XAMPP) atau `www` (Laragon)
2. Buat database baru bernama `apos` di phpMyAdmin
3. Ikuti langkah 2-5 di atas
4. Akses via `http://localhost/apos/public`

---

## 📖 Cara Penggunaan

### Alur Bisnis Dasar

1. **Setup Awal** → Atur profil perusahaan di Pengaturan → Perusahaan
2. **Data Master** → Input kategori, satuan, brand, supplier, produk
3. **Stok Awal** → Set stok awal produk melalui form produk
4. **Buka Shift** → Kasir buka shift sebelum mulai bertransaksi
5. **Transaksi POS** → Pilih/ketik produk, proses pembayaran, cetak struk
6. **Tutup Shift** → Kasir tutup shift, setor uang
7. **Laporan** → Cek laporan penjualan, laba, stok secara berkala

### Manajemen Stok

- **Kartu Stok** — Lihat riwayat lengkap per SKU (Inventori → Stok → Kartu Stok)
- **Opname** — Lakukan opname berkala untuk mencocokkan stok fisik (Inventori → Opname)
- **Stok Minimum** — Atur `min_stock` di produk, notifikasi muncul di dashboard

### Pembelian

1. Buat **Purchase Order** ke supplier
2. Terima barang via **Receiving** (stok otomatis bertambah)
3. Jika ada barang rusak/salah → **Retur Pembelian**
4. Bayar invoice PO via tombol **Bayar** (tracking hutang)

### Piutang Member

1. Set **limit kredit** di data member
2. Saat transaksi POS, pilih member → transaksi dicatat sebagai piutang
3. Bayar piutang via Merchandise → Piutang Member
4. Cek histori pembayaran per member

### Gift Card

1. Buat gift card via Pengaturan → Gift Card
2. Lakukan top-up saldo
3. Di POS, pelanggan bisa redeem gift card saat pembayaran

---

## 📄 Lisensi

Didistribusikan di bawah **MIT License**. Lihat `LICENSE` untuk informasi lebih lanjut.

---

## ☕ Dukungan

Jika aplikasi ini bermanfaat, Anda dapat mendukung saya melalui:

<a href="https://saweria.co/overloaders" target="_blank">
  <img src="https://img.shields.io/badge/Support%20Me-Saweria-FF6600?style=for-the-badge&logo=ko-fi&logoColor=white" alt="Saweria">
</a>

**https://saweria.co/overloaders**

Setiap dukungan sangat berarti untuk pengembangan fitur-fitur selanjutnya. Terima kasih! 🙏

---

<div align="center">
  Dibuat dengan ❤️ oleh <a href="https://github.com/overloaders">overloaders</a>
</div>
