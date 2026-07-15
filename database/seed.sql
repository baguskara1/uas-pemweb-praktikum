-- ============================================
-- SEED DATA: Bengkel Racing Cihuy
-- Jalankan setelah schema.sql
-- ============================================

USE db_bengkel_racing;

-- ============================================
-- JASA
-- ============================================
INSERT INTO jasa (id_kategori, nama, deskripsi) VALUES
(1, 'Service Biasa', 'Service ringan: ganti oli + filter'),
(1, 'Service Wow', 'Service lengkap: oli + filter + busi + general check'),
(1, 'Service Full', 'Service total: semua komponen diperiksa dan diservis'),
(2, 'Tune Up Standar', 'Tune up standar untuk performa harian'),
(2, 'Tune Up Racing', 'Tune up racing untuk performa maksimal'),
(3, 'Stroke Up 150', 'Stroke up ukuran 150cc'),
(3, 'Stroke Up 180', 'Stroke up ukuran 180cc'),
(4, 'Bore Up Kit A', 'Bore up paket A (125cc → 150cc)'),
(4, 'Bore Up Kit B', 'Bore up paket B (150cc → 180cc)'),
(4, 'Bore Up Custom', 'Bore up custom sesuai permintaan');

-- ============================================
-- MASTER ITEM (untuk breakdown)
-- ============================================
INSERT INTO master_item (nama_item, satuan) VALUES
('Oli Mesin', 'botol'),
('Filter Oli', 'pcs'),
('Gasket Set', 'set'),
('Piston', 'pcs'),
('Ring Piston', 'set'),
('Seal Ring', 'pcs'),
('Oil Seal', 'pcs'),
('Bearing Kruk As', 'pcs'),
('Gasket Klep', 'pcs'),
('Oli Gardan', 'botol'),
('Busi', 'pcs'),
('Filter Udara', 'pcs'),
('Tali Gas', 'pcs'),
('Kampas Kopling', 'set'),
('Laher Roda', 'pcs');

-- ============================================
-- VARIAN JASA (kombinasi jasa + merek + CC)
-- ============================================

-- Basic Service: Service Biasa (id=1)
INSERT INTO varian_jasa (id_jasa, id_merek, cc_min, cc_max, nama_varian, total_harga, is_custom) VALUES
(1, 1, 100, 125, 'Service Biasa Honda 100-125cc', 80000, 0),
(1, 1, 125, 150, 'Service Biasa Honda 125-150cc', 95000, 0),
(1, 2, 100, 125, 'Service Biasa Yamaha 100-125cc', 80000, 0),
(1, 2, 125, 150, 'Service Biasa Yamaha 125-150cc', 95000, 0),
(1, 3, 100, 125, 'Service Biasa Suzuki 100-125cc', 80000, 0),
(1, 4, 100, 150, 'Service Biasa Kawasaki 100-150cc', 90000, 0);

-- Basic Service: Service Wow (id=2)
INSERT INTO varian_jasa (id_jasa, id_merek, cc_min, cc_max, nama_varian, total_harga, is_custom) VALUES
(2, 1, 100, 125, 'Service Wow Honda 100-125cc', 150000, 0),
(2, 1, 125, 150, 'Service Wow Honda 125-150cc', 175000, 0),
(2, 2, 100, 125, 'Service Wow Yamaha 100-125cc', 150000, 0),
(2, 2, 125, 150, 'Service Wow Yamaha 125-150cc', 175000, 0);

-- Basic Service: Service Full (id=3)
INSERT INTO varian_jasa (id_jasa, id_merek, cc_min, cc_max, nama_varian, total_harga, is_custom) VALUES
(3, 1, 100, 125, 'Service Full Honda 100-125cc', 250000, 0),
(3, 1, 125, 150, 'Service Full Honda 125-150cc', 300000, 0),
(3, 2, 100, 125, 'Service Full Yamaha 100-125cc', 250000, 0),
(3, 2, 125, 150, 'Service Full Yamaha 125-150cc', 300000, 0);

