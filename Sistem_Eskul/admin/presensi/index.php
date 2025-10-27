<?php
// admin/presensi/index.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Kelola Presensi';
$current_user = getCurrentUser();

// Filter
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$eskul_filter = $_GET['eskul'] ?? '';

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

// Ambil presensi
if ($eskul_filter) {
    $presensi = query("
        SELECT p.*, u.name, u.nis, u.kelas, e.nama_ekskul
        FROM presensis p
        JOIN anggota_ekskul ae ON p.anggota_id = ae.id
        JOIN users u ON ae.user_id = u.id
        JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
        WHERE p.tanggal = ? AND ae.ekstrakurikuler_id = ?
        ORDER BY u.name
    ", [$tanggal, $eskul_filter], 'si');
} else {
    $presensi = query("
        SELECT p.*, u.name, u.nis, u.kelas, e.nama_ekskul
        FROM presensis p
        JOIN anggota_ekskul ae ON p.anggota_id = ae.id
        JOIN users u ON ae.user_id = u.id
        JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
        WHERE p.tanggal = ?
        ORDER BY e.nama_ekskul, u.name
    ", [$tanggal], 's');
}
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
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/anggota/manage.php">
                            <i class="bi bi-people-fill"></i> Anggota
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/jadwal/index.php">
                            <i class="bi bi-calendar-check"></i> Jadwal
                        </a>
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/presensi/index.php">
                            <i class="bi bi-clipboard-check"></i> Presensi
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

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-clipboard-check"></i> Kelola Presensi</h2>
                    <a href="<?php echo BASE_URL; ?>admin/presensi/input.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Input Presensi
                    </a>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" name="tanggal" class="form-control" value="<?php echo $tanggal; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ekstrakurikuler</label>
                                    <select name="eskul" class="form-select">
                                        <option value="">Semua Ekstrakurikuler</option>
                                        <?php 
                                        $eskul_list->data_seek(0);
                                        while ($eskul = $eskul_list->fetch_assoc()): 
                                        ?>
                                        <option value="<?php echo $eskul['id']; ?>" <?php echo $eskul_filter == $eskul['id'] ? 'selected' : ''; ?>>
                                            <?php echo $eskul['nama_ekskul']; ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Data Presensi -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Data Presensi: <?php echo formatTanggal($tanggal); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-success">
                                    <tr>
                                        <th>No</th>
                                        <th>NIS</th>
                                        <th>Nama</th>
                                        <th>Kelas</th>
                                        <th>Ekstrakurikuler</th>
                                        <th>Status</th>
                                        <th>Waktu</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($presensi && $presensi->num_rows > 0):
                                        $no = 1;
                                        while ($row = $presensi->fetch_assoc()):
                                            $badge_class = [
                                                'hadir' => 'success',
                                                'izin' => 'warning',
                                                'sakit' => 'info',
                                                'alpha' => 'danger'
                                            ];
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $row['nis']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['kelas']; ?></td>
                                        <td><?php echo $row['nama_ekskul']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $badge_class[$row['status']] ?? 'secondary'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $row['waktu_presensi'] ? date('H:i', strtotime($row['waktu_presensi'])) : '-'; ?></td>
                                        <td><?php echo $row['keterangan'] ?? '-'; ?></td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mt-2">Tidak ada data presensi untuk tanggal ini</p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($presensi && $presensi->num_rows > 0): ?>
                        <!-- Statistik -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h6>Ringkasan:</h6>
                                <?php
                                $presensi->data_seek(0);
                                $stats = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0];
                                while ($row = $presensi->fetch_assoc()) {
                                    $stats[$row['status']]++;
                                }
                                ?>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h3><?php echo $stats['hadir']; ?></h3>
                                                <p class="mb-0">Hadir</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h3><?php echo $stats['izin']; ?></h3>
                                                <p class="mb-0">Izin</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h3><?php echo $stats['sakit']; ?></h3>
                                                <p class="mb-0">Sakit</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-danger text-white">
                                            <div class="card-body text-center">
                                                <h3><?php echo $stats['alpha']; ?></h3>
                                                <p class="mb-0">Alpha</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>