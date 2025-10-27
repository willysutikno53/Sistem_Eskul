<?php
// admin/galeri/upload.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Upload Foto Galeri';
$current_user = getCurrentUser();

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
    $deskripsi = $_POST['deskripsi'];
    $tanggal_upload = $_POST['tanggal_upload'];
    $urutan = $_POST['urutan'];
    
    // Upload multiple images
    if (isset($_FILES['gambar']) && !empty($_FILES['gambar']['name'][0])) {
        $success_count = 0;
        $total_files = count($_FILES['gambar']['name']);
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gambar']['error'][$i] == 0) {
                $file = [
                    'name' => $_FILES['gambar']['name'][$i],
                    'type' => $_FILES['gambar']['type'][$i],
                    'tmp_name' => $_FILES['gambar']['tmp_name'][$i],
                    'error' => $_FILES['gambar']['error'][$i],
                    'size' => $_FILES['gambar']['size'][$i]
                ];
                
                $upload = uploadFile($file, 'galeri');
                if ($upload['success']) {
                    $gambar = $upload['filename'];
                    $judul_foto = $judul . ($total_files > 1 ? ' - Foto ' . ($i + 1) : '');
                    
                    execute("INSERT INTO galeris (ekstrakurikuler_id, judul, gambar, deskripsi, tanggal_upload, urutan) 
                            VALUES (?, ?, ?, ?, ?, ?)",
                        [$ekstrakurikuler_id, $judul_foto, $gambar, $deskripsi, $tanggal_upload, $urutan], 'issssi');
                    
                    $success_count++;
                }
            }
        }
        
        if ($success_count > 0) {
            setFlash('success', "$success_count foto berhasil diupload!");
            redirect('admin/galeri/index.php');
        } else {
            setFlash('danger', 'Gagal mengupload foto!');
        }
    } else {
        setFlash('danger', 'Silakan pilih foto untuk diupload!');
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
    <style>
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .preview-item {
            position: relative;
            width: 150px;
            height: 150px;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }
        .preview-item .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
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
                    <a href="<?php echo BASE_URL; ?>admin/galeri/index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <h2 class="mb-4"><i class="bi bi-cloud-upload"></i> Upload Foto Galeri</h2>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Ekstrakurikuler <span class="text-danger">*</span></label>
                                <select name="ekstrakurikuler_id" class="form-select" required>
                                    <option value="">-- Pilih Ekstrakurikuler --</option>
                                    <?php while ($eskul = $eskul_list->fetch_assoc()): ?>
                                    <option value="<?php echo $eskul['id']; ?>">
                                        <?php echo $eskul['nama_ekskul']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control" 
                                    placeholder="Contoh: Kegiatan Latihan Rutin" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3" 
                                    placeholder="Deskripsi singkat tentang foto"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_upload" class="form-control" 
                                            value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Urutan</label>
                                        <input type="number" name="urutan" class="form-control" 
                                            value="0" min="0">
                                        <small class="text-muted">Urutan tampilan (0 = default)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Foto <span class="text-danger">*</span></label>
                                <input type="file" name="gambar[]" class="form-control" 
                                    accept="image/*" multiple required id="fileInput">
                                <small class="text-muted">Bisa pilih beberapa foto sekaligus. Max 5MB per foto.</small>
                            </div>

                            <div id="previewContainer" class="preview-container"></div>

                            <hr>

                            <div class="text-end">
                                <a href="<?php echo BASE_URL; ?>admin/galeri/index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-cloud-upload"></i> Upload
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
    <script>
    // Preview multiple images
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('previewContainer');
        previewContainer.innerHTML = '';
        
        const files = Array.from(e.target.files);
        
        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="remove-btn" onclick="removePreview(this, ${index})">
                            <i class="bi bi-x"></i>
                        </button>
                    `;
                    previewContainer.appendChild(previewItem);
                }
                
                reader.readAsDataURL(file);
            }
        });
    });

    function removePreview(btn, index) {
        const fileInput = document.getElementById('fileInput');
        const dt = new DataTransfer();
        const files = Array.from(fileInput.files);
        
        files.forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        fileInput.files = dt.files;
        btn.parentElement.remove();
    }
    </script>
</body>
</html>