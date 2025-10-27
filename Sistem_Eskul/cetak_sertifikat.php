<?php
// cetak_sertifikat.php
$page_title = 'Cetak Sertifikat';
require_once 'includes/header.php';

$print_mode = false;
$sertifikat = null;

// Cek sertifikat berdasarkan NIS
if (isset($_POST['nis']) || isset($_GET['nis'])) {
    $nis = $_POST['nis'] ?? $_GET['nis'];
    
    $result = query("
        SELECT u.nis, u.name, u.kelas, e.nama_ekskul, pembina.name as nama_pembina, 
               sert.nomor_sertifikat, sert.tanggal_terbit, sert.keterangan
        FROM users u
        JOIN anggota_ekskul ae ON u.id = ae.user_id
        JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
        LEFT JOIN users pembina ON e.pembina_id = pembina.id
        LEFT JOIN sertifikats sert ON ae.id = sert.anggota_id
        WHERE u.nis = ? AND ae.status = 'diterima' AND u.role = 'siswa'
        ORDER BY sert.tanggal_terbit DESC
        LIMIT 1
    ", [$nis], 's');
    
    if ($result && $result->num_rows > 0) {
        $sertifikat = $result->fetch_assoc();
        
        // Generate nomor sertifikat jika belum ada
        if (!$sertifikat['nomor_sertifikat']) {
            $nomor = 'CERT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $tanggal_terbit = date('Y-m-d');
            
            // Ambil anggota_id
            $anggota = query("
                SELECT ae.id 
                FROM anggota_ekskul ae
                JOIN users u ON ae.user_id = u.id
                WHERE u.nis = ? AND ae.status = 'diterima'
                LIMIT 1
            ", [$nis], 's')->fetch_assoc();
            
            execute("INSERT INTO sertifikats (anggota_id, nomor_sertifikat, tanggal_terbit) VALUES (?, ?, ?)",
                [$anggota['id'], $nomor, $tanggal_terbit], 'iss');
            
            $sertifikat['nomor_sertifikat'] = $nomor;
            $sertifikat['tanggal_terbit'] = $tanggal_terbit;
        }
        
        if (isset($_GET['print'])) {
            $print_mode = true;
        }
    }
}
?>

<?php if (!$print_mode): ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-success text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="bi bi-award-fill"></i> Cetak Sertifikat
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Masukkan NIS Anda untuk mengecek dan mencetak sertifikat
                    </div>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">NIS (Nomor Induk Siswa)</label>
                            <input type="text" name="nis" class="form-control form-control-lg" 
                                   placeholder="Masukkan NIS Anda" required autofocus>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-search"></i> Cek Sertifikat
                            </button>
                            <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>

                    <?php if (isset($_POST['nis']) && !$sertifikat): ?>
                    <div class="alert alert-warning mt-4">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Sertifikat tidak ditemukan. Pastikan Anda sudah terdaftar dan aktif di ekstrakurikuler.
                    </div>
                    <?php endif; ?>

                    <?php if ($sertifikat && !$print_mode): ?>
                    <hr class="my-4">
                    <div class="alert alert-success">
                        <h5><i class="bi bi-check-circle"></i> Sertifikat Ditemukan!</h5>
                        <hr>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="150"><strong>Nama</strong></td>
                                <td>: <?php echo $sertifikat['name']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>NIS</strong></td>
                                <td>: <?php echo $sertifikat['nis']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Kelas</strong></td>
                                <td>: <?php echo $sertifikat['kelas']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Ekstrakurikuler</strong></td>
                                <td>: <?php echo $sertifikat['nama_ekskul']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nomor Sertifikat</strong></td>
                                <td>: <?php echo $sertifikat['nomor_sertifikat']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Terbit</strong></td>
                                <td>: <?php echo formatTanggal($sertifikat['tanggal_terbit']); ?></td>
                            </tr>
                        </table>
                        <div class="d-grid mt-3">
                            <a href="?nis=<?php echo $sertifikat['nis']; ?>&print=1" target="_blank" class="btn btn-primary">
                                <i class="bi bi-printer"></i> Cetak Sertifikat
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Mode Print -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat - <?php echo $sertifikat['name']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            size: A4 landscape;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: flex;
            gap: 10px;
        }
        
        .no-print button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-print {
            background: #198754;
            color: white;
        }
        
        .btn-print:hover {
            background: #146c43;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-close {
            background: #6c757d;
            color: white;
        }
        
        .btn-close:hover {
            background: #5c636a;
        }
        
        .sertifikat-wrapper {
            width: 297mm;
            height: 210mm;
            background: white;
            position: relative;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
        }
        
        /* Border Design */
        .border-outer {
            position: absolute;
            top: 15mm;
            left: 15mm;
            right: 15mm;
            bottom: 15mm;
            border: 6px solid #198754;
            border-radius: 4px;
        }
        
        .border-inner {
            position: absolute;
            top: 18mm;
            left: 18mm;
            right: 18mm;
            bottom: 18mm;
            border: 2px solid #ffc107;
            border-radius: 4px;
        }
        
        /* Corner Ornaments */
        .corner {
            position: absolute;
            width: 70px;
            height: 70px;
            border: 3px solid #ffc107;
        }
        
        .corner-tl {
            top: 24mm;
            left: 24mm;
            border-right: none;
            border-bottom: none;
            border-radius: 4px 0 0 0;
        }
        
        .corner-tr {
            top: 24mm;
            right: 24mm;
            border-left: none;
            border-bottom: none;
            border-radius: 0 4px 0 0;
        }
        
        .corner-bl {
            bottom: 24mm;
            left: 24mm;
            border-right: none;
            border-top: none;
            border-radius: 0 0 0 4px;
        }
        
        .corner-br {
            bottom: 24mm;
            right: 24mm;
            border-left: none;
            border-top: none;
            border-radius: 0 0 4px 0;
        }
        
        /* Content Container */
        .content {
            position: absolute;
            top: 30mm;
            left: 30mm;
            right: 30mm;
            bottom: 30mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        /* Logo */
        .logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #198754, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            border: 4px solid #ffc107;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        /* Header */
        .school-name {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: #198754;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }
        
        .school-address {
            font-size: 13px;
            color: #666;
            margin-bottom: 25px;
            font-weight: 300;
        }
        
        /* Divider Line */
        .divider {
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, transparent, #198754, transparent);
            margin: 15px 0;
        }
        
        /* Title */
        .title {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            font-weight: 700;
            color: #198754;
            margin: 20px 0 15px 0;
            text-transform: uppercase;
            letter-spacing: 8px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.05);
        }
        
        .subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            font-style: italic;
            font-weight: 300;
        }
        
        /* Recipient Section */
        .recipient-name {
            font-family: 'Great Vibes', cursive;
            font-size: 48px;
            color: #198754;
            margin: 25px 0;
            font-weight: 400;
            position: relative;
            display: inline-block;
        }
        
        .recipient-name::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, transparent, #ffc107, transparent);
        }
        
        .recipient-info {
            font-size: 15px;
            color: #555;
            margin: 20px 0;
            line-height: 1.8;
            font-weight: 400;
        }
        
        .recipient-info strong {
            color: #198754;
            font-weight: 600;
        }
        
        /* Achievement Text */
        .achievement-text {
            font-size: 15px;
            color: #666;
            margin: 15px 0;
            line-height: 1.8;
            max-width: 650px;
            font-weight: 300;
        }
        
        .eskul-name {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #198754;
            font-weight: 700;
            margin: 25px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        /* Footer Section */
        .footer {
            position: absolute;
            bottom: 30mm;
            left: 50mm;
            right: 50mm;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-date {
            margin-bottom: 10px;
            font-size: 13px;
            color: #555;
            font-weight: 400;
        }
        
        .signature-title {
            font-weight: 600;
            margin-bottom: 70px;
            font-size: 13px;
            color: #333;
        }
        
        .signature-name {
            font-weight: 700;
            border-top: 2px solid #333;
            padding-top: 10px;
            font-size: 14px;
            color: #198754;
        }
        
        .signature-nip {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
            font-weight: 300;
        }
        
        /* Certificate Number */
        .cert-number {
            position: absolute;
            bottom: 22mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11px;
            color: #999;
            font-weight: 300;
        }
        
        /* Decorative Elements */
        .deco-left, .deco-right {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 150px;
            background: linear-gradient(to bottom, transparent, rgba(25, 135, 84, 0.1), transparent);
        }
        
        .deco-left {
            left: 22mm;
            border-radius: 0 20px 20px 0;
        }
        
        .deco-right {
            right: 22mm;
            border-radius: 20px 0 0 20px;
        }
        
        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none !important;
            }
            .sertifikat-wrapper {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ Print Sertifikat
        </button>
        <button class="btn-close" onclick="window.close()">
            ✖️ Close
        </button>
    </div>

    <!-- Certificate -->
    <div class="sertifikat-wrapper">
        <!-- Borders -->
        <div class="border-outer"></div>
        <div class="border-inner"></div>
        
        <!-- Corner Ornaments -->
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>
        
        <!-- Decorative Side Elements -->
        <div class="deco-left"></div>
        <div class="deco-right"></div>

        <!-- Content -->
        <div class="content">
            <!-- Logo -->
            <div class="logo">🏆</div>
            
            <!-- Header -->
            <div class="school-name">MTSN 1 LEBAK</div>
            <div class="school-address">Jl. Raya Rangkasbitung, Lebak, Banten</div>
            
            <div class="divider"></div>

            <!-- Title -->
            <div class="title">SERTIFIKAT</div>
            <div class="subtitle">Diberikan Kepada</div>

            <!-- Recipient -->
            <div class="recipient-name"><?php echo $sertifikat['name']; ?></div>
            
            <div class="recipient-info">
                NIS: <strong><?php echo $sertifikat['nis']; ?></strong> | Kelas: <strong><?php echo $sertifikat['kelas']; ?></strong>
            </div>
            
            <div class="achievement-text">
                Telah mengikuti kegiatan ekstrakurikuler
            </div>
            
            <div class="eskul-name">
                <?php echo $sertifikat['nama_ekskul']; ?>
            </div>
            
            <div class="achievement-text">
                di MTsN 1 Lebak dengan penuh dedikasi dan tanggung jawab
            </div>
            
            <?php if ($sertifikat['keterangan']): ?>
            <div class="achievement-text" style="font-style: italic; color: #999; font-size: 13px; margin-top: 5px;">
                <?php echo $sertifikat['keterangan']; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="signature-box">
                <div class="signature-date">Lebak, <?php echo formatTanggal($sertifikat['tanggal_terbit']); ?></div>
                <div class="signature-title">Kepala Madrasah</div>
                <div class="signature-name">Drs. H. Ahmad Yani, M.Pd</div>
                <div class="signature-nip">NIP. 197001011997031001</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-date">Mengetahui,</div>
                <div class="signature-title">Pembina Ekstrakurikuler</div>
                <div class="signature-name"><?php echo $sertifikat['nama_pembina'] ?? 'Pembina'; ?></div>
                <div class="signature-nip">&nbsp;</div>
            </div>
        </div>

        <!-- Certificate Number -->
        <div class="cert-number">
            Nomor Sertifikat: <strong><?php echo $sertifikat['nomor_sertifikat']; ?></strong>
        </div>
    </div>
</body>
</html>
<?php exit; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>