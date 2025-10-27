<?php
// admin/eskul/edit.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Edit Ekstrakurikuler';
$current_user = getCurrentUser();
$id = $_GET['id'] ?? 0;

// Ambil data eskul
$eskul = query("SELECT * FROM ekstrakurikulers WHERE id = ?", [$id], 'i');
if (!$eskul || $eskul->num_rows == 0) {
    setFlash('danger', 'Data tidak ditemukan!');
    redirect('admin/eskul/index.php');
}
$data = $eskul->fetch_assoc();

// Cek akses (pembina hanya bisa edit eskul sendiri)
if ($current_user['role'] == 'pembina' && $data['pembina_id'] != $current_user['id']) {
    setFlash('danger', 'Anda tidak memiliki akses untuk mengedit ekstrakurikuler ini!');
    redirect('admin/eskul/index.php');
}

// Ambil daftar pembina
$pembina_list = query("SELECT id, name FROM users WHERE role = 'pembina' AND is_active = 1 ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_ekskul = $_POST['nama_ekskul'];
    $deskripsi = $_POST['deskripsi'];
    $pembina_id = $_POST['pembina_id'] ?: NULL;
    $kuota = $_POST['kuota'];
    $status = $_POST['status'];
    
    $gambar = $data['gambar'];
    
    // Upload gambar baru jika ada
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        if ($data['gambar']) {
            deleteFile($data['gambar']);
        }
        
        $upload = uploadFile($_FILES['gambar'], 'eskul');
        if ($upload['success']) {
            $gambar = $upload['filename'];
        }
    }
    
    $sql = "UPDATE ekstrakurikulers SET 
            nama_ekskul = ?, deskripsi = ?, pembina_id = ?, gambar = ?, kuota = ?, status = ?
            WHERE id = ?";
    
    $result = execute($sql, [$nama_ekskul, $deskripsi, $pembina_id, $gambar, $kuota, $status, $id], 'ssisssi');
    
    if ($result['success']) {
        setFlash('success', 'Ekstrakurikuler berhasil diupdate!');
        redirect('admin/eskul/index.php');
    } else {
        setFlash('danger', 'Gagal mengupdate ekstrakurikuler!');
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
                <i class="bi bi-speedometer2"></i> Dashboard Admin
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
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/eskul/index.php">
                            <i class="bi bi-grid-fill"></i> Ekstrakurikuler
                        </a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/anggota/manage.php">
                            <i class="bi bi-people-fill"></i> Anggota
                        </a>
                    </nav>
                </div>
            </div>

            <div class="col-md-10 p-4">
                <div class="mb-4">
                    <a href="<?php echo BASE_URL; ?>admin/eskul/index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <h2 class="mb-4"><i class="bi bi-pencil-square"></i> Edit Ekstrakurikuler</h2>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_ekskul" class="form-control" value="<?php echo htmlspecialchars($data['nama_ekskul']); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Pembina</label>
                                        <select name="pembina_id" class="form-select" <?php echo $current_user['role'] == 'pembina' ? 'disabled' : ''; ?>>
                                            <option value="">-- Pilih Pembina --</option>
                                            <?php while ($pembina = $pembina_list->fetch_assoc()): ?>
                                            <option value="<?php echo $pembina['id']; ?>" <?php echo $data['pembina_id'] == $pembina['id'] ? 'selected' : ''; ?>>
                                                <?php echo $pembina['name']; ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea name="deskripsi" class="form-control" rows="4"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Kuota Peserta <span class="text-danger">*</span></label>
                                        <input type="number" name="kuota" class="form-control" value="<?php echo $data['kuota']; ?>" min="1" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="aktif" <?php echo $data['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="nonaktif" <?php echo $data['status'] == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Gambar</label>
                                        <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImage(this, 'preview')">
                                        <small class="text-muted">Kosongkan jika tidak ingin mengubah</small>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <?php if ($data['gambar']): ?>
                                        <img src="<?php echo UPLOAD_URL . $data['gambar']; ?>" class="img-thumbnail" style="max-width: 200px;">
                                        <?php endif; ?>
                                        <img id="preview" src="" class="img-thumbnail ms-2" style="max-width: 200px; display: none;">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="text-end">
                                <a href="<?php echo BASE_URL; ?>admin/eskul/index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Update
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