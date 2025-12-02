<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Koneksi Database</h1>";

// Konfigurasi Database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_wisuda";

echo "<h2>1. Cek Koneksi ke MySQL</h2>";
$conn = mysqli_connect($host, $username, $password);

if (!$conn) {
    die("<p style='color:red;'>❌ Koneksi ke MySQL GAGAL: " . mysqli_connect_error() . "</p>");
}
echo "<p style='color:green;'>✅ Koneksi ke MySQL BERHASIL</p>";

echo "<h2>2. Cek Database 'db_wisuda'</h2>";
$db_selected = mysqli_select_db($conn, $database);

if (!$db_selected) {
    echo "<p style='color:red;'>❌ Database 'db_wisuda' TIDAK DITEMUKAN</p>";
    echo "<p><strong>Solusi:</strong></p>";
    echo "<ol>";
    echo "<li>Buka phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Buat database baru dengan nama: <strong>db_wisuda</strong></li>";
    echo "<li>Import file <strong>database.sql</strong></li>";
    echo "</ol>";
    die();
}
echo "<p style='color:green;'>✅ Database 'db_wisuda' DITEMUKAN</p>";

echo "<h2>3. Cek Tabel 'users'</h2>";
$check_users = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($check_users) == 0) {
    echo "<p style='color:red;'>❌ Tabel 'users' TIDAK DITEMUKAN</p>";
    echo "<p><strong>Solusi:</strong> Import file database.sql</p>";
} else {
    echo "<p style='color:green;'>✅ Tabel 'users' DITEMUKAN</p>";
    
    // Cek data admin
    $check_admin = mysqli_query($conn, "SELECT * FROM users WHERE level = 'admin'");
    if (mysqli_num_rows($check_admin) > 0) {
        echo "<p style='color:green;'>✅ User admin DITEMUKAN</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ User admin TIDAK DITEMUKAN</p>";
    }
}

echo "<h2>4. Cek Tabel 'pendaftaran_wisuda'</h2>";
$check_pendaftaran = mysqli_query($conn, "SHOW TABLES LIKE 'pendaftaran_wisuda'");
if (mysqli_num_rows($check_pendaftaran) == 0) {
    echo "<p style='color:red;'>❌ Tabel 'pendaftaran_wisuda' TIDAK DITEMUKAN</p>";
    echo "<p><strong>Solusi:</strong> Import file database.sql</p>";
} else {
    echo "<p style='color:green;'>✅ Tabel 'pendaftaran_wisuda' DITEMUKAN</p>";
}

echo "<h2>5. Cek Folder 'uploads'</h2>";
if (file_exists('uploads')) {
    echo "<p style='color:green;'>✅ Folder 'uploads' DITEMUKAN</p>";
    if (is_writable('uploads')) {
        echo "<p style='color:green;'>✅ Folder 'uploads' BISA DITULIS</p>";
    } else {
        echo "<p style='color:red;'>❌ Folder 'uploads' TIDAK BISA DITULIS</p>";
        echo "<p><strong>Solusi:</strong> Set permission folder uploads menjadi 777</p>";
    }
} else {
    echo "<p style='color:red;'>❌ Folder 'uploads' TIDAK DITEMUKAN</p>";
    echo "<p><strong>Solusi:</strong> Buat folder 'uploads' di folder website</p>";
}

echo "<h2>✅ KESIMPULAN</h2>";
echo "<p>Jika semua pengecekan di atas ✅ (hijau), maka website sudah siap digunakan!</p>";
echo "<p><a href='index.html'>Kembali ke Homepage</a></p>";

mysqli_close($conn);
?>