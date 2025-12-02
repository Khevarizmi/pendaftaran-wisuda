<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan level mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

// Cek apakah sudah pernah daftar wisuda
$user_id = $_SESSION['user_id'];
$check_sql = "SELECT * FROM pendaftaran_wisuda WHERE user_id = '$user_id'";
$check_result = mysqli_query($conn, $check_sql);
$sudah_daftar = mysqli_num_rows($check_result) > 0;

if ($sudah_daftar) {
    $data_pendaftaran = mysqli_fetch_assoc($check_result);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Sistem Wisuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar: BIRU TUA → ORANGE TUA -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="mahasiswa_dashboard.php">
                <i class="fas fa-graduation-cap"></i> Sistem Wisuda Online
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="mahasiswa_dashboard.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
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
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard Mahasiswa</h2>
                <p class="text-muted">Selamat datang, <strong><?php echo $_SESSION['username']; ?></strong></p>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['message']; unset($_SESSION['message'], $_SESSION['message_type']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!$sudah_daftar): ?>
            <!-- Belum Daftar -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Belum Terdaftar</h4>
                        <p>Anda belum mendaftar untuk acara wisuda. Silakan klik tombol di bawah untuk mendaftar.</p>
                        <hr>
                        <a href="form_daftar_wisuda.php" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Daftar Acara Wisuda
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informasi -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card feature-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Dokumen yang Harus Disiapkan</h5>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>Foto Formal (JPG/PNG, max 2MB)</li>
                                <li>Surat Keterangan Lulus (PDF, max 2MB)</li>
                                <li>Transkrip Nilai (PDF, max 2MB)</li>
                                <li>Kuitansi Pembayaran (PDF/JPG, max 2MB)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card feature-card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Langkah Pendaftaran</h5>
                        </div>
                        <div class="card-body">
                            <ol class="mb-0">
                                <li>Klik tombol "Daftar Acara Wisuda"</li>
                                <li>Isi formulir dengan lengkap</li>
                                <li>Upload semua dokumen persyaratan</li>
                                <li>Submit formulir</li>
                                <li>Tunggu verifikasi dari admin</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Sudah Daftar -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success">
                        <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Pendaftaran Berhasil!</h4>
                        <p>Anda sudah terdaftar untuk acara wisuda. Status pendaftaran Anda saat ini:</p>
                        <hr>
                        <p class="mb-0">
                            Status: 
                            <?php 
                            $badge_class = 'warning';
                            if ($data_pendaftaran['status_verifikasi'] == 'Terverifikasi') {
                                $badge_class = 'success';
                            } elseif ($data_pendaftaran['status_verifikasi'] == 'Ditolak') {
                                $badge_class = 'danger';
                            }
                            ?>
                            <span class="badge bg-<?php echo $badge_class; ?> fs-6">
                                <?php echo $data_pendaftaran['status_verifikasi']; ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detail Pendaftaran -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Foto Formal</h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if (!empty($data_pendaftaran['foto_formal']) && file_exists($data_pendaftaran['foto_formal'])): ?>
                                <img src="<?php echo $data_pendaftaran['foto_formal']; ?>" alt="Foto" class="img-fluid rounded" style="max-height: 250px;">
                            <?php else: ?>
                                <i class="fas fa-image fa-5x text-muted"></i>
                                <p class="text-muted mt-2">Foto tidak tersedia</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card feature-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Data Pendaftaran</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="200"><strong>NPM</strong></td>
                                    <td>: <?php echo $data_pendaftaran['npm']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Lengkap</strong></td>
                                    <td>: <?php echo $data_pendaftaran['nama_lengkap']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Program Studi</strong></td>
                                    <td>: <?php echo $data_pendaftaran['program_studi']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Fakultas</strong></td>
                                    <td>: <?php echo $data_pendaftaran['fakultas']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>IPK</strong></td>
                                    <td>: <span class="badge bg-success"><?php echo $data_pendaftaran['ipk']; ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Tahun Lulus</strong></td>
                                    <td>: <?php echo $data_pendaftaran['tahun_lulus']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Daftar</strong></td>
                                    <td>: <?php echo date('d-m-Y H:i', strtotime($data_pendaftaran['tanggal_daftar'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dokumen Upload -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card feature-card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-file-upload"></i> Dokumen yang Diupload</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-file-pdf text-danger"></i> Surat Keterangan Lulus:</strong><br>
                                    <?php if (!empty($data_pendaftaran['surat_lulus'])): ?>
                                        <a href="<?php echo $data_pendaftaran['surat_lulus']; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-eye"></i> Lihat Dokumen
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada file</span>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-file-pdf text-danger"></i> Transkrip Nilai:</strong><br>
                                    <?php if (!empty($data_pendaftaran['transkrip_nilai'])): ?>
                                        <a href="<?php echo $data_pendaftaran['transkrip_nilai']; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-eye"></i> Lihat Dokumen
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada file</span>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-receipt text-warning"></i> Kuitansi Pembayaran:</strong><br>
                                    <?php if (!empty($data_pendaftaran['kuitansi_pembayaran'])): ?>
                                        <a href="<?php echo $data_pendaftaran['kuitansi_pembayaran']; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-eye"></i> Lihat Dokumen
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada file</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($data_pendaftaran['catatan_admin'])): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-comment"></i> Catatan dari Admin:</h5>
                        <p class="mb-0"><?php echo $data_pendaftaran['catatan_admin']; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pendaftaran Wisuda Online. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>