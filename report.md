# Laporan UAS Praktikum Pemrograman Web

## POS Bengkel Racing Cihuy

**Mahasiswa:** Rio Ardiyansyah  
**NIM:** 241110075  
**Repository:** https://github.com/baguskara1/uas-pemweb-praktikum  
**Google Drive (Screenshot & Video):** https://drive.google.com/drive/folders/1HfNmJ8nKIyGhCtzyaTa4rFpT1Z0X4vZH?usp=sharing

---

## Ringkasan Eksekutif

Dokumen ini menyajikan implementasi lengkap sistem Point of Sale (POS) untuk bengkel motor "Bengkel Racing Cihuy" yang dibangun sebagai proyek akhir (UAS) mata kuliah Pemrograman Web Praktikum. Sistem ini dibangun menggunakan native PHP dengan Tailwind CSS dan MySQL, mengikuti ketentuan tanpa menggunakan framework PHP.

**Capaian utama:** Sistem POS berfungsi penuh dengan manajemen transaksi, pelacakan inventaris, data pelanggan/kendaraan, katalog jasa/sparepart, laporan dengan ekspor Excel, dan antarmuka bergelap bertema racing.

---

## Gambaran Umum Proyek

### Identitas Proyek

| Atribut | Detail |
|---------|--------|
| Nama Proyek | POS Bengkel Motor Racing (Bengkel Racing Cihuy) |
| Mata Kuliah | Pemrograman Web Praktikum — UAS |
| Mahasiswa | Rio Ardiyansyah |
| NIM | 241110075 |
| Repository | https://github.com/baguskara1/uas-pemweb-praktikum |
| Server | PHP 8.5.7 built-in server (php -S localhost:8080) |

### Ketentuan & Kebutuhan Teknis

- Native PHP saja — tanpa Laravel, CodeIgniter, atau framework lain
- Tailwind CSS via CDN untuk styling
- Database MySQL dengan 17 tabel
- Alpine.js untuk interaktivitas (diperbolehkan karena library ringan, bukan framework)
- Library Composer: FPDF, phpdotenv, endroid/qr-code, PhpSpreadsheet
- Tema gelap racing dengan palet warna merah-emas-oranye
- Responsif mobile: sidebar drawer, tabel scroll horizontal

---

## Arsitektur Teknis

### Struktur Direktori

```
uas-pemweb-praktikum/
├── assets/
│   ├── css/style.css          # Custom styles + mobile fixes
│   ├── img/logo.png           # Transparent PNG logo (2048×2048)
│   └── js/                    # (Alpine via CDN)
├── config/
│   ├── database.php           # MySQLi connection + session_start
│   └── session.php            # Auth guard (cek_login)
├── database/
│   └── schema.sql             # 17 tables, FK constraints, seed data
├── functions/
│   ├── transaksi.php          # simpan_transaksi() + detail logic
│   ├── helper.php             # format_rupiah, catatLogStok()
│   └── wa.php                 # Fonnte WA integration (text only)
├── layout/
│   ├── header.php             # HTML head, Tailwind CDN, viewport fix
│   ├── sidebar.php            # Navigation + hamburger toggle
│   └── footer.php             # Scripts + footer
├── pages/
│   ├── dashboard.php          # Chart.js 7-day + filter
│   ├── login.php              # Auth (admin/admin123)
│   ├── transaksi/
│   │   ├── baru.php           # POS core (Jasa + Sparepart tabs)
│   │   ├── index.php          # List + pagination (20/halaman)
│   │   ├── detail.php         # Nota view + cetak PDF
│   │   └── hapus.php          # Delete transaction
│   ├── pelanggan/             # CRUD + search + pagination
│   ├── kendaraan/             # CRUD + search + pagination
│   ├── jasa/                  # CRUD + varian + kategori
│   ├── kategori_jasa/
│   ├── varian/                # CRUD + item_varian breakdown
│   ├── master_item/           # Foto upload (JPG/PNG/WebP, 2MB)
│   ├── sparepart/             # CRUD + stock sort asc/desc
│   ├── kategori_sparepart/
│   ├── merek/
│   ├── laporan/
│   │   ├── index.php          # Daily report per month + Chart.js
│   │   └── export_xlsx.php    # PhpSpreadsheet export
│   └── pengaturan/
│       └── index.php          # Settings form + backup/restore DB
├── vendor/                    # Composer dependencies (gitignored)
├── nota/                      # Generated PDF invoices (gitignored)
├── .env                       # DB creds, Fonnte token, Google Form link
├── composer.json              # Dependencies
└── PLAN.md                    # Project plan matrix
```

### Desain Database (17 Tabel)

