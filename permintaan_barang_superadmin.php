<?php
require 'function.php';
require 'cek.php';
cek_role(['super_admin']);

$sql = "SELECT pb.*, du.name AS nama_user, a.name AS nama_admin
        FROM permintaan_barang pb
        JOIN users du ON pb.user_id = du.id
        LEFT JOIN users a ON pb.admin_id = a.id
        ORDER BY pb.tanggal_permintaan DESC";

$result = $conn->query($sql);
if (!$result) die("Query gagal: " . $conn->error);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Permintaan Barang | Super Admin</title>
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

                    <a class="nav-link" href="permintaan_barang_superadmin.php">
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

                    <a class="nav-link" href="data_user.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-users-cog"></i></div> User Management 
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
    <h2>Persetujuan Permintaan Barang</h2>
    <div class="card mt-3 mb-5">
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Customer</th>
                        <th>Admin</th>
                        <th>Barang</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no=1; while($row=$result->fetch_assoc()): 
                $permintaan_id = $row['id']; // ✅ tambahkan ini
                 ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_user']); ?></td>
                        <td><?= $row['nama_admin'] ?: '<i>Belum diverifikasi</i>'; ?></td>
                        <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                        <td><?= $row['jumlah']; ?></td>
                        <td>
                            <?php 
                            if ($row['status']=='Diterima') echo "<span class='badge bg-success'>Diterima</span>";
                            elseif ($row['status']=='Ditolak') echo "<span class='badge bg-danger'>Ditolak</span>";
                            elseif ($row['status']=='Menunggu Super Admin') echo "<span class='badge bg-info text-dark'>Menunggu Super Admin</span>";
                            else echo "<span class='badge bg-warning text-dark'>Proses</span>";
                            ?>
                        </td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal_permintaan'])); ?></td>
                        <td>
                            <?php if ($row['status']=='Menunggu Super Admin'): ?>
                                <a href="proses_permintaan_superadmin.php?id=<?= $row['id']; ?>&aksi=terima" class="btn btn-success btn-sm">
                                    <i class="fas fa-check"></i> Setujui
                                </a>
                                <a href="proses_permintaan_superadmin.php?id=<?= $row['id']; ?>&aksi=tolak" class="btn btn-danger btn-sm">
                                    <i class="fas fa-times"></i> Tolak
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>
