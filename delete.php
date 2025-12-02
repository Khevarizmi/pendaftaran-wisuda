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

// Ambil data peserta untuk mendapatkan foto
$sql = "SELECT foto FROM peserta_wisuda WHERE id = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    
    // Hapus foto jika ada
    if (!empty($data['foto']) && file_exists($data['foto'])) {
        unlink($data['foto']);
    }
    
    // Hapus data dari database
    $delete_sql = "DELETE FROM peserta_wisuda WHERE id = '$id'";
    
    if (mysqli_query($conn, $delete_sql)) {
        $_SESSION['message'] = "Data berhasil dihapus!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
}

header("Location: dashboard.php");
exit();
?>