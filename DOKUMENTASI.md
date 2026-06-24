# 📋 DOKUMENTASI SISTEM POS - APOS

**Aplikasi Point of Sale berbasis web untuk manajemen ritel & supermarket.**

---

## 1. Ringkasan Proyek

| Item | Detail |
|------|--------|
| **Nama Aplikasi** | APOS - POS Supermarket |
| **Deskripsi** | Sistem Point of Sale berbasis web untuk mengelola penjualan, inventori, pembelian, member, promosi, dan laporan keuangan ritel/supermarket. |
| **Tujuan** | Memudahkan operasional kasir, stok barang, pembelian, dan pelaporan dalam satu platform terintegrasi. |
| **URL** | `https://apos.asrar.my.id` |
| **Environment** | Production (Wagle Panel - CentOS/CloudLinux) |
| **Timezone** | Asia/Jakarta |
| **PHP CLI** | `/www/server/php/83/bin/php` |

### Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| **Backend Framework** | Laravel 11 (`laravel/framework: ^11.31`) |
| **PHP Version** | PHP 8.3 |
| **Database** | MariaDB 10.11 (MySQL) |
| **Frontend** | Bootstrap 4, jQuery, AdminLTE 3 |
| **UI Components** | Select2, SweetAlert2, Chart.js, Ionicons, Font Awesome |
| **Barcode Scanner** | html5-qrcode |
| **Build Tool** | Vite 6, Laravel Vite Plugin |
| **CSS Framework** | Tailwind CSS 3 (konfigurasi, tidak dominan) |
| **Queue/Cache** | Database driver |

---

## 2. Fitur Utama

### 2.1 Dashboard
- Ringkasan penjualan hari ini (total, jumlah transaksi)
- Jumlah barang stok rendah
- Total member aktif
- Grafik penjualan mingguan (Chart.js)
- 10 transaksi terakhir hari ini
- Grafik pergerakan 7 hari

### 2.2 Master Data
- **Produk** — CRUD, barcode, SKU, gambar, harga jual/member, kategori, brand, unit, minimal stok
- **Kategori** — Pengelompokan produk
- **Supplier** — Data pemasok barang
- **Unit** — Satuan barang (pcs, kg, box, dll)
- **Brand** — Merek barang
- **Warehouse/Gudang** — Lokasi penyimpanan stok

### 2.3 Pembelian (Purchasing)
- **Purchase Order (PO)** — Buat pesanan pembelian, ubah status (draft → ordered → partial/received/cancelled)
- **Receiving/Penerimaan** — Terima barang dari PO, update stok otomatis
- **Retur Pembelian** — Retur barang ke supplier dengan penyesuaian stok

### 2.4 Inventori (Inventory)
- **Stok** — Lihat stok per produk per gudang, stok rendah, adjustment manual
- **Mutasi Stok** — Pindah stok antar gudang
- **Opname Stok** — Buat opname, input stok fisik, hitung selisih, approve → otomatis buat expense & stock movement

### 2.5 POS (Point of Sale)
- **Kasir** — Antarmuka kasir dengan keranjang belanja real-time, barcode scanner, promo, member, cetak struk
- **History Penjualan** — Riwayat transaksi
- **Shift** — Buka/tutup shift, ringkasan penjualan per shift, selisih kas
- **Cash Register** — Kelola mesin kasir per gudang

### 2.6 Merchandise
- **Promosi** — Buat promo: diskon persen, diskon nominal, buy X get Y, bundle, member discount
- **Member** — Kelola member, level (Silver/Gold/Platinum), poin, riwayat transaksi, kartu member

### 2.7 Keuangan (Finance)
- **Biaya/Expenses** — Catat pengeluaran, approval bertingkat (pending → approved/rejected)
- **Kategori Biaya** — Gaji, Listrik, Sewa, dll

### 2.8 Laporan (Reports)
- **Penjualan** — Detail per-item, export CSV, tampilan print
- **Pembelian** — Riwayat PO & receiving, print
- **Stok** — Stok akhir, stok rendah, print
- **Laba Rugi** — Profit kotor/bersih, filter tanggal, print
- **Pergerakan Stok** — Mutasi barang masuk/keluar, print
- **Retur Pembelian** — Riwayat retur, print
- **Penerimaan** — Histori receiving, print

