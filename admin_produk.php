<?php
require 'function.php';
require 'cek.php';

// hanya admin
cek_role(['admin']);

// ---- ADD PRODUK ----
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];

    // upload gambar
    $filename = $_FILES['gambar']['name'];
    $tmp_name = $_FILES['gambar']['tmp_name'];

    $newname = time() . '_' . $filename;
    move_uploaded_file($tmp_name, "uploads/" . $newname);

    mysqli_query($conn, "INSERT INTO produk (nama_produk, harga, gambar, deskripsi, created_at) 
        VALUES ('$nama', '$harga', '$newname', '$deskripsi', NOW())");

    header("Location: admin_produk.php?success=tambah");
    exit;
}

// ---- DELETE PRODUK ----
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    // hapus file gambar
    $cek = mysqli_query($conn, "SELECT gambar FROM produk WHERE id='$id'");
    $g = mysqli_fetch_assoc($cek)['gambar'];
    if (file_exists("uploads/" . $g)) unlink("uploads/" . $g);

    mysqli_query($conn, "DELETE FROM produk WHERE id='$id'");
    header("Location: admin_produk.php?success=hapus");
    exit;
}

// ---- EDIT PRODUK ----
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];

    // cek apakah upload gambar baru
    if ($_FILES['gambar']['name'] != "") {
        $filename = $_FILES['gambar']['name'];
        $tmp_name = $_FILES['gambar']['tmp_name'];

        $newname = time() . '_' . $filename;
        move_uploaded_file($tmp_name, "uploads/" . $newname);

        // hapus gambar lama
        $cek = mysqli_query($conn, "SELECT gambar FROM produk WHERE id='$id'");
        $g = mysqli_fetch_assoc($cek)['gambar'];
        if (file_exists("uploads/" . $g)) unlink("uploads/" . $g);

        $update_gambar = ", gambar='$newname'";
    } else {
        $update_gambar = "";
    }

    mysqli_query($conn, "UPDATE produk SET 
        nama_produk='$nama', 
        harga='$harga',
        deskripsi='$deskripsi'
        $update_gambar
        WHERE id='$id'");

    header("Location: admin_produk.php?success=edit");
    exit;
}

// ambil semua produk
$produk = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Super Admin Dashboard | DigiPlan Indonesia</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
<!-- Top Navbar -->
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="#">DigiPlan Indonesia</a>
    <button class="btn btn-link btn-sm" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <ul class="navbar-nav ms-auto me-3 me-lg-4">
        <li class="nav-item dropdown">
        </li>
    </ul>
</nav>

<!-- Layout -->
<div id="layoutSidenav">
    <!-- Sidebar -->
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading">Menu</div>

                    <a class="nav-link" href="superadmin_dashboard.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div> Dashboard
                    </a>

                    <a class="nav-link" href="admin_produk.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div> Kelola Produk
                    </a>

                    <a class="nav-link" href="permintaan_barang_admin.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div> Permintaan Barang
                    </a>

                    <a class="nav-link" href="pengadaan_barang.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-truck-loading"></i></div> Pengadaan Barang
                    </a>

                    <a class="nav-link" href="distribusi_barang.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-shipping-fast"></i></div> Distribusi Barang
                    </a>

                    <a class="nav-link" href="stok_barang.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div> Barang
                    </a>

                    <a class="nav-link" href="laporan.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div> Laporan
                    </a>

                    <a class="nav-link" href="logout.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div> Logout
                    </a>
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                <?= htmlspecialchars($_SESSION['name']); ?>
            </div>
        </nav>
    </div>

<!-- Content -->
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h1 class="mb-4">Kelola Produk</h1>
                <div class="card mb-4">
                <div class="card-body">

    <!-- Notifikasi -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php
                if ($_GET['success'] == 'tambah') echo "Produk berhasil ditambahkan!";
                if ($_GET['success'] == 'edit') echo "Produk berhasil diperbarui!";
                if ($_GET['success'] == 'hapus') echo "Produk berhasil dihapus!";
            ?>
        </div>
    <?php endif; ?>

    <!-- Tombol Tambah -->
    <?php if (!isset($_GET['edit'])): ?>
    <a href="admin_produk.php?tambah=1" class="btn btn-primary mb-3">Tambah Produk</a>
    <?php endif; ?>

    <!-- FORM TAMBAH -->
    <?php if (isset($_GET['tambah'])): ?>
    <div class="card mb-4">
        <div class="card-header">Tambah Produk</div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <label>Nama Produk</label>
                <input type="text" name="nama_produk" required class="form-control mb-2">
                
                <label>Harga</label>
                <input type="number" name="harga" required class="form-control mb-2">
                
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control mb-2"></textarea>

                <label>Gambar</label>
                <input type="file" name="gambar" required class="form-control mb-3">

                <button name="tambah" class="btn btn-success">Simpan</button>
                <a href="admin_produk.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- FORM EDIT -->
    <?php if (isset($_GET['edit'])): 
        $id = $_GET['edit'];
        $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id='$id'"));
    ?>
    <div class="card mb-4">
        <div class="card-header">Edit Produk</div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $p['id']; ?>">

                <label>Nama Produk</label>
                <input type="text" name="nama_produk" value="<?= $p['nama_produk']; ?>" required class="form-control mb-2">
                
                <label>Harga</label>
                <input type="number" name="harga" value="<?= $p['harga']; ?>" required class="form-control mb-2">
                
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control mb-2"><?= $p['deskripsi']; ?></textarea>

                <label>Gambar Saat Ini</label><br>
                <img src="uploads/<?= $p['gambar']; ?>" width="120" class="mb-3"><br>

                <label>Ganti Gambar (opsional)</label>
                <input type="file" name="gambar" class="form-control mb-3">

                <button name="edit" class="btn btn-warning">Update</button>
                <a href="admin_produk.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- LIST PRODUK -->
    <div class="card">
        <div class="card-header">Daftar Produk</div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($p = mysqli_fetch_assoc($produk)): ?>
                    <tr>
                        <td><img src="uploads/<?= $p['gambar']; ?>" width="80"></td>
                        <td><?= $p['nama_produk']; ?></td>
                        <td>Rp <?= number_format($p['harga']); ?></td>
                        <td><?= $p['deskripsi']; ?></td>
                        <td width="150">
                            <a href="admin_produk.php?edit=<?= $p['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a onclick="return confirm('Hapus produk?')" 
                               href="admin_produk.php?hapus=<?= $p['id']; ?>" 
                               class="btn btn-sm btn-danger">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile ?>
                </tbody>
            </table>
        </div>
    </div>
    
</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>
