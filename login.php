<?php
// Enable error reporting untuk debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

$error = "";

// Cek koneksi database
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Jika sudah login, redirect ke dashboard sesuai level
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['level'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: mahasiswa_dashboard.php");
    }
    exit();
}

// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = clean_input($_POST['login']); // bisa email atau username
    $password = MD5(clean_input($_POST['password']));
    
    // Cek apakah login menggunakan email atau username
    $sql = "SELECT * FROM users WHERE (email = '$login' OR username = '$login') AND password = '$password'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['level'] = $user['level'];
        
        // Redirect berdasarkan level
        if ($user['level'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: mahasiswa_dashboard.php");
        }
        exit();
    } else {
        $error = "Email/Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Wisuda</title>
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
        <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
            <div class="col-md-5">
                <!-- Card Header: ORANGE TUA → BIRU TUA (dari CSS .login-header) -->
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-user-circle fa-4x mb-3"></i>
                        <h3>Login</h3>
                        <p class="mb-0">Masuk ke akun Anda</p>
                    </div>
                    <div class="login-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="login" class="form-label">
                                    <i class="fas fa-user"></i> Email atau Username
                                </label>
                                <input type="text" class="form-control form-control-lg" id="login" name="login" required placeholder="Email atau username">
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required placeholder="Password">
                            </div>

                            <div class="d-grid gap-2">
                                <!-- Button: ORANGE TUA → BIRU TUA (dari CSS .btn-primary) -->
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-muted mb-2">Belum punya akun?</p>
                            <a href="register.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus"></i> Buat Akun
                            </a>
                        </div>

                        <div class="alert alert-info mt-4" role="alert">
                            <strong>Demo Login Admin:</strong><br>
                            Email: <code>admin@wisuda.ac.id</code><br>
                            Password: <code>admin123</code>
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
</body>
</html>