-- Tune Up: Tune Up Standar (id=4)
INSERT INTO varian_jasa (id_jasa, id_merek, cc_min, cc_max, nama_varian, total_harga, is_custom) VALUES
(4, 1, 100, 150, 'Tune Up Standar Honda', 200000, 0),
(4, 2, 100, 150, 'Tune Up Standar Yamaha', 200000, 0),
(4, 3, 100, 150, 'Tune Up Standar Suzuki', 200000, 0);

-- Tune Up: Tune Up Racing (id=5)
INSERT INTO varian_jasa (id_jasa, id_merek, cc_min, cc_max, nama_varian, total_harga, is_custom) VALUES
(5, 1, 125, 150, 'Tune Up Racing Honda', 350000, 0),
(5, 2, 125, 150, 'Tune Up Racing Yamaha', 350000, 0);

-- Stroke Up: Stroke Up 150 (id=6)
INSERT INTO varian_jasa (id_jasa, id_merek, cc_min, cc_max, nama_varian, total_harga, is_custom) VALUES
(6, 1, 100, 125, 'Stroke Up 150 Honda', 400000, 0),
(6, 2, 100, 125, 'Stroke Up 150 Yamaha', 400000, 0);

-- Stroke Up: Stroke Up 180 (id=7)
INSERT INTO varian_jasa (id_jasa, id_merek, cc_min, cc_max, nama_varian, total_harga, is_custom) VALUES
(7, 1, 125, 150, 'Stroke Up 180 Honda', 550000, 0),
(7, 2, 125, 150, 'Stroke Up 180 Yamaha', 550000, 0);

-- Bore Up: Bore Up Kit A (id=8)
INSERT INTO varian_jasa (id_jasa, id_merek, cc_min, cc_max, nama_varian, total_harga, is_custom) VALUES
(8, 1, 100, 125, 'Bore Up Kit A Honda', 750000, 0),
(8, 2, 100, 125, 'Bore Up Kit A Yamaha', 750000, 0);

-- Bore Up: Bore Up Kit B (id=9)
INSERT INTO varian_jasa (id_jasa, id_merek, cc_min, cc_max, nama_varian, total_harga, is_custom) VALUES
(9, 1, 125, 150, 'Bore Up Kit B Honda', 950000, 0),
(9, 2, 125, 150, 'Bore Up Kit B Yamaha', 950000, 0);

-- Bore Up: Bore Up Custom (id=10)
INSERT INTO varian_jasa (id_jasa, id_merek, cc_min, cc_max, nama_varian, total_harga, is_custom) VALUES
(10, 1, 100, 200, 'Bore Up Custom Honda', 0, 1),
(10, 2, 100, 200, 'Bore Up Custom Yamaha', 0, 1);

-- ============================================
-- ITEM VARIAN (breakdown items per variant)
-- ============================================

-- Service Biasa Honda 100-125cc (varian id=1)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(1, 1, 1, 45000),
(1, 2, 1, 25000),
(1, 12, 1, 15000);

-- Service Biasa Honda 125-150cc (varian id=2)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(2, 1, 1, 55000),
(2, 2, 1, 30000),
(2, 12, 1, 15000);

-- Service Biasa Yamaha 100-125cc (varian id=3)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(3, 1, 1, 45000),
(3, 2, 1, 25000),
(3, 12, 1, 15000);

-- Service Biasa Yamaha 125-150cc (varian id=4)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(4, 1, 1, 55000),
(4, 2, 1, 30000),
(4, 12, 1, 15000);

-- Service Wow Honda 100-125cc (varian id=7)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(7, 1, 1, 45000),
(7, 2, 1, 25000),
(7, 11, 1, 25000),
(7, 12, 1, 15000),
(7, 13, 1, 20000);

-- Service Wow Honda 125-150cc (varian id=8)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(8, 1, 1, 55000),
(8, 2, 1, 30000),
(8, 11, 1, 35000),
(8, 12, 1, 20000),
(8, 13, 1, 25000);

