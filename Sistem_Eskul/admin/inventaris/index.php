<?php
// admin/inventaris/index.php
require_once '../../config/database.php';
requireRole(['admin']);

$page_title = 'Kelola Inventaris';
$current_user = getCurrentUser();

// Hapus
if (isset($_GET['delete'])) {
    execute("DELETE FROM inventaris WHERE id = ?", [$_GET['delete']], 'i');
    setFlash('success', 'Barang berhasil dihapus!');
    redirect('admin/inventaris/index.php');
}

// Filter
$eskul_filter = $_GET['eskul'] ?? '';

$where = "";
$params = [];
$types = "";

if ($eskul_filter) {
    $where = "WHERE i.ekstrakurikuler_id = ?";
    $params = [$eskul_filter];
    $types = "i";
}

$inventaris = query("
    SELECT i.*, e.nama_ekskul
    FROM inventaris i
    JOIN ekstrakurikulers e ON i.ekstrakurikuler_id = e.id
    $where
    ORDER BY i.created_at DESC
", $params, $types);

$eskul_list = query("SELECT id, nama_ekskul FROM ekstrakurikulers ORDER BY nama_ekskul");

// Tambah/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ekstrakurikuler_id = $_POST['ekstrakurikuler_id'];
    $nama_barang = $_POST['nama_barang'];
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah'];
    $satuan = $_POST['satuan'];
    $kondisi = $_POST['kondisi'];
    $tanggal_beli = $_POST['tanggal_beli'] ?: NULL;
    $harga = $_POST['harga'] ?: 0;
    $lokasi = $_POST['lokasi'];
    $keterangan = $_POST['keterangan'];
    
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $sql = "UPDATE inventaris SET ekstrakurikuler_id=?, nama_barang=?, kategori=?, jumlah=?, satuan=?, kondisi=?, tanggal_beli=?, harga=?, lokasi=?, keterangan=? WHERE id=?";
        execute($sql, [$ekstrakurikuler_id, $nama_barang, $kategori, $jumlah, $satuan, $kondisi, $tanggal_beli, $harga, $lokasi, $keterangan, $_POST['id']], 'issssssdssi');
    } else {
        // Insert
        $sql = "INSERT INTO inventaris (ekstrakurikuler_id, nama_barang, kategori, jumlah, satuan, kondisi, tanggal_beli, harga, lokasi, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        execute($sql, [$ekstrakurikuler_id, $nama_barang, $kategori, $jumlah, $satuan, $kondisi, $tanggal_beli, $harga, $lokasi, $keterangan], 'isssssdss');
    }
    
    setFlash('success', 'Data inventaris berhasil disimpan!');
    redirect('admin/inventaris/index.php');
}

// Get data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_data = query("SELECT * FROM inventaris WHERE id = ?", [$_GET['edit']], 'i')->fetch_assoc();
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
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/keuangan/index.php">
                            <i class="bi bi-cash-stack"></i> Keuangan
                        </a>
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/inventaris/index.php">
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
                    <h2><i class="bi bi-box-seam"></i> Kelola Inventaris</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#formModal">
                        <i class="bi bi-plus-circle"></i> Tambah Barang
                    </button>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-10">
                                    <select name="eskul" class="form-select">
                                        <option value="">Semua Eskul</option>
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
                                        <th>Nama Barang</th>
                                        <th>Kategori</th>
                                        <th>Jumlah</th>
                                        <th>Kondisi</th>
                                        <th>Lokasi</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($inventaris && $inventaris->num_rows > 0):
                                        $no = 1;
                                        while ($row = $inventaris->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo $row['nama_barang']; ?></strong><br>
                                            <small class="text-muted"><?php echo $row['nama_ekskul']; ?></small>
                                        </td>
                                        <td><?php echo $row['kategori']; ?></td>
                                        <td><?php echo $row['jumlah'] . ' ' . $row['satuan']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $row['kondisi'] == 'baik' ? 'success' : 
                                                    ($row['kondisi'] == 'rusak ringan' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($row['kondisi']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $row['lokasi']; ?></td>
                                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirmDelete()">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">Tidak ada data inventaris</td>
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

    <!-- Modal Form -->
    <div class="modal fade" id="formModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? ''; ?>">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Barang</h5>
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
                                <option value="<?php echo $eskul['id']; ?>" <?php echo ($edit_data && $edit_data['ekstrakurikuler_id'] == $eskul['id']) ? 'selected' : ''; ?>>
                                    <?php echo $eskul['nama_ekskul']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Nama Barang *</label>
                                <input type="text" name="nama_barang" class="form-control" value="<?php echo $edit_data['nama_barang'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kategori</label>
                                <input type="text" name="kategori" class="form-control" value="<?php echo $edit_data['kategori'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jumlah *</label>
                                <input type="number" name="jumlah" class="form-control" value="<?php echo $edit_data['jumlah'] ?? 1; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Satuan *</label>
                                <input type="text" name="satuan" class="form-control" value="<?php echo $edit_data['satuan'] ?? 'unit'; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kondisi *</label>
                                <select name="kondisi" class="form-select" required>
                                    <option value="baik" <?php echo ($edit_data && $edit_data['kondisi'] == 'baik') ? 'selected' : ''; ?>>Baik</option>
                                    <option value="rusak ringan" <?php echo ($edit_data && $edit_data['kondisi'] == 'rusak ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                                    <option value="rusak berat" <?php echo ($edit_data && $edit_data['kondisi'] == 'rusak berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Beli</label>
                                <input type="date" name="tanggal_beli" class="form-control" value="<?php echo $edit_data['tanggal_beli'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="harga" class="form-control" value="<?php echo $edit_data['harga'] ?? 0; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi</label>
                            <input type="text" name="lokasi" class="form-control" value="<?php echo $edit_data['lokasi'] ?? ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2"><?php echo $edit_data['keterangan'] ?? ''; ?></textarea>
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
    <?php if ($edit_data): ?>
    <script>
    // Auto open modal for edit
    var modal = new bootstrap.Modal(document.getElementById('formModal'));
    modal.show();
    </script>
    <?php endif; ?>
</body>
</html>