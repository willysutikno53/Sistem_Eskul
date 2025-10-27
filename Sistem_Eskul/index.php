<?php
// index.php
$page_title = 'Beranda';
require_once 'includes/header.php';

// Statistik
$total_eskul = query("SELECT COUNT(*) as total FROM ekstrakurikulers WHERE status = 'aktif'")->fetch_assoc()['total'];
$total_siswa = query("SELECT COUNT(*) as total FROM users WHERE role = 'siswa' AND is_active = 1")->fetch_assoc()['total'];
$total_anggota = query("SELECT COUNT(*) as total FROM anggota_ekskul WHERE status = 'diterima'")->fetch_assoc()['total'];

// Eskul populer
$eskul_populer = query("
    SELECT e.*, u.name as nama_pembina, COUNT(ae.id) as jumlah_anggota
    FROM ekstrakurikulers e
    LEFT JOIN users u ON e.pembina_id = u.id
    LEFT JOIN anggota_ekskul ae ON e.id = ae.ekstrakurikuler_id AND ae.status = 'diterima'
    WHERE e.status = 'aktif'
    GROUP BY e.id
    ORDER BY jumlah_anggota DESC
    LIMIT 6
");

// Berita terbaru
$berita_terbaru = query("
    SELECT b.*, e.nama_ekskul
    FROM berita b
    JOIN ekstrakurikulers e ON b.ekstrakurikuler_id = e.id
    WHERE b.is_published = 1
    ORDER BY b.created_at DESC
    LIMIT 3
");

// Prestasi terbaru
$prestasi_terbaru = query("
    SELECT p.*, e.nama_ekskul, u.name as nama_siswa
    FROM prestasis p
    JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
    LEFT JOIN anggota_ekskul ae ON p.anggota_id = ae.id
    LEFT JOIN users u ON ae.user_id = u.id
    ORDER BY p.tanggal DESC
    LIMIT 4
");
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-white">
                <h1 class="display-4 fw-bold">Sistem Ekstrakurikuler</h1>
                <h2 class="mb-4">MTsN 1 Lebak</h2>
                <p class="lead mb-4">
                    Bergabunglah dengan berbagai ekstrakurikuler untuk mengembangkan bakat dan minat Anda! 
                    Kami menyediakan lebih dari <?php echo $total_eskul; ?> pilihan ekstrakurikuler yang menarik.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?php echo BASE_URL; ?>daftar_eskul.php" class="btn btn-light btn-lg">
                        <i class="bi bi-pencil-square"></i> Daftar Sekarang
                    </a>
                    <a href="<?php echo BASE_URL; ?>profile_eskul.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-grid-fill"></i> Lihat Eskul
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center mt-4 mt-lg-0">
                <img src="https://via.placeholder.com/500x400/198754/ffffff?text=MTsN+1+Lebak" alt="Hero" class="img-fluid rounded shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Statistik -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <div class="icon mb-3">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $total_eskul; ?>">0</h3>
                    <p class="mb-0 text-muted">Ekstrakurikuler Aktif</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <div class="icon mb-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $total_siswa; ?>">0</h3>
                    <p class="mb-0 text-muted">Siswa Terdaftar</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <div class="icon mb-3">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $total_anggota; ?>">0</h3>
                    <p class="mb-0 text-muted">Anggota Aktif</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ekstrakurikuler Populer -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Ekstrakurikuler Populer</h2>
            <p class="text-muted">Pilih ekstrakurikuler sesuai dengan minat dan bakat Anda</p>
        </div>

        <div class="row g-4">
            <?php while ($eskul = $eskul_populer->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card eskul-card h-100">
                    <?php if ($eskul['gambar']): ?>
                    <img src="<?php echo UPLOAD_URL . $eskul['gambar']; ?>" class="card-img-top" alt="<?php echo $eskul['nama_ekskul']; ?>">
                    <?php else: ?>
                    <img src="https://via.placeholder.com/400x200/198754/ffffff?text=<?php echo urlencode($eskul['nama_ekskul']); ?>" class="card-img-top" alt="<?php echo $eskul['nama_ekskul']; ?>">
                    <?php endif; ?>
                    
                    <span class="badge bg-primary"><?php echo $eskul['jumlah_anggota']; ?> Anggota</span>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $eskul['nama_ekskul']; ?></h5>
                        <p class="card-text text-muted">
                            <?php echo substr($eskul['deskripsi'], 0, 100); ?>...
                        </p>
                        <div class="pembina mb-3">
                            <i class="bi bi-person"></i> <?php echo $eskul['nama_pembina'] ?? 'Belum ada pembina'; ?>
                        </div>
                        <a href="<?php echo BASE_URL; ?>profile_eskul.php?id=<?php echo $eskul['id']; ?>" class="btn btn-success w-100">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo BASE_URL; ?>profile_eskul.php" class="btn btn-success btn-lg">
                Lihat Semua Ekstrakurikuler <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Prestasi -->
<?php if ($prestasi_terbaru && $prestasi_terbaru->num_rows > 0): ?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Prestasi Terbaru</h2>
            <p class="text-muted">Kebanggaan siswa MTsN 1 Lebak</p>
        </div>

        <div class="row g-4">
            <?php 
            $badge_color = [
                'internasional' => 'danger',
                'nasional' => 'primary',
                'provinsi' => 'success',
                'kabupaten' => 'info',
                'kecamatan' => 'warning',
                'sekolah' => 'secondary'
            ];
            while ($prestasi = $prestasi_terbaru->fetch_assoc()): 
            ?>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-trophy-fill text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <span class="badge bg-<?php echo $badge_color[$prestasi['tingkat']] ?? 'secondary'; ?> mb-2">
                            <?php echo ucfirst($prestasi['tingkat']); ?>
                        </span>
                        <h5 class="card-title"><?php echo $prestasi['nama_prestasi']; ?></h5>
                        <p class="text-warning fw-bold"><?php echo $prestasi['peringkat']; ?></p>
                        <p class="small text-muted mb-2"><?php echo $prestasi['nama_ekskul']; ?></p>
                        <?php if ($prestasi['nama_siswa']): ?>
                        <p class="small"><strong><?php echo $prestasi['nama_siswa']; ?></strong></p>
                        <?php endif; ?>
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> <?php echo formatTanggal($prestasi['tanggal']); ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Berita & Kegiatan -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Berita & Kegiatan Terbaru</h2>
            <p class="text-muted">Update terkini dari kegiatan ekstrakurikuler</p>
        </div>

        <div class="row g-4">
            <?php if ($berita_terbaru && $berita_terbaru->num_rows > 0): ?>
                <?php while ($berita = $berita_terbaru->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card news-card h-100">
                        <?php if ($berita['gambar']): ?>
                        <img src="<?php echo UPLOAD_URL . $berita['gambar']; ?>" class="card-img-top" alt="<?php echo $berita['judul']; ?>">
                        <?php else: ?>
                        <img src="https://via.placeholder.com/400x200/20c997/ffffff?text=Berita" class="card-img-top" alt="<?php echo $berita['judul']; ?>">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <span class="badge bg-success mb-2"><?php echo $berita['nama_ekskul']; ?></span>
                            <h5 class="card-title"><?php echo $berita['judul']; ?></h5>
                            <p class="card-text text-muted">
                                <?php echo substr(strip_tags($berita['konten']), 0, 120); ?>...
                            </p>
                            <div class="news-meta">
                                <i class="bi bi-calendar"></i> <?php echo formatTanggal($berita['tanggal_post']); ?>
                                <span class="ms-3">
                                    <i class="bi bi-eye"></i> <?php echo $berita['views']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="<?php echo BASE_URL; ?>post_berita.php?id=<?php echo $berita['id']; ?>" class="btn btn-outline-success btn-sm w-100">
                                Baca Selengkapnya <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">Belum ada berita</div>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo BASE_URL; ?>update_kegiatan.php" class="btn btn-outline-success btn-lg">
                Lihat Semua Berita <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-success text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Siap Bergabung?</h2>
        <p class="lead mb-4">Daftarkan diri Anda sekarang dan kembangkan potensi terbaik Anda!</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?php echo BASE_URL; ?>daftar_eskul.php" class="btn btn-light btn-lg">
                <i class="bi bi-pencil-square"></i> Daftar Ekstrakurikuler
            </a>
            <a href="<?php echo BASE_URL; ?>cetak_sertifikat.php" class="btn btn-outline-light btn-lg">
                <i class="bi bi-award"></i> Cetak Sertifikat
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>