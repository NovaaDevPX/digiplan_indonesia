<?php
// Mulai sesi & koneksi
require 'include/conn.php';
require 'include/auth.php';

// Pastikan hanya customer yang bisa akses
cek_role(['customer']);

// Ambil ID user dari session
$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Ambil data permintaan user dari tabel permintaan_barang
// Status bisa "proses", "diterima", "ditolak"
$q_proses = mysqli_query($conn, "SELECT COUNT(*) AS total FROM permintaan_barang WHERE user_id = '$user_id' AND LOWER(status) = 'proses'");
$q_diterima = mysqli_query($conn, "SELECT COUNT(*) AS total FROM permintaan_barang WHERE user_id = '$user_id' AND LOWER(status) = 'diterima'");
$q_ditolak = mysqli_query($conn, "SELECT COUNT(*) AS total FROM permintaan_barang WHERE user_id = '$user_id' AND LOWER(status) = 'ditolak'");

$produk_q = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC LIMIT 6");

$proses   = ($q_proses)   ? mysqli_fetch_assoc($q_proses)['total']   : 0;
$diterima = ($q_diterima) ? mysqli_fetch_assoc($q_diterima)['total'] : 0;
$ditolak  = ($q_ditolak)  ? mysqli_fetch_assoc($q_ditolak)['total']  : 0;

// Total jumlah permintaan
$q_total = mysqli_query(
  $conn,
  "SELECT COUNT(*) AS total 
     FROM permintaan_barang 
     WHERE user_id = '$user_id'"
);

$total_permintaan = ($q_total) ? mysqli_fetch_assoc($q_total)['total'] : 0;

// Informasi pembayaran terakhir user
$q_bayar = mysqli_query(
  $conn,
  "SELECT * FROM pembayaran 
     WHERE user_id = '$user_id' 
     ORDER BY tanggal DESC 
     LIMIT 1"
);

