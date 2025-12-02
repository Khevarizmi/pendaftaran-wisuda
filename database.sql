-- Buat Database
CREATE DATABASE IF NOT EXISTS db_wisuda;
USE db_wisuda;

-- Tabel Users untuk Login (Mahasiswa & Admin)
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    level ENUM('admin', 'mahasiswa') NOT NULL DEFAULT 'mahasiswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabel Pendaftaran Wisuda
CREATE TABLE IF NOT EXISTS pendaftaran_wisuda (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    npm VARCHAR(20) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(50) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    alamat TEXT NOT NULL,
    no_telp VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    program_studi VARCHAR(100) NOT NULL,
    fakultas VARCHAR(100) NOT NULL,
    ipk DECIMAL(3,2) NOT NULL,
    tahun_lulus YEAR NOT NULL,
    
    -- File Upload
    foto_formal VARCHAR(255) DEFAULT NULL,
    surat_lulus VARCHAR(255) DEFAULT NULL,
    transkrip_nilai VARCHAR(255) DEFAULT NULL,
    kuitansi_pembayaran VARCHAR(255) DEFAULT NULL,
    
    -- Status
    status_verifikasi ENUM('Pending', 'Terverifikasi', 'Ditolak') DEFAULT 'Pending',
    catatan_admin TEXT DEFAULT NULL,
    verified_by INT(11) DEFAULT NULL,
    verified_at DATETIME DEFAULT NULL,
    
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert default admin
INSERT INTO users (email, username, password, level) VALUES 
('admin@wisuda.ac.id', 'admin', MD5('admin123'), 'admin');

-- Contoh data mahasiswa
INSERT INTO users (email, username, password, level) VALUES 
('budi@gmail.com', 'budi_santoso', MD5('123456'), 'mahasiswa'),
('ani@gmail.com', 'ani_wijaya', MD5('123456'), 'mahasiswa');

-- Contoh pendaftaran
INSERT INTO pendaftaran_wisuda (user_id, npm, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, no_telp, email, program_studi, fakultas, ipk, tahun_lulus, status_verifikasi) VALUES
(2, '2021001234', 'Budi Santoso', 'Jakarta', '2000-05-15', 'Laki-laki', 'Jl. Sudirman No. 123, Jakarta', '081234567890', 'budi@gmail.com', 'Teknik Informatika', 'Teknik', 3.75, 2024, 'Terverifikasi'),
(3, '2021005678', 'Ani Wijaya', 'Bandung', '2001-03-20', 'Perempuan', 'Jl. Asia Afrika No. 45, Bandung', '081345678901', 'ani@gmail.com', 'Manajemen', 'Ekonomi', 3.85, 2024, 'Pending');