-- Service Wow Yamaha 100-125cc (varian id=9)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(9, 1, 1, 45000),
(9, 2, 1, 25000),
(9, 11, 1, 25000),
(9, 12, 1, 15000),
(9, 13, 1, 20000);

-- Service Full Honda 100-125cc (varian id=11)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(11, 1, 1, 45000),
(11, 2, 1, 25000),
(11, 11, 1, 25000),
(11, 12, 1, 15000),
(11, 13, 1, 20000),
(11, 14, 1, 75000);

-- Bore Up Kit A Honda (varian id=21)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(21, 4, 1, 200000),
(21, 5, 1, 85000),
(21, 6, 1, 35000),
(21, 7, 1, 25000),
(21, 8, 1, 45000),
(21, 9, 1, 50000),
(21, 10, 1, 35000);

-- Bore Up Kit A Yamaha (varian id=22)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(22, 4, 1, 200000),
(22, 5, 1, 85000),
(22, 6, 1, 35000),
(22, 7, 1, 25000),
(22, 8, 1, 45000),
(22, 9, 1, 50000),
(22, 10, 1, 35000);

-- Bore Up Kit B Honda (varian id=23)
INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES
(23, 4, 1, 300000),
(23, 5, 1, 120000),
(23, 6, 1, 45000),
(23, 7, 1, 35000),
(23, 8, 1, 65000),
(23, 9, 1, 70000),
(23, 10, 1, 45000);

-- ============================================
-- SPAREPART
-- ============================================
INSERT INTO sparepart (id_kategori, kode, nama, stok, harga_beli, harga_jual) VALUES
(1, 'OLI-MPX1', 'Oli MPX 1 SAE 20W-50', 48, 35000, 45000),
(1, 'OLI-MPX2', 'Oli MPX 2 SAE 10W-40', 30, 40000, 55000),
(1, 'OLI-FED', 'Oli Federal Evalube 20W-50', 25, 25000, 35000),
(1, 'OLI-TOP', 'Oli Top 1 S至尊 10W-40', 15, 60000, 85000),
(1, 'OLI-GARDAN', 'Oli Gardan MPX 90', 20, 25000, 35000),
(2, 'FIL-OIL-H', 'Filter Oli Honda Original', 18, 20000, 30000),
(2, 'FIL-OIL-Y', 'Filter Oli Yamaha Original', 15, 20000, 30000),
(2, 'FIL-UD', 'Filter Udara Racing K&N', 10, 80000, 125000),
(3, 'BUSI-STD', 'Busi Standar NGK CPR8E', 40, 15000, 25000),
(3, 'BUSI-IRID', 'Busi Iridium NGK CR8EIX', 20, 55000, 85000),
(3, 'BUSI-RAC', 'Busi Racing Daytona', 12, 75000, 110000),
(4, 'BAN-DUNLOP', 'Ban Depan Dunlop TT900 70/90', 8, 180000, 275000),
(4, 'BAN-MICHELIN', 'Ban Belakang Michelin Pilot 100/80', 6, 250000, 375000),
(4, 'BAN-PIRELLI', 'Ban Pirelli Diablo Rosso 90/80', 4, 350000, 550000),
(5, 'RANTAI-RK', 'Rantai RK Racing X Ring', 10, 150000, 225000),
(5, 'RANTAI-DID', 'Rantai DID Standard', 12, 85000, 135000),
(5, 'GEAR-SET', 'Gear Set Racing Pro 14-38T', 7, 120000, 185000),
(6, 'KAMPAS-REAR', 'Kampas Rem Belakang Original', 20, 25000, 40000),
(6, 'KAMPAS-FRONT', 'Kampas Rem Depan Racing', 15, 45000, 75000),
(6, 'AKI-GS', 'Aki GS GTX5L-BS', 8, 120000, 185000),
(6, 'SPION', 'Spion Retro Bulat Racing', 10, 35000, 55000),
(7, 'BODY-FENDER', 'Body Fender Depan Honda Vario', 5, 150000, 275000),
(7, 'BODY-DEK', 'Body Deck Belakang Yamaha Mio', 4, 120000, 200000),
(7, 'STICKER-RAC', 'Sticker Kit Racing Merah-Hitam', 20, 15000, 35000),
(8, 'KUNCI-HONDA', 'Kunci Kontak Honda Original', 10, 40000, 75000),
(8, 'KUNCI-YAMAHA', 'Kunci Kontak Yamaha Original', 8, 40000, 75000),
(8, 'LAHER-RODA', 'Laher Roda SKF 6202', 30, 12000, 25000);

-- ============================================
-- PELANGGAN & KENDARAAN
-- ============================================
INSERT INTO pelanggan (nama, no_telp, alamat) VALUES
('Ryo Pratama', '081234567890', 'Jl. Kaliurang KM 10, Sleman, Yogyakarta'),
('Budi Santoso', '085678912345', 'Jl. Monjali No. 25, Sleman'),
('Sinta Dewi', '087890123456', 'Perumahan Seturan Indah Blok A3, Depok'),
('Adi Nugroho', '082345678901', 'Jl. Magelang KM 5, Yogyakarta'),
('Rina Marlina', '081112223334', 'Jl. Timoho No. 88, Yogyakarta'),
('Fajar Hidayat', '085555666777', 'Perum Bantul Permai No. 12, Bantul'),
('Dian Permata', '087778889999', 'Jl. Parangtritis KM 8, Bantul');

INSERT INTO kendaraan (id_pelanggan, id_merek, plat_no, model, cc, tahun) VALUES
(1, 1, 'AB 4391 ZC', 'Honda Vario 125', 125, 2022),
(1, 2, 'AB 1234 YT', 'Yamaha Nmax 155', 155, 2023),
(2, 1, 'AB 5678 XZ', 'Honda Beat 110', 110, 2021),
(2, 3, 'AB 9012 WV', 'Suzuki Satria FU 150', 150, 2020),
(3, 2, 'AB 3456 YU', 'Yamaha Aerox 155', 155, 2024),
(4, 1, 'AB 7890 ST', 'Honda CBR 150R', 150, 2022),
(4, 4, 'AB 2345 QR', 'Kawasaki Ninja 250', 250, 2023),
(5, 2, 'AB 6789 OP', 'Yamaha Jupiter MX 135', 135, 2021),
(5, 1, 'AB 1122 KL', 'Honda Scoopy 110', 110, 2023),
(6, 1, 'AB 3344 IJ', 'Honda Revo 100', 100, 2020),
(7, 3, 'AB 5566 GH', 'Suzuki Nex 115', 115, 2022),
(7, 2, 'AB 7788 EF', 'Yamaha Lexi 125', 125, 2023);

-- ============================================
-- SAMPLE TRANSAKSI (untuk demo dashboard)
-- ============================================
INSERT INTO transaksi (id_pelanggan, id_kendaraan, id_user, tgl, total, status) VALUES
(1, 1, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), 150000, 'lunas'),
(2, 3, 1, DATE_SUB(NOW(), INTERVAL 1 DAY), 250000, 'lunas'),
(3, 5, 1, DATE_SUB(NOW(), INTERVAL 1 DAY), 95000, 'lunas'),
(4, 6, 1, NOW(), 750000, 'antrian'),
(5, 8, 1, NOW(), 200000, 'dikerjakan');

INSERT INTO detail_jasa (id_transaksi, id_varian, nama_jasa, total_harga) VALUES
(1, 7, 'Service Wow Honda 100-125cc', 150000),
(2, 11, 'Service Full Honda 100-125cc', 250000),
(3, 4, 'Service Biasa Yamaha 125-150cc', 95000),
(4, 21, 'Bore Up Kit A Honda', 750000),
(5, 10, 'Tune Up Standar Honda', 200000);