### 2.9 Pengaturan (Settings)
- **Perusahaan** — Nama, logo, alamat, NPWP, pajak, pesan struk
- **User** — CRUD user, pilih role, badge permission

### 2.10 Fitur Lainnya
- **Barcode Scanner** — Scan otomatis via kamera (html5-qrcode) di semua halaman via modal global
- **QR Code** — Generate QR untuk produk & transaksi
- **Cetak Struk** — Preview & cetak via iframe
- **Profile** — Ganti password sendiri
- **Login** — Autentikasi username/email + password

---

## 3. Teknologi & Arsitektur

### Struktur Direktori

```
/www/wwwroot/apos.asrar.my.id/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/           # LoginController
│   │   │   ├── DashboardController.php
│   │   │   ├── Finance/        # ExpenseController, ExpenseCategoryController
│   │   │   ├── Inventory/      # StockController, MutationController, OpnameController
│   │   │   ├── Master/         # ProductController, CategoryController, dll
│   │   │   ├── Merchandise/    # MemberController, PromotionController
│   │   │   ├── Pos/            # CashierController, ShiftController, dll
│   │   │   ├── ProfileController.php
│   │   │   ├── Purchasing/     # PurchaseOrderController, ReceivingController, PurchaseReturnController
│   │   │   ├── Report/         # ReportController
│   │   │   └── Setting/        # CompanyController, UserController
│   │   ├── Middleware/
│   │   │   └── CheckPermission.php
│   │   └── ...
│   ├── Models/
│   │   ├── Brand.php
│   │   ├── CashRegister.php
│   │   ├── Category.php
│   │   ├── CompanySetting.php
│   │   ├── Expense.php
│   │   ├── ExpenseCategory.php
│   │   ├── Member.php
│   │   ├── Payment.php
│   │   ├── Product.php
│   │   ├── ProductPrice.php
│   │   ├── Promotion.php
│   │   ├── PromotionProduct.php
│   │   ├── PurchaseOrder.php
│   │   ├── PurchaseOrderItem.php
│   │   ├── PurchaseReceiving.php
│   │   ├── PurchaseReceivingItem.php
│   │   ├── PurchaseReturn.php
│   │   ├── PurchaseReturnItem.php
│   │   ├── Role.php
│   │   ├── Sale.php
│   │   ├── SaleItem.php
│   │   ├── Shift.php
│   │   ├── Stock.php
│   │   ├── StockMovement.php
│   │   ├── StockOpname.php
│   │   ├── StockOpnameItem.php
│   │   ├── Supplier.php
│   │   ├── Unit.php
│   │   ├── User.php
│   │   └── Warehouse.php
│   └── Providers/
│       └── AppServiceProvider.php
├── config/                     # Konfigurasi Laravel
├── database/
│   ├── migrations/             # Semua migrasi database
│   └── seeders/
│       └── DatabaseSeeder.php  # Seeder awal (admin, roles, dll)
├── resources/
│   ├── views/
│   │   ├── auth/               # Login
│   │   ├── finance/            # Expenses
│   │   ├── inventory/          # Stok, Mutasi, Opname
│   │   ├── layouts/            # AdminLTE layout
│   │   ├── master/             # Produk, Kategori, Supplier, dll
│   │   ├── members/            # Member detail & card
│   │   ├── pos/                # Cashier, History, Shift
│   │   ├── promotions/         # Promo management
│   │   ├── purchasing/         # PO, Receiving, Return
│   │   ├── reports/            # Semua laporan + print
│   │   └── settings/           # Company, User
│   └── js/                     # JavaScript / jQuery
├── routes/
│   └── web.php                 # Semua route web
├── public/                     # Assets publik
├── storage/                    # Log, cache, upload
├── vendor/                     # Composer dependencies
├── composer.json
├── package.json
└── vite.config.js
```

---

## 4. Struktur Database

### 4.1 Users & Roles

**`roles`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| name | varchar | Nama role |
| slug | varchar | Slug unik |
| permissions | json/jsonb | Array permission, `["*"]` untuk admin |
| timestamps | | created_at, updated_at |

