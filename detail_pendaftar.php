<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan level admin
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';

if (empty($id)) {
    header("Location: admin_dashboard.php");
    exit();
}

// Ambil data pendaftar
$sql = "SELECT p.*, u.username, u.email as user_email 
        FROM pendaftaran_wisuda p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: admin_dashboard.php");
    exit();
}

$data = mysqli_fetch_assoc($result);

// Proses verifikasi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $catatan = clean_input($_POST['catatan']);
    $alasan_penolakan = clean_input($_POST['alasan_penolakan']);
    
    $verified_by = $_SESSION['user_id'];
    $verified_at = date('Y-m-d H:i:s');
    
    if ($action == 'verifikasi') {
        $status = 'Terverifikasi';
        $dapat_edit = 0; // Tidak bisa edit lagi
        
        // Generate Surat Undangan dan Kartu Wisuda
        $surat_filename = "surat_undangan_" . $data['npm'] . "_" . time() . ".pdf";
        $kartu_filename = "kartu_wisuda_" . $data['npm'] . "_" . time() . ".pdf";
        
        // Simpan path file (akan di-generate saat mahasiswa download)
        $surat_undangan = "uploads/surat/" . $surat_filename;
        $kartu_wisuda = "uploads/kartu/" . $kartu_filename;
        
        // Buat folder jika belum ada
        if (!file_exists('uploads/surat/')) {
            mkdir('uploads/surat/', 0777, true);
        }
        if (!file_exists('uploads/kartu/')) {
            mkdir('uploads/kartu/', 0777, true);
        }
        
        // Set tanggal wisuda (contoh: 3 bulan dari sekarang)
        $tanggal_wisuda = date('Y-m-d', strtotime('+3 months'));
        
        $update_sql = "UPDATE pendaftaran_wisuda SET 
                       status_verifikasi = '$status',
                       catatan_admin = '$catatan',
                       alasan_penolakan = NULL,
                       verified_by = '$verified_by',
                       verified_at = '$verified_at',
                       dapat_edit = $dapat_edit,
                       surat_undangan = '$surat_undangan',
                       kartu_wisuda = '$kartu_wisuda',
                       tanggal_wisuda = '$tanggal_wisuda'
                       WHERE id = '$id'";
        
        $message = "Data mahasiswa berhasil diverifikasi! Surat undangan dan kartu wisuda telah dibuat.";
        
    } elseif ($action == 'tolak') {
        $status = 'Ditolak';
        $dapat_edit = 1; // Bisa edit untuk revisi
        
        if (empty($alasan_penolakan)) {
            $message = "Alasan penolakan harus diisi!";
            $message_type = "danger";
        } else {
            // Hitung jumlah revisi
            $update_sql = "UPDATE pendaftaran_wisuda SET 
                           status_verifikasi = '$status',
                           catatan_admin = '$catatan',
                           alasan_penolakan = '$alasan_penolakan',
                           verified_by = '$verified_by',
                           verified_at = '$verified_at',
                           dapat_edit = $dapat_edit,
                           jumlah_revisi = jumlah_revisi + 1,
                           surat_undangan = NULL,
                           kartu_wisuda = NULL,
                           tanggal_wisuda = NULL
                           WHERE id = '$id'";
            
            $message = "Data mahasiswa ditolak dan dapat melakukan revisi!";
        }
    } elseif ($action == 'ubah_status') {
        // Admin mengubah status yang sudah ada
        $status_baru = clean_input($_POST['status_baru']);
        
        if ($status_baru == 'Terverifikasi') {
            $dapat_edit = 0;
            // Generate ulang surat dan kartu jika belum ada
            if (empty($data['surat_undangan'])) {
                $surat_filename = "surat_undangan_" . $data['npm'] . "_" . time() . ".pdf";
                $kartu_filename = "kartu_wisuda_" . $data['npm'] . "_" . time() . ".pdf";
                $surat_undangan = "uploads/surat/" . $surat_filename;
                $kartu_wisuda = "uploads/kartu/" . $kartu_filename;
                $tanggal_wisuda = date('Y-m-d', strtotime('+3 months'));
                
                $update_sql = "UPDATE pendaftaran_wisuda SET 
                               status_verifikasi = '$status_baru',
                               catatan_admin = '$catatan',
                               verified_by = '$verified_by',
                               verified_at = '$verified_at',
                               dapat_edit = $dapat_edit,
                               surat_undangan = '$surat_undangan',
                               kartu_wisuda = '$kartu_wisuda',
                               tanggal_wisuda = '$tanggal_wisuda'
                               WHERE id = '$id'";
            } else {
                $update_sql = "UPDATE pendaftaran_wisuda SET 
                               status_verifikasi = '$status_baru',
                               catatan_admin = '$catatan',
                               verified_by = '$verified_by',
                               verified_at = '$verified_at',
                               dapat_edit = $dapat_edit
                               WHERE id = '$id'";
            }
        } else {
            $dapat_edit = 1;
            $update_sql = "UPDATE pendaftaran_wisuda SET 
                           status_verifikasi = '$status_baru',
                           catatan_admin = '$catatan',
                           alasan_penolakan = '$alasan_penolakan',
                           verified_by = '$verified_by',
                           verified_at = '$verified_at',
                           dapat_edit = $dapat_edit,
                           jumlah_revisi = jumlah_revisi + 1
                           WHERE id = '$id'";
        }
        
        $message = "Status berhasil diubah ke " . $status_baru . "!";
    }
    
    if (isset($update_sql) && empty($message_type)) {
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['message'] = $message;
            $_SESSION['message_type'] = "success";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftar - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(90deg, #004E89 0%, #FF6B35 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-graduation-cap"></i> Sistem Wisuda Online - Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin_dashboard.php">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user-graduate"></i> Detail Pendaftar Wisuda</h2>
                    <span class="badge bg-<?php 
                        echo $data['status_verifikasi'] == 'Terverifikasi' ? 'success' : 
                            ($data['status_verifikasi'] == 'Ditolak' ? 'danger' : 'warning'); 
                    ?> fs-5">
                        <?php echo $data['status_verifikasi']; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Foto Formal -->
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <div class="card-header text-white" style="background: linear-gradient(90deg, #004E89 0%, #FF6B35 100%);">
                        <h5 class="mb-0"><i class="fas fa-camera"></i> Foto Formal</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($data['foto_formal']) && file_exists($data['foto_formal'])): ?>
                            <img src="<?php echo $data['foto_formal']; ?>" alt="Foto" class="img-fluid rounded" style="max-height: 300px;">
                            <a href="<?php echo $data['foto_formal']; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-3">
                                <i class="fas fa-download"></i> Download
                            </a>
                        <?php else: ?>
                            <i class="fas fa-image fa-5x text-muted"></i>
                            <p class="text-muted mt-2">Tidak ada foto</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Data Pribadi & Akademik -->
            <div class="col-md-8 mb-4">
                <div class="card feature-card">
                    <div class="card-header text-white" style="background: linear-gradient(90deg, #004E89 0%, #FF6B35 100%);">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Data Pendaftar</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary">Data Pribadi:</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="200"><strong>NPM</strong></td>
                                <td>: <?php echo $data['npm']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nama Lengkap</strong></td>
                                <td>: <?php echo $data['nama_lengkap']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>TTL</strong></td>
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
                        </table>

                        <hr>
                        <h6 class="text-primary">Data Akademik:</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="200"><strong>Fakultas</strong></td>
                                <td>: <?php echo $data['fakultas']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Program Studi</strong></td>
                                <td>: <?php echo $data['program_studi']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>IPK</strong></td>
                                <td>: <span class="badge bg-success fs-6"><?php echo $data['ipk']; ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Tahun Lulus</strong></td>
                                <td>: <?php echo $data['tahun_lulus']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Daftar</strong></td>
                                <td>: <?php echo date('d M Y, H:i', strtotime($data['tanggal_daftar'])); ?> WIB</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dokumen Upload -->
        <div class="row">
            <div class="col-12">
                <div class="card feature-card">
                    <div class="card-header text-white" style="background: linear-gradient(90deg, #28a745 0%, #FFD700 100%);">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> Dokumen Persyaratan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                        <h6>Surat Keterangan Lulus</h6>
                                        <?php if (!empty($data['surat_lulus'])): ?>
                                            <a href="<?php echo $data['surat_lulus']; ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> Lihat Dokumen
                                            </a>
                                        <?php else: ?>
                                            <p class="text-muted">Tidak ada file</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                        <h6>Transkrip Nilai</h6>
                                        <?php if (!empty($data['transkrip_nilai'])): ?>
                                            <a href="<?php echo $data['transkrip_nilai']; ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> Lihat Dokumen
                                            </a>
                                        <?php else: ?>
                                            <p class="text-muted">Tidak ada file</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-receipt fa-3x text-warning mb-2"></i>
                                        <h6>Kuitansi Pembayaran</h6>
                                        <?php if (!empty($data['kuitansi_pembayaran'])): ?>
                                            <a href="<?php echo $data['kuitansi_pembayaran']; ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> Lihat Dokumen
                                            </a>
                                        <?php else: ?>
                                            <p class="text-muted">Tidak ada file</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Verifikasi/Ubah Status -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card feature-card">
                    <div class="card-header <?php echo $data['status_verifikasi'] == 'Pending' ? 'bg-warning text-dark' : 'bg-info text-white'; ?>">
                        <h5 class="mb-0">
                            <i class="fas fa-edit"></i> 
                            <?php echo $data['status_verifikasi'] == 'Pending' ? 'Verifikasi Data' : 'Ubah Status Verifikasi'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($data['status_verifikasi'] == 'Pending'): ?>
                            <!-- Form Verifikasi Pertama Kali -->
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Catatan untuk Mahasiswa (Opsional)</label>
                                    <textarea class="form-control" name="catatan" rows="2" placeholder="Catatan umum..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Alasan Penolakan (Wajib jika menolak)</label>
                                    <textarea class="form-control" name="alasan_penolakan" rows="4" placeholder="Jelaskan kesalahan data yang harus diperbaiki mahasiswa...

Contoh:
- Foto formal tidak sesuai (latar belakang harus merah/biru)
- Transkrip nilai tidak lengkap (kurang semester 7)
- Kuitansi pembayaran tidak terbaca dengan jelas"></textarea>
                                    <small class="text-danger">* Akan ditampilkan ke mahasiswa jika data ditolak</small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" name="action" value="verifikasi" class="btn btn-success btn-lg">
                                        <i class="fas fa-check-circle"></i> Verifikasi & Setujui
                                    </button>
                                    <button type="submit" name="action" value="tolak" class="btn btn-danger btn-lg" onclick="return confirm('Yakin menolak? Mahasiswa akan diminta revisi.')">
                                        <i class="fas fa-times-circle"></i> Tolak & Minta Revisi
                                    </button>
                                    <a href="admin_dashboard.php" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </form>
                        
                        <?php else: ?>
                            <!-- Form Ubah Status -->
                            <div class="alert alert-info mb-3">
                                <h6><i class="fas fa-info-circle"></i> Status Saat Ini:</h6>
                                <p class="mb-2"><strong>Status:</strong> 
                                    <span class="badge bg-<?php echo $data['status_verifikasi'] == 'Terverifikasi' ? 'success' : 'danger'; ?>">
                                        <?php echo $data['status_verifikasi']; ?>
                                    </span>
                                </p>
                                <?php if (!empty($data['catatan_admin'])): ?>
                                    <p class="mb-2"><strong>Catatan:</strong> <?php echo $data['catatan_admin']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($data['alasan_penolakan'])): ?>
                                    <p class="mb-2"><strong>Alasan Penolakan:</strong><br><?php echo nl2br($data['alasan_penolakan']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($data['verified_at'])): ?>
                                    <p class="mb-0"><strong>Waktu:</strong> <?php echo date('d M Y, H:i', strtotime($data['verified_at'])); ?> WIB</p>
                                <?php endif; ?>
                                <?php if ($data['jumlah_revisi'] > 0): ?>
                                    <p class="mb-0"><strong>Revisi Ke:</strong> <?php echo $data['jumlah_revisi']; ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ubah Status Ke:</label>
                                    <select class="form-select" name="status_baru" required>
                                        <option value="">-- Pilih Status Baru --</option>
                                        <option value="Terverifikasi" <?php echo $data['status_verifikasi'] == 'Terverifikasi' ? 'selected' : ''; ?>>
                                            Terverifikasi (Disetujui)
                                        </option>
                                        <option value="Ditolak" <?php echo $data['status_verifikasi'] == 'Ditolak' ? 'selected' : ''; ?>>
                                            Ditolak (Minta Revisi)
                                        </option>
                                        <option value="Pending">Pending (Sedang Ditinjau)</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Update Catatan (Opsional)</label>
                                    <textarea class="form-control" name="catatan" rows="2"><?php echo $data['catatan_admin']; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Alasan Penolakan (Jika status diubah ke Ditolak)</label>
                                    <textarea class="form-control" name="alasan_penolakan" rows="4"><?php echo $data['alasan_penolakan']; ?></textarea>
                                    <small class="text-danger">* Wajib diisi jika mengubah status ke Ditolak</small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" name="action" value="ubah_status" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Simpan Perubahan Status
                                    </button>
                                    <a href="admin_dashboard.php" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pendaftaran Wisuda Online. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>