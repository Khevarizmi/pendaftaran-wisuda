<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan level mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";

// Ambil ID
$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';

if (empty($id)) {
    header("Location: mahasiswa_dashboard.php");
    exit();
}

// Ambil data pendaftaran
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM pendaftaran_wisuda WHERE id = '$id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: mahasiswa_dashboard.php");
    exit();
}

$data = mysqli_fetch_assoc($result);

// Cek apakah status ditolak (hanya yang ditolak bisa edit)
if ($data['status_verifikasi'] != 'Ditolak') {
    $_SESSION['message'] = "Anda hanya bisa mengedit data yang ditolak!";
    $_SESSION['message_type'] = "warning";
    header("Location: mahasiswa_dashboard.php");
    exit();
}

// Proses update data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $npm = clean_input($_POST['npm']);
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $tempat_lahir = clean_input($_POST['tempat_lahir']);
    $tanggal_lahir = clean_input($_POST['tanggal_lahir']);
    $jenis_kelamin = clean_input($_POST['jenis_kelamin']);
    $alamat = clean_input($_POST['alamat']);
    $no_telp = clean_input($_POST['no_telp']);
    $email = clean_input($_POST['email']);
    $program_studi = clean_input($_POST['program_studi']);
    $fakultas = clean_input($_POST['fakultas']);
    $ipk = clean_input($_POST['ipk']);
    $tahun_lulus = clean_input($_POST['tahun_lulus']);
    
    // Upload file baru jika ada
    $foto_formal = $data['foto_formal'];
    $surat_lulus = $data['surat_lulus'];
    $transkrip_nilai = $data['transkrip_nilai'];
    $kuitansi_pembayaran = $data['kuitansi_pembayaran'];
    
    $upload_dir = "uploads/";
    $allowed_image = array("jpg", "jpeg", "png");
    $allowed_pdf = array("pdf", "jpg", "jpeg", "png");
    $max_size = 2 * 1024 * 1024;
    
    // Upload Foto Formal (jika ada file baru)
    if (isset($_FILES['foto_formal']) && $_FILES['foto_formal']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["foto_formal"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_image) && $_FILES["foto_formal"]["size"] <= $max_size) {
            // Hapus file lama
            if (!empty($foto_formal) && file_exists($foto_formal)) {
                unlink($foto_formal);
            }
            $filename = "foto_" . $npm . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES["foto_formal"]["tmp_name"], $upload_dir . $filename)) {
                $foto_formal = $upload_dir . $filename;
            }
        }
    }
    
    // Upload Surat Lulus (jika ada file baru)
    if (isset($_FILES['surat_lulus']) && $_FILES['surat_lulus']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["surat_lulus"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_pdf) && $_FILES["surat_lulus"]["size"] <= $max_size) {
            if (!empty($surat_lulus) && file_exists($surat_lulus)) {
                unlink($surat_lulus);
            }
            $filename = "surat_" . $npm . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES["surat_lulus"]["tmp_name"], $upload_dir . $filename)) {
                $surat_lulus = $upload_dir . $filename;
            }
        }
    }
    
    // Upload Transkrip (jika ada file baru)
    if (isset($_FILES['transkrip_nilai']) && $_FILES['transkrip_nilai']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["transkrip_nilai"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_pdf) && $_FILES["transkrip_nilai"]["size"] <= $max_size) {
            if (!empty($transkrip_nilai) && file_exists($transkrip_nilai)) {
                unlink($transkrip_nilai);
            }
            $filename = "transkrip_" . $npm . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES["transkrip_nilai"]["tmp_name"], $upload_dir . $filename)) {
                $transkrip_nilai = $upload_dir . $filename;
            }
        }
    }
    
    // Upload Kuitansi (jika ada file baru)
    if (isset($_FILES['kuitansi_pembayaran']) && $_FILES['kuitansi_pembayaran']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["kuitansi_pembayaran"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_pdf) && $_FILES["kuitansi_pembayaran"]["size"] <= $max_size) {
            if (!empty($kuitansi_pembayaran) && file_exists($kuitansi_pembayaran)) {
                unlink($kuitansi_pembayaran);
            }
            $filename = "kuitansi_" . $npm . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES["kuitansi_pembayaran"]["tmp_name"], $upload_dir . $filename)) {
                $kuitansi_pembayaran = $upload_dir . $filename;
            }
        }
    }
    
    // Update ke database dan ubah status ke Pending
    $sql_update = "UPDATE pendaftaran_wisuda SET 
                   npm = '$npm',
                   nama_lengkap = '$nama_lengkap',
                   tempat_lahir = '$tempat_lahir',
                   tanggal_lahir = '$tanggal_lahir',
                   jenis_kelamin = '$jenis_kelamin',
                   alamat = '$alamat',
                   no_telp = '$no_telp',
                   email = '$email',
                   program_studi = '$program_studi',
                   fakultas = '$fakultas',
                   ipk = '$ipk',
                   tahun_lulus = '$tahun_lulus',
                   foto_formal = '$foto_formal',
                   surat_lulus = '$surat_lulus',
                   transkrip_nilai = '$transkrip_nilai',
                   kuitansi_pembayaran = '$kuitansi_pembayaran',
                   status_verifikasi = 'Pending',
                   catatan_admin = NULL,
                   verified_by = NULL,
                   verified_at = NULL
                   WHERE id = '$id' AND user_id = '$user_id'";
    
    if (mysqli_query($conn, $sql_update)) {
        $_SESSION['message'] = "Data berhasil diperbaiki dan dikirim ulang untuk verifikasi!";
        $_SESSION['message_type'] = "success";
        header("Location: mahasiswa_dashboard.php");
        exit();
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pendaftaran - STIE Mulia Pratama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="mahasiswa_dashboard.php">
                <i class="fas fa-graduation-cap"></i> STIE Mulia Pratama
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="mahasiswa_dashboard.php">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-container">
                    <h2 class="text-center mb-4 text-uppercase">
                        <i class="fas fa-edit"></i> Perbaiki Data Pendaftaran
                    </h2>

                    <?php if (!empty($data['catatan_admin'])): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-comment"></i> Catatan dari Admin:</h5>
                        <p class="mb-0"><strong><?php echo nl2br($data['catatan_admin']); ?></strong></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Petunjuk:</strong> Perbaiki data sesuai catatan admin di atas, lalu klik "Kirim Ulang"
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <h5 class="text-primary mb-3 text-uppercase"><i class="fas fa-user"></i> Data Pribadi</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NPM *</label>
                                <input type="text" class="form-control" name="npm" value="<?php echo $data['npm']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" name="nama_lengkap" value="<?php echo $data['nama_lengkap']; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tempat Lahir *</label>
                                <input type="text" class="form-control" name="tempat_lahir" value="<?php echo $data['tempat_lahir']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir *</label>
                                <input type="date" class="form-control" name="tanggal_lahir" value="<?php echo $data['tanggal_lahir']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin *</label>
                            <select class="form-select" name="jenis_kelamin" required>
                                <option value="Laki-laki" <?php echo ($data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?php echo ($data['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap *</label>
                            <textarea class="form-control" name="alamat" rows="3" required><?php echo $data['alamat']; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Telepon *</label>
                                <input type="tel" class="form-control" name="no_telp" value="<?php echo $data['no_telp']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo $data['email']; ?>" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="text-primary mb-3 text-uppercase"><i class="fas fa-graduation-cap"></i> Data Akademik</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fakultas *</label>
                                <select class="form-select" name="fakultas" required>
                                    <option value="Teknik" <?php echo ($data['fakultas'] == 'Teknik') ? 'selected' : ''; ?>>Teknik</option>
                                    <option value="Ekonomi" <?php echo ($data['fakultas'] == 'Ekonomi') ? 'selected' : ''; ?>>Ekonomi</option>
                                    <option value="Hukum" <?php echo ($data['fakultas'] == 'Hukum') ? 'selected' : ''; ?>>Hukum</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Program Studi *</label>
                                <input type="text" class="form-control" name="program_studi" value="<?php echo $data['program_studi']; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">IPK *</label>
                                <input type="number" class="form-control" name="ipk" step="0.01" min="0" max="4" value="<?php echo $data['ipk']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahun Lulus *</label>
                                <input type="number" class="form-control" name="tahun_lulus" min="2000" max="2030" value="<?php echo $data['tahun_lulus']; ?>" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="text-primary mb-3 text-uppercase"><i class="fas fa-file-upload"></i> Upload Ulang Dokumen</h5>
                        <p class="text-muted">Kosongkan jika tidak ingin mengubah file. Upload file baru jika ada yang perlu diperbaiki.</p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Foto Formal</label>
                                <input type="file" class="form-control" name="foto_formal" accept="image/*">
                                <?php if (!empty($data['foto_formal'])): ?>
                                <small class="text-success">✓ File saat ini: <?php echo basename($data['foto_formal']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Surat Keterangan Lulus</label>
                                <input type="file" class="form-control" name="surat_lulus" accept=".pdf,image/*">
                                <?php if (!empty($data['surat_lulus'])): ?>
                                <small class="text-success">✓ File saat ini: <?php echo basename($data['surat_lulus']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transkrip Nilai</label>
                                <input type="file" class="form-control" name="transkrip_nilai" accept=".pdf,image/*">
                                <?php if (!empty($data['transkrip_nilai'])): ?>
                                <small class="text-success">✓ File saat ini: <?php echo basename($data['transkrip_nilai']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kuitansi Pembayaran</label>
                                <input type="file" class="form-control" name="kuitansi_pembayaran" accept=".pdf,image/*">
                                <?php if (!empty($data['kuitansi_pembayaran'])): ?>
                                <small class="text-success">✓ File saat ini: <?php echo basename($data['kuitansi_pembayaran']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Perhatian:</strong> Setelah Anda klik "Kirim Ulang", status pendaftaran akan kembali menjadi "Pending" dan menunggu verifikasi ulang dari admin.
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane"></i> KIRIM ULANG DATA
                            </button>
                            <a href="mahasiswa_dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 STIE Mulia Pratama. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>