**`users`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| name | varchar | Nama lengkap |
| username | varchar | Username login |
| email | varchar | Email |
| password | varchar | Hash bcrypt |
| role_id | bigint (FK) | → roles.id |
| phone | varchar | |
| address | text | |
| is_active | boolean | |
| timestamps | | |

Relasi: `User` → belongsTo → `Role` → hasMany → `User`

### 4.2 Produk & Master

**`categories`** — id, name, slug, description, is_active, timestamps

**`units`** — id, name, slug, description, is_active, timestamps

**`brands`** — id, name, slug, description, is_active, timestamps

**`suppliers`** — id, name, code, phone, email, address, city, fax npwp, is_active, timestamps

**`warehouses`** — id, name, code, address, is_main, timestamps

**`products`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| code | varchar | Kode produk |
| barcode | varchar | Barcode/SKU |
| name | varchar | Nama produk |
| slug | varchar | Slug URL |
| description | text | |
| category_id | bigint (FK) | → categories.id |
| brand_id | bigint (FK) | → brands.id |
| unit_id | bigint (FK) | → units.id |
| cost_price | decimal | Harga modal |
| selling_price | decimal | Harga jual |
| member_price | decimal | Harga khusus member |
| min_stock | integer | Minimal stok |
| max_stock | integer | Maksimal stok |
| image | varchar | Path gambar |
| tax_group | varchar | Grup pajak |
| has_serial | boolean | Serial number |
| is_weighing | boolean | Produk timbangan |
| is_active | boolean | |
| timestamps | | + softDeletes |

**`product_prices`** — id, product_id (FK), warehouse_id (FK), price, effective_date, timestamps

### 4.3 Stok & Inventori

**`stocks`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| product_id | bigint (FK) | → products.id |
| warehouse_id | bigint (FK) | → warehouses.id |
| quantity | float | Jumlah stok |
| reserved | float | Stok dipesan |
| average_cost | decimal | Harga rata-rata |
| timestamps | | |

**`stock_movements`** — id, product_id (FK), warehouse_from_id (FK), warehouse_to_id (FK), user_id (FK), type (in/out/transfer/adjustment/opname/return), quantity, reference_type, reference_id, notes, timestamps

**`stock_opnames`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| code | varchar | OP-YYYYMMDD-NNNN |
| warehouse_id | bigint (FK) | → warehouses.id |
| opname_date | date | |
| status | varchar | draft/approved/rejected |
| notes | text | |
| user_id | bigint (FK) | Pembuat |
| approved_by | bigint (FK) | → users.id (approver) |
| timestamps | | |

**`stock_opname_items`** — id, stock_opname_id (FK), product_id (FK), system_qty (float), physical_qty (float), difference (float), cost_price (decimal), unit_cost (decimal), notes, timestamps

### 4.4 Pembelian

**`purchase_orders`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| code | varchar | Kode PO |
| supplier_id | bigint (FK) | → suppliers.id |
| warehouse_id | bigint (FK) | → warehouses.id |
| user_id | bigint (FK) | → users.id |
| order_date | date | |
| expected_date | date | |
| subtotal | decimal | |
| discount_amount | decimal | |
| tax_amount | decimal | |
| shipping_cost | decimal | |
| total | decimal | |
| status | varchar | draft/ordered/partial/received/cancelled |
| notes | text | |
| timestamps | | + softDeletes |

**`purchase_order_items`** — id, purchase_order_id (FK), product_id (FK), quantity (float), received_quantity (float), unit_price (decimal), subtotal (decimal), timestamps

**`purchase_receivings`** — id, code, purchase_order_id (FK), warehouse_id (FK), user_id (FK), receiving_date, status, notes, timestamps

**`purchase_receiving_items`** — id, purchase_receiving_id (FK), purchase_order_item_id (FK), product_id (FK), quantity (float), unit_price (decimal), subtotal (decimal), timestamps

**`purchase_returns`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| code | varchar | RET-YYYYMMDD-NNN |
| purchase_order_id | bigint (FK) | → purchase_orders.id |
| supplier_id | bigint (FK) | → suppliers.id |
| warehouse_id | bigint (FK) | → warehouses.id |
| user_id | bigint (FK) | → users.id |
| return_date | date | |
| status | varchar | draft/completed |
| notes | text | |
| timestamps | | |