$pembayaran = ($q_bayar) ? mysqli_fetch_assoc($q_bayar) : null;


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

  <div id="notif-container" style="position: fixed; top: 20px; right: 20px; z-index: 2000;"></div>

  <script>
    function tampilkanNotifikasi(pesan) {
      let box = document.createElement('div');
      box.innerHTML = pesan;
      box.className = 'alert alert-info shadow';
      box.style = 'margin-bottom:10px; min-width:250px;';
      document.getElementById('notif-container').appendChild(box);
      setTimeout(() => box.remove(), 5000);
    }

    function cekNotifikasi() {
      fetch('get_notifikasi.php')
        .then(res => res.json())
        .then(data => {
          data.forEach(n => tampilkanNotifikasi(n.pesan));
        });
    }

    setInterval(cekNotifikasi, 10000); // cek tiap 10 detik
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

    <!-- Content -->
    <div id="layoutSidenav_content">
      <main>
        <div class="container-fluid px-4 mt-4">
          <h1 class="mb-4">Selamat Datang, <?= htmlspecialchars($_SESSION['name']); ?>!</h1>
          <div class="card mb-4">
            <div class="card-body">

              <!-- RINGKASAN PERMINTAAN -->
              <h2 class="fw-bold mb-3">Ringkasan Permintaan</h2>

              <div class="row text-center">

                <div class="col-md-4 mb-3">
                  <div class="card border-left-dark shadow-sm py-3">
                    <div class="card-body">
                      <h6 class="text-muted mb-1">Total Permintaan</h6>
                      <h2 class="fw-bold"><?= $total_permintaan; ?></h2>
                    </div>
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <div class="card border-left-info shadow-sm py-3">
                    <div class="card-body">
                      <h6 class="text-muted mb-1">Permintaan Terbaru</h6>
                      <h4 class="fw-bold">
                        <?= $proses + $diterima + $ditolak > 0 ? "Ada Permintaan" : "Tidak Ada"; ?>
                      </h4>
                    </div>
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <div class="card border-left-warning shadow-sm py-3">
                    <div class="card-body">
                      <h6 class="text-muted mb-1">Status Terakhir</h6>
                      <h4 class="fw-bold text-warning">
                        <?= $proses > 0 ? "Dalam Proses" : ($diterima > 0 ? "Diterima" : "Ditolak"); ?>
                      </h4>
                    </div>
                  </div>
                </div>

              </div>

              <!-- STATUS PERMINTAAN -->
              <h2 class="mt-5 mb-3 fw-bold">Status Permintaan</h2>

              <div class="row text-center animate-container">
                <div class="col-md-4 mb-3">
                  <div class="card border-left-primary shadow-sm py-3 fade-in-up">
                    <div class="card-body">
                      <h5 class="text-primary mb-1">Dalam Proses 🕓</h5>
                      <h3 class="fw-bold"><?= $proses; ?></h3>
                    </div>
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <div class="card border-left-success shadow-sm py-3 fade-in-up">
                    <div class="card-body">
                      <h5 class="text-success mb-1">Diterima ✅</h5>
                      <h3 class="fw-bold"><?= $diterima; ?></h3>
                    </div>
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <div class="card border-left-danger shadow-sm py-3 fade-in-up">
                    <div class="card-body">
                      <h5 class="text-danger mb-1">Ditolak ❌</h5>
                      <h3 class="fw-bold"><?= $ditolak; ?></h3>
                    </div>
                  </div>
                </div>
              </div>

              <!-- INFORMASI PEMBAYARAN -->
              <h2 class="mt-5 mb-3 fw-bold">Informasi Pembayaran</h2>

              <?php if ($pembayaran) { ?>
                <div class="card shadow-sm">
                  <div class="card-body">

                    <div class="row">
                      <div class="col-md-4">
                        <h6 class="text-muted mb-1">Status Pembayaran</h6>
                        <span class="fw-bold 
                                                <?= $pembayaran['status'] == 'lunas' ? 'text-success' : 'text-warning'; ?>">
                          <?= strtoupper($pembayaran['status']); ?>
                        </span>
                      </div>

                      <div class="col-md-4">
                        <h6 class="text-muted mb-1">Jumlah</h6>
                        <span class="fw-bold">
                          Rp <?= number_format($pembayaran['jumlah'], 0, ',', '.'); ?>
                        </span>
                      </div>

                      <div class="col-md-4">
                        <h6 class="text-muted mb-1">Tanggal</h6>
                        <span class="fw-bold">
                          <?= date('d M Y', strtotime($pembayaran['tanggal'])); ?>
                        </span>
                      </div>
                    </div>

                  </div>
                </div>
              <?php } else { ?>
                <div class="alert alert-info">
                  Belum ada informasi pembayaran.
                </div>
              <?php } ?>

              <!-- PRODUK TERSEDIA (tetap seperti aslinya) -->
              <h2 class="mt-5 mb-3 fw-bold">Produk Tersedia</h2>


              <?php while ($p = mysqli_fetch_assoc($produk_q)) { ?>
                <div class="col-md-4 mb-4">
                  <div class="card shadow-sm h-100">

                    <img src="uploads/<?= $p['gambar']; ?>"
                      class="card-img-top"
                      style="height:180px; object-fit:cover;">

                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($p['nama_produk']); ?></h5>
                      <p class="text-muted">
                        <?= substr($p['deskripsi'], 0, 60); ?>...
                      </p>

                      <h6 class="fw-bold text-primary">
                        Rp <?= number_format($p['harga'], 0, ',', '.'); ?>
                      </h6>

                      <a href="detail_produk.php?id=<?= $p['id']; ?>"
                        class="btn btn-sm btn-primary mt-2">
                        Lihat Detail
                      </a>
                    </div>

                  </div>
                </div>
              <?php } ?>
            </div>

          </div>
        </div>
    </div>
    </main>
  </div>
  </div>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/scripts.js"></script>
</body>

</html>