<?php
// admin/prestasi/index.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Kelola Prestasi';
$current_user = getCurrentUser();

// Hapus prestasi
if (isset($_GET['delete'])) {
    $prestasi = query("SELECT sertifikat FROM prestasis WHERE id = ?", [$_GET['delete']], 'i')->fetch_assoc();
    if ($prestasi['sertifikat']) {
        deleteFile($prestasi['sertifikat']);
    }
    execute("DELETE FROM prestasis WHERE id = ?", [$_GET['delete']], 'i');
    setFlash('success', 'Prestasi berhasil dihapus!');
    redirect('admin/prestasi/index.php');
}

// Filter
$where_clause = "";
$params = [];
$types = "";

if ($current_user['role'] == 'pembina') {
    $where_clause = "WHERE e.pembina_id = ?";
    $params = [$current_user['id']];
    $types = "i";
}

// Ambil prestasi
$prestasi = query("
    SELECT p.*, e.nama_ekskul, u.name as nama_siswa, u.kelas
    FROM prestasis p
    JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
    LEFT JOIN anggota_ekskul ae ON p.anggota_id = ae.id
    LEFT JOIN users u ON ae.user_id = u.id
    $where_clause
    ORDER BY p.tanggal DESC
", $params, $types);
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
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/prestasi/index.php">
                            <i class="bi bi-trophy-fill"></i> Prestasi
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/galeri/index.php">
                            <i class="bi bi-images"></i> Galeri
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
                    <h2><i class="bi bi-trophy-fill"></i> Kelola Prestasi</h2>
                    <a href="<?php echo BASE_URL; ?>admin/prestasi/tambah.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Tambah Prestasi
                    </a>
                </div>

                <div class="row">
                    <?php 
                    if ($prestasi && $prestasi->num_rows > 0):
                        while ($row = $prestasi->fetch_assoc()):
                            $badge_color = [
                                'internasional' => 'danger',
                                'nasional' => 'primary',
                                'provinsi' => 'success',
                                'kabupaten' => 'info',
                                'kecamatan' => 'warning',
                                'sekolah' => 'secondary'
                            ];
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-<?php echo $badge_color[$row['tingkat']] ?? 'secondary'; ?>">
                                        <?php echo ucfirst($row['tingkat']); ?>
                                    </span>
                                    <span class="badge bg-success"><?php echo $row['nama_ekskul']; ?></span>
                                </div>
                                
                                <h5 class="card-title"><?php echo $row['nama_prestasi']; ?></h5>
                                
                                <div class="mb-2">
                                    <strong class="text-warning">
                                        <i class="bi bi-award-fill"></i> <?php echo $row['peringkat'] ?? 'Peserta'; ?>
                                    </strong>
                                </div>

                                <?php if ($row['nama_siswa']): ?>
                                <p class="mb-1">
                                    <i class="bi bi-person"></i> <strong><?php echo $row['nama_siswa']; ?></strong> 
                                    (<?php echo $row['kelas']; ?>)
                                </p>
                                <?php endif; ?>

                                <p class="mb-2">
                                    <i class="bi bi-calendar"></i> <?php echo formatTanggal($row['tanggal']); ?>
                                </p>

                                <?php if ($row['penyelenggara']): ?>
                                <p class="mb-2">
                                    <i class="bi bi-building"></i> <?php echo $row['penyelenggara']; ?>
                                </p>
                                <?php endif; ?>

                                <?php if ($row['deskripsi']): ?>
                                <p class="text-muted small">
                                    <?php echo substr($row['deskripsi'], 0, 100); ?>...
                                </p>
                                <?php endif; ?>

                                <?php if ($row['sertifikat']): ?>
                                <a href="<?php echo UPLOAD_URL . $row['sertifikat']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark-pdf"></i> Lihat Sertifikat
                                </a>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="btn-group w-100">
                                    <a href="<?php echo BASE_URL; ?>admin/prestasi/tambah.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="bi bi-trophy fs-1"></i>
                            <p class="mt-3 mb-0">Belum ada prestasi yang tercatat</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>