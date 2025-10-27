<?php
// includes/footer.php
?>
<!-- Footer -->
<footer class="bg-success text-white mt-5">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-mortarboard-fill"></i> MTsN 1 Lebak
                </h5>
                <p>Sistem Informasi Manajemen Ekstrakurikuler untuk memudahkan pengelolaan dan monitoring kegiatan ekstrakurikuler sekolah.</p>
                <div class="mt-3">
                    <a href="#" class="text-white me-3"><i class="bi bi-facebook fs-4"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-instagram fs-4"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-youtube fs-4"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-envelope fs-4"></i></a>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>" class="text-white text-decoration-none">
                            <i class="bi bi-chevron-right"></i> Beranda
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>profile_eskul.php" class="text-white text-decoration-none">
                            <i class="bi bi-chevron-right"></i> Ekstrakurikuler
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>update_kegiatan.php" class="text-white text-decoration-none">
                            <i class="bi bi-chevron-right"></i> Berita & Kegiatan
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>daftar_eskul.php" class="text-white text-decoration-none">
                            <i class="bi bi-chevron-right"></i> Pendaftaran
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>cetak_sertifikat.php" class="text-white text-decoration-none">
                            <i class="bi bi-chevron-right"></i> Cetak Sertifikat
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3">Kontak</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-geo-alt-fill"></i> Jl. Raya Rangkasbitung, Lebak, Banten
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-telephone-fill"></i> (0252) 123456
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-envelope-fill"></i> info@mtsn1lebak.sch.id
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-clock-fill"></i> Senin - Jumat: 07:00 - 15:00
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="border-top border-light">
        <div class="container py-3">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <small>&copy; <?php echo date('Y'); ?> MTsN 1 Lebak. All Rights Reserved.</small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small>Developed with <i class="bi bi-heart-fill text-danger"></i> by Tim IT MTsN 1 Lebak</small>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>

</body>
</html>