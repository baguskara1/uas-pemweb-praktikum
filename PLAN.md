# 🏍️ Bengkel Racing Cihuy — POS System

> UAS Praktikum Pemrograman Web — Semester Genap 2025/2026

---

## A. IDENTITAS

| | |
|---|---|
| **Nama Aplikasi** | Bengkel Racing Cihuy |
| **Nama Mahasiswa** | _(isi sendiri)_ |
| **NIM** | _(isi sendiri)_ |
| **Kelas** | _(isi sendiri)_ |
| **Dosen** | _(isi sendiri)_ |

---

## B. DESKRIPSI APLIKASI

Aplikasi POS (Point of Sale) berbasis web untuk bengkel motor spesialis racing. Fitur utama:

- **POS Transaksi** — mencatat jasa servis + sparepart dalam satu transaksi
- **Manajemen Pelanggan & Kendaraan** — data pelanggan dan motor mereka
- **Manajemen Jasa Servis** — jasa + varian + breakdown item (biaya turunan)
- **Manajemen Sparepart** — stok, harga, foto
- **Laporan & Nota** — PDF nota dengan QR Code
- **Integrasi WhatsApp** — kirim nota via Fonnte API

---

## C. TEKNOLOGI

| Stack | Teknologi |
|-------|-----------|
| **Backend** | Native PHP 8+ (tanpa framework) |
| **Database** | MySQL (XAMPP / Laragon) |
| **Frontend** | Tailwind CSS + Alpine.js |
| **PDF** | FPDF via Composer |
| **QR Code** | endroid/qr-code via Composer |
| **WA API** | Fonnte (https://fonnte.com) |
| **Env** | vlucas/phpdotenv via Composer |

### Via CDN (di `layout/header.php`)
| Library | Fungsi |
|---------|--------|
| **Tailwind CSS** | Styling UI (CDN script) |
| **Font Awesome 6** | Ikon sidebar, tombol, badge |
| **SweetAlert2** | Popup sukses/gagal/konfirmasi hapus |
| **Chart.js** | Grafik penjualan di dashboard |
| **Alpine.js** | Interaktivitas (tab, modal, dropdown) tanpa jQuery |

---

## D. STRUKTUR FOLDER

```
uas-pemweb-praktikum/
├── index.php                     # Redirect ke login
├── login.php                     # Halaman login
├── logout.php                    # Proses logout
├── dashboard.php                 # Dashboard utama
│
├── config/
│   ├── database.php              # Koneksi MySQL
│   └── session.php               # Cek session login
│
├── layout/
│   ├── header.php                # Navbar / Topbar + CDN libraries
│   ├── sidebar.php               # Sidebar menu (mobile drawer)
│   └── footer.php                # Footer + script
│
├── functions/
│   ├── pelanggan.php             # Fungsi CRUD pelanggan
│   ├── kendaraan.php             # Fungsi CRUD kendaraan
│   ├── jasa.php                  # Fungsi CRUD jasa & varian
│   ├── sparepart.php             # Fungsi CRUD sparepart
│   ├── transaksi.php             # Fungsi CRUD transaksi
│   ├── whatsapp.php              # Fungsi kirim WA via Fonnte API
│   └── nota.php                  # Fungsi generate PDF nota + QR Code
│
├── pages/
│   ├── pelanggan/
│   │   ├── index.php             # Daftar pelanggan (pagination)
│   │   ├── tambah.php            # Tambah pelanggan
│   │   ├── edit.php              # Edit pelanggan
│   │   └── hapus.php             # Hapus pelanggan
│   ├── kendaraan/
│   │   ├── index.php             # Daftar kendaraan (pagination)
│   │   ├── tambah.php
│   │   ├── edit.php
│   │   └── hapus.php
│   ├── jasa/
│   │   ├── index.php             # Daftar jasa + varian (pagination)
│   │   ├── tambah.php
│   │   ├── edit.php
│   │   └── hapus.php
│   ├── varian/
│   │   ├── index.php             # Daftar varian per jasa (pagination)
│   │   ├── tambah.php
│   │   ├── edit.php
│   │   └── item.php              # Kelola item_varian (breakdown)
│   ├── master_item/
│   │   ├── index.php             # Daftar master item (pagination)
│   │   ├── tambah.php
│   │   └── edit.php
│   ├── sparepart/
│   │   ├── index.php             # Daftar sparepart (pagination + foto)
│   │   ├── tambah.php            # + upload foto
│   │   ├── edit.php              # + upload foto
│   │   └── hapus.php
│   ├── merek/
│   │   ├── index.php             # Daftar merek motor (pagination)
│   │   ├── tambah.php
│   │   ├── edit.php
│   │   └── hapus.php
│   ├── kategori_jasa/
│   │   ├── index.php
│   │   ├── tambah.php
│   │   └── edit.php
│   ├── kategori_sparepart/
│   │   ├── index.php
│   │   ├── tambah.php
│   │   └── edit.php
│   └── transaksi/
│       ├── index.php             # Daftar transaksi (pagination + filter)
│       ├── baru.php              # POS transaksi (mobile responsive)
│       ├── detail.php            # Detail / nota transaksi
│       ├── update_status.php     # Update status transaksi
│       └── cetak_nota.php        # Generate PDF nota
│
├── assets/
│   ├── css/
│   │   └── style.css             # Custom CSS (scrollbar, table, mobile)
│   ├── img/
│   │   └── sparepart/            # Upload foto sparepart
│   └── js/
│       └── app.js
│
├── vendor/                       # Composer dependencies (auto-generated)
├── .env                          # Environment config (tidak di-commit)
├── .gitignore
├── composer.json
├── composer.lock
├── nota/                         # PDF nota tersimpan (gitignored)
└── database/
    └── schema.sql                # Dump database
```

---

## E. DATABASE DESIGN

```sql
-- 1. MEREK MOTOR
CREATE TABLE merek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE
);
-- Honda, Yamaha, Suzuki, Kawasaki

-- 2. KATEGORI JASA
CREATE TABLE kategori_jasa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE
);

-- 3. JASA SERVIS
CREATE TABLE jasa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    nama VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    estimasi_waktu INT DEFAULT 60, -- menit
    FOREIGN KEY (id_kategori) REFERENCES kategori_jasa(id)
);

-- 4. VARIAN JASA
CREATE TABLE varian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_jasa INT NOT NULL,
    nama VARCHAR(200) NOT NULL,
    harga DECIMAL(12,0) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_jasa) REFERENCES jasa(id) ON DELETE CASCADE
);

-- 5. MASTER ITEM (item turunan untuk breakdown biaya)
CREATE TABLE master_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(200) NOT NULL UNIQUE,
    satuan VARCHAR(20) DEFAULT 'pcs'
);

-- 6. ITEM VARIAN (detail breakdown dari varian)
CREATE TABLE item_varian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_varian INT NOT NULL,
    id_master_item INT NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    harga DECIMAL(12,0) NOT NULL DEFAULT 0,
    subtotal DECIMAL(12,0) GENERATED ALWAYS AS (qty * harga) STORED,
    FOREIGN KEY (id_varian) REFERENCES varian(id) ON DELETE CASCADE,
    FOREIGN KEY (id_master_item) REFERENCES master_item(id)
);

-- 7. KATEGORI SPAREPART
CREATE TABLE kategori_sparepart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE
);

-- 8. SPAREPART
CREATE TABLE sparepart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    kode VARCHAR(50) UNIQUE,
    nama VARCHAR(200) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    harga_beli DECIMAL(12,0) NOT NULL DEFAULT 0,
    harga_jual DECIMAL(12,0) NOT NULL DEFAULT 0,
    gambar VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (id_kategori) REFERENCES kategori_sparepart(id)
);

-- 9. PELANGGAN
CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(200) NOT NULL,
    no_telp VARCHAR(20) NOT NULL UNIQUE,
    alamat TEXT
);

-- 10. KENDARAAN
CREATE TABLE kendaraan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    id_merek INT NOT NULL,
    model VARCHAR(100) NOT NULL,
    cc INT DEFAULT 0,
    tahun YEAR,
    plat_no VARCHAR(20) UNIQUE,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON DELETE CASCADE,
    FOREIGN KEY (id_merek) REFERENCES merek(id)
);

-- 11. TRANSAKSI
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    id_kendaraan INT,
    tgl DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('antrian','dikerjakan','selesai','lunas') NOT NULL DEFAULT 'antrian',
    total DECIMAL(12,0) NOT NULL DEFAULT 0,
    catatan TEXT,
    id_user INT NOT NULL,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id),
    FOREIGN KEY (id_kendaraan) REFERENCES kendaraan(id),
    FOREIGN KEY (id_user) REFERENCES users(id)
);

-- 12. DETAIL JASA (items jasa dalam transaksi)
CREATE TABLE detail_jasa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_varian INT NOT NULL,
    total_harga DECIMAL(12,0) NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_varian) REFERENCES varian(id)
);

-- 13. DETAIL ITEM JASA (breakdown biaya dalam jasa yg dipilih)
CREATE TABLE detail_item_jasa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_detail_jasa INT NOT NULL,
    id_master_item INT NOT NULL,
    nama_item VARCHAR(200),
    qty INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(12,0) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_detail_jasa) REFERENCES detail_jasa(id) ON DELETE CASCADE,
    FOREIGN KEY (id_master_item) REFERENCES master_item(id)
);

-- 14. DETAIL SPAREPART
CREATE TABLE detail_sparepart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_sparepart INT NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    harga_jual DECIMAL(12,0) NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_sparepart) REFERENCES sparepart(id)
);

-- 15. USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(200) NOT NULL,
    role ENUM('admin','kasir') NOT NULL DEFAULT 'kasir'
);
```

---

## F. FITUR-FITUR UTAMA

| Fitur | CRUD | Pagination | Search | Keterangan |
|-------|------|------------|--------|------------|
| **Login** | - | - | - | Session-based |
| **Dashboard** | - | - | - | Statistik + Chart.js grafik 7 hari |
| **Pelanggan** | ✅ | ✅ | - | Data pelanggan |
| **Kendaraan** | ✅ | ✅ | - | Per pelanggan |
| **Jasa Servis** | ✅ | ✅ | - | + kategori jasa |
| **Varian Jasa** | ✅ | ✅ | - | + item breakdown |
| **Master Item** | ✅ | ✅ | - | Item turunan biaya |
| **Sparepart** | ✅ | ✅ | ✅ Search + sort stok | + upload foto |
| **Merek Motor** | ✅ | ✅ | - | Referensi kendaraan |
| **Kategori Jasa** | ✅ | - | - | Referensi |
| **Kategori Sparepart** | ✅ | - | - | Referensi |
| **Transaksi (POS)** | ✅ | ✅ | ✅ Filter status | Core POS + WA + PDF |

---

## G. SCREENSHOTS (tambahkan sendiri)
_(Screenshot dashboard, POS, tabel, PDF nota, dll)_

---

## H. PANDUAN INSTALASI

### Prasyarat
- PHP 8.0+
- MySQL / MariaDB
- Composer
- XAMPP / Laragon

### Langkah Instalasi
```bash
# 1. Clone repository
git clone https://github.com/username/uas-pemweb-praktikum.git
cd uas-pemweb-praktikum

# 2. Install dependensi Composer
composer install

# 3. Setup database
# - Buka phpMyAdmin
# - Buat database: db_bengkel_racing
# - Import database/schema.sql

# 4. Konfigurasi .env
cp .env.example .env
# Edit .env:
#   DB_HOST=localhost
#   DB_USER=root
#   DB_PASS=
#   DB_NAME=db_bengkel_racing
#   FONNTE_TOKEN=token_anda

# 5. Jalankan
php -S localhost:8080 -t .

# 6. Login
#   Username: admin
#   Password: admin123
```

### Setup Gambar (jika ada)
```bash
mkdir -p assets/img/sparepart
chmod 755 assets/img/sparepart
```

### Fonnte Setup (Untuk Demo UAS)
1. Daftar di https://fonnte.com
2. Hubungkan nomor WA ke dashboard Fonnte
3. Salin token ke `.env`: `FONNTE_TOKEN=token_anda`
4. Paket **Free** (1000 pesan/bulan, teks only) — cukup untuk demo UAS
5. Kirim WA dengan attachment PDF membutuhkan device connected

## I. KETENTUAN UAS

| Kriteria | Status |
|----------|--------|
| Native PHP (tanpa framework) | ✅ |
| MySQL Database | ✅ |
| Session-based Login | ✅ |
| CRUD minimal 3 modul | ✅ (8 modul) |
| PDF (FPDF) | ✅ (nota + QR Code) |
| Integrasi API Eksternal | ✅ (Fonnte WA) |
| Tailwind CSS (CSS Framework) | ✅ |
| Chart.js (grafik) | ✅ (dashboard) |
| QR Code | ✅ (endroid/qr-code di nota) |
| Upload Gambar | ✅ (foto sparepart) |
| Mobile Responsive | ✅ (sidebar drawer, table scroll) |
| Pagination | ✅ (semua tabel) |
| Composer | ✅ (FPDF, phpdotenv, endroid/qr-code) |
| Database schema.sql | ✅ |
| Video Presentasi (max 10 menit) | ⬜ Belum |
| GitHub | ⬜ Belum |

---

## J. PROGRESS LOG

| Tanggal | Perubahan |
|---------|-----------|
| 13 Jul | Initial app creation, all pages, DB schema |
| 14 Jul | Bug fixes (FPDF, sidebar, Alpine, POS state) |
| 14 Jul | PDF nota format, stock sorting, WA fixes |
| 15 Jul | PDF attachment ke WA, error handling rewrite |
| 15 Jul | Pagination all tables, QR Code, foto sparepart, mobile responsive, cleanup |
