<?php
// admin/anggota/manage.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Kelola Anggota';
$current_user = getCurrentUser();

// Proses approve/reject pendaftaran
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    
    if ($action == 'approve') {
        $result = execute("UPDATE anggota_ekskul SET status = 'diterima', tanggal_diterima = CURDATE() WHERE id = ?", [$id], 'i');
        if ($result['success']) {
            setFlash('success', 'Pendaftaran berhasil disetujui!');
        }
    } elseif ($action == 'reject') {
        $result = execute("UPDATE anggota_ekskul SET status = 'ditolak' WHERE id = ?", [$id], 'i');
        if ($result['success']) {
            setFlash('success', 'Pendaftaran berhasil ditolak!');
        }
    }
    redirect('admin/anggota/manage.php');
}

// Filter untuk pembina (hanya lihat eskul sendiri)
$where_clause = "";
$params = [];
$types = "";

if ($current_user['role'] == 'pembina') {
    $where_clause = "WHERE e.pembina_id = ?";
    $params = [$current_user['id']];
    $types = "i";
}

// Ambil data anggota
$anggota = query("
    SELECT ae.*, u.name, u.nis, u.kelas, u.jenis_kelamin, u.no_hp, e.nama_ekskul, e.pembina_id
    FROM anggota_ekskul ae
    JOIN users u ON ae.user_id = u.id
    JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
    $where_clause
    ORDER BY ae.created_at DESC
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
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/anggota/manage.php">
                            <i class="bi bi-people-fill"></i> Anggota
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/jadwal/index.php">
                            <i class="bi bi-calendar-check"></i> Jadwal
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/presensi/index.php">
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

                <h2 class="mb-4"><i class="bi bi-people-fill"></i> Kelola Anggota</h2>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#pending">
                            <i class="bi bi-clock"></i> Pending
                            <?php 
                            $count_sql = "SELECT COUNT(*) as total FROM anggota_ekskul ae JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id WHERE ae.status = 'pending'";
                            if ($current_user['role'] == 'pembina') {
                                $count_sql .= " AND e.pembina_id = " . $current_user['id'];
                            }
                            $count_pending = query($count_sql)->fetch_assoc()['total'];
                            if ($count_pending > 0) echo "<span class='badge bg-warning ms-1'>$count_pending</span>";
                            ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#diterima">
                            <i class="bi bi-check-circle"></i> Diterima
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#ditolak">
                            <i class="bi bi-x-circle"></i> Ditolak
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Pending -->
                    <div class="tab-pane fade show active" id="pending">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-warning">
                                            <tr>
                                                <th>No</th>
                                                <th>NIS</th>
                                                <th>Nama</th>
                                                <th>Kelas</th>
                                                <th>Eskul</th>
                                                <th>Tanggal Daftar</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $anggota->data_seek(0);
                                            $no = 1;
                                            $found = false;
                                            while ($row = $anggota->fetch_assoc()):
                                                if ($row['status'] == 'pending'):
                                                    $found = true;
                                            ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $row['nis']; ?></td>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['kelas']; ?></td>
                                                <td><?php echo $row['nama_ekskul']; ?></td>
                                                <td><?php echo formatTanggal($row['tanggal_daftar']); ?></td>
                                                <td>
                                                    <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" title="Setujui">
                                                        <i class="bi bi-check-circle"></i>
                                                    </a>
                                                    <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pendaftaran ini?')" title="Tolak">
                                                        <i class="bi bi-x-circle"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php 
                                                endif;
                                            endwhile;
                                            if (!$found):
                                            ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Tidak ada pendaftaran pending</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Diterima -->
                    <div class="tab-pane fade" id="diterima">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-success">
                                            <tr>
                                                <th>No</th>
                                                <th>NIS</th>
                                                <th>Nama</th>
                                                <th>Kelas</th>
                                                <th>JK</th>
                                                <th>No HP</th>
                                                <th>Eskul</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $anggota->data_seek(0);
                                            $no = 1;
                                            $found = false;
                                            while ($row = $anggota->fetch_assoc()):
                                                if ($row['status'] == 'diterima'):
                                                    $found = true;
                                            ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $row['nis']; ?></td>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['kelas']; ?></td>
                                                <td><?php echo $row['jenis_kelamin']; ?></td>
                                                <td><?php echo $row['no_hp']; ?></td>
                                                <td><?php echo $row['nama_ekskul']; ?></td>
                                                <td><span class="badge bg-success">Diterima</span></td>
                                            </tr>
                                            <?php 
                                                endif;
                                            endwhile;
                                            if (!$found):
                                            ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">Belum ada anggota diterima</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ditolak -->
                    <div class="tab-pane fade" id="ditolak">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-danger">
                                            <tr>
                                                <th>No</th>
                                                <th>NIS</th>
                                                <th>Nama</th>
                                                <th>Kelas</th>
                                                <th>Eskul</th>
                                                <th>Tanggal Daftar</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $anggota->data_seek(0);
                                            $no = 1;
                                            $found = false;
                                            while ($row = $anggota->fetch_assoc()):
                                                if ($row['status'] == 'ditolak'):
                                                    $found = true;
                                            ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $row['nis']; ?></td>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['kelas']; ?></td>
                                                <td><?php echo $row['nama_ekskul']; ?></td>
                                                <td><?php echo formatTanggal($row['tanggal_daftar']); ?></td>
                                                <td><span class="badge bg-danger">Ditolak</span></td>
                                            </tr>
                                            <?php 
                                                endif;
                                            endwhile;
                                            if (!$found):
                                            ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Tidak ada pendaftaran ditolak</td>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>