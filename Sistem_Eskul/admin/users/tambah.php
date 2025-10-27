<?php
// admin/users/tambah.php
require_once '../../config/database.php';
requireRole(['admin']);

$page_title = 'Tambah User';
$current_user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'] ?? NULL;
    $nis = $_POST['nis'] ?? NULL;
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $kelas = $_POST['kelas'] ?? NULL;
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? NULL;
    $no_hp = $_POST['no_hp'] ?? NULL;
    $alamat = $_POST['alamat'] ?? NULL;
    
    $sql = "INSERT INTO users (name, email, nis, password, role, kelas, jenis_kelamin, no_hp, alamat) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $result = execute($sql, [$name, $email, $nis, $password, $role, $kelas, $jenis_kelamin, $no_hp, $alamat], 'sssssssss');
    
    if ($result['success']) {
        setFlash('success', 'User berhasil ditambahkan!');
        redirect('admin/users/index.php');
    } else {
        setFlash('danger', 'Gagal menambahkan user!');
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
</head>
<body>
    <nav class="navbar navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-4">
                    <a href="<?php echo BASE_URL; ?>admin/users/index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <h2 class="mb-4"><i class="bi bi-plus-circle"></i> Tambah User</h2>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" id="roleSelect" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="pembina">Pembina</option>
                                    <option value="siswa">Siswa</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3" id="emailField">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>

                                <div class="col-md-6 mb-3 d-none" id="nisField">
                                    <label class="form-label">NIS <span class="text-danger">*</span></label>
                                    <input type="text" name="nis" class="form-control">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>

                            <div id="siswaFields" class="d-none">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kelas</label>
                                        <select name="kelas" class="form-select">
                                            <option value="">Pilih Kelas</option>
                                            <?php 
                                            for ($i = 7; $i <= 9; $i++) {
                                                foreach (['A', 'B', 'C', 'D'] as $huruf) {
                                                    echo "<option value='$i$huruf'>$i$huruf</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="form-select">
                                            <option value="">Pilih</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">No HP</label>
                                        <input type="text" name="no_hp" class="form-control">
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Alamat</label>
                                        <textarea name="alamat" class="form-control" rows="2"></textarea>
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
    <script>
    document.getElementById('roleSelect').addEventListener('change', function() {
        const role = this.value;
        const siswaFields = document.getElementById('siswaFields');
        const nisField = document.getElementById('nisField');
        const emailField = document.getElementById('emailField');
        
        if (role === 'siswa') {
            siswaFields.classList.remove('d-none');
            nisField.classList.remove('d-none');
            nisField.querySelector('input').required = true;
            emailField.querySelector('input').required = false;
        } else {
            siswaFields.classList.add('d-none');
            nisField.classList.add('d-none');
            nisField.querySelector('input').required = false;
            emailField.querySelector('input').required = true;
        }
    });
    </script>
</body>
</html>