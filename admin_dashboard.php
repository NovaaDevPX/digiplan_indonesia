<?php
require 'function.php';
require 'cek.php';
cek_role(['admin']);

function queryCheck($conn, $query) {
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("<b>Query error:</b> " . mysqli_error($conn));
    }
    return $result;
}

$today = date('Y-m-d');
$currentMonth = date('Y-m');

// Permintaan barang hari ini
$q1 = queryCheck($conn, "SELECT COUNT(*) AS total FROM permintaan_barang WHERE DATE(tanggal_permintaan) = '$today'");
$permintaan_hari_ini = mysqli_fetch_assoc($q1)['total'] ?? 0;

// Permintaan barang diterima
$q2 = queryCheck($conn, "SELECT COUNT(*) AS total FROM permintaan_barang WHERE status = 'Diterima'");
$permintaan_diterima = mysqli_fetch_assoc($q2)['total'] ?? 0;

// Barang masuk bulan ini
$q3 = queryCheck($conn, "SELECT SUM(jumlah) AS total FROM pengadaan_barang WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$currentMonth'");
$barang_masuk = mysqli_fetch_assoc($q3)['total'] ?? 0;

// Barang keluar bulan ini
$q4 = queryCheck($conn, "SELECT SUM(jumlah) AS total FROM distribusi_barang WHERE DATE_FORMAT(tanggal_pengiriman, '%Y-%m') = '$currentMonth'");
$barang_keluar = mysqli_fetch_assoc($q4)['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Admin Dashboard | DigiPlan Indonesia</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">

<div id="notif-container" style="position: fixed; top: 20px; right: 20px; z-index: 2000;"></div>

<script>
function tampilkanNotifikasi(pesan) {
    let box = document.createElement('div');
    box.innerHTML = pesan;
    box.className = 'alert alert-info shadow';
    box.style = 'margin-bottom:10px; min-width:250px;';
    document.getElementById('notif-container').appendChild(box);
    setTimeout(() => box.remove(), 6000);
}

function cekNotifikasi() {
    fetch('get_notifikasi.php')
        .then(res => res.json())
        .then(data => {
            data.forEach(n => tampilkanNotifikasi(n.pesan));
        });
}

setInterval(cekNotifikasi, 10000); // cek setiap 10 detik
</script>


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
                    <a class="nav-link" href="admin_dashboard.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div> Dashboard
                    </a>
                    <a class="nav-link" href="admin_produk.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div> Kelola Produk
                    </a>
                    <a class="nav-link" href="permintaan_barang_admin.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div> Permintaan Barang
                    </a>
                    <a class="nav-link" href="pengadaan_barang_admin.php">
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
                    <a class="nav-link" href="chat.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-comments"></i></div> Chat
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
                <h1 class="mb-4">Aktivitas sistem hari ini:</h1>
                <div class="card mb-4">
                <div class="card-body">

                <!-- Statistik Ringkasan -->
                <div class="row mt-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white shadow-sm">
                            <div class="card-body text-center">
                                <h5>Permintaan Hari Ini</h5>
                                <h2><?= $permintaan_hari_ini; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white shadow-sm">
                            <div class="card-body text-center">
                                <h5>Permintaan Diterima</h5>
                                <h2><?= $permintaan_diterima; ?></h2>
                            </div>
                        </div>
                    </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white shadow-sm">
                <div class="card-body text-center">
                    <h5>Barang Masuk Bulan Ini</h5>
                    <h2><?= $barang_masuk; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body text-center">
                    <h5>Barang Keluar Bulan Ini</h5>
                    <h2><?= $barang_keluar; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Permintaan vs Distribusi -->
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-light">
            <i class="fas fa-chart-bar me-1"></i> Grafik Permintaan vs Distribusi Barang
        </div>
        <div class="card-body">
            <canvas id="chartPermintaanDistribusi" height="100"></canvas>
        </div>
    </div>
</div>

                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>