**`purchase_return_items`** — id, purchase_return_id (FK), purchase_receiving_item_id (FK), product_id (FK), quantity, unit_price, subtotal, reason, timestamps

### 4.5 POS & Penjualan

**`cash_registers`** — id, code, name, warehouse_id (FK), description, is_active, timestamps

**`shifts`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| code | varchar | SHIFT-YYYYMMDD-NNN |
| cash_register_id | bigint (FK) | → cash_registers.id |
| user_id | bigint (FK) | → users.id |
| opening_cash | decimal | Kas awal |
| closing_cash | decimal | Kas akhir (sistem) |
| actual_cash | decimal | Kas riil |
| difference | decimal | Selisih |
| status | varchar | open/closed |
| opened_at | datetime | |
| closed_at | datetime | |
| notes | text | |
| timestamps | | |

**`sales`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| code | varchar | TRX-YYYYMMDD-NNN |
| cash_register_id | bigint (FK) | |
| shift_id | bigint (FK) | → shifts.id |
| member_id | bigint (FK) | → members.id (nullable) |
| user_id | bigint (FK) | → users.id |
| sale_date | date | |
| subtotal | decimal | |
| discount_amount | decimal | Total diskon (promo+member) |
| member_discount | decimal | Diskon level member |
| tax_amount | decimal | PPN 11% |
| total | decimal | Grand total |
| amount_paid | decimal | Bayar |
| change_amount | decimal | Kembalian |
| payment_method | varchar | cash/card/transfer/ewallet/mixed |
| status | varchar | completed/cancelled/refunded |
| notes | text | |
| points_earned | integer | Poin diperoleh |
| points_redeemed | integer | Poin ditukar |
| points_discount | decimal | Diskon poin |
| timestamps | | + softDeletes |

**`sale_items`** — id, sale_id (FK), product_id (FK), promotion_id (FK), quantity (float), unit_price (decimal), discount_amount (decimal), subtotal (decimal), timestamps

**`payments`** — id, referenceable_id, referenceable_type, payment_method, amount, reference_number, notes, timestamps (polymorphic)

### 4.6 Member & Promosi

**`members`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| code | varchar | Kode member |
| name | varchar | Nama |
| phone | varchar | |
| email | varchar | |
| address | text | |
| gender | varchar | |
| birth_date | date | |
| membership_level | varchar | bronze/silver/gold/platinum |
| points | float | Poin |
| total_spent | decimal | Total belanja |
| is_active | boolean | |
| timestamps | | |

**`promotions`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| name | varchar | Nama promo |
| code | varchar | Kode promo |
| type | varchar | discount_percent / discount_amount / buy_x_get_y / bundle / member_discount |
| value | decimal | Nilai diskon |
| min_purchase | decimal | Minimal belanja |
| buy_qty | float | Beli berapa |
| get_qty | float | Dapat berapa |
| start_date | date | |
| end_date | date | |
| is_active | boolean | |
| notes | text | |
| timestamps | | |

**`promotion_products`** — id, promotion_id (FK), product_id (FK), timestamps

### 4.7 Keuangan

**`expense_categories`** — id, name, description, is_active, timestamps

**`expenses`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | |
| code | varchar | |
| expense_category_id | bigint (FK) | → expense_categories.id |
| amount | decimal | |
| expense_date | date | |
| description | text | |
| receipt_number | varchar | No. bukti |
| status | varchar | pending/approved/rejected |
| user_id | bigint (FK) | Pembuat |
| approved_by | bigint (FK) | Approver |
| timestamps | | |

### 4.8 Pengaturan

**`company_settings`** — id, company_name, phone, email, website, address, city, province, postal_code, npwp, fax, tax_rate, receipt_message, receipt_header, receipt_footer, logo, timestamps

### Relasi Utama

