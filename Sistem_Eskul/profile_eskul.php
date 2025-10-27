<?php
// profile_eskul.php
$page_title = 'Profil Ekstrakurikuler';
require_once 'includes/header.php';

// Jika ada ID, tampilkan detail
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $eskul = query("
        SELECT e.*, u.name as nama_pembina, u.email as email_pembina
        FROM ekstrakurikulers e
        LEFT JOIN users u ON e.pembina_id = u.id
        WHERE e.id = ? AND e.status = 'aktif'
    ", [$id], 'i')->fetch_assoc();
    
    if (!$eskul) {
        setFlash('danger', 'Ekstrakurikuler tidak ditemukan!');
        redirect('profile_eskul.php');
    }
    
    // Ambil anggota
    $anggota = query("SELECT COUNT(*) as total FROM anggota_ekskul WHERE ekstrakurikuler_id = ? AND status = 'diterima'", [$id], 'i')->fetch_assoc()['total'];
    
    // Ambil jadwal
    $jadwal = query("SELECT * FROM jadwal_latihans WHERE ekstrakurikuler_id = ? AND is_active = 1 ORDER BY 
        CASE hari 
            WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 
            WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 WHEN 'Minggu' THEN 7 
        END", [$id], 'i');
    
    // Ambil berita eskul
    $berita = query("SELECT * FROM berita WHERE ekstrakurikuler_id = ? AND is_published = 1 ORDER BY created_at DESC LIMIT 3", [$id], 'i');
    
    // Ambil prestasi
    $prestasi = query("SELECT * FROM prestasis WHERE ekstrakurikuler_id = ? ORDER BY tanggal DESC LIMIT 5", [$id], 'i');
    
    // Ambil galeri
    $galeri = query("SELECT * FROM galeris WHERE ekstrakurikuler_id = ? AND is_active = 1 ORDER BY tanggal_upload DESC LIMIT 6", [$id], 'i');
    ?>
    
    <!-- Detail Eskul -->
    <div class="container py-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>profile_eskul.php">Ekstrakurikuler</a></li>
                <li class="breadcrumb-item active"><?php echo $eskul['nama_ekskul']; ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <?php if ($eskul['gambar']): ?>
                    <img src="<?php echo UPLOAD_URL . $eskul['gambar']; ?>" class="card-img-top" style="max-height: 400px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h2 class="fw-bold mb-3"><?php echo $eskul['nama_ekskul']; ?></h2>
                        
                        <div class="mb-4">
                            <span class="badge bg-success me-2">
                                <i class="bi bi-people-fill"></i> <?php echo $anggota; ?>/<?php echo $eskul['kuota']; ?> Anggota
                            </span>
                            <span class="badge bg-primary">
                                <i class="bi bi-check-circle"></i> Aktif
                            </span>
                        </div>

                        <h5 class="text-success mb-3">Deskripsi</h5>
                        <p class="text-muted"><?php echo nl2br($eskul['deskripsi']); ?></p>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-success">
                                    <i class="bi bi-person-fill"></i> Pembina
                                </h6>
                                <p class="mb-0"><?php echo $eskul['nama_pembina'] ?? 'Belum ada pembina'; ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-success">
                                    <i class="bi bi-people"></i> Kuota
                                </h6>
                                <p class="mb-0"><?php echo $eskul['kuota']; ?> siswa</p>
                            </div>
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <a href="<?php echo BASE_URL; ?>daftar_eskul.php" class="btn btn-success btn-lg">
                                <i class="bi bi-pencil-square"></i> Daftar Sekarang
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Jadwal -->
                <?php if ($jadwal && $jadwal->num_rows > 0): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check"></i> Jadwal Latihan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php while ($j = $jadwal->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo $j['hari']; ?></strong><br>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> <?php echo substr($j['jam_mulai'], 0, 5); ?> - <?php echo substr($j['jam_selesai'], 0, 5); ?><br>
                                            <i class="bi bi-geo-alt"></i> <?php echo $j['lokasi']; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Prestasi -->
                <?php if ($prestasi && $prestasi->num_rows > 0): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-trophy-fill text-warning"></i> Prestasi
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php while ($p = $prestasi->fetch_assoc()): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <span class="badge bg-success"><?php echo ucfirst($p['tingkat']); ?></span>
                            <h6 class="mb-1 mt-2"><?php echo $p['nama_prestasi']; ?></h6>
                            <p class="text-warning mb-1"><strong><?php echo $p['peringkat']; ?></strong></p>
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> <?php echo formatTanggal($p['tanggal']); ?>
                            </small>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Galeri -->
                <?php if ($galeri && $galeri->num_rows > 0): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-images"></i> Galeri Foto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php while ($g = $galeri->fetch_assoc()): ?>
                            <div class="col-4">
                                <img src="<?php echo UPLOAD_URL . $g['gambar']; ?>" 
                                     class="img-thumbnail" 
                                     alt="<?php echo $g['judul']; ?>"
                                     style="height: 150px; width: 100%; object-fit: cover;">
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Berita Eskul -->
                <?php if ($berita && $berita->num_rows > 0): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-newspaper"></i> Berita & Kegiatan
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php while ($b = $berita->fetch_assoc()): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <h6 class="mb-2">
                                <a href="<?php echo BASE_URL; ?>post_berita.php?id=<?php echo $b['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo $b['judul']; ?>
                                </a>
                            </h6>
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> <?php echo formatTanggal($b['tanggal_post']); ?>
                            </small>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i> Informasi
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-exclamation-circle"></i> 
                                Pendaftaran akan diverifikasi oleh admin sebelum Anda resmi menjadi anggota.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Eskul Lainnya -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-grid"></i> Eskul Lainnya
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $other_eskul = query("SELECT * FROM ekstrakurikulers WHERE status = 'aktif' AND id != ? ORDER BY RAND() LIMIT 5", [$id], 'i');
                        while ($other = $other_eskul->fetch_assoc()):
                        ?>
                        <div class="mb-2">
                            <a href="?id=<?php echo $other['id']; ?>" class="text-decoration-none">
                                <i class="bi bi-arrow-right-circle"></i> <?php echo $other['nama_ekskul']; ?>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
} else {
    // Tampilkan semua eskul
    $all_eskul = query("
        SELECT e.*, u.name as nama_pembina, COUNT(ae.id) as jumlah_anggota 
        FROM ekstrakurikulers e 
        LEFT JOIN users u ON e.pembina_id = u.id
        LEFT JOIN anggota_ekskul ae ON e.id = ae.ekstrakurikuler_id AND ae.status = 'diterima'
        WHERE e.status = 'aktif' 
        GROUP BY e.id 
        ORDER BY e.nama_ekskul
    ");
    ?>
    
    <div class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Daftar Ekstrakurikuler</h2>
                <p class="text-muted">Pilih ekstrakurikuler yang sesuai dengan minat Anda</p>
            </div>

            <div class="row g-4">
                <?php while ($eskul = $all_eskul->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card eskul-card h-100">
                        <?php if ($eskul['gambar']): ?>
                        <img src="<?php echo UPLOAD_URL . $eskul['gambar']; ?>" class="card-img-top" alt="<?php echo $eskul['nama_ekskul']; ?>">
                        <?php else: ?>
                        <img src="https://via.placeholder.com/400x200/198754/ffffff?text=<?php echo urlencode($eskul['nama_ekskul']); ?>" class="card-img-top">
                        <?php endif; ?>
                        
                        <span class="badge bg-primary"><?php echo $eskul['jumlah_anggota']; ?>/<?php echo $eskul['kuota']; ?></span>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $eskul['nama_ekskul']; ?></h5>
                            <p class="card-text text-muted">
                                <?php echo substr($eskul['deskripsi'], 0, 100); ?>...
                            </p>
                            <div class="pembina mb-3">
                                <i class="bi bi-person"></i> <?php echo $eskul['nama_pembina'] ?? 'Belum ada pembina'; ?>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="?id=<?php echo $eskul['id']; ?>" class="btn btn-success">
                                    <i class="bi bi-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <?php
}

require_once 'includes/footer.php';
?>