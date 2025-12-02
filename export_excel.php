<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan level admin
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data
$status = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';

if ($status == 'all') {
    $sql = "SELECT * FROM pendaftaran_wisuda ORDER BY tanggal_daftar DESC";
} else {
    $sql = "SELECT * FROM pendaftaran_wisuda WHERE status_verifikasi = '$status' ORDER BY tanggal_daftar DESC";
}

$result = mysqli_query($conn, $sql);

// Set header untuk download Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Data_Wisuda_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output Excel
echo "<table border='1'>";
echo "<thead>";
echo "<tr style='background-color: #FF6B35; color: white; font-weight: bold;'>";
echo "<th>No</th>";
echo "<th>NPM</th>";
echo "<th>Nama Lengkap</th>";
echo "<th>Tempat Lahir</th>";
echo "<th>Tanggal Lahir</th>";
echo "<th>Jenis Kelamin</th>";
echo "<th>Alamat</th>";
echo "<th>No. Telepon</th>";
echo "<th>Email</th>";
echo "<th>Fakultas</th>";
echo "<th>Program Studi</th>";
echo "<th>IPK</th>";
echo "<th>Tahun Lulus</th>";
echo "<th>Status Verifikasi</th>";
echo "<th>Tanggal Daftar</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

$no = 1;
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $no++ . "</td>";
    echo "<td>" . $row['npm'] . "</td>";
    echo "<td>" . $row['nama_lengkap'] . "</td>";
    echo "<td>" . $row['tempat_lahir'] . "</td>";
    echo "<td>" . $row['tanggal_lahir'] . "</td>";
    echo "<td>" . $row['jenis_kelamin'] . "</td>";
    echo "<td>" . $row['alamat'] . "</td>";
    echo "<td>" . $row['no_telp'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . $row['fakultas'] . "</td>";
    echo "<td>" . $row['program_studi'] . "</td>";
    echo "<td>" . $row['ipk'] . "</td>";
    echo "<td>" . $row['tahun_lulus'] . "</td>";
    echo "<td>" . $row['status_verifikasi'] . "</td>";
    echo "<td>" . date('d-m-Y H:i:s', strtotime($row['tanggal_daftar'])) . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
?>