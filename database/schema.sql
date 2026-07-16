CREATE DATABASE IF NOT EXISTS db_bengkel_racing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_bengkel_racing;

-- 1. MEREK MOTOR
CREATE TABLE merek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE
);

-- 2. KATEGORI JASA
CREATE TABLE kategori_jasa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    punya_breakdown TINYINT(1) DEFAULT 0
);

-- 3. JASA
CREATE TABLE jasa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    nama VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    FOREIGN KEY (id_kategori) REFERENCES kategori_jasa(id)
);

-- 4. VARIAN JASA
CREATE TABLE varian_jasa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_jasa INT NOT NULL,
    id_merek INT NOT NULL,
    cc_min INT NOT NULL,
    cc_max INT NOT NULL,
    nama_varian VARCHAR(200),
    total_harga INT NOT NULL DEFAULT 0,
    is_custom TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id_jasa) REFERENCES jasa(id),
    FOREIGN KEY (id_merek) REFERENCES merek(id)
);

-- 5. MASTER ITEM
CREATE TABLE master_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_item VARCHAR(200) NOT NULL,
    satuan VARCHAR(50) DEFAULT 'pcs'
);

-- 6. ITEM VARIAN
CREATE TABLE item_varian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_varian INT NOT NULL,
    id_master_item INT NOT NULL,
    qty_default INT NOT NULL DEFAULT 1,
    harga_default INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_varian) REFERENCES varian_jasa(id),
    FOREIGN KEY (id_master_item) REFERENCES master_item(id)
);

-- 7. KATEGORI SPAREPART
CREATE TABLE kategori_sparepart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL
);

-- 8. SPAREPART
CREATE TABLE sparepart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    kode VARCHAR(50) UNIQUE,
    nama VARCHAR(200) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    harga_beli INT NOT NULL,
    harga_jual INT NOT NULL,
    gambar VARCHAR(255),
    FOREIGN KEY (id_kategori) REFERENCES kategori_sparepart(id)
);

-- 9. PELANGGAN
CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_telp VARCHAR(20),
    alamat TEXT
);

-- 10. KENDARAAN
CREATE TABLE kendaraan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    id_merek INT NOT NULL,
    plat_no VARCHAR(20),
    model VARCHAR(100),
    cc INT NOT NULL,
    tahun YEAR,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON DELETE CASCADE,
    FOREIGN KEY (id_merek) REFERENCES merek(id)
);

-- 11. USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'kasir') DEFAULT 'kasir',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 12. TRANSAKSI
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT,
    id_kendaraan INT,
    id_user INT NOT NULL,
    tgl DATETIME DEFAULT CURRENT_TIMESTAMP,
    total INT NOT NULL DEFAULT 0,
    status ENUM('antrian', 'dikerjakan', 'selesai', 'lunas') DEFAULT 'antrian',
    catatan TEXT,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON DELETE SET NULL,
    FOREIGN KEY (id_kendaraan) REFERENCES kendaraan(id) ON DELETE SET NULL,
    FOREIGN KEY (id_user) REFERENCES users(id)
);

-- 13. DETAIL TRANSAKSI - JASA
CREATE TABLE detail_jasa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_varian INT NOT NULL,
    nama_jasa VARCHAR(200),
    total_harga INT NOT NULL,
    catatan VARCHAR(255),
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_varian) REFERENCES varian_jasa(id)
);

-- 14. DETAIL TRANSAKSI - ITEM JASA
CREATE TABLE detail_item_jasa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_detail_jasa INT NOT NULL,
    id_master_item INT,
    nama_item VARCHAR(200) NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    harga_satuan INT NOT NULL,
    subtotal INT NOT NULL,
    FOREIGN KEY (id_detail_jasa) REFERENCES detail_jasa(id) ON DELETE CASCADE
);

-- 15. DETAIL TRANSAKSI - SPAREPART
CREATE TABLE detail_sparepart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_sparepart INT NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    harga_jual INT NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_sparepart) REFERENCES sparepart(id)
);

-- SEEDER: DEFAULT DATA
-- Password: admin123 (hash dibuat via PHP saat pertama setup)
INSERT INTO users (username, password, nama, role) VALUES
('admin', '$2y$12$ndjlbUTnW91uLL7lxHfJEe6N0g.0GlmSxBn1MLujYMAQ5rhmLMj26', 'Administrator', 'admin');

INSERT INTO merek (nama) VALUES
('Honda'), ('Yamaha'), ('Suzuki'), ('Kawasaki');

INSERT INTO kategori_jasa (nama, icon, punya_breakdown) VALUES
('Basic Service', 'fa-wrench', 1),
('Tune Up', 'fa-bolt', 0),
('Stroke Up', 'fa-arrow-up', 0),
('Bore Up', 'fa-fire', 1);

INSERT INTO kategori_sparepart (nama) VALUES
('Oli'), ('Filter'), ('Busi'), ('Ban'), ('Rantai'), ('Kelistrikan'), ('Body'), ('Lainnya');

-- 16. PENGATURAN (Settings)
CREATE TABLE pengaturan (
    kunci VARCHAR(50) PRIMARY KEY,
    nilai TEXT,
    grup VARCHAR(20) NOT NULL DEFAULT 'umum',
    deskripsi VARCHAR(200)
);

INSERT INTO pengaturan (kunci, nilai, grup, deskripsi) VALUES
('nama_bengkel', 'Bengkel Racing Cihuy', 'umum', 'Nama bengkel'),
('alamat', 'Pogung Baru Blok G No.1, Yogyakarta', 'umum', 'Alamat bengkel'),
('no_telp', '081234567890', 'umum', 'Nomor telepon bengkel'),
('email', 'racing@bengkelcihuy.com', 'umum', 'Email bengkel'),
('nota_footer', 'Terima Kasih - Bengkel Racing Cihuy', 'nota', 'Teks footer PDF nota'),
('fonnte_token', '', 'wa', 'Token API Fonnte'),
('garansi_link', 'https://example.com/garansi', 'garansi', 'Link form garansi'),
('timezone', 'Asia/Jakarta', 'sistem', 'Zona waktu');

-- 17. LOG STOK (Stock movement history)
CREATE TABLE log_stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_sparepart INT NOT NULL,
    tipe ENUM('masuk', 'keluar', 'penyesuaian') NOT NULL,
    qty INT NOT NULL,
    stok_sebelum INT NOT NULL DEFAULT 0,
    stok_sesudah INT NOT NULL DEFAULT 0,
    referensi VARCHAR(100),
    id_user INT,
    catatan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sparepart) REFERENCES sparepart(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id)
);

-- ============================================
-- IMPORT SEED DATA (demo / development)
-- ============================================
-- Jalankan file terpisah untuk seed data lengkap:
--   mysql -uroot db_bengkel_racing < database/seed.sql
--
-- Atau: SOURCE database/seed.sql;
