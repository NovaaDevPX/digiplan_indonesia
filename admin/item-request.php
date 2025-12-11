<?php
require 'item-request-func.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Permintaan Barang | Admin</title>
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
          <h2>Daftar Permintaan Barang dari Customer</h2>
          <div class="card mt-3 mb-5">
            <div class="card-body">
              <table class="table table-border">
                <thead class="table-dark">
                  <tr>
                    <th>No</th>
                    <th>Nama Customer</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $no = 1;
                  while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $no++ ?></td>
                      <td><?= htmlspecialchars($row['nama_user']); ?></td>
                      <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                      <td><?= $row['jumlah']; ?></td>
                      <td>
                        <?php
                        if ($row['status'] == 'Diterima') echo "<span class='badge bg-success'>Diterima</span>";
                        elseif ($row['status'] == 'Ditolak') echo "<span class='badge bg-danger'>Ditolak</span>";
                        elseif ($row['status'] == 'Menunggu Super Admin') echo "<span class='badge bg-info'>Menunggu Super Admin</span>";
                        else echo "<span class='badge bg-warning text-dark'>Proses</span>";
                        ?>
                      </td>
                      <td><?= date('d-m-Y', strtotime($row['tanggal_permintaan'])); ?></td>
                      <td>
                        <?php if ($row['status'] == 'Proses'): ?>
                          <a href="proses_permintaan_admin.php?id=<?= $row['id']; ?>&aksi=ajukan" class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Ajukan ke Super Admin
                          </a>
                          <a href="proses_permintaan_admin.php?id=<?= $row['id']; ?>&aksi=tolak" class="btn btn-danger btn-sm">
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