| Tabel | Fungsi | Relasi Kunci |
|-------|--------|--------------|
| users | Admin login | id (PK) |
| pelanggan | Data pelanggan | id (PK) |
| kendaraan | Kendaraan pelanggan | id_pelanggan → pelanggan.id (ON DELETE CASCADE) |
| merek | Merek motor | id (PK) |
| kategori_jasa | Kategori jasa servis | id (PK) |
| jasa | Daftar jasa servis | id_kategori → kategori_jasa.id |
| varian_jasa | Varian jasa + breakdown | id_jasa → jasa.id |
| item_varian | Item default per varian | id_varian → varian_jasa.id, id_master_item → master_item.id |
| master_item | Inventaris part (dengan foto) | id (PK) |
| kategori_sparepart | Kategori sparepart | id (PK) |
| sparepart | Stok & harga sparepart | id_kategori → kategori_sparepart.id |
| transaksi | Header transaksi utama | id_pelanggan → pelanggan.id, id_kendaraan → kendaraan.id, id_user → users.id |
| detail_jasa | Item jasa per transaksi | id_transaksi → transaksi.id (ON DELETE CASCADE), id_varian → varian_jasa.id |
| detail_item_jasa | Breakdown item per jasa | id_detail_jasa → detail_jasa.id (ON DELETE CASCADE) |
| detail_sparepart | Item sparepart per transaksi | id_transaksi → transaksi.id (ON DELETE CASCADE), id_sparepart → sparepart.id |
| log_stok | Audit trail stok | id_master_item → master_item.id, id_sparepart → sparepart.id |
| pengaturan | Pengaturan sistem (key-value) | key (PK) |

### Strategi Foreign Key

Keputusan FK penting saat pengembangan:

- `kendaraan.id_pelanggan` → `pelanggan.id` **ON DELETE CASCADE** (hapus pelanggan = hapus kendaraannya)
- `transaksi.id_pelanggan` → `pelanggan.id` **ON DELETE SET NULL** (riwayat transaksi tetap aman)
- `transaksi.id_kendaraan` → `kendaraan.id` **ON DELETE SET NULL** (riwayat transaksi tetap aman)
- `detail_jasa/sparepart.id_transaksi` → `transaksi.id` **ON DELETE CASCADE** (hapus transaksi = hapus itemnya)
- `detail_item_jasa.id_detail_jasa` → `detail_jasa.id` **ON DELETE CASCADE**

---

## Fitur Utama yang Diimplementasikan

### 1. Autentikasi & Sesi
- Satu user admin (username: admin, password: admin123)
- Auth berbasis session dengan guard `cek_login()` di setiap halaman
- Logout via `/logout.php` (path absolut biar work di semua halaman)

### 2. Dashboard (dashboard.php)
- Chart.js line chart — tren pendapatan 7 hari
- Filter: Hari Ini / Bulan Ini / Bulan Lalu (GET params, tanpa reload)
- Kartu statistik: Total Transaksi, Total Pendapatan, Total Pelanggan, Stok Rendah

### 3. POS Transaksi Baru (transaksi/baru.php) — Fitur Inti
- Pilih Pelanggan + Kendaraan (dropdown bergantung via AJAX)
- Tabbed interface: Jasa & Sparepart
- Tab Jasa: cari nama, filter kategori (kategori_jasa)
- Tab Sparepart: cari + urutkan stok naik/turun
- Panel keranjang (kanan): breakdown item, edit qty/harga, item custom
- Mobile: stack vertikal (max-h-[50vh] mobile, tanpa tinggi tetap desktop)
- Simpan → `simpan_transaksi()` generate ID, cascade ke detail tables, log stok, kosongkan keranjang

### 4. Daftar & Detail Transaksi
- Pagination 20 item/halaman, cari pelanggan/kendaraan
- View detail: breakdown lengkap (jasa + sparepart + item + sparepart)
- Alur status: Antrian → Dikerjakan → Selesai → Lunas

### 5. Nota PDF dengan QR Code (FPDF + endroid/qr-code v6)
- Otomatis tergenerate saat simpan, disimpan ke `/nota/` untuk arsip
- QR code meng-encode link Google Form + ID transaksi untuk registrasi garansi
- Integrasi WhatsApp via Fonnte (hanya teks karena batasan free plan)

### 6. Manajemen Data Master (CRUD)
- Pelanggan, Kendaraan, Jasa, Varian, Kategori, Merek, Master Item, Sparepart
- Semua daftar: pencarian + pagination (20/halaman) + tabel responsif scroll
- Master Item: upload foto (JPG/PNG/WebP, max 2MB, thumb 40×40 di tabel)
- Varian Jasa: breakdown item (item_varian) dengan qty/harga default
- Sparepart: toggle sort stok (klik header "Stok")

