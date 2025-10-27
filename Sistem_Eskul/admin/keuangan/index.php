<?php
// admin/keuangan/index.php
require_once '../../config/database.php';
requireRole(['admin']);

$page_title = 'Kelola Keuangan';
$current_user = getCurrentUser();

// Hapus transaksi
if (isset($_GET['delete'])) {
    $keuangan = query("SELECT bukti FROM keuangan WHERE id = ?", [$_GET['delete']], 'i')->fetch_assoc();
    if ($keuangan['bukti']) {
        deleteFile($keuangan['bukti']);
    }
    execute("DELETE FROM keuangan WHERE id = ?", [$_GET['delete']], 'i');
    setFlash('success', 'Transaksi berhasil dihapus!');
    redirect('admin/keuangan/index.php');
}

// Filter
$eskul_filter = $_GET['eskul'] ?? '';
$bulan = $_GET['bulan'] ?? date('Y-m');

$where = "WHERE DATE_FORMAT(k.tanggal, '%Y-%m') = ?";
$params = [$bulan];
$types = "s";

if ($eskul_filter) {
    $where .= " AND k.ekstrakurikuler_id = ?";
    $params[] = $eskul_filter;
    $types .= "i";
}

// Ambil transaksi
$keuangan = query("
    SELECT k.*, e.nama_ekskul, u.name as input_by
    FROM keuangan k
    JOIN ekstrakurikulers e ON k.ekstrakurikuler_id = e.id
    LEFT JOIN users u ON k.user_id = u.id
    $where
    ORDER BY k.tanggal DESC, k.created_at DESC
", $params, $types);

// Hitung total
$totals = query("
    SELECT 
        SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END) as total_masuk,
        SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END) as total_keluar
    FROM keuangan k
    $where
", $params, $types)->fetch_assoc();

$saldo = $totals['total_masuk'] - $totals['total_keluar'];

// List eskul
$eskul_list = query("SELECT id, nama_ekskul FROM ekstrakurikulers ORDER BY nama_ekskul");

// Tambah transaksi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ekstrakurikuler_id = $_POST['ekstrakurikuler_id'];
    $jenis = $_POST['jenis'];
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $deskripsi = $_POST['deskripsi'];
    
    $bukti = '';
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $upload = uploadFile($_FILES['bukti'], 'keuangan');
        if ($upload['success']) {
            $bukti = $upload['filename'];
        }
    }
    
    $sql = "INSERT INTO keuangan (ekstrakurikuler_id, user_id, jenis, kategori, jumlah, tanggal, deskripsi, bukti) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $result = execute($sql, [$ekstrakurikuler_id, $current_user['id'], $jenis, $kategori, $jumlah, $tanggal, $deskripsi, $bukti], 'iissdsss');
    
    if ($result['success']) {
        setFlash('success', 'Transaksi berhasil ditambahkan!');
        redirect('admin/keuangan/index.php');
    }
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
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/users/index.php">
                            <i class="bi bi-person-gear"></i> Users
                        </a>
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/keuangan/index.php">
                            <i class="bi bi-cash-stack"></i> Keuangan
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/inventaris/index.php">
                            <i class="bi bi-box-seam"></i> Inventaris
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
                    <h2><i class="bi bi-cash-stack"></i> Kelola Keuangan</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="bi bi-plus-circle"></i> Tambah Transaksi
                    </button>
                </div>

                <!-- Ringkasan -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Total Pemasukan</h6>
                                <h3>Rp <?php echo number_format($totals['total_masuk'], 0, ',', '.'); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6>Total Pengeluaran</h6>
                                <h3>Rp <?php echo number_format($totals['total_keluar'], 0, ',', '.'); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-<?php echo $saldo >= 0 ? 'primary' : 'warning'; ?> text-white">
                            <div class="card-body">
                                <h6>Saldo</h6>
                                <h3>Rp <?php echo number_format($saldo, 0, ',', '.'); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-5">
                                    <input type="month" name="bulan" class="form-control" value="<?php echo $bulan; ?>">
                                </div>
                                <div class="col-md-5">
                                    <select name="eskul" class="form-select">
                                        <option value="">Semua Eskul</option>
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

                <!-- Tabel Transaksi -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-success">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Eskul</th>
                                        <th>Kategori</th>
                                        <th>Deskripsi</th>
                                        <th>Pemasukan</th>
                                        <th>Pengeluaran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($keuangan && $keuangan->num_rows > 0):
                                        while ($row = $keuangan->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo formatTanggal($row['tanggal']); ?></td>
                                        <td><?php echo $row['nama_ekskul']; ?></td>
                                        <td><?php echo $row['kategori']; ?></td>
                                        <td><?php echo $row['deskripsi']; ?></td>
                                        <td class="text-success">
                                            <?php echo $row['jenis'] == 'pemasukan' ? 'Rp ' . number_format($row['jumlah'], 0, ',', '.') : '-'; ?>
                                        </td>
                                        <td class="text-danger">
                                            <?php echo $row['jenis'] == 'pengeluaran' ? 'Rp ' . number_format($row['jumlah'], 0, ',', '.') : '-'; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['bukti']): ?>
                                            <a href="<?php echo UPLOAD_URL . $row['bukti']; ?>" target="_blank" class="btn btn-sm btn-info me-1">
                                                <i class="bi bi-file-earmark"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">Tidak ada transaksi</td>
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

    <!-- Modal Tambah -->
    <div class="modal fade" id="tambahModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Tambah Transaksi</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Ekstrakurikuler *</label>
                            <select name="ekstrakurikuler_id" class="form-select" required>
                                <option value="">Pilih Eskul</option>
                                <?php 
                                $eskul_list->data_seek(0);
                                while ($eskul = $eskul_list->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $eskul['id']; ?>"><?php echo $eskul['nama_ekskul']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis *</label>
                                <select name="jenis" class="form-select" required>
                                    <option value="pemasukan">Pemasukan</option>
                                    <option value="pengeluaran">Pengeluaran</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal *</label>
                                <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori *</label>
                            <input type="text" name="kategori" class="form-control" placeholder="Contoh: Iuran, Peralatan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah *</label>
                            <input type="number" name="jumlah" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bukti (Opsional)</label>
                            <input type="file" name="bukti" class="form-control" accept="image/*,application/pdf">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>