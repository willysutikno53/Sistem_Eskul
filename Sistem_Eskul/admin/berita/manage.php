<?php
// admin/berita/manage.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Kelola Berita';
$current_user = getCurrentUser();

// Hapus berita
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $berita = query("SELECT gambar FROM berita WHERE id = ?", [$id], 'i')->fetch_assoc();
    if ($berita['gambar']) {
        deleteFile($berita['gambar']);
    }
    execute("DELETE FROM berita WHERE id = ?", [$id], 'i');
    setFlash('success', 'Berita berhasil dihapus!');
    redirect('admin/berita/manage.php');
}

// Filter untuk pembina
$where_clause = "";
$params = [];
$types = "";

if ($current_user['role'] == 'pembina') {
    $where_clause = "WHERE e.pembina_id = ?";
    $params = [$current_user['id']];
    $types = "i";
}

// Ambil semua berita
$berita = query("
    SELECT b.*, e.nama_ekskul, u.name as penulis
    FROM berita b
    JOIN ekstrakurikulers e ON b.ekstrakurikuler_id = e.id
    LEFT JOIN users u ON b.user_id = u.id
    $where_clause
    ORDER BY b.created_at DESC
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
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/galeri/index.php">
                            <i class="bi bi-images"></i> Galeri
                        </a>
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/berita/manage.php">
                            <i class="bi bi-newspaper"></i> Berita
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/laporan/index.php">
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

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-newspaper"></i> Kelola Berita</h2>
                    <a href="<?php echo BASE_URL; ?>admin/berita/tambah.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Tambah Berita
                    </a>
                </div>

                <div class="row">
                    <?php 
                    if ($berita && $berita->num_rows > 0):
                        while ($row = $berita->fetch_assoc()):
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <?php if ($row['gambar']): ?>
                            <img src="<?php echo UPLOAD_URL . $row['gambar']; ?>" class="card-img-top" alt="<?php echo $row['judul']; ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                            <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="No Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <span class="badge bg-success mb-2"><?php echo $row['nama_ekskul']; ?></span>
                                <h5 class="card-title"><?php echo $row['judul']; ?></h5>
                                <p class="card-text text-muted small">
                                    <?php echo substr(strip_tags($row['konten']), 0, 100); ?>...
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> <?php echo formatTanggal($row['tanggal_post']); ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-eye"></i> <?php echo $row['views']; ?>
                                    </small>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> <?php echo $row['penulis'] ?? 'Admin'; ?>
                                    </small>
                                </div>
                                <div class="mt-2">
                                    <?php if ($row['is_published']): ?>
                                    <span class="badge bg-success">Published</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Draft</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="btn-group w-100">
                                    <a href="<?php echo BASE_URL; ?>admin/berita/tambah.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
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
                            <i class="bi bi-info-circle fs-1"></i>
                            <p class="mt-2">Belum ada berita. Silakan tambah berita baru.</p>
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