```
User → Role (belongsTo)
Product → Category, Brand, Unit (belongsTo)
Product → Stock (hasMany per warehouse)
Product → SaleItem (hasMany)
Product → PurchaseOrderItem (hasMany)
Product → PromotionProduct (hasMany)
Sale → SaleItem (hasMany)
Sale → Shift (belongsTo)
Sale → Member (belongsTo)
Shift → CashRegister (belongsTo)
Shift → User (belongsTo)
Shift → Sale (hasMany)
PurchaseOrder → PurchaseOrderItem (hasMany)
PurchaseOrder → PurchaseReceiving (hasMany)
PurchaseOrder → Supplier (belongsTo)
Stock → Product, Warehouse (belongsTo)
StockOpname → StockOpnameItem (hasMany)
Expense → ExpenseCategory (belongsTo)
Promotion → PromotionProduct → Product (belongsToMany)
```

---

## 5. Role & Hak Akses

Sistem menggunakan **Role-Based Access Control (RBAC)** dengan permission disimpan sebagai JSON array di kolom `roles.permissions`.

### Mekanisme
1. Middleware `CheckPermission` membaca `role->permissions` dari user login
2. Jika role memiliki `"*"` (wildcard) → akses penuh (Admin)
3. Gate didefinisikan di `AppServiceProvider` untuk penggunaan di Blade (`@can`)
4. Sidebar menggunakan `@can` untuk menampilkan menu sesuai permission

### Daftar Permission

| Permission | Deskripsi |
|------------|-----------|
| `*` | Super admin — semua akses |
| `master.manage` | Kelola produk, kategori, supplier, unit, brand, gudang |
| `purchasing.manage` | Kelola purchase order |
| `receiving.manage` | Penerimaan barang |
| `suppliers.manage` | Kelola supplier |
| `stock.manage` | Kelola stok, mutasi, opname |
| `stock.approve` | Approve opname stok |
| `pos.access` | Akses POS (kasir) |
| `sales.view` | Lihat riwayat penjualan |
| `promotions.manage` | Kelola promosi |
| `members.view` | Lihat member |
| `members.create` | Buat member baru |
| `expenses.manage` | Kelola biaya |
| `expenses.approve` | Approve biaya |
| `reports.view` | Akses laporan |
| `reports.export` | Export laporan (CSV) |
| `settings.manage` | Kelola pengaturan & user |

### Role Default (Seeder)

| Role | Slug | Permissions |
|------|------|-------------|
| **Administrator** | `admin` | `["*"]` — full akses |
| **Manager** | `manager` | `reports.view`, `reports.export`, `settings.manage`, `expenses.approve`, `stock.approve` |
| **Kasir** | `kasir` | `pos.access`, `sales.view`, `members.view`, `members.create` |
| **Gudang** | `gudang` | `stock.manage`, `purchasing.manage`, `receiving.manage` |
| **Purchasing** | `purchasing` | `purchasing.manage`, `suppliers.manage` |

### Catatan
- Role **Manager** mendapat permission minimal (approval) karena akses ke menu dijaga oleh middleware `permission:X` di route
- Role **Kasir** hanya bisa akses POS, history penjualan, dan cari/tambah member
- Role **Gudang** bisa kelola stok dan purchasing
- Sidebar menampilkan menu berdasarkan Gate yang cocok

---

## 6. Modul POS (Detail)

### 6.1 Tampilan Kasir
- Layout dua kolom: produk (kiri/grid) dan keranjang (kanan)
- Grid produk menampilkan: gambar, nama, harga, stok
- Pencarian produk real-time via AJAX
- Scan barcode otomatis (modal global html5-qrcode)
- Input quantity, lihat subtotal per item
- Tombol hapus item dari keranjang

### 6.2 Sistem Shift
- Shift harus **dibuka** sebelum bertransaksi
- Shift dibuka dengan mengisi `opening_cash` (kas awal)
- Format kode: `SHIFT-YYYYMMDD-NNN`
- Kasir hanya bisa melihat & menutup shift miliknya sendiri
- Saat tutup shift:
  - Sistem hitung `expected_cash` = opening_cash + total penjualan
  - Kasir input `actual_cash` (kas riil)
  - Sistem hitung `difference` = actual_cash - expected_cash
  - Ringkasan: total penjualan, jumlah transaksi, selisih

