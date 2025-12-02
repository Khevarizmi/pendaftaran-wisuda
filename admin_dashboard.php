<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan level admin
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil semua data pendaftaran
$sql = "SELECT p.*, u.username, u.email as user_email 
        FROM pendaftaran_wisuda p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.tanggal_daftar DESC";
$result = mysqli_query($conn, $sql);

// Hitung statistik
$total = mysqli_num_rows($result);
$sql_pending = "SELECT COUNT(*) as total FROM pendaftaran_wisuda WHERE status_verifikasi = 'Pending'";
$result_pending = mysqli_query($conn, $sql_pending);
$pending = mysqli_fetch_assoc($result_pending)['total'];

$sql_verified = "SELECT COUNT(*) as total FROM pendaftaran_wisuda WHERE status_verifikasi = 'Terverifikasi'";
$result_verified = mysqli_query($conn, $sql_verified);
$verified = mysqli_fetch_assoc($result_verified)['total'];

$sql_rejected = "SELECT COUNT(*) as total FROM pendaftaran_wisuda WHERE status_verifikasi = 'Ditolak'";
$result_rejected = mysqli_query($conn, $sql_rejected);
$rejected = mysqli_fetch_assoc($result_rejected)['total'];

// Data untuk grafik fakultas
$sql_fakultas = "SELECT fakultas, COUNT(*) as jumlah FROM pendaftaran_wisuda GROUP BY fakultas";
$result_fakultas = mysqli_query($conn, $sql_fakultas);
$data_fakultas = [];
while($row = mysqli_fetch_assoc($result_fakultas)) {
    $data_fakultas[] = $row;
}

// Data untuk grafik per bulan
$sql_perbulan = "SELECT DATE_FORMAT(tanggal_daftar, '%Y-%m') as bulan, COUNT(*) as jumlah 
                 FROM pendaftaran_wisuda 
                 GROUP BY DATE_FORMAT(tanggal_daftar, '%Y-%m')
                 ORDER BY bulan ASC
                 LIMIT 6";
$result_perbulan = mysqli_query($conn, $sql_perbulan);
$data_perbulan = [];
while($row = mysqli_fetch_assoc($result_perbulan)) {
    $data_perbulan[] = $row;
}

// Data untuk grafik IPK
$sql_ipk = "SELECT 
            SUM(CASE WHEN ipk >= 3.5 THEN 1 ELSE 0 END) as cum_laude,
            SUM(CASE WHEN ipk >= 3.0 AND ipk < 3.5 THEN 1 ELSE 0 END) as sangat_memuaskan,
            SUM(CASE WHEN ipk >= 2.75 AND ipk < 3.0 THEN 1 ELSE 0 END) as memuaskan,
            SUM(CASE WHEN ipk < 2.75 THEN 1 ELSE 0 END) as cukup
            FROM pendaftaran_wisuda";
$result_ipk = mysqli_query($conn, $sql_ipk);
$data_ipk = mysqli_fetch_assoc($result_ipk);

