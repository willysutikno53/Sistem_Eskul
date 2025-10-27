<?php
// admin/galeri/index.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Kelola Galeri';
$current_user = getCurrentUser();

// Hapus galeri
if (isset($_GET['delete'])) {
    $galeri = query("SELECT gambar FROM galeris WHERE id = ?", [$_GET['delete']], 'i')->fetch_assoc();
    if ($galeri['gambar']) {
        deleteFile($galeri['gambar']);
    }
    execute("DELETE FROM galeris WHERE id = ?", [$_GET['delete']], 'i');
    setFlash('success', 'Foto berhasil dihapus!');
    redirect('admin/galeri/index.php');
}

// Toggle status
if (isset($_GET['toggle']) && isset($_GET['status'])) {
    $status = $_GET['status'] == '1' ? 0 : 1;
    execute("UPDATE galeris SET is_active = ? WHERE id = ?", [$status, $_GET['toggle']], 'ii');
    setFlash('success', 'Status berhasil diupdate!');
    redirect('admin/galeri/index.php');
}

// Filter
$eskul_filter = $_GET['eskul'] ?? '';

$where_clause = "";
$params = [];
$types = "";

if ($current_user['role'] == 'pembina') {
    $where_clause = "WHERE e.pembina_id = ?";
    $params = [$current_user['id']];
    $types = "i";
    
    if ($eskul_filter) {
        $where_clause .= " AND g.ekstrakurikuler_id = ?";
        $params[] = $eskul_filter;
        $types .= "i";
    }
} elseif ($eskul_filter) {
    $where_clause = "WHERE g.ekstrakurikuler_id = ?";
    $params = [$eskul_filter];
    $types = "i";
}

// Ambil galeri
$galeri = query("
    SELECT g.*, e.nama_ekskul
    FROM galeris g
    JOIN ekstrakurikulers e ON g.ekstrakurikuler_id = e.id
    $where_clause
    ORDER BY g.tanggal_upload DESC, g.urutan ASC
", $params, $types);

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
    <style>
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
        }
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .gallery-item:hover img {
            transform: scale(1.1);
        }
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 1rem;
        }
        .gallery-actions {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
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
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/prestasi/index.php">
                            <i class="bi bi-trophy-fill"></i> Prestasi
                        </a>
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/galeri/index.php">
                            <i class="bi bi-images"></i> Galeri
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/berita/manage.php">
                            <i class="bi bi-newspaper"></i> Berita
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
                    <h2><i class="bi bi-images"></i> Kelola Galeri</h2>
                    <a href="<?php echo BASE_URL; ?>admin/galeri/upload.php" class="btn btn-success">
                        <i class="bi bi-cloud-upload"></i> Upload Foto
                    </a>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-10">
                                    <select name="eskul" class="form-select">
                                        <option value="">Semua Ekstrakurikuler</option>
                                        <?php while ($eskul = $eskul_list->fetch_assoc()): ?>
                                        <option value="<?php echo $eskul['id']; ?>" <?php echo $eskul_filter == $eskul['id'] ? 'selected' : ''; ?>>
                                            <?php echo $eskul['nama_ekskul']; ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Galeri -->
                <div class="row g-3">
                    <?php 
                    if ($galeri && $galeri->num_rows > 0):
                        while ($row = $galeri->fetch_assoc()):
                    ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="gallery-item card border-0 shadow-sm">
                            <img src="<?php echo UPLOAD_URL . $row['gambar']; ?>" alt="<?php echo htmlspecialchars($row['judul']); ?>">
                            
                            <div class="gallery-actions">
                                <div class="btn-group-vertical">
                                    <a href="?toggle=<?php echo $row['id']; ?>&status=<?php echo $row['is_active']; ?>" 
                                       class="btn btn-sm btn-<?php echo $row['is_active'] ? 'success' : 'secondary'; ?>" 
                                       title="<?php echo $row['is_active'] ? 'Aktif' : 'Nonaktif'; ?>">
                                        <i class="bi bi-<?php echo $row['is_active'] ? 'eye' : 'eye-slash'; ?>"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirmDelete()" 
                                       title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="gallery-overlay">
                                <span class="badge bg-success mb-1"><?php echo $row['nama_ekskul']; ?></span>
                                <h6 class="mb-0"><?php echo htmlspecialchars($row['judul']); ?></h6>
                                <?php if ($row['deskripsi']): ?>
                                <small><?php echo substr($row['deskripsi'], 0, 50); ?>...</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="bi bi-image fs-1"></i>
                            <p class="mt-3 mb-0">Belum ada foto di galeri</p>
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