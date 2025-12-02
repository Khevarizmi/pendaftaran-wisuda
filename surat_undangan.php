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

// Cek status harus terverifikasi
if ($data['status_verifikasi'] != 'Terverifikasi') {
    echo "<script>alert('Surat undangan hanya tersedia untuk mahasiswa yang sudah terverifikasi!'); window.close();</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Undangan Wisuda - STIE Mulia Pratama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
        body {
            font-family: 'Times New Roman', serif;
        }
        .surat-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 25mm;
            background: white;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 4px double #C41E3A;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .kop-surat img {
            width: 80px;
            height: 80px;
        }
        .kop-surat h3 {
            margin: 10px 0 5px 0;
            font-size: 24px;
            font-weight: bold;
            color: #C41E3A;
        }
        .kop-surat h4 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            color: #2C3E50;
        }
        .kop-surat p {
            margin: 5px 0;
            font-size: 11px;
            line-height: 1.4;
        }
        .nomor-surat {
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }
        .isi-surat {
            text-align: justify;
            line-height: 1.8;
            margin: 30px 0;
        }
        .isi-surat p {
            margin-bottom: 15px;
        }
        .data-mahasiswa {
            margin: 20px 0 20px 50px;
        }
        .data-mahasiswa table {
            width: 100%;
        }
        .data-mahasiswa td {
            padding: 5px;
        }
        .data-wisuda {
            background: #FFF5F5;
            border-left: 4px solid #C41E3A;
            padding: 20px;
            margin: 20px 0;
        }
        .ttd-section {
            margin-top: 50px;
            text-align: right;
        }
        .ttd-box {
            display: inline-block;
            text-align: center;
            min-width: 250px;
        }
    </style>
</head>
<body>
    <div class="no-print text-center p-3 bg-light">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Cetak Surat
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <div class="surat-container">
        <!-- Kop Surat -->
        <div class="kop-surat">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='%23C41E3A'/%3E%3Ctext x='50' y='60' font-size='40' text-anchor='middle' fill='white' font-weight='bold'%3EMP%3C/text%3E%3C/svg%3E" alt="Logo STIE MP">
            
            <h3>SEKOLAH TINGGI ILMU EKONOMI</h3>
            <h4>MULIA PRATAMA BEKASI</h4>
            <p style="font-style: italic; color: #C41E3A; font-weight: bold;">
                "The Spirit of Education | We Create Your Success"
            </p>
            <p>
                Jl. HM. Joyo Martono No. Kav. 5, Margahayu, Bekasi Timur, Kota Bekasi, Jawa Barat<br>
                Telp: (021) 8835 3599, 8835 4599, 8835 9799 | Email: stiemp@gmail.com<br>
                Website: www.stiemp.ac.id
            </p>
        </div>

        <!-- Nomor Surat -->
        <div class="nomor-surat">
            <u>SURAT UNDANGAN WISUDA</u><br>
            Nomor: <?php echo sprintf("%03d/WISUDA-STIEMP/%s/%d", $data['id'], strtoupper(date('M')), date('Y')); ?>
        </div>

        <!-- Isi Surat -->
        <div class="isi-surat">
            <p>Kepada Yth.<br>
            <strong>Sdr./i <?php echo $data['nama_lengkap']; ?></strong><br>
            <?php echo $data['alamat']; ?></p>

            <p>Dengan hormat,</p>

            <p>Berdasarkan hasil verifikasi data akademik, dengan ini kami mengundang Saudara/i untuk mengikuti <strong>Upacara Wisuda STIE Mulia Pratama</strong> yang akan dilaksanakan pada:</p>

            <div class="data-wisuda">
                <table style="width: 100%;">
                    <tr>
                        <td width="150"><strong>Hari/Tanggal</strong></td>
                        <td>: <strong>Sabtu, 15 Juni 2025</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Waktu</strong></td>
                        <td>: <strong>08.00 WIB - Selesai</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Tempat</strong></td>
                        <td>: <strong>Auditorium STIE Mulia Pratama</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>&nbsp;&nbsp;<em>Jl. HM. Joyo Martono No. Kav. 5, Bekasi Timur</em></td>
                    </tr>
                </table>
            </div>

            <p><strong>Data Wisudawan/Wisudawati:</strong></p>
            
            <div class="data-mahasiswa">
                <table>
                    <tr>
                        <td width="180">NPM</td>
                        <td width="20">:</td>
                        <td><strong><?php echo $data['npm']; ?></strong></td>
                    </tr>
                    <tr>
                        <td>Nama Lengkap</td>
                        <td>:</td>
                        <td><strong><?php echo $data['nama_lengkap']; ?></strong></td>
                    </tr>
                    <tr>
                        <td>Program Studi</td>
                        <td>:</td>
                        <td><?php echo $data['program_studi']; ?></td>
                    </tr>
                    <tr>
                        <td>Fakultas</td>
                        <td>:</td>
                        <td><?php echo $data['fakultas']; ?></td>
                    </tr>
                    <tr>
                        <td>IPK</td>
                        <td>:</td>
                        <td><strong><?php echo $data['ipk']; ?></strong> 
                            (<?php 
                            if ($data['ipk'] >= 3.5) echo "Cum Laude";
                            elseif ($data['ipk'] >= 3.0) echo "Sangat Memuaskan";
                            elseif ($data['ipk'] >= 2.75) echo "Memuaskan";
                            else echo "Cukup";
                            ?>)
                        </td>
                    </tr>
                    <tr>
                        <td>Tahun Lulus</td>
                        <td>:</td>
                        <td><?php echo $data['tahun_lulus']; ?></td>
                    </tr>
                </table>
            </div>

            <p><strong>Ketentuan Mengikuti Wisuda:</strong></p>
            <ol>
                <li>Hadir tepat waktu maksimal pukul 07.30 WIB</li>
                <li>Membawa Kartu Peserta Wisuda yang sudah dicetak</li>
                <li>Berpakaian Toga Wisuda (dapat disewa di kampus)</li>
                <li>Membawa KTM atau identitas diri lainnya</li>
                <li>Mengikuti seluruh rangkaian acara wisuda</li>
                <li>Menjaga ketertiban dan kekhidmatan acara</li>
            </ol>

            <p>Demikian surat undangan ini kami sampaikan. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.</p>
        </div>

        <!-- TTD -->
        <div class="ttd-section">
            <div class="ttd-box">
                <p>Bekasi, <?php echo date('d F Y'); ?><br>
                <strong>Ketua Panitia Wisuda</strong></p>
                
                <div style="margin: 60px 0 10px 0;">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Ccircle cx='40' cy='40' r='38' fill='none' stroke='%23C41E3A' stroke-width='2'/%3E%3Ctext x='40' y='50' font-size='12' text-anchor='middle' fill='%23C41E3A' font-weight='bold'%3ESTEMPEL%3C/text%3E%3C/svg%3E" alt="Stempel" style="width: 80px;">
                </div>
                
                <p><strong><u>Drs. H. Nama Ketua, M.M.</u></strong><br>
                NIP. 123456789012345678</p>
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #ccc; font-size: 10px; text-align: center; color: #666;">
            <p>Dokumen ini dicetak dari Sistem Pendaftaran Wisuda Online STIE Mulia Pratama<br>
            Tanggal cetak: <?php echo date('d F Y H:i:s'); ?> WIB</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>