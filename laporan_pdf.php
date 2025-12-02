<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan level admin
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data
$sql = "SELECT p.*, u.username 
        FROM pendaftaran_wisuda p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.fakultas, p.program_studi, p.npm";
$result = mysqli_query($conn, $sql);

// Statistik
$total = mysqli_num_rows($result);
$sql_pending = "SELECT COUNT(*) as total FROM pendaftaran_wisuda WHERE status_verifikasi = 'Pending'";
$pending = mysqli_fetch_assoc(mysqli_query($conn, $sql_pending))['total'];

$sql_verified = "SELECT COUNT(*) as total FROM pendaftaran_wisuda WHERE status_verifikasi = 'Terverifikasi'";
$verified = mysqli_fetch_assoc(mysqli_query($conn, $sql_verified))['total'];

$sql_rejected = "SELECT COUNT(*) as total FROM pendaftaran_wisuda WHERE status_verifikasi = 'Ditolak'";
$rejected = mysqli_fetch_assoc(mysqli_query($conn, $sql_rejected))['total'];

// Data per fakultas
$sql_fakultas = "SELECT fakultas, COUNT(*) as jumlah FROM pendaftaran_wisuda GROUP BY fakultas ORDER BY jumlah DESC";
$result_fakultas = mysqli_query($conn, $sql_fakultas);

// Reset result
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendaftaran Wisuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-surat h3 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .kop-surat p {
            margin: 2px 0;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th {
            background-color: #FF6B35;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #000;
        }
        table td {
            padding: 6px;
            border: 1px solid #000;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
        }
        .stats-box {
            border: 2px solid #FF6B35;
            padding: 15px;
            margin: 20px 0;
            background: #fff8e1;
        }
    </style>
</head>
<body>
    <div class="no-print text-center p-3 bg-light">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <div class="container my-4">
        <!-- Kop Surat -->
        <div class="kop-surat">
            <h3>UNIVERSITAS</h3>
            <p>Jalan Raya Universitas No. 123, Kota, Provinsi 12345</p>
            <p>Telp: (021) 1234-5678 | Email: info@universitas.ac.id | Website: www.universitas.ac.id</p>
        </div>

        <!-- Judul Laporan -->
        <div class="text-center mb-4">
            <h4><u>LAPORAN DATA PENDAFTARAN WISUDA</u></h4>
            <p>Tahun Akademik 2024/2025</p>
            <p>Tanggal Cetak: <?php echo date('d F Y'); ?></p>
        </div>

        <!-- Statistik -->
        <div class="stats-box">
            <h5>Ringkasan Data:</h5>
            <div class="row">
                <div class="col-3">
                    <strong>Total Pendaftar:</strong> <?php echo $total; ?> orang
                </div>
                <div class="col-3">
                    <strong>Terverifikasi:</strong> <?php echo $verified; ?> orang
                </div>
                <div class="col-3">
                    <strong>Pending:</strong> <?php echo $pending; ?> orang
                </div>
                <div class="col-3">
                    <strong>Ditolak:</strong> <?php echo $rejected; ?> orang
                </div>
            </div>
        </div>

        <!-- Data Per Fakultas -->
        <h5>Distribusi Per Fakultas:</h5>
        <table>
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Fakultas</th>
                    <th width="100">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result_fakultas)): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['fakultas']; ?></td>
                    <td><?php echo $row['jumlah']; ?> orang</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Data Detail Pendaftar -->
        <h5 class="mt-4">Data Detail Pendaftar:</h5>
        <table>
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th width="80">NPM</th>
                    <th>Nama</th>
                    <th>Prodi</th>
                    <th width="60">IPK</th>
                    <th width="100">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['npm']; ?></td>
                    <td><?php echo $row['nama_lengkap']; ?></td>
                    <td><?php echo $row['program_studi']; ?></td>
                    <td><?php echo $row['ipk']; ?></td>
                    <td><?php echo $row['status_verifikasi']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- TTD -->
        <div class="signature">
            <p>Mengetahui,</p>
            <p><strong>Ketua Panitia Wisuda</strong></p>
            <br><br><br>
            <p>_______________________</p>
            <p><strong>NIP. ______________</strong></p>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4" style="font-size: 10px; color: #666;">
            <p>Dokumen ini dicetak dari Sistem Pendaftaran Wisuda Online</p>
            <p>Dicetak oleh: <?php echo $_SESSION['username']; ?> pada <?php echo date('d F Y H:i:s'); ?> WIB</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto print ketika halaman dibuka (opsional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>