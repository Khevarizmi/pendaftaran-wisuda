<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';

if (empty($id)) {
    header("Location: dashboard.php");
    exit();
}

// Ambil data peserta
$sql = "SELECT * FROM peserta_wisuda WHERE id = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: dashboard.php");
    exit();
}

$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peserta Wisuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
                        <a class="nav-link" href="dashboard.php">
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

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-user"></i> Detail Peserta Wisuda</h2>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="200"><strong>NIM</strong></td>
                                    <td>: <?php echo $data['nim']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Lengkap</strong></td>
                                    <td>: <?php echo $data['nama_lengkap']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tempat, Tanggal Lahir</strong></td>
                                    <td>: <?php echo $data['tempat_lahir'] . ', ' . format_tanggal_indonesia($data['tanggal_lahir']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Kelamin</strong></td>
                                    <td>: <?php echo $data['jenis_kelamin']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat</strong></td>
                                    <td>: <?php echo $data['alamat']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>No. Telepon</strong></td>
                                    <td>: <?php echo $data['no_telp']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td>: <?php echo $data['email']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Fakultas</strong></td>
                                    <td>: <?php echo $data['fakultas']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Program Studi</strong></td>
                                    <td>: <?php echo $data['program_studi']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>IPK</strong></td>
                                    <td>: <?php echo $data['ipk']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tahun Lulus</strong></td>
                                    <td>: <?php echo $data['tahun_lulus']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Pendaftaran</strong></td>
                                    <td>: <?php echo date('d-m-Y H:i:s', strtotime($data['tanggal_daftar'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status Pendaftaran</strong></td>
                                    <td>: 
                                        <?php 
                                        $badge_class = 'secondary';
                                        if ($data['status_pendaftaran'] == 'Disetujui') {
                                            $badge_class = 'success';
                                        } elseif ($data['status_pendaftaran'] == 'Pending') {
                                            $badge_class = 'warning';
                                        } elseif ($data['status_pendaftaran'] == 'Ditolak') {
                                            $badge_class = 'danger';
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>">
                                            <?php echo $data['status_pendaftaran']; ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4 text-center">
                            <?php if (!empty($data['foto']) && file_exists($data['foto'])): ?>
                                <img src="<?php echo $data['foto']; ?>" alt="Foto" class="img-fluid rounded shadow" style="max-width: 250px;">
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p>Foto belum di-upload</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <a href="edit.php?id=<?php echo $data['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Data
                        </a>
                        <a href="delete.php?id=<?php echo $data['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                            <i class="fas fa-trash"></i> Hapus Data
                        </a>
                    </div>
                </div>
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