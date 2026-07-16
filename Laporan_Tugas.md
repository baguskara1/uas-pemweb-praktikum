# Laporan Tugas UAS Praktikum Pemrograman Web

## 1. Deskripsi Project
Sistem POS (Point of Sales) Bengkel adalah aplikasi berbasis web yang dirancang untuk mengelola transaksi jasa perbaikan dan penjualan suku cadang (sparepart) pada sebuah bengkel. Aplikasi ini mempermudah pencatatan pelanggan, kendaraan, hingga proses transaksi secara *real-time* dengan fitur keranjang belanja yang dinamis. 

Fitur Utama:
- **Manajemen Transaksi**: Proses POS yang responsif, mengelompokkan kategori jasa, suku cadang, dan menghitung total harga secara otomatis.
- **Manajemen Pelanggan & Kendaraan**: Menyimpan data riwayat pelanggan dan kendaraan.
- **Manajemen Jasa & Sparepart**: Pencatatan layanan perbaikan dan stok barang.
- **Dashboard Interaktif**: Ringkasan transaksi dan pendapatan.

## 2. Link Penting
- **URL GitHub**: [https://github.com/baguskara1/uas-pemweb-praktikum](https://github.com/baguskara1/uas-pemweb-praktikum)
- **URL Video Demo**: [Google Drive Link](https://drive.google.com/drive/folders/1HfNmJ8nKIyGhCtzyaTa4rFpT1Z0X4vZH?usp=sharing)

## 3. Screenshot Fitur

### Halaman Login
![Login](assets/screenshots/login.png)

### Dashboard Utama
![Dashboard](assets/screenshots/dashboard.png)

### Transaksi Baru (POS)
![POS](assets/screenshots/pos.png)

### Data Pelanggan
![Pelanggan](assets/screenshots/pelanggan.png)

## 4. Struktur Database (Schema)

Berikut adalah struktur database utama (ERD / Relasi Tabel):

```mermaid
erDiagram
    USERS {
        int id PK
        varchar username
        varchar password
        varchar nama
        enum role
    }
    PELANGGAN {
        int id PK
        varchar nama
        varchar no_telp
        text alamat
    }
    KENDARAAN {
        int id PK
        int id_pelanggan FK
        varchar plat_no
        varchar model
    }
    TRANSAKSI {
        int id PK
        varchar no_transaksi
        int id_pelanggan FK
        int id_kendaraan FK
        decimal total
        text catatan
    }
    TRANSAKSI_JASA {
        int id PK
        int id_transaksi FK
        int id_varian_jasa FK
    }
    TRANSAKSI_SPAREPART {
        int id PK
        int id_transaksi FK
        int id_sparepart FK
        int qty
        decimal harga
    }

    PELANGGAN ||--o{ KENDARAAN : "memiliki"
    PELANGGAN ||--o{ TRANSAKSI : "melakukan"
    KENDARAAN ||--o{ TRANSAKSI : "diservis"
    TRANSAKSI ||--o{ TRANSAKSI_JASA : "mencakup"
    TRANSAKSI ||--o{ TRANSAKSI_SPAREPART : "membeli"
```

Struktur ini mendukung pencatatan yang terintegrasi antara data master dan data transaksi harian bengkel.
