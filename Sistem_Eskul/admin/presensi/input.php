<?php
// admin/presensi/input.php
require_once '../../config/database.php';
requireRole(['admin', 'pembina']);

$page_title = 'Input Presensi';
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

// Proses submit presensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_presensi'])) {
    $tanggal = $_POST['tanggal'];
    $presensi_data = $_POST['presensi'] ?? [];
    
    $success_count = 0;
    foreach ($presensi_data as $anggota_id => $data) {
        $status = $data['status'];
        $keterangan = $data['keterangan'] ?? '';
        
        // Cek apakah sudah ada presensi untuk hari ini
        $check = query("SELECT id FROM presensis WHERE anggota_id = ? AND tanggal = ?", [$anggota_id, $tanggal], 'is');
        
        if ($check && $check->num_rows > 0) {
            // Update
            execute("UPDATE presensis SET status = ?, keterangan = ?, waktu_presensi = NOW() WHERE anggota_id = ? AND tanggal = ?",
                [$status, $keterangan, $anggota_id, $tanggal], 'ssis');
        } else {
            // Insert
            execute("INSERT INTO presensis (anggota_id, tanggal, status, keterangan, waktu_presensi) VALUES (?, ?, ?, ?, NOW())",
                [$anggota_id, $tanggal, $status, $keterangan], 'isss');
        }
        $success_count++;
    }
    
    setFlash('success', "Presensi berhasil disimpan untuk $success_count anggota!");
    redirect('admin/presensi/index.php?tanggal=' . $tanggal);
}

// Load anggota jika eskul dipilih
$anggota_list = null;
$selected_eskul = '';
$selected_tanggal = date('Y-m-d');

if (isset($_GET['eskul']) && isset($_GET['tanggal'])) {
    $selected_eskul = $_GET['eskul'];
    $selected_tanggal = $_GET['tanggal'];
    
    $anggota_list = query("
        SELECT ae.id as anggota_id, u.name, u.nis, u.kelas,
        (SELECT status FROM presensis WHERE anggota_id = ae.id AND tanggal = ? LIMIT 1) as status_presensi,
        (SELECT keterangan FROM presensis WHERE anggota_id = ae.id AND tanggal = ? LIMIT 1) as keterangan_presensi
        FROM anggota_ekskul ae
        JOIN users u ON ae.user_id = u.id
        WHERE ae.ekstrakurikuler_id = ? AND ae.status = 'diterima'
        ORDER BY u.name
    ", [$selected_tanggal, $selected_tanggal, $selected_eskul], 'ssi');
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
                    <a href="<?php echo BASE_URL; ?>admin/presensi/index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <h2 class="mb-4"><i class="bi bi-clipboard-check"></i> Input Presensi</h2>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control" value="<?php echo $selected_tanggal; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ekstrakurikuler <span class="text-danger">*</span></label>
                                    <select name="eskul" class="form-select" required>
                                        <option value="">-- Pilih Ekstrakurikuler --</option>
                                        <?php 
                                        $eskul_list->data_seek(0);
                                        while ($eskul = $eskul_list->fetch_assoc()): 
                                        ?>
                                        <option value="<?php echo $eskul['id']; ?>" <?php echo $selected_eskul == $eskul['id'] ? 'selected' : ''; ?>>
                                            <?php echo $eskul['nama_ekskul']; ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Tampilkan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Form Presensi -->
                <?php if ($anggota_list): ?>
                <form method="POST" action="">
                    <input type="hidden" name="tanggal" value="<?php echo $selected_tanggal; ?>">
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Daftar Anggota - <?php echo formatTanggal($selected_tanggal); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Centang checkbox untuk menandai status presensi
                            </div>

                            <?php if ($anggota_list->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">NIS</th>
                                            <th width="25%">Nama</th>
                                            <th width="10%">Kelas</th>
                                            <th width="25%">Status</th>
                                            <th width="20%">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        while ($anggota = $anggota_list->fetch_assoc()): 
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $anggota['nis']; ?></td>
                                            <td><?php echo $anggota['name']; ?></td>
                                            <td><?php echo $anggota['kelas']; ?></td>
                                            <td>
                                                <select name="presensi[<?php echo $anggota['anggota_id']; ?>][status]" class="form-select form-select-sm" required>
                                                    <option value="hadir" <?php echo $anggota['status_presensi'] == 'hadir' ? 'selected' : ''; ?>>Hadir</option>
                                                    <option value="izin" <?php echo $anggota['status_presensi'] == 'izin' ? 'selected' : ''; ?>>Izin</option>
                                                    <option value="sakit" <?php echo $anggota['status_presensi'] == 'sakit' ? 'selected' : ''; ?>>Sakit</option>
                                                    <option value="alpha" <?php echo $anggota['status_presensi'] == 'alpha' ? 'selected' : ''; ?>>Alpha</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="presensi[<?php echo $anggota['anggota_id']; ?>][keterangan]" 
                                                    class="form-control form-control-sm" 
                                                    value="<?php echo htmlspecialchars($anggota['keterangan_presensi'] ?? ''); ?>"
                                                    placeholder="Keterangan">
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <hr>

                            <div class="text-end">
                                <button type="submit" name="submit_presensi" class="btn btn-success btn-lg">
                                    <i class="bi bi-save"></i> Simpan Presensi
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> Tidak ada anggota aktif untuk ekstrakurikuler ini
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle fs-1"></i>
                    <p class="mt-3 mb-0">Silakan pilih tanggal dan ekstrakurikuler untuk memulai input presensi</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
    <script>
    // Set semua status menjadi hadir/alpha dengan cepat
    function setAllStatus(status) {
        document.querySelectorAll('select[name*="[status]"]').forEach(select => {
            select.value = status;
        });
    }
    </script>
</body>
</html>