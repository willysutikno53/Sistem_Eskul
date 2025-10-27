<?php
// admin/eskul/index.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Kelola Ekstrakurikuler';
$current_user = getCurrentUser();

// Ambil semua data eskul
$eskul = query("
    SELECT e.*, 
    u.name as nama_pembina,
    (SELECT COUNT(*) FROM anggota_ekskul WHERE ekstrakurikuler_id = e.id AND status = 'diterima') as jumlah_anggota
    FROM ekstrakurikulers e
    LEFT JOIN users u ON e.pembina_id = u.id
    ORDER BY e.created_at DESC
");
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
                <i class="bi bi-speedometer2"></i> Dashboard Admin
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
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/eskul/index.php">
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
                    <h2><i class="bi bi-grid-fill"></i> Kelola Ekstrakurikuler</h2>
                    <?php if ($current_user['role'] == 'admin'): ?>
                    <a href="<?php echo BASE_URL; ?>admin/eskul/tambah.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Tambah Eskul
                    </a>
                    <?php endif; ?>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari ekstrakurikuler..." onkeyup="searchTable('searchInput', 'eskulTable')">
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="eskulTable">
                                <thead class="table-success">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">Gambar</th>
                                        <th width="20%">Nama Eskul</th>
                                        <th width="15%">Pembina</th>
                                        <th width="10%">Anggota</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($eskul && $eskul->num_rows > 0):
                                        $no = 1;
                                        while ($row = $eskul->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <?php if ($row['gambar']): ?>
                                            <img src="<?php echo UPLOAD_URL . $row['gambar']; ?>" class="img-thumbnail" style="max-width: 80px;">
                                            <?php else: ?>
                                            <img src="https://via.placeholder.com/80" class="img-thumbnail">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $row['nama_ekskul']; ?></td>
                                        <td><?php echo $row['nama_pembina'] ?? '-'; ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo $row['jumlah_anggota']; ?>/<?php echo $row['kuota']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] == 'aktif'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo BASE_URL; ?>admin/eskul/detail.php?id=<?php echo $row['id']; ?>" class="btn btn-info" title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if ($current_user['role'] == 'admin' || $current_user['id'] == $row['pembina_id']): ?>
                                                <a href="<?php echo BASE_URL; ?>admin/eskul/edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php endif; ?>
                                                <?php if ($current_user['role'] == 'admin'): ?>
                                                <a href="<?php echo BASE_URL; ?>admin/eskul/hapus.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirmDelete()" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mt-2">Belum ada data ekstrakurikuler</p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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