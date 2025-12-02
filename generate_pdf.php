<?php
// generate_pdf.php - Library untuk generate PDF
// Menggunakan FPDF library (pastikan sudah diinstall)
// Download dari: http://www.fpdf.org/

require_once('fpdf/fpdf.php');

function generateSuratUndangan($data) {
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    
    // Header
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'UNIVERSITAS NEGERI INDONESIA', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, 'Jl. Pendidikan No. 123, Jakarta', 0, 1, 'C');
    $pdf->Cell(0, 8, 'Telp: (021) 1234567 | Email: info@uni.ac.id', 0, 1, 'C');
    
    // Garis
    $pdf->Line(20, 40, 190, 40);
    $pdf->Ln(10);
    
    // Judul
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'SURAT UNDANGAN WISUDA', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 8, 'Nomor: ' . generateNomorSurat($data['id']), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Isi surat
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(0, 7, 'Dengan hormat,', 0, 'L');
    $pdf->Ln(5);
    
    $isi = "Berdasarkan hasil verifikasi dan persetujuan dari pihak akademik, dengan ini kami mengundang:";
    $pdf->MultiCell(0, 7, $isi, 0, 'J');
    $pdf->Ln(5);
    
    // Data mahasiswa
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(40, 7, 'Nama', 0, 0);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(0, 7, $data['nama_lengkap'], 0, 1);
    
    $pdf->Cell(40, 7, 'NIM', 0, 0);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(0, 7, $data['nim'], 0, 1);
    
    $pdf->Cell(40, 7, 'Program Studi', 0, 0);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(0, 7, $data['program_studi'], 0, 1);
    
    $pdf->Cell(40, 7, 'Fakultas', 0, 0);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(0, 7, $data['fakultas'], 0, 1);
    
    $pdf->Cell(40, 7, 'IPK', 0, 0);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(0, 7, $data['ipk'], 0, 1);
    
    $pdf->Ln(8);
    
    // Detail acara
    $pdf->MultiCell(0, 7, 'Untuk menghadiri acara Wisuda Periode ' . $data['tahun_lulus'] . ' yang akan dilaksanakan pada:', 0, 'J');
    $pdf->Ln(3);
    
    $pdf->Cell(40, 7, 'Hari/Tanggal', 0, 0);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(0, 7, 'Sabtu, 15 Juni ' . $data['tahun_lulus'], 0, 1);
    
    $pdf->Cell(40, 7, 'Waktu', 0, 0);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(0, 7, '08.00 WIB s/d Selesai', 0, 1);
    
    $pdf->Cell(40, 7, 'Tempat', 0, 0);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(0, 7, 'Gedung Auditorium Kampus Pusat', 0, 1);
    
    $pdf->Ln(8);
    
    $pdf->MultiCell(0, 7, 'Demikian surat undangan ini kami sampaikan. Atas perhatian dan kehadiran Saudara, kami ucapkan terima kasih.', 0, 'J');
    
    $pdf->Ln(15);
    
    // Tanda tangan
    $pdf->Cell(0, 7, 'Jakarta, ' . date('d F Y'), 0, 1, 'R');
    $pdf->Cell(0, 7, 'Rektor Universitas,', 0, 1, 'R');
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, 'Prof. Dr. H. Ahmad Sulthon, M.Pd', 0, 1, 'R');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 7, 'NIP. 196501011990031001', 0, 1, 'R');
    
    // Simpan file
    $filename = 'surat_undangan_' . $data['nim'] . '_' . time() . '.pdf';
    $filepath = PDF_DIR . $filename;
    $pdf->Output('F', $filepath);
    
    return $filepath;
}

function generateKartuWisuda($data) {
    $pdf = new FPDF('L', 'mm', array(150, 100));
    $pdf->AddPage();
    
    // Background
    $pdf->SetFillColor(102, 126, 234);
    $pdf->Rect(0, 0, 150, 100, 'F');
    
    // Border putih
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->SetLineWidth(2);
    $pdf->Rect(5, 5, 140, 90);
    
    // Header
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetXY(10, 12);
    $pdf->Cell(130, 8, 'KARTU WISUDA', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(10);
    $pdf->Cell(130, 6, 'UNIVERSITAS NEGERI INDONESIA', 0, 1, 'C');
    
    // Area putih untuk data
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(15, 35, 120, 50, 'F');
    
    // Data mahasiswa
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(20, 40);
    $pdf->Cell(110, 6, strtoupper($data['nama_lengkap']), 0, 1);
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetX(20);
    $pdf->Cell(110, 5, 'NIM: ' . $data['nim'], 0, 1);
    
    $pdf->SetX(20);
    $pdf->Cell(110, 5, $data['program_studi'], 0, 1);
    
    $pdf->SetX(20);
    $pdf->Cell(110, 5, $data['fakultas'], 0, 1);
    
    $pdf->Ln(3);
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetX(20);
    $pdf->Cell(110, 5, 'Periode Wisuda: ' . $data['tahun_lulus'], 0, 1);
    
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetX(20);
    $pdf->Cell(110, 5, 'Sabtu, 15 Juni ' . $data['tahun_lulus'] . ' | 08.00 WIB', 0, 1);
    
    $pdf->SetX(20);
    $pdf->Cell(110, 5, 'Gedung Auditorium Kampus Pusat', 0, 1);
    
    // Simpan file
    $filename = 'kartu_wisuda_' . $data['nim'] . '_' . time() . '.pdf';
    $filepath = PDF_DIR . $filename;
    $pdf->Output('F', $filepath);
    
    return $filepath;
}

function generateNomorSurat($id) {
    $tahun = date('Y');
    $bulan = date('m');
    return sprintf('%03d/WISUDA/UNI/%s/%s', $id, $bulan, $tahun);
}
?>