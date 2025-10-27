<?php
// admin/berita/tambah.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Tambah Berita';
$current_user = getCurrentUser();
$edit_mode = false;
$data = null;

// Check edit mode
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $result = query("SELECT * FROM berita WHERE id = ?", [$id], 'i');
    $data = $result->fetch_assoc();
    $page_title = 'Edit Berita';
}

// Ambil daftar eskul
$where_eskul = "";
$params_eskul = [];
$types_eskul = "";

if ($current_user['role'] == 'pembina') {
    $where_eskul = "WHERE pembina_id = ?";
    $params_eskul = [$current_user['id']];
    $types_eskul = "i";
}

$eskul_list = query("SELECT id, nama_ekskul FROM ekstrakurikulers $where_eskul ORDER BY nama_ekskul", $params_eskul, $types_eskul);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ekstrakurikuler_id = $_POST['ekstrakurikuler_id'];
    $judul = $_POST['judul'];
    $konten = $_POST['konten'];
    $tanggal_post = $_POST['tanggal_post'];
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    $gambar = $edit_mode ? $data['gambar'] : '';
    
    // Upload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        if ($edit_mode && $data['gambar']) {
            deleteFile($data['gambar']);
        }
        $upload = uploadFile($_FILES['gambar'], 'berita');
        if ($upload['success']) {
            $gambar = $upload['filename'];
        }
    }
    
    if ($edit_mode) {
        $sql = "UPDATE berita SET 
                ekstrakurikuler_id = ?, judul = ?, konten = ?, gambar = ?, 
                tanggal_post = ?, is_published = ?
                WHERE id = ?";
        $result = execute($sql, [$ekstrakurikuler_id, $judul, $konten, $gambar, $tanggal_post, $is_published, $id], 'issssii');
    } else {
        $sql = "INSERT INTO berita (ekstrakurikuler_id, user_id, judul, konten, gambar, tanggal_post, is_published) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $result = execute($sql, [$ekstrakurikuler_id, $current_user['id'], $judul, $konten, $gambar, $tanggal_post, $is_published], 'iissssi');
    }
    
    if ($result['success']) {
        setFlash('success', 'Berita berhasil ' . ($edit_mode ? 'diupdate' : 'ditambahkan') . '!');
        redirect('admin/berita/manage.php');
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
            <div class="col-lg-10">
                <div class="mb-4">
                    <a href="<?php echo BASE_URL; ?>admin/berita/manage.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <h2 class="mb-4">
                    <i class="bi bi-<?php echo $edit_mode ? 'pencil-square' : 'plus-circle'; ?>"></i> 
                    <?php echo $page_title; ?>
                </h2>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
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
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Post <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_post" class="form-control" 
                                            value="<?php echo $edit_mode ? $data['tanggal_post'] : date('Y-m-d'); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Judul Berita <span class="text-danger">*</span></label>
                                        <input type="text" name="judul" class="form-control" 
                                            value="<?php echo $edit_mode ? htmlspecialchars($data['judul']) : ''; ?>" 
                                            placeholder="Masukkan judul berita yang menarik" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Konten <span class="text-danger">*</span></label>
                                        <textarea name="konten" class="form-control" rows="12" required><?php echo $edit_mode ? htmlspecialchars($data['konten']) : ''; ?></textarea>
                                        <small class="text-muted">Gunakan enter untuk paragraf baru</small>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Gambar</label>
                                        <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImage(this, 'preview')">
                                        <small class="text-muted">Max 5MB (JPG, PNG, GIF)</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" 
                                                <?php echo (!$edit_mode || $data['is_published']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_published">
                                                Publikasikan
                                            </label>
                                        </div>
                                        <small class="text-muted">Matikan untuk menyimpan sebagai draft</small>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <?php if ($edit_mode && $data['gambar']): ?>
                                        <img src="<?php echo UPLOAD_URL . $data['gambar']; ?>" class="img-thumbnail" style="max-width: 300px;">
                                        <?php endif; ?>
                                        <img id="preview" src="" class="img-thumbnail" style="max-width: 300px; display: none;">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="text-end">
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
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