### 6.3 Member Benefits
- **Harga Member** — Jika produk memiliki `member_price`, harga khusus untuk member
- **Diskon Level**:
  - Silver: 2%
  - Gold: 5%
  - Platinum: 10%
- **Poin**:
  - 1 poin per Rp1.000 belanja (pembulatan ke bawah)
  - 100 poin = Rp100 (1 poin = Rp1)
  - Bisa ditukar di kasir saat transaksi
  - Otomatis ditambahkan/dikurangi
- **Kartu Member** — Halaman detail dengan QR code, level badge, poin

### 6.4 Cetak Struk
- Preview struk di area cetak (hidden iframe)
- Isi: nama toko, alamat, tanggal, item, total, bayar, kembalian, poin
- Pesan footer dari pengaturan perusahaan
- Tombol print

### 6.5 Integrasi Promo
- Promo aktif otomatis diterapkan saat transaksi
- Tipe promo:
  - `discount_percent` — Diskon % untuk produk tertentu
  - `discount_amount` — Diskon nominal tetap
  - `buy_x_get_y` — Beli X dapat Y gratis
  - `bundle` — Paket harga khusus
  - `member_discount` — Diskon khusus member
- Produk dengan promo ditandai di grid

### 6.6 Proses Transaksi
1. Pilih produk (scan barcode / grid / search)
2. Pilih member (opsional) → diskon + poin otomatis
3. Pilih metode bayar (cash/card/transfer/ewallet/mixed)
4. Input jumlah bayar
5. Klik "Bayar" → AJAX ke `CashierController@processSale`
6. Sistem:
   - Buat `Sale` + `SaleItem`
   - Kurangi stok (dari semua warehouse)
   - Tambah/poin member
   - Tampilkan struk
7. Cetak struk

---

## 7. Perbaikan & Riwayat Perubahan

| Tanggal | Perbaikan | Detail |
|---------|-----------|--------|
| 21/06/2026 | **POS Promotions** | Fix BadMethodCallException pada relasi promotions, tambah fitur edit/update promo, integrasi Select2 untuk pemilihan produk |
| 21/06/2026 | **POS Shifts** | Fix ShiftCashMovement relation, tambah summaries (total sales, transaction count), migration tambah kolom code & description |
| 21/06/2026 | **Cashier Controller** | Rewrite SaleDetail → SaleItem, fix validasi items, perbaiki stock deduction logic (query stocks dengan quantity > 0) |
| 21/06/2026 | **Member Benefits** | Implementasi member_price, poin otomatis, diskon level, reedem poin di kasir, halaman detail member + kartu member |
| 21/06/2026 | **Mobile Responsive** | Cart collapse di mobile, floating button tambah produk, responsive grid produk |
| 21/06/2026 | **Reports** | Laporan penjualan per-item, export CSV, tampilan print semua laporan, Chart.js untuk grafik |
| 21/06/2026 | **Stock Opname** | Tambah kolom cost_price & unit_cost di stock_opname_items, approve otomatis buat Expense + StockMovement |
| 21/06/2026 | **Purchase Returns** | Modul retur pembelian dengan penyesuaian stok otomatis (stock movement tipe 'return') |
| 21/06/2026 | **Roles & Permissions** | Middleware CheckPermission, Gate definitions di AppServiceProvider, sidebar conditional dengan @can |
| 21/06/2026 | **Barcode Scanner** | Integrasi html5-qrcode, modal global scanner, bisa digunakan di semua halaman |
| 21/06/2026 | **Company Logo** | Tampilkan logo di sidebar, login page, receipt struk, laporan print |
| 22/06/2026 | **Member Delete Protection** | Cek relasi sales & points sebelum mengizinkan delete member |
| 22/06/2026 | **User Management** | Dynamic roles dropdown, permission badges di halaman user, soft delete user |
| 23/06/2026 | **Shift Filter** | Kasir hanya bisa lihat & tutup shift miliknya sendiri (filter by user_id) |
| 23/06/2026 | **POS Member Card** | Redesain tampilan member card lebih besar, tambah QR code, level badge, poin, total belanja |

---

## 8. Cara Akses

### URL Aplikasi
```
https://apos.asrar.my.id
```

