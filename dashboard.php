<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data peserta wisuda
$sql = "SELECT * FROM peserta_wisuda ORDER BY tanggal_daftar DESC";
$result = mysqli_query($conn, $sql);

// Hitung statistik
$total_peserta = mysqli_num_rows($result);
$sql_pending = "SELECT COUNT(*) as total FROM peserta_wisuda WHERE status_pendaftaran = 'Pending'";
$result_pending = mysqli_query($conn, $sql_pending);
$pending = mysqli_fetch_assoc($result_pending)['total'];

$sql_disetujui = "SELECT COUNT(*) as total FROM peserta_wisuda WHERE status_pendaftaran = 'Disetujui'";
$result_disetujui = mysqli_query($conn, $sql_disetujui);
$disetujui = mysqli_fetch_assoc($result_disetujui)['total'];

// Reset result untuk tampilan tabel
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Wisuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap"></i> Sistem Wisuda
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['nama']; ?>
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
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-0"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h2>
                <p class="text-muted">Selamat datang, <?php echo $_SESSION['nama']; ?></p>
            </div>
        </div>

        <?php 
        // Tampilkan pesan jika ada
        if (isset($_SESSION['message'])): 
        ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistik Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card feature-card text-center">
                    <div class="card-body">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $total_peserta; ?></h3>
                        <p class="text-muted mb-0">Total Peserta</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card text-center">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="color: #ffc107;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $pending; ?></h3>
                        <p class="text-muted mb-0">Menunggu Verifikasi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card text-center">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="color: #28a745;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo $disetujui; ?></h3>
                        <p class="text-muted mb-0">Disetujui</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data Peserta -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="fas fa-list"></i> Data Peserta Wisuda</h4>
                <a href="daftar.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Peserta
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Program Studi</th>
                            <th>Fakultas</th>
                            <th>IPK</th>
                            <th>Tahun Lulus</th>
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
                            <td><?php echo $row['nim']; ?></td>
                            <td><?php echo $row['nama_lengkap']; ?></td>
                            <td><?php echo $row['program_studi']; ?></td>
                            <td><?php echo $row['fakultas']; ?></td>
                            <td><?php echo $row['ipk']; ?></td>
                            <td><?php echo $row['tahun_lulus']; ?></td>
                            <td>
                                <?php 
                                $badge_class = 'secondary';
                                if ($row['status_pendaftaran'] == 'Disetujui') {
                                    $badge_class = 'success';
                                } elseif ($row['status_pendaftaran'] == 'Pending') {
                                    $badge_class = 'warning';
                                } elseif ($row['status_pendaftaran'] == 'Ditolak') {
                                    $badge_class = 'danger';
                                }
                                ?>
                                <span class="badge bg-<?php echo $badge_class; ?>">
                                    <?php echo $row['status_pendaftaran']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm btn-action">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm btn-action">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="9" class="text-center">Belum ada data peserta wisuda</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pendaftaran Wisuda. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>