### 6. Riwayat Stok (log_stok)
- Otomatis tercatat setiap transaksi via `catatLogStok()`
- Form penyesuaian manual (Masuk/Keluar + keterangan)
- Audit trail lengkap: user, timestamp, qty sebelum/sesudah

### 7. Laporan Penghasilan (laporan/index.php)
- Rincian harian per bulan yang dipilih
- Chart.js batang: pendapatan harian
- Ekspor XLSX (PhpSpreadsheet) dengan format rupiah & total

### 8. Pengaturan & Backup/Restore (pengaturan/index.php)
- Pengaturan disimpan di tabel DB `pengaturan` (key-value)
- Backup: generate dump SQL penuh via mysqldump (fallback PHP)
- Restore: eksekusi file .sql yang di-upload

---

## Keputusan Desain UI/UX

### Evolusi Palet Warna

| Versi | Primary | Secondary | Accent/Warning | Alasan |
|-------|---------|-----------|----------------|--------|
| Awal | #e60000 | #ffd700 | #ff6600 | Merah racing murni |
| Opsi 1 | #ccff00 | #000000 | #ff3366 | Eksperimen hijau limau |
| Opsi 3 | #ccff00 | #ff00ff | #ffd700 | Campuran limau-magenta-emas |
| Final (Opsi G) | #e60000 | #ffd700 | #ff6600 | Merah racing + emas — konsisten brand |

### Perbaikan Responsif
- Sidebar: drawer off-canvas dengan hamburger toggle (Alpine.js x-data)
- Tabel: wrapper `.table-responsive` dengan `overflow-x-auto`
- Tab POS: `flex-1 min-h-0 overflow-y-auto` (dihapus batas `max-h-96` mobile)
- Viewport: `<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">`
- `html { overflow-x: hidden }` mencegah scroll horizontal di mobile

---

## Integrasi Layanan Eksternal

### Notifikasi WhatsApp (Fonnte)
- Token disimpan di `.env` (FONNTE_TOKEN)
- Batasan free plan: hanya kirim teks, tidak bisa kirim file
- Implementasi: kirim ringkasan transaksi + link garansi
- PDF tetap tergenerate & diarsipkan lokal walau WA gagal

### Registrasi Garansi (Google Forms)
- URL pre-filled: `LINK_GARANSI` + `entry.2014103664=TRX-XXXX`
- QR code di PDF meng-encode URL ini

---

## Keterbatasan & Masalah Terkenal

- Fonnte device terkadang disconnected → WA gagal diam (di-flash)
- Tidak ada lampiran file di WA (batasan free plan)
- Tidak ada otomatisasi test — verifikasi manual
- Satu user admin (belum ada role-based access)
- Pengurangan stok saat simpan transaksi, bukan saat masuk keranjang

---

## Checklist Deployment

- `composer install` (folder vendor/)
- Import `database/schema.sql` → buat `db_bengkel_racing`
- Konfigurasi `.env` dengan kredensial DB, token Fonnte, link Google Forms
- Jalankan: `php -S localhost:8080 -t .`
- Login: admin / admin123
- Produksi: Apache/Nginx + PHP-FPM, HTTPS, amanin `.env`

---

## Penutup

Sistem POS Bengkel Racing Cihuy menunjukkan solusi manajemen bengkel lengkap yang siap pakai, dibangun sepenuhnya dengan native PHP. Semua kebutuhan UAS terpenuhi: pemrosesan transaksi dengan breakdown jasa/sparepart, pelacakan inventaris dengan log audit, pelaporan dengan grafik visual dan ekspor Excel, pembuatan nota PDF dengan QR code, integrasi WhatsApp, serta antarmuka bergelap bertema racing yang nyaman di desktop maupun mobile.

**Repository:** https://github.com/baguskara1/uas-pemweb-praktikum — berisi source code lengkap, schema.sql, PLAN.md, dan dump database.

---

## Referensi & Teknologi

- PHP 8.5 Documentation — php.net
- Tailwind CSS 3.x — tailwindcss.com
- Alpine.js v3 — alpinejs.dev
- Chart.js 4.x — chartjs.org
- FPDF — fpdf.org
- endroid/qr-code v6 — github.com/endroid/qr-code
- PhpSpreadsheet — phpoffice.github.io/PhpSpreadsheet
- Fonnte WhatsApp API — fonnte.com
- MySQL 8.0 / MariaDB 10.4 — Foreign Key Constraints, ON DELETE CASCADE/SET NULL