// Reset result
$result = mysqli_query($conn, $sql);
?>
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Wisuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(90deg, #004E89 0%, #FF6B35 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-graduation-cap"></i> Sistem Wisuda Online - Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_dashboard.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield"></i> <?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid my-4">
        <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['message']; unset($_SESSION['message'], $_SESSION['message_type']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistik -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card feature-card text-center">
                    <div class="card-body">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $total; ?></h3>
                        <p class="text-muted mb-0">Total Pendaftar</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card feature-card text-center">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="color: #F39C12;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $pending; ?></h3>
                        <p class="text-muted mb-0">Menunggu Verifikasi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card feature-card text-center">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="color: #27AE60;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $verified; ?></h3>
                        <p class="text-muted mb-0">Terverifikasi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card feature-card text-center">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="color: #C41E3A;">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $rejected; ?></h3>
                        <p class="text-muted mb-0">Ditolak</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRAFIK SECTION -->
        <div class="row g-4 mb-4">
            <!-- Grafik Lingkaran - Status Verifikasi -->
            <div class="col-md-4">
                <div class="card feature-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Grafik Status Verifikasi</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartPie"></canvas>
                    </div>
                </div>
            </div>

            <!-- Grafik Batang - Per Fakultas -->
            <div class="col-md-4">
                <div class="card feature-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Grafik Per Fakultas</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartBar"></canvas>
                    </div>
                </div>
            </div>

            <!-- Grafik Garis - Pendaftar Per Bulan -->
            <div class="col-md-4">
                <div class="card feature-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Grafik Per Bulan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartLine"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik IPK -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card feature-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Distribusi Predikat Kelulusan (IPK)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartIPK" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="fas fa-list"></i> Daftar Pendaftar Wisuda</h4>
                <div>
                    <a href="export_excel.php?status=all" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <a href="laporan_pdf.php" class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Cetak Laporan
                    </a>
                    <a href="kirim_email.php" class="btn btn-info">
                        <i class="fas fa-envelope"></i> Kirim Email
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NPM</th>
                            <th>Nama</th>
                            <th>Program Studi</th>
                            <th>IPK</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['npm']; ?></td>
                            <td><?php echo $row['nama_lengkap']; ?></td>
                            <td><?php echo $row['program_studi']; ?></td>
                            <td><span class="badge bg-info"><?php echo $row['ipk']; ?></span></td>
                            <td><?php echo date('d-m-Y H:i', strtotime($row['tanggal_daftar'])); ?></td>
                            <td>
                                <?php 
                                $badge = 'warning';
                                if ($row['status_verifikasi'] == 'Terverifikasi') $badge = 'success';
                                if ($row['status_verifikasi'] == 'Ditolak') $badge = 'danger';
                                ?>
                                <span class="badge bg-<?php echo $badge; ?>">
                                    <?php echo $row['status_verifikasi']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="detail_pendaftar.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="8" class="text-center">Belum ada pendaftar</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pendaftaran Wisuda Online. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Data dari PHP
        const pending = <?php echo $pending; ?>;
        const verified = <?php echo $verified; ?>;
        const rejected = <?php echo $rejected; ?>;

        // Grafik Lingkaran - Status Verifikasi (WARNA STIE MP)
        const ctxPie = document.getElementById('chartPie').getContext('2d');
        const chartPie = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: ['Pending', 'Terverifikasi', 'Ditolak'],
                datasets: [{
                    data: [pending, verified, rejected],
                    backgroundColor: ['#F39C12', '#27AE60', '#C41E3A'], // Kuning, Hijau, Merah STIE MP
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Data Fakultas dari PHP
        const dataFakultas = <?php echo json_encode($data_fakultas); ?>;
        const labelsFakultas = dataFakultas.map(d => d.fakultas);
        const valuesFakultas = dataFakultas.map(d => d.jumlah);

        // Grafik Batang - Per Fakultas (WARNA STIE MP)
        const ctxBar = document.getElementById('chartBar').getContext('2d');
        const chartBar = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: labelsFakultas,
                datasets: [{
                    label: 'Jumlah Mahasiswa',
                    data: valuesFakultas,
                    backgroundColor: '#C41E3A',  // Merah STIE MP
                    borderColor: '#2C3E50',      // Abu Gelap
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Data Per Bulan dari PHP
        const dataPerBulan = <?php echo json_encode($data_perbulan); ?>;
        const labelsBulan = dataPerBulan.map(d => d.bulan);
        const valuesBulan = dataPerBulan.map(d => d.jumlah);

        // Grafik Garis - Pendaftar Per Bulan (WARNA STIE MP)
        const ctxLine = document.getElementById('chartLine').getContext('2d');
        const chartLine = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: labelsBulan,
                datasets: [{
                    label: 'Pendaftar',
                    data: valuesBulan,
                    borderColor: '#C41E3A',                  // Merah STIE MP
                    backgroundColor: 'rgba(196, 30, 58, 0.2)', // Merah STIE MP (opacity)
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Grafik IPK (WARNA STIE MP)
        const ctxIPK = document.getElementById('chartIPK').getContext('2d');
        const chartIPK = new Chart(ctxIPK, {
            type: 'bar',
            data: {
                labels: ['Cum Laude (≥3.5)', 'Sangat Memuaskan (3.0-3.49)', 'Memuaskan (2.75-2.99)', 'Cukup (<2.75)'],
                datasets: [{
                    label: 'Jumlah Mahasiswa',
                    data: [
                        <?php echo $data_ipk['cum_laude']; ?>,
                        <?php echo $data_ipk['sangat_memuaskan']; ?>,
                        <?php echo $data_ipk['memuaskan']; ?>,
                        <?php echo $data_ipk['cukup']; ?>
                    ],
                    backgroundColor: ['#27AE60', '#2C3E50', '#F39C12', '#C41E3A'], // Hijau, Abu Gelap, Kuning, Merah STIE MP
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>