### Default Credentials (dari DatabaseSeeder)

| Role | Email/Username | Password |
|------|----------------|----------|
| **Administrator** | `admin@pos.test` / `admin` | `password` |
| **Kasir** | `kasir@pos.test` / `kasir` | `password` |

> **Catatan:** Password default bisa berubah jika sudah diubah oleh admin. Gunakan fitur lupa password atau hubungi administrator.

### Default Data Seeder
- 2 Gudang: Gudang Utama (GUD-001), Gudang Cabang (GUD-002)
- 2 Cash Register: Kasir 1 (CR-001), Kasir 2 (CR-002)
- 7 Kategori Biaya: Gaji, Listrik, Sewa, Perlengkapan, Transport, Maintenance, Lain-lain
- Setting awal: Nama toko "POS Supermarket", pajak 11%

### Perintah Artisan
```bash
# Gunakan PHP 8.3 khusus
/www/server/php/83/bin/php artisan <command>

# Contoh:
/www/server/php/83/bin/php artisan migrate
/www/server/php/83/bin/php artisan db:seed
/www/server/php/83/bin/php artisan cache:clear
/www/server/php/83/bin/php artisan config:clear
/www/server/php/83/bin/php artisan view:clear
/www/server/php/83/bin/php artisan queue:work
```

---

## 9. Catatan Penting

### 9.1 Keterbatasan Server
- **php_fileinfo TIDAK AKTIF** — Ekstensi FileInfo tidak terinstall. Akibatnya:
  - Upload gambar tidak bisa menggunakan validasi `image` (mime type detection)
  - Gunakan validasi ekstensi file saja (`mimes:jpg,png`)
  - Tidak bisa menggunakan fungsi `finfo_*`

### 9.2 Format Kode Otomatis

| Entitas | Format | Contoh |
|---------|--------|--------|
| Shift | `SHIFT-YYYYMMDD-NNN` | SHIFT-20260621-001 |
| Transaksi | `TRX-YYYYMMDD-NNN` | TRX-20260621-042 |
| Opname Stok | `OP-YYYYMMDD-NNNN` | OP-20260621-0001 |
| Retur Pembelian | `RET-YYYYMMDD-NNN` | RET-20260621-001 |
| Purchase Order | `PO-YYYYMMDD-NNN` | PO-20260621-001 (generated by system) |
| Receiving | `RCV-YYYYMMDD-NNN` | RCV-20260621-001 (generated by system) |

### 9.3 Aturan Bisnis Penting
- Stok dikurangi saat transaksi **completed** (bukan draft/pending)
- Opname yang sudah di-approve tidak bisa diubah
- Member hanya bisa dihapus jika tidak memiliki transaksi dan poin = 0
- Kasir hanya bisa melihat shift miliknya sendiri
- Pajak (PPN) = 11% dari final amount
- Poin member: 1 poin per Rp1.000 belanja, 1 poin = Rp1
- Diskon member level: Bronze=0%, Silver=2%, Gold=5%, Platinum=10%

### 9.4 Keamanan
- Password di-hash menggunakan bcrypt (cost 12)
- Semua route kecuali login menggunakan middleware `auth`
- Route master, purchasing, inventory, pos, finance, reports, settings menggunakan middleware `permission:X`
- Gate didefinisikan di `AppServiceProvider` untuk proteksi Blade
- Role dengan wildcard `"*"` memiliki akses tak terbatas

### 9.5 Queue & Cache
- Queue connection: `database` (menggunakan tabel jobs)
- Cache: `database`
- Session: `database`
- Tidak menggunakan Redis secara aktif (hanya terkonfigurasi)

### 9.6 Dependency Frontend (CDN)
Aplikasi menggunakan CDN untuk library frontend, tidak di-bundle via Vite:
- Bootstrap 4.6
- jQuery 3.7
- AdminLTE 3.2
- Font Awesome 5 / Ionicons
- Select2 4.1
- SweetAlert2 11
- Chart.js 4.4
- html5-qrcode 2.3

Hanya asset kustom (CSS/JS sendiri) yang di-build via Vite dengan Tailwind CSS.

---

*Dokumentasi ini diperbarui pada 23 Juni 2026.*
