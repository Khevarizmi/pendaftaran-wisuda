<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan level admin
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";

// Proses kirim email
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $penerima = clean_input($_POST['penerima']);
    $subjek = clean_input($_POST['subjek']);
    $pesan = clean_input($_POST['pesan']);
    
    // Ambil email berdasarkan penerima
    if ($penerima == 'all') {
        $sql = "SELECT p.email FROM pendaftaran_wisuda p";
    } elseif ($penerima == 'verified') {
        $sql = "SELECT p.email FROM pendaftaran_wisuda p WHERE p.status_verifikasi = 'Terverifikasi'";
    } elseif ($penerima == 'pending') {
        $sql = "SELECT p.email FROM pendaftaran_wisuda p WHERE p.status_verifikasi = 'Pending'";
    } else {
        $sql = "SELECT p.email FROM pendaftaran_wisuda p WHERE p.status_verifikasi = 'Ditolak'";
    }
    
    $result = mysqli_query($conn, $sql);
    $email_list = [];
    
    while($row = mysqli_fetch_assoc($result)) {
        $email_list[] = $row['email'];
    }
    
    if (count($email_list) > 0) {
        // Simulasi kirim email (untuk demo)
        // Dalam production, gunakan PHPMailer atau SMTP
        
        $to = implode(", ", $email_list);
        $headers = "From: admin@wisuda.ac.id\r\n";
        $headers .= "Reply-To: admin@wisuda.ac.id\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $email_body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { padding: 20px; background: #f8f9fa; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .content { background: white; padding: 20px; margin: 20px 0; }
                .footer { text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Sistem Pendaftaran Wisuda Online</h2>
                </div>
                <div class='content'>
                    <p>$pesan</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Sistem Pendaftaran Wisuda. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Simpan log email
        $log = "Tanggal: " . date('Y-m-d H:i:s') . "\n";
        $log .= "Penerima: $penerima (" . count($email_list) . " email)\n";
        $log .= "Subjek: $subjek\n";
        $log .= "Email List: " . implode(", ", $email_list) . "\n\n";
        
        file_put_contents('email_log.txt', $log, FILE_APPEND);
        
        // Simulasi berhasil kirim
        $message = "Email berhasil dikirim ke " . count($email_list) . " penerima!";
        $message_type = "success";
        
        // NOTE: Uncomment untuk production dengan PHPMailer
        // if(mail($to, $subjek, $email_body, $headers)) {
        //     $message = "Email berhasil dikirim!";
        //     $message_type = "success";
        // } else {
        //     $message = "Gagal mengirim email!";
        //     $message_type = "danger";
        // }
    } else {
        $message = "Tidak ada penerima yang dipilih!";
        $message_type = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Email - Admin</title>
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
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <h2 class="text-center mb-4">
                        <i class="fas fa-envelope"></i> Kirim Email Notifikasi
                    </h2>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Penerima *</label>
                            <select class="form-select" name="penerima" required>
                                <option value="">Pilih Penerima</option>
                                <option value="all">Semua Peserta</option>
                                <option value="verified">Hanya Terverifikasi</option>
                                <option value="pending">Hanya Pending</option>
                                <option value="rejected">Hanya Ditolak</option>
                            </select>
                            <small class="text-muted">Email akan dikirim ke semua mahasiswa dalam kategori yang dipilih</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subjek Email *</label>
                            <input type="text" class="form-control" name="subjek" required placeholder="Contoh: Informasi Acara Wisuda">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pesan Email *</label>
                            <textarea class="form-control" name="pesan" rows="8" required placeholder="Tulis pesan email di sini..."></textarea>
                            <small class="text-muted">Gunakan HTML untuk format yang lebih baik</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <strong>Template Contoh:</strong>
                            <pre class="mb-0 mt-2">Kepada Yth. Peserta Wisuda,

Dengan ini kami informasikan bahwa acara wisuda akan dilaksanakan pada:
Hari/Tanggal: Sabtu, 15 Juni 2025
Waktu: 08.00 WIB
Tempat: Auditorium Universitas

Mohon untuk hadir tepat waktu dan membawa kartu peserta wisuda.

Terima kasih.</pre>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Kirim Email
                            </button>
                            <a href="admin_dashboard.php" class="btn btn-secondary">
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
</body>
</html>