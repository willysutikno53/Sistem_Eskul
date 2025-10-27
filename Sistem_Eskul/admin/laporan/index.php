<?php
// admin/laporan/index.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Laporan';
$current_user = getCurrentUser();

// Ambil daftar eskul untuk filter
$where_eskul = "";
$params_eskul = [];
$types_eskul = "";

if ($current_user['role'] == 'pembina') {
    $where_eskul = "WHERE pembina_id = ?";
    $params_eskul = [$current_user['id']];
    $types_eskul = "i";
}

$eskul_list = query("SELECT id, nama_ekskul FROM ekstrakurikulers $where_eskul ORDER BY nama_ekskul", $params_eskul, $types_eskul);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
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
            <div class="col-md-2 bg-light p-0">
                <div class="sidebar">
                    <nav class="nav flex-column">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/eskul/index.php">
                            <i class="bi bi-grid-fill"></i> Ekstrakurikuler
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/berita/manage.php">
                            <i class="bi bi-newspaper"></i> Berita
                        </a>
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/laporan/index.php">
                            <i class="bi bi-file-earmark-text"></i> Laporan
                        </a>
                    </nav>
                </div>
            </div>

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

                <h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> Laporan</h2>

                <div class="row">
                    <!-- Laporan Anggota per Eskul -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-people-fill text-primary"></i> Laporan Anggota per Eskul
                                </h5>
                                <p class="text-muted">Cetak daftar anggota ekstrakurikuler</p>
                                <form method="GET" action="cetak_anggota.php" target="_blank">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Ekstrakurikuler</label>
                                        <select name="eskul_id" class="form-select" required>
                                            <option value="">-- Pilih Eskul --</option>
                                            <?php 
                                            $eskul_list->data_seek(0);
                                            while ($eskul = $eskul_list->fetch_assoc()): 
                                            ?>
                                            <option value="<?php echo $eskul['id']; ?>">
                                                <?php echo $eskul['nama_ekskul']; ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-printer"></i> Cetak Laporan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Laporan Rekap Semua Eskul -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-grid-fill text-success"></i> Laporan Rekap Eskul
                                </h5>
                                <p class="text-muted">Cetak rekap semua ekstrakurikuler dan jumlah anggota</p>
                                <a href="cetak_rekap.php" target="_blank" class="btn btn-success w-100">
                                    <i class="bi bi-printer"></i> Cetak Laporan
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Laporan Pendaftaran -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-clipboard-check text-warning"></i> Laporan Pendaftaran
                                </h5>
                                <p class="text-muted">Cetak laporan pendaftaran berdasarkan periode</p>
                                <form method="GET" action="cetak_pendaftaran.php" target="_blank">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Dari Tanggal</label>
                                            <input type="date" name="dari" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Sampai Tanggal</label>
                                            <input type="date" name="sampai" class="form-control" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="bi bi-printer"></i> Cetak Laporan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Export Data -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-download text-info"></i> Export Data
                                </h5>
                                <p class="text-muted">Download data dalam format Excel (CSV)</p>
                                <div class="d-grid gap-2">
                                    <a href="export_eskul.php" class="btn btn-outline-info">
                                        <i class="bi bi-file-earmark-excel"></i> Export Data Eskul
                                    </a>
                                    <a href="export_anggota.php" class="btn btn-outline-info">
                                        <i class="bi bi-file-earmark-excel"></i> Export Data Anggota
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Statistik -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-bar-chart-fill"></i> Statistik Singkat
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $stats = query("
                                    SELECT 
                                        e.nama_ekskul,
                                        COUNT(ae.id) as total_anggota,
                                        e.kuota,
                                        ROUND((COUNT(ae.id) / e.kuota * 100), 2) as persentase
                                    FROM ekstrakurikulers e
                                    LEFT JOIN anggota_ekskul ae ON e.id = ae.ekstrakurikuler_id AND ae.status = 'diterima'
                                    WHERE e.status = 'aktif'
                                    GROUP BY e.id
                                    ORDER BY total_anggota DESC
                                ");
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Ekstrakurikuler</th>
                                                <th>Total Anggota</th>
                                                <th>Kuota</th>
                                                <th>Persentase</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $stats->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo $row['nama_ekskul']; ?></strong></td>
                                                <td><?php echo $row['total_anggota']; ?></td>
                                                <td><?php echo $row['kuota']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 25px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                            style="width: <?php echo $row['persentase']; ?>%" 
                                                            aria-valuenow="<?php echo $row['persentase']; ?>" 
                                                            aria-valuemin="0" aria-valuemax="100">
                                                            <?php echo $row['persentase']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($row['persentase'] >= 80): ?>
                                                        <span class="badge bg-danger">Hampir Penuh</span>
                                                    <?php elseif ($row['persentase'] >= 50): ?>
                                                        <span class="badge bg-warning">Setengah</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Tersedia</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>