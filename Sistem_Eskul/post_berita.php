<?php
// post_berita.php
require_once 'config/database.php';

$id = $_GET['id'] ?? 0;

// Ambil data berita
$berita = query("
    SELECT b.*, e.nama_ekskul, e.id as eskul_id, u.name as penulis
    FROM berita b
    JOIN ekstrakurikulers e ON b.ekstrakurikuler_id = e.id
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.is_published = 1
", [$id], 'i');

if (!$berita || $berita->num_rows == 0) {
    setFlash('danger', 'Berita tidak ditemukan!');
    redirect('update_kegiatan.php');
}

$data = $berita->fetch_assoc();

// Update views
execute("UPDATE berita SET views = views + 1 WHERE id = ?", [$id], 'i');

$page_title = $data['judul'];
require_once 'includes/header.php';

// Berita lainnya dari eskul yang sama
$related = query("
    SELECT * FROM berita 
    WHERE ekstrakurikuler_id = ? AND id != ? AND is_published = 1
    ORDER BY created_at DESC 
    LIMIT 3
", [$data['eskul_id'], $id], 'ii');
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>update_kegiatan.php">Berita</a></li>
            <li class="breadcrumb-item active"><?php echo substr($data['judul'], 0, 30); ?>...</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <article class="card border-0 shadow-sm mb-4">
                <?php if ($data['gambar']): ?>
                <img src="<?php echo UPLOAD_URL . $data['gambar']; ?>" class="card-img-top" alt="<?php echo $data['judul']; ?>" style="max-height: 500px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body">
                    <!-- Badge Eskul -->
                    <div class="mb-3">
                        <a href="<?php echo BASE_URL; ?>profile_eskul.php?id=<?php echo $data['eskul_id']; ?>" class="badge bg-success text-decoration-none">
                            <i class="bi bi-grid"></i> <?php echo $data['nama_ekskul']; ?>
                        </a>
                    </div>

                    <!-- Title -->
                    <h1 class="fw-bold mb-3"><?php echo $data['judul']; ?></h1>

                    <!-- Meta Info -->
                    <div class="d-flex align-items-center text-muted mb-4 pb-3 border-bottom flex-wrap gap-3">
                        <div>
                            <i class="bi bi-person-circle"></i> <?php echo $data['penulis'] ?? 'Admin'; ?>
                        </div>
                        <div>
                            <i class="bi bi-calendar"></i> <?php echo formatTanggal($data['tanggal_post']); ?>
                        </div>
                        <div>
                            <i class="bi bi-eye"></i> <?php echo $data['views']; ?> views
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="content">
                        <?php echo nl2br(htmlspecialchars($data['konten'])); ?>
                    </div>

                    <!-- Share Buttons -->
                    <hr class="my-4">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <strong class="me-3">Bagikan:</strong>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BASE_URL . 'post_berita.php?id=' . $id); ?>" 
                           target="_blank" class="btn btn-sm btn-primary">
                            <i class="bi bi-facebook"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(BASE_URL . 'post_berita.php?id=' . $id); ?>&text=<?php echo urlencode($data['judul']); ?>" 
                           target="_blank" class="btn btn-sm btn-info text-white">
                            <i class="bi bi-twitter"></i> Twitter
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($data['judul'] . ' - ' . BASE_URL . 'post_berita.php?id=' . $id); ?>" 
                           target="_blank" class="btn btn-sm btn-success">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
            </article>

            <!-- Berita Terkait -->
            <?php if ($related && $related->num_rows > 0): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-newspaper"></i> Berita Lainnya dari <?php echo $data['nama_ekskul']; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php while ($rel = $related->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="card h-100 border">
                                <?php if ($rel['gambar']): ?>
                                <img src="<?php echo UPLOAD_URL . $rel['gambar']; ?>" class="card-img-top" alt="<?php echo $rel['judul']; ?>" style="height: 150px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="?id=<?php echo $rel['id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo substr($rel['judul'], 0, 50); ?>...
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> <?php echo formatTanggal($rel['tanggal_post']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info Eskul -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Tentang Eskul Ini
                    </h6>
                </div>
                <div class="card-body">
                    <h5><?php echo $data['nama_ekskul']; ?></h5>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>profile_eskul.php?id=<?php echo $data['eskul_id']; ?>" class="btn btn-success">
                            <i class="bi bi-eye"></i> Lihat Profil
                        </a>
                        <a href="<?php echo BASE_URL; ?>daftar_eskul.php" class="btn btn-outline-success">
                            <i class="bi bi-pencil-square"></i> Daftar Sekarang
                        </a>
                    </div>
                </div>
            </div>

            <!-- Berita Terpopuler -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="bi bi-fire"></i> Berita Terpopuler
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    $populer = query("
                        SELECT b.id, b.judul, b.views, b.tanggal_post
                        FROM berita b
                        WHERE b.is_published = 1
                        ORDER BY b.views DESC
                        LIMIT 5
                    ");
                    $no = 1;
                    while ($pop = $populer->fetch_assoc()):
                    ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex">
                            <div class="badge bg-success me-2"><?php echo $no++; ?></div>
                            <div class="flex-grow-1">
                                <a href="?id=<?php echo $pop['id']; ?>" class="text-decoration-none text-dark">
                                    <strong><?php echo substr($pop['judul'], 0, 60); ?>...</strong>
                                </a>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-eye"></i> <?php echo $pop['views']; ?> views
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.content {
    font-size: 1.1rem;
    line-height: 1.8;
    text-align: justify;
}
.content p {
    margin-bottom: 1.5rem;
}
</style>

<?php require_once 'includes/footer.php'; ?>