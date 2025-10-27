<?php
// admin/jadwal/tambah.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Tambah Jadwal';
$current_user = getCurrentUser();
$edit_mode = false;
$data = null;

// Check edit mode
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $result = query("SELECT * FROM jadwal_latihans WHERE id = ?", [$id], 'i');
    $data = $result->fetch_assoc();
    $page_title = 'Edit Jadwal';
}

// Ambil daftar eskul
$where_clause = "";
$params = [];
$types = "";

if ($current_user['role'] == 'pembina') {
    $where_clause = "WHERE pembina_id = ?";
    $params = [$current_user['id']];
    $types = "i";
}

$eskul_list = query("SELECT id, nama_ekskul FROM ekstrakurikulers $where_clause ORDER BY nama_ekskul", $params, $types);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ekstrakurikuler_id = $_POST['ekstrakurikuler_id'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $lokasi = $_POST['lokasi'];
    $keterangan = $_POST['keterangan'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if ($edit_mode) {
        $sql = "UPDATE jadwal_latihans SET 
                ekstrakurikuler_id = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, 
                lokasi = ?, keterangan = ?, is_active = ?
                WHERE id = ?";
        $result = execute($sql, [$ekstrakurikuler_id, $hari, $jam_mulai, $jam_selesai, $lokasi, $keterangan, $is_active, $id], 'isssssii');
    } else {
        $sql = "INSERT INTO jadwal_latihans (ekstrakurikuler_id, hari, jam_mulai, jam_selesai, lokasi, keterangan, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $result = execute($sql, [$ekstrakurikuler_id, $hari, $jam_mulai, $jam_selesai, $lokasi, $keterangan, $is_active], 'isssssi');
    }
    
    if ($result['success']) {
        setFlash('success', 'Jadwal berhasil ' . ($edit_mode ? 'diupdate' : 'ditambahkan') . '!');
        redirect('admin/jadwal/index.php');
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

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-4">
                    <a href="<?php echo BASE_URL; ?>admin/jadwal/index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <h2 class="mb-4">
                    <i class="bi bi-<?php echo $edit_mode ? 'pencil-square' : 'plus-circle'; ?>"></i> 
                    <?php echo $page_title; ?>
                </h2>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Ekstrakurikuler <span class="text-danger">*</span></label>
                                <select name="ekstrakurikuler_id" class="form-select" required>
                                    <option value="">-- Pilih Ekstrakurikuler --</option>
                                    <?php while ($eskul = $eskul_list->fetch_assoc()): ?>
                                    <option value="<?php echo $eskul['id']; ?>" 
                                        <?php echo ($edit_mode && $data['ekstrakurikuler_id'] == $eskul['id']) ? 'selected' : ''; ?>>
                                        <?php echo $eskul['nama_ekskul']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Hari <span class="text-danger">*</span></label>
                                        <select name="hari" class="form-select" required>
                                            <option value="">-- Pilih Hari --</option>
                                            <?php 
                                            $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                            foreach ($hari_list as $hari):
                                            ?>
                                            <option value="<?php echo $hari; ?>" 
                                                <?php echo ($edit_mode && $data['hari'] == $hari) ? 'selected' : ''; ?>>
                                                <?php echo $hari; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                                        <input type="text" name="lokasi" class="form-control" 
                                            value="<?php echo $edit_mode ? htmlspecialchars($data['lokasi']) : ''; ?>" 
                                            placeholder="Contoh: Lapangan, Lab, Ruang Musik" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                        <input type="time" name="jam_mulai" class="form-control" 
                                            value="<?php echo $edit_mode ? $data['jam_mulai'] : ''; ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                        <input type="time" name="jam_selesai" class="form-control" 
                                            value="<?php echo $edit_mode ? $data['jam_selesai'] : ''; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3" 
                                    placeholder="Keterangan tambahan (opsional)"><?php echo $edit_mode ? htmlspecialchars($data['keterangan']) : ''; ?></textarea>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                    <?php echo (!$edit_mode || $data['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    Aktif
                                </label>
                            </div>

                            <hr>

                            <div class="text-end">
                                <a href="<?php echo BASE_URL; ?>admin/jadwal/index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> <?php echo $edit_mode ? 'Update' : 'Simpan'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>