<?php
// admin/dashboard.php
require_once '../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Dashboard';
$current_user = getCurrentUser();

// Statistik
$total_eskul = query("SELECT COUNT(*) as total FROM ekstrakurikulers WHERE status = 'aktif'")->fetch_assoc()['total'];
$total_siswa = query("SELECT COUNT(*) as total FROM users WHERE role = 'siswa' AND is_active = 1")->fetch_assoc()['total'];
$total_anggota = query("SELECT COUNT(*) as total FROM anggota_ekskul WHERE status = 'diterima'")->fetch_assoc()['total'];
$total_pembina = query("SELECT COUNT(*) as total FROM users WHERE role = 'pembina' AND is_active = 1")->fetch_assoc()['total'];

// Pendaftaran terbaru
$pendaftaran_baru = query("
    SELECT ae.*, u.name, u.kelas, e.nama_ekskul
    FROM anggota_ekskul ae
    JOIN users u ON ae.user_id = u.id
    JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
    WHERE ae.status = 'pending'
    ORDER BY ae.created_at DESC
    LIMIT 5
");

// Eskul populer
$eskul_populer = query("
    SELECT e.nama_ekskul, COUNT(ae.id) as jumlah_anggota
    FROM ekstrakurikulers e
    LEFT JOIN anggota_ekskul ae ON e.id = ae.ekstrakurikuler_id AND ae.status = 'diterima'
    WHERE e.status = 'aktif'
    GROUP BY e.id
    ORDER BY jumlah_anggota DESC
    LIMIT 5
");

// Presensi hari ini
$presensi_hari_ini = query("
    SELECT COUNT(*) as total FROM presensis WHERE tanggal = CURDATE()
")->fetch_assoc()['total'];

// Berita terbaru
$berita_terbaru = query("
    SELECT b.*, e.nama_ekskul, u.name as penulis
    FROM berita b
    JOIN ekstrakurikulers e ON b.ekstrakurikuler_id = e.id
    LEFT JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - MTsN 1 Lebak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-success sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <div class="d-flex align-items-center text-white">
                <span class="badge bg-light text-success me-2"><?php echo ucfirst($current_user['role']); ?></span>
                <span class="me-3">
                    <i class="bi bi-person-circle"></i> <?php echo $current_user['name']; ?>
                </span>
                <a href="<?php echo BASE_URL; ?>admin/logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-light p-0">
                <div class="sidebar">
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/eskul/index.php">
                            <i class="bi bi-grid-fill"></i> Ekstrakurikuler
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/anggota/manage.php">
                            <i class="bi bi-people-fill"></i> Anggota
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/jadwal/index.php">
                            <i class="bi bi-calendar-check"></i> Jadwal
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/presensi/index.php">
                            <i class="bi bi-clipboard-check"></i> Presensi
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/prestasi/index.php">
                            <i class="bi bi-trophy-fill"></i> Prestasi
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/berita/manage.php">
                            <i class="bi bi-newspaper"></i> Berita
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/galeri/index.php">
                            <i class="bi bi-images"></i> Galeri
                        </a>
                        <?php if ($current_user['role'] == 'admin'): ?>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/users/index.php">
                            <i class="bi bi-person-gear"></i> Users
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/keuangan/index.php">
                            <i class="bi bi-cash-stack"></i> Keuangan
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/inventaris/index.php">
                            <i class="bi bi-box-seam"></i> Inventaris
                        </a>
                        <?php endif; ?>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/laporan/index.php">
                            <i class="bi bi-file-earmark-text"></i> Laporan
                        </a>
                        <hr class="text-white my-2">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>">
                            <i class="bi bi-house-fill"></i> Lihat Website
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <?php
                $flash = getFlash();
                if ($flash):
                ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <h2 class="mb-4">Dashboard</h2>

                <!-- Statistik Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Ekstrakurikuler</h6>
                                        <h2 class="mb-0 counter" data-target="<?php echo $total_eskul; ?>">0</h2>
                                    </div>
                                    <div class="bg-success text-white rounded-circle p-3">
                                        <i class="bi bi-grid-fill fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Siswa</h6>
                                        <h2 class="mb-0 counter" data-target="<?php echo $total_siswa; ?>">0</h2>
                                    </div>
                                    <div class="bg-primary text-white rounded-circle p-3">
                                        <i class="bi bi-people-fill fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Anggota Aktif</h6>
                                        <h2 class="mb-0 counter" data-target="<?php echo $total_anggota; ?>">0</h2>
                                    </div>
                                    <div class="bg-warning text-white rounded-circle p-3">
                                        <i class="bi bi-person-check-fill fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Presensi Hari Ini</h6>
                                        <h2 class="mb-0 counter" data-target="<?php echo $presensi_hari_ini; ?>">0</h2>
                                    </div>
                                    <div class="bg-info text-white rounded-circle p-3">
                                        <i class="bi bi-clipboard-check fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Pendaftaran Baru -->
                    <div class="col-md-7 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-bell-fill text-warning"></i> Pendaftaran Baru
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($pendaftaran_baru && $pendaftaran_baru->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Siswa</th>
                                                <th>Kelas</th>
                                                <th>Eskul</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $pendaftaran_baru->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['kelas']; ?></td>
                                                <td><?php echo $row['nama_ekskul']; ?></td>
                                                <td>
                                                    <a href="<?php echo BASE_URL; ?>admin/anggota/manage.php" class="btn btn-sm btn-success">
                                                        Lihat
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-muted text-center py-3">Tidak ada pendaftaran baru</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Eskul Populer -->
                    <div class="col-md-5 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-star-fill text-warning"></i> Eskul Populer
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($eskul_populer && $eskul_populer->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php 
                                    $no = 1;
                                    while ($row = $eskul_populer->fetch_assoc()): 
                                    ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <span class="badge bg-success">#<?php echo $no++; ?></span>
                                            <span class="ms-2"><?php echo $row['nama_ekskul']; ?></span>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo $row['jumlah_anggota']; ?> anggota
                                        </span>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-muted text-center py-3">Belum ada data</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Berita Terbaru -->
                <?php if ($berita_terbaru && $berita_terbaru->num_rows > 0): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-newspaper"></i> Berita Terbaru
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php while ($berita = $berita_terbaru->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <span class="badge bg-success mb-2"><?php echo $berita['nama_ekskul']; ?></span>
                                        <h6><?php echo substr($berita['judul'], 0, 50); ?>...</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-person"></i> <?php echo $berita['penulis'] ?? 'Admin'; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>