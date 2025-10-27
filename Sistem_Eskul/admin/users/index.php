<?php
// admin/users/index.php
require_once '../../config/database.php';
requireRole(['admin']);

$page_title = 'Kelola Users';
$current_user = getCurrentUser();

// Hapus user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($id != $current_user['id']) {
        execute("DELETE FROM users WHERE id = ?", [$id], 'i');
        setFlash('success', 'User berhasil dihapus!');
    } else {
        setFlash('danger', 'Tidak dapat menghapus akun sendiri!');
    }
    redirect('admin/users/index.php');
}

// Toggle status
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    execute("UPDATE users SET is_active = NOT is_active WHERE id = ?", [$id], 'i');
    setFlash('success', 'Status user berhasil diupdate!');
    redirect('admin/users/index.php');
}

// Filter
$role_filter = $_GET['role'] ?? '';

$where = "";
$params = [];
$types = "";

if ($role_filter) {
    $where = "WHERE role = ?";
    $params = [$role_filter];
    $types = "s";
}

$users = query("SELECT * FROM users $where ORDER BY created_at DESC", $params, $types);
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
                <span class="badge bg-light text-success me-2">Admin</span>
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
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/users/index.php">
                            <i class="bi bi-person-gear"></i> Users
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/keuangan/index.php">
                            <i class="bi bi-cash-stack"></i> Keuangan
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/inventaris/index.php">
                            <i class="bi bi-box-seam"></i> Inventaris
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
                    <h2><i class="bi bi-person-gear"></i> Kelola Users</h2>
                    <a href="<?php echo BASE_URL; ?>admin/users/tambah.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Tambah User
                    </a>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-10">
                                    <select name="role" class="form-select">
                                        <option value="">Semua Role</option>
                                        <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="pembina" <?php echo $role_filter == 'pembina' ? 'selected' : ''; ?>>Pembina</option>
                                        <option value="siswa" <?php echo $role_filter == 'siswa' ? 'selected' : ''; ?>>Siswa</option>
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

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-success">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Email/NIS</th>
                                        <th>Role</th>
                                        <th>Kelas</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($users && $users->num_rows > 0):
                                        $no = 1;
                                        while ($row = $users->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['email'] ?? $row['nis']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['role'] == 'admin' ? 'danger' : ($row['role'] == 'pembina' ? 'primary' : 'info'); ?>">
                                                <?php echo ucfirst($row['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $row['kelas'] ?? '-'; ?></td>
                                        <td>
                                            <?php if ($row['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?toggle=<?php echo $row['id']; ?>" class="btn btn-<?php echo $row['is_active'] ? 'warning' : 'success'; ?>" title="Toggle Status">
                                                    <i class="bi bi-<?php echo $row['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                </a>
                                                <?php if ($row['id'] != $current_user['id']): ?>
                                                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirmDelete()" title="Hapus">
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
                                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada data user</td>
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