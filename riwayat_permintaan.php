<?php
require 'function.php';
require 'cek.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;

// --- Ambil input pencarian & filter ---
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : "";
$date_from = isset($_GET['date_from']) ? $conn->real_escape_string($_GET['date_from']) : "";
$date_to = isset($_GET['date_to']) ? $conn->real_escape_string($_GET['date_to']) : "";

// --- Query dasar ---
$sql = "SELECT * FROM permintaan_barang WHERE user_id = $user_id";

// --- Filter pencarian ---
if (!empty($search)) {
    $sql .= " AND (
                nama_barang LIKE '%$search%' 
                OR merk LIKE '%$search%' 
                OR warna LIKE '%$search%' 
                OR status LIKE '%$search%'
              )";
}

// --- Filter status ---
if (!empty($status_filter)) {
    $sql .= " AND status = '$status_filter'";
}

// --- Filter tanggal (range) ---
if (!empty($date_from) && !empty($date_to)) {
    $sql .= " AND DATE(tanggal_permintaan) BETWEEN '$date_from' AND '$date_to'";
} elseif (!empty($date_from)) {
    $sql .= " AND DATE(tanggal_permintaan) >= '$date_from'";
} elseif (!empty($date_to)) {
    $sql .= " AND DATE(tanggal_permintaan) <= '$date_to'";
}

// --- Urutkan terbaru ---
$sql .= " ORDER BY tanggal_permintaan DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Query gagal: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Riwayat Permintaan Barang | DigiPlan Indonesia</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="#">DigiPlan Indonesia</a>
        <button class="btn btn-link btn-sm" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ms-auto me-3 me-lg-4"></ul>
    </nav>

    <!-- Sidebar + Content -->
    <div id="layoutSidenav">
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
                    <h2>Riwayat Permintaan Barang</h2>
                    <div class="card mt-3 mb-5">
                        <div class="card-body">

                            <!-- Form Filter -->
                            <form method="get" class="row g-3 mb-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Cari Barang</label>
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Nama barang, merek, warna..."
                                           value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">-- Semua Status --</option>
                                        <option value="Proses" <?= ($status_filter == 'Proses') ? 'selected' : ''; ?>>Proses</option>
                                        <option value="Diterima" <?= ($status_filter == 'Diterima') ? 'selected' : ''; ?>>Diterima</option>
                                        <option value="Ditolak" <?= ($status_filter == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Dari Tanggal</label>
                                    <input type="date" name="date_from" class="form-control"
                                           value="<?= htmlspecialchars($date_from) ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Sampai Tanggal</label>
                                    <input type="date" name="date_to" class="form-control"
                                           value="<?= htmlspecialchars($date_to) ?>">
                                </div>
                                <div class="col-md-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="riwayat_permintaan.php" class="btn btn-secondary">
                                        <i class="fas fa-sync"></i> Reset
                                    </a>
                                </div>
                            </form>

                            <!-- Tabel Riwayat -->
                            <table class="table table-bordered mt-3">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Merek</th>
                                        <th>Warna</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Tanggal Permintaan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                $no = 1;
                                if ($result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                        <td><?= htmlspecialchars($row['merk']) ?></td>
                                        <td><?= htmlspecialchars($row['warna']) ?></td>
                                        <td><?= $row['jumlah'] ?></td>
                                        <td>
                                            <?php 
                                            if ($row['status'] == 'Diterima') {
                                                echo "<span class='badge bg-success'>Diterima</span>";
                                            } elseif ($row['status'] == 'Ditolak') {
                                                echo "<span class='badge bg-danger'>Ditolak</span>";
                                            } else {
                                                echo "<span class='badge bg-warning text-dark'>Proses</span>";
                                            }
                                            ?>
                                        </td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_permintaan'])) ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'Proses') : ?>
                                                <a href="edit_permintaan.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="hapus_permintaan.php?id=<?= $row['id']; ?>" 
                                                onclick="return confirm('Yakin ingin menghapus permintaan ini?');" 
                                                class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Tidak dapat diubah</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Tidak ada data ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
