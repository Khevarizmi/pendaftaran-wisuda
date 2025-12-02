<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan level mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

// Cek apakah sudah pernah daftar
$user_id = $_SESSION['user_id'];
$check_sql = "SELECT * FROM pendaftaran_wisuda WHERE user_id = '$user_id'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    header("Location: mahasiswa_dashboard.php");
    exit();
}

$message = "";
$message_type = "";

// Proses form
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
    
    // Upload files
    $foto_formal = "";
    $surat_lulus = "";
    $transkrip_nilai = "";
    $kuitansi_pembayaran = "";
    
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_image = array("jpg", "jpeg", "png");
    $allowed_pdf = array("pdf", "jpg", "jpeg", "png");
    $max_size = 2 * 1024 * 1024; // 2MB
    
    // Upload Foto Formal
    if (isset($_FILES['foto_formal']) && $_FILES['foto_formal']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["foto_formal"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_image) && $_FILES["foto_formal"]["size"] <= $max_size) {
            $filename = "foto_" . $npm . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES["foto_formal"]["tmp_name"], $upload_dir . $filename)) {
                $foto_formal = $upload_dir . $filename;
            }
        }
    }
    
    // Upload Surat Lulus
    if (isset($_FILES['surat_lulus']) && $_FILES['surat_lulus']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["surat_lulus"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_pdf) && $_FILES["surat_lulus"]["size"] <= $max_size) {
            $filename = "surat_" . $npm . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES["surat_lulus"]["tmp_name"], $upload_dir . $filename)) {
                $surat_lulus = $upload_dir . $filename;
            }
        }
    }
    
    // Upload Transkrip
    if (isset($_FILES['transkrip_nilai']) && $_FILES['transkrip_nilai']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["transkrip_nilai"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_pdf) && $_FILES["transkrip_nilai"]["size"] <= $max_size) {
            $filename = "transkrip_" . $npm . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES["transkrip_nilai"]["tmp_name"], $upload_dir . $filename)) {
                $transkrip_nilai = $upload_dir . $filename;
            }
        }
    }
    
    // Upload Kuitansi
    if (isset($_FILES['kuitansi_pembayaran']) && $_FILES['kuitansi_pembayaran']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["kuitansi_pembayaran"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_pdf) && $_FILES["kuitansi_pembayaran"]["size"] <= $max_size) {
            $filename = "kuitansi_" . $npm . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES["kuitansi_pembayaran"]["tmp_name"], $upload_dir . $filename)) {
                $kuitansi_pembayaran = $upload_dir . $filename;
            }
        }
    }
    
    // Insert ke database
    $sql = "INSERT INTO pendaftaran_wisuda (user_id, npm, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, no_telp, email, program_studi, fakultas, ipk, tahun_lulus, foto_formal, surat_lulus, transkrip_nilai, kuitansi_pembayaran) 
            VALUES ('$user_id', '$npm', '$nama_lengkap', '$tempat_lahir', '$tanggal_lahir', '$jenis_kelamin', '$alamat', '$no_telp', '$email', '$program_studi', '$fakultas', '$ipk', '$tahun_lulus', '$foto_formal', '$surat_lulus', '$transkrip_nilai', '$kuitansi_pembayaran')";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Pendaftaran wisuda berhasil! Silakan tunggu verifikasi dari admin.";
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
    <title>Form Pendaftaran Wisuda</title>
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
                    <h2 class="text-center mb-4">
                        <!-- Heading: BIRU TUA -->
                        <i class="fas fa-edit"></i> Form Pendaftaran Wisuda
                    </h2>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="formDaftar">
                        <h5 class="text-primary mb-3"><i class="fas fa-user"></i> Data Pribadi</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NPM *</label>
                                <input type="text" class="form-control" name="npm" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" name="nama_lengkap" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tempat Lahir *</label>
                                <input type="text" class="form-control" name="tempat_lahir" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir *</label>
                                <input type="date" class="form-control" name="tanggal_lahir" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin *</label>
                            <select class="form-select" name="jenis_kelamin" required>
                                <option value="">Pilih</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap *</label>
                            <textarea class="form-control" name="alamat" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Telepon *</label>
                                <input type="tel" class="form-control" name="no_telp" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo $_SESSION['email']; ?>" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-graduation-cap"></i> Data Akademik</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fakultas *</label>
                                <select class="form-select" name="fakultas" required>
                                    <option value="">Pilih Fakultas</option>
                                    <option value="Teknik">Teknik</option>
                                    <option value="Ekonomi">Ekonomi</option>
                                    <option value="Hukum">Hukum</option>
                                    <option value="MIPA">MIPA</option>
                                    <option value="Kedokteran">Kedokteran</option>
                                    <option value="Ilmu Sosial dan Politik">Ilmu Sosial dan Politik</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Program Studi *</label>
                                <input type="text" class="form-control" name="program_studi" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">IPK *</label>
                                <input type="number" class="form-control" name="ipk" step="0.01" min="0" max="4" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahun Lulus *</label>
                                <input type="number" class="form-control" name="tahun_lulus" min="2000" max="2030" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-file-upload"></i> Upload Dokumen</h5>
                        <p class="text-muted">Format: JPG/PNG untuk foto, PDF/JPG/PNG untuk dokumen. Maksimal 2MB per file.</p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Foto Formal * <span class="text-danger">(Wajib)</span></label>
                                <input type="file" class="form-control" name="foto_formal" accept="image/*" required onchange="previewFile(this, 'preview1')">
                                <small class="text-muted">Pas foto 4x6 latar merah/biru</small>
                                <img id="preview1" class="file-preview mt-2" style="display:none;">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Surat Keterangan Lulus * <span class="text-danger">(Wajib)</span></label>
                                <input type="file" class="form-control" name="surat_lulus" accept=".pdf,image/*" required>
                                <small class="text-muted">Surat dari fakultas</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transkrip Nilai * <span class="text-danger">(Wajib)</span></label>
                                <input type="file" class="form-control" name="transkrip_nilai" accept=".pdf,image/*" required>
                                <small class="text-muted">Transkrip lengkap</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kuitansi Pembayaran * <span class="text-danger">(Wajib)</span></label>
                                <input type="file" class="form-control" name="kuitansi_pembayaran" accept=".pdf,image/*" required>
                                <small class="text-muted">Bukti bayar wisuda</small>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Perhatian:</strong> Pastikan semua dokumen sudah benar dan jelas. Data yang sudah disubmit tidak bisa diubah.
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Submit Pendaftaran
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
            <p class="mb-0">&copy; 2025 Sistem Pendaftaran Wisuda Online. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewFile(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Validasi ukuran file
        document.querySelectorAll('input[type="file"]').forEach(function(input) {
            input.addEventListener('change', function() {
                if (this.files[0].size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB.');
                    this.value = '';
                }
            });
        });
    </script>
</body>
</html>