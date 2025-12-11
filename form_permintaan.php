<?php
session_start();
require 'function.php';
require 'cek.php'; // memastikan user sudah login

// Ambil ID user dari session login
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Jika form dikirim
if (isset($_POST['submit'])) {
    $nama_barang = trim($_POST['nama_barang']);
    $merk = trim($_POST['merk']);
    $warna = trim($_POST['warna']);
    $deskripsi = trim($_POST['deskripsi']);
    $jumlah = intval($_POST['jumlah']);

    if (empty($nama_barang) || empty($jumlah)) {
        echo "<script>alert('Nama barang dan jumlah wajib diisi!');</script>";
    } else {
        $stmt = $conn->prepare("
    INSERT INTO permintaan_barang (user_id, nama_barang, merk, warna, deskripsi, jumlah, status, tanggal_permintaan)
    VALUES (?, ?, ?, ?, ?, ?, 'proses', NOW())");
    $stmt->bind_param("issssi", $user_id, $nama_barang, $merk, $warna, $deskripsi, $jumlah);
   
    if ($insert_success) {
    $permintaan_id = mysqli_insert_id($conn);

    // Ambil semua admin & super admin
    $res_admin = mysqli_query($conn, "SELECT id FROM users WHERE role IN ('admin','super_admin')");
    $daftar_admin = [];
    while ($r = mysqli_fetch_assoc($res_admin)) {
        $daftar_admin[] = $r['id'];
    }

    tambahNotifikasi($daftar_admin, $permintaan_id, "Ada <b>permintaan barang baru</b> dari customer.");
}

        if ($stmt->execute()) {
            echo "<script>alert('Permintaan barang berhasil dikirim!'); window.location='riwayat_permintaan.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal menyimpan data. Coba lagi!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Dashboard Customer | DigiPlan Indonesia</title>
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


    <!-- Sidebar + Content Layout -->
    <div id="layoutSidenav">
        <!-- Sidebar -->
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Menu</div>
                        <a class="nav-link" href="customer_dashboard.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link" href="form_permintaan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-edit"></i></div>
                            Form Permintaan Barang
                        </a>
                        <a class="nav-link" href="riwayat_permintaan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-history"></i></div>
                            Riwayat Permintaan
                        </a>
                        <a class="nav-link" href="invoice_customer.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-history"></i></div>
                            Invoice
                        </a>
                        <a class="nav-link" href="logout.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                            Logout
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    <?= htmlspecialchars($_SESSION['name']); ?>
                </div>
            </nav>
        </div>

<body class="sb-nav-fixed">
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Form Permintaan Barang</h2>
                <div class="card mt-3 mb-5">
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" name="nama_barang" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Merek</label>
                                <input type="text" class="form-control" name="merk">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Warna</label>
                                <input type="text" class="form-control" name="warna">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jumlah</label>
                                <input type="number" class="form-control" name="jumlah" min="1" required>
                            </div>

                            <button type="submit" name="submit" class="btn btn-primary">Kirim Permintaan</button>
                
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
     <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
