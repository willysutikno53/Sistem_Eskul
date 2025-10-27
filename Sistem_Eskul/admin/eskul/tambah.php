<?php
// admin/eskul/tambah.php
require_once '../../config/database.php';
requireRole(['admin']);

$page_title = 'Tambah Ekstrakurikuler';
$current_user = getCurrentUser();

// Ambil daftar pembina
$pembina_list = query("SELECT id, name FROM users WHERE role = 'pembina' AND is_active = 1 ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_ekskul = $_POST['nama_ekskul'];
    $deskripsi = $_POST['deskripsi'];
    $pembina_id = $_POST['pembina_id'] ?: NULL;
    $kuota = $_POST['kuota'];
    $status = $_POST['status'];
    
    $gambar = '';
    
    // Upload gambar jika ada
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload = uploadFile($_FILES['gambar'], 'eskul');
        if ($upload['success']) {
            $gambar = $upload['filename'];
        } else {
            setFlash('danger', $upload['message']);
            redirect('admin/eskul/tambah.php');
        }
    }
    
    $sql = "INSERT INTO ekstrakurikulers (nama_ekskul, deskripsi, pembina_id, gambar, kuota, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $result = execute($sql, [$nama_ekskul, $deskripsi, $pembina_id, $gambar, $kuota, $status], 'ssisss');
    
    if ($result['success']) {
        setFlash('success', 'Ekstrakurikuler berhasil ditambahkan!');
        redirect('admin/eskul/index.php');
    } else {
        setFlash('danger', 'Gagal menambahkan ekstrakurikuler!');
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
                <div class="mb-4">
                    <a href="<?php echo BASE_URL; ?>admin/eskul/index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <h2 class="mb-4"><i class="bi bi-plus-circle"></i> Tambah Ekstrakurikuler</h2>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_ekskul" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Pembina</label>
                                        <select name="pembina_id" class="form-select">
                                            <option value="">-- Pilih Pembina --</option>
                                            <?php while ($pembina = $pembina_list->fetch_assoc()): ?>
                                            <option value="<?php echo $pembina['id']; ?>">
                                                <?php echo $pembina['name']; ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <small class="text-muted">Opsional - bisa diisi nanti</small>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan tentang ekstrakurikuler ini..."></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Kuota Peserta <span class="text-danger">*</span></label>
                                        <input type="number" name="kuota" class="form-control" value="30" min="1" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="aktif">Aktif</option>
                                            <option value="nonaktif">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Gambar</label>
                                        <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImage(this, 'preview')">
                                        <small class="text-muted">Max 5MB (JPG, PNG, GIF)</small>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <img id="preview" src="" class="img-thumbnail" style="max-width: 200px; display: none;">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="text-end">
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Simpan
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