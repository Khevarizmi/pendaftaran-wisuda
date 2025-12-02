<?php
session_start();
require_once 'config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil ID
$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';

if (empty($id)) {
    header("Location: mahasiswa_dashboard.php");
    exit();
}

// Ambil data
$sql = "SELECT * FROM pendaftaran_wisuda WHERE id = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: mahasiswa_dashboard.php");
    exit();
}

$data = mysqli_fetch_assoc($result);

// Cek authorization
if ($_SESSION['level'] == 'mahasiswa' && $data['user_id'] != $_SESSION['user_id']) {
    header("Location: mahasiswa_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Peserta Wisuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
        .kartu-container {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
        }
        .kartu {
            border: 3px solid #FF6B35;
            padding: 30px;
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(255,215,0,0.1) 0%, rgba(255,165,0,0.1) 100%);
        }
        .header-kartu {
            text-align: center;
            border-bottom: 3px solid #004E89;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .foto-kartu {
            width: 150px;
            height: 200px;
            object-fit: cover;
            border: 3px solid #FF6B35;
            border-radius: 10px;
        }
        .barcode {
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 24px;
            letter-spacing: 3px;
            margin-top: 20px;
            padding: 10px;
            background: white;
            border: 2px dashed #FFA500;
        }
    </style>
</head>
<body>
    <div class="no-print text-center p-3 bg-light">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Cetak Kartu
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <div class="kartu-container">
        <div class="kartu">
            <div class="header-kartu">
                <h3 class="mb-0" style="color: #004E89;">UNIVERSITAS</h3>
                <h5 class="mb-0">KARTU PESERTA WISUDA</h5>
                <p class="mb-0">Tahun Akademik <?php echo $data['tahun_lulus']; ?></p>
            </div>

            <div class="row">
                <div class="col-md-4 text-center">
                    <?php if (!empty($data['foto_formal']) && file_exists($data['foto_formal'])): ?>
                        <img src="<?php echo $data['foto_formal']; ?>" alt="Foto" class="foto-kartu mb-3">
                    <?php else: ?>
                        <div class="foto-kartu d-flex align-items-center justify-content-center bg-light">
                            <i class="fas fa-user fa-5x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="barcode">
                        <?php echo $data['npm']; ?>
                    </div>
                </div>

                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>NPM</strong></td>
                            <td><strong>: <?php echo $data['npm']; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong>Nama</strong></td>
                            <td><strong>: <?php echo $data['nama_lengkap']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Tempat, Tgl Lahir</td>
                            <td>: <?php echo $data['tempat_lahir'] . ', ' . date('d-m-Y', strtotime($data['tanggal_lahir'])); ?></td>
                        </tr>
                        <tr>
                            <td>Fakultas</td>
                            <td>: <?php echo $data['fakultas']; ?></td>
                        </tr>
                        <tr>
                            <td>Program Studi</td>
                            <td>: <?php echo $data['program_studi']; ?></td>
                        </tr>
                        <tr>
                            <td>IPK</td>
                            <td>: <strong><?php echo $data['ipk']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Predikat</td>
                            <td>: <strong>
                                <?php 
                                if ($data['ipk'] >= 3.5) echo "Cum Laude";
                                elseif ($data['ipk'] >= 3.0) echo "Sangat Memuaskan";
                                elseif ($data['ipk'] >= 2.75) echo "Memuaskan";
                                else echo "Cukup";
                                ?>
                            </strong></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>: <span style="color: <?php echo $data['status_verifikasi'] == 'Terverifikasi' ? 'green' : 'red'; ?>">
                                <strong><?php echo $data['status_verifikasi']; ?></strong>
                            </span></td>
                        </tr>
                    </table>

                    <div class="alert alert-info mt-3">
                        <small>
                            <strong>Catatan:</strong> Kartu ini harus dibawa saat mengikuti acara wisuda. 
                            Harap datang 30 menit sebelum acara dimulai.
                        </small>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6 text-center">
                    <p class="mb-5">Ketua Panitia Wisuda</p>
                    <p class="mb-0">___________________</p>
                    <p><strong>NIP. ______________</strong></p>
                </div>
                <div class="col-6 text-center">
                    <p class="mb-1">Dicetak tanggal: <?php echo date('d F Y'); ?></p>
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect width='100' height='100' fill='%23FF6B35'/%3E%3Ctext x='50' y='50' font-size='14' text-anchor='middle' fill='white' dy='.3em'%3ESTEMPEL%3C/text%3E%3C/svg%3E" alt="Stempel" style="width: 100px;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>