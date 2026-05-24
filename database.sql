-- =============================================
-- DATABASE: AbsenHub - Sistem Absensi Pegawai
-- =============================================

CREATE DATABASE IF NOT EXISTS absenhub;
USE absenhub;

-- Tabel pegawai (class Pegawai)
CREATE TABLE pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(50) NOT NULL
);

-- Tabel absensi (class Absensi)
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME,
    jam_pulang TIME,
    status VARCHAR(20) DEFAULT 'Tepat Waktu',
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id)
);

-- =============================================
-- DATA PEGAWAI (2 pegawai)
-- =============================================
-- Gatot: tepat waktu masuk & pulang
-- Iwan : terlambat masuk & pulang lebih awal
INSERT INTO pegawai (nama, username, password) VALUES
('Gatot Subroto', 'gatot', 'gatot123'),
('Iwan Setiawan', 'iwan',  'iwan123');

-- =============================================
-- DATA ABSENSI SIMULASI (bukan real-time)
-- =============================================
-- Gatot: masuk 06:55 (tepat waktu), pulang 17:05 (tepat waktu)
INSERT INTO absensi (pegawai_id, tanggal, jam_masuk, jam_pulang, status) VALUES
(1, '2026-05-19', '06:55:00', '17:05:00', 'Tepat Waktu'),
(1, '2026-05-20', '06:50:00', '17:10:00', 'Tepat Waktu'),
(1, '2026-05-21', '06:45:00', '17:00:00', 'Tepat Waktu');

-- Iwan: masuk 08:30 (terlambat), pulang 15:30 (lebih awal)
INSERT INTO absensi (pegawai_id, tanggal, jam_masuk, jam_pulang, status) VALUES
(2, '2026-05-19', '08:30:00', '15:30:00', 'Terlambat'),
(2, '2026-05-20', '09:15:00', '14:45:00', 'Terlambat'),
(2, '2026-05-21', '07:45:00', '16:00:00', 'Terlambat');
