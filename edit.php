<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";

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

// Proses update data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim = clean_input($_POST['nim']);
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
    $status_pendaftaran = clean_input($_POST['status_pendaftaran']);
    
    // Upload foto baru jika ada
    $foto = $data['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
        $new_filename = $nim . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $allowed_types = array("jpg", "jpeg", "png", "gif");
        $max_size = 2 * 1024 * 1024;
        
        if (!in_array($file_extension, $allowed_types)) {
            $message = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
            $message_type = "danger";
        } elseif ($_FILES["foto"]["size"] > $max_size) {
            $message = "Ukuran file terlalu besar. Maksimal 2MB.";
            $message_type = "danger";
        } else {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                // Hapus foto lama jika ada
                if (!empty($foto) && file_exists($foto)) {
                    unlink($foto);
                }
                $foto = $target_file;
            }
        }
    }
    
    if (empty($message)) {
        // Update data
        $sql = "UPDATE peserta_wisuda SET 
                nim = '$nim',
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
                foto = '$foto',
                status_pendaftaran = '$status_pendaftaran'
                WHERE id = '$id'";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Data berhasil diupdate!";
            $message_type = "success";
            
            // Refresh data
            $result = mysqli_query($conn, "SELECT * FROM peserta_wisuda WHERE id = '$id'");
            $data = mysqli_fetch_assoc($result);
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
    <title>Edit Data Peserta</title>
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
                    <h2 class="text-center mb-4">
                        <i class="fas fa-edit"></i> Edit Data Peserta Wisuda
                    </h2>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nim" class="form-label">NIM *</label>
                                <input type="text" class="form-control" id="nim" name="nim" value="<?php echo $data['nim']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo $data['nama_lengkap']; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tempat_lahir" class="form-label">Tempat Lahir *</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?php echo $data['tempat_lahir']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir *</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo $data['tanggal_lahir']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin *</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="Laki-laki" <?php echo ($data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?php echo ($data['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat Lengkap *</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo $data['alamat']; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="no_telp" class="form-label">No. Telepon *</label>
                                <input type="tel" class="form-control" id="no_telp" name="no_telp" value="<?php echo $data['no_telp']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $data['email']; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fakultas" class="form-label">Fakultas *</label>
                                <select class="form-select" id="fakultas" name="fakultas" required>
                                    <option value="Teknik" <?php echo ($data['fakultas'] == 'Teknik') ? 'selected' : ''; ?>>Teknik</option>
                                    <option value="Ekonomi" <?php echo ($data['fakultas'] == 'Ekonomi') ? 'selected' : ''; ?>>Ekonomi</option>
                                    <option value="Hukum" <?php echo ($data['fakultas'] == 'Hukum') ? 'selected' : ''; ?>>Hukum</option>
                                    <option value="MIPA" <?php echo ($data['fakultas'] == 'MIPA') ? 'selected' : ''; ?>>MIPA</option>
                                    <option value="Kedokteran" <?php echo ($data['fakultas'] == 'Kedokteran') ? 'selected' : ''; ?>>Kedokteran</option>
                                    <option value="Ilmu Sosial dan Politik" <?php echo ($data['fakultas'] == 'Ilmu Sosial dan Politik') ? 'selected' : ''; ?>>Ilmu Sosial dan Politik</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="program_studi" class="form-label">Program Studi *</label>
                                <input type="text" class="form-control" id="program_studi" name="program_studi" value="<?php echo $data['program_studi']; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="ipk" class="form-label">IPK *</label>
                                <input type="number" class="form-control" id="ipk" name="ipk" step="0.01" min="0" max="4" value="<?php echo $data['ipk']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tahun_lulus" class="form-label">Tahun Lulus *</label>
                                <input type="number" class="form-control" id="tahun_lulus" name="tahun_lulus" min="2000" max="2030" value="<?php echo $data['tahun_lulus']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="status_pendaftaran" class="form-label">Status *</label>
                                <select class="form-select" id="status_pendaftaran" name="status_pendaftaran" required>
                                    <option value="Pending" <?php echo ($data['status_pendaftaran'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Disetujui" <?php echo ($data['status_pendaftaran'] == 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                    <option value="Ditolak" <?php echo ($data['status_pendaftaran'] == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="foto" class="form-label">Upload Foto Baru (Kosongkan jika tidak ingin mengubah)</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*" onchange="previewImage(this)">
                            
                            <?php if (!empty($data['foto']) && file_exists($data['foto'])): ?>
                                <div class="mt-2">
                                    <p class="mb-1">Foto Saat Ini:</p>
                                    <img src="<?php echo $data['foto']; ?>" alt="Foto" class="upload-preview">
                                </div>
                            <?php endif; ?>
                            
                            <img id="preview" class="upload-preview mt-2" style="display: none;">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Update Data
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
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
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>