<?php
require_once 'config.php';

$message = "";
$message_type = "";

// Proses registrasi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = clean_input($_POST['email']);
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    $konfirmasi_password = clean_input($_POST['konfirmasi_password']);
    
    // Validasi
    if (empty($email) || empty($username) || empty($password)) {
        $message = "Semua field harus diisi!";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
        $message_type = "danger";
    } elseif ($password !== $konfirmasi_password) {
        $message = "Password dan konfirmasi password tidak cocok!";
        $message_type = "danger";
    } elseif (strlen($password) < 6) {
        $message = "Password minimal 6 karakter!";
        $message_type = "danger";
    } else {
        // Cek apakah email sudah terdaftar
        $check_email = "SELECT * FROM users WHERE email = '$email'";
        $result_email = mysqli_query($conn, $check_email);
        
        if (mysqli_num_rows($result_email) > 0) {
            $message = "Email sudah terdaftar!";
            $message_type = "danger";
        } else {
            // Cek apakah username sudah digunakan
            $check_username = "SELECT * FROM users WHERE username = '$username'";
            $result_username = mysqli_query($conn, $check_username);
            
            if (mysqli_num_rows($result_username) > 0) {
                $message = "Username sudah digunakan!";
                $message_type = "danger";
            } else {
                // Insert user baru
                $password_hash = MD5($password);
                $sql = "INSERT INTO users (email, username, password, level) VALUES ('$email', '$username', '$password_hash', 'mahasiswa')";
                
                if (mysqli_query($conn, $sql)) {
                    $message = "Registrasi berhasil! Silakan login dengan akun Anda.";
                    $message_type = "success";
                    
                    // Redirect ke login setelah 2 detik
                    header("refresh:2;url=login.php");
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $message_type = "danger";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun - Sistem Wisuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar: BIRU TUA → ORANGE TUA -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-graduation-cap"></i> Sistem Wisuda Online
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Card Header: ORANGE TUA → BIRU TUA (dari CSS .login-header) -->
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-user-plus fa-4x mb-3"></i>
                        <h3>Buat Akun Baru</h3>
                        <p class="mb-0">Daftar untuk mengikuti wisuda</p>
                    </div>
                    <div class="login-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="formRegister">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" required placeholder="contoh@email.com">
                                <small class="text-muted">Gunakan email aktif Anda</small>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user"></i> Username
                                </label>
                                <input type="text" class="form-control form-control-lg" id="username" name="username" required placeholder="username">
                                <small class="text-muted">Username untuk login</small>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required placeholder="Minimal 6 karakter">
                            </div>

                            <div class="mb-4">
                                <label for="konfirmasi_password" class="form-label">
                                    <i class="fas fa-lock"></i> Konfirmasi Password
                                </label>
                                <input type="password" class="form-control form-control-lg" id="konfirmasi_password" name="konfirmasi_password" required placeholder="Ketik ulang password">
                            </div>

                            <div class="d-grid gap-2">
                                <!-- Button: ORANGE TUA → BIRU TUA (dari CSS .btn-primary) -->
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus"></i> Daftar
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-muted mb-0">Sudah punya akun?</p>
                            <a href="login.php" class="btn btn-outline-primary mt-2">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </div>
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
    <script>
        document.getElementById('formRegister').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const konfirmasi = document.getElementById('konfirmasi_password').value;
            
            if (password !== konfirmasi) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
        });
    </script>
</body>
</html>