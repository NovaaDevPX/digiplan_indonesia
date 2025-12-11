<?php
require 'function.php';
require 'cek.php';
cek_role(['admin']);

// =============================
//  PROSES SIMPAN / INSERT
// =============================
if (isset($_POST['simpan_pengadaan'])) {
    $admin_id        = $_POST['admin_id'];
    $barang_id       = $_POST['barang_id'];
    $jumlah          = $_POST['jumlah'];
    $harga_total     = $_POST['harga_total'];
    $merk            = $_POST['merk'];
    $warna           = $_POST['warna'];
    $deskripsi       = $_POST['deskripsi_barang'];
    $nama            = $_POST['nama'];
    $kontak          = $_POST['kontak'];
    $alamat          = $_POST['alamat'];
    $tanggal         = $_POST['tanggal'];

    mysqli_query($conn, "INSERT INTO pengadaan_barang 
        (admin_id, barang_id, jumlah, harga_total, merk, warna, deskripsi_barang, nama, kontak, alamat, tanggal)
        VALUES ('$admin_id','$barang_id','$jumlah','$harga_total','$merk','$warna','$deskripsi','$nama','$kontak','$alamat','$tanggal')");
}

// =============================
//  PROSES UPDATE
// =============================
if (isset($_POST['edit_pengadaan'])) {
    $id             = $_POST['id'];
    $admin_id       = $_POST['admin_id'];
    $barang_id      = $_POST['barang_id'];
    $jumlah         = $_POST['jumlah'];
    $harga_total    = $_POST['harga_total'];
    $merk           = $_POST['merk'];
    $warna          = $_POST['warna'];
    $deskripsi      = $_POST['deskripsi_barang'];
    $nama           = $_POST['nama'];
    $kontak         = $_POST['kontak'];
    $alamat         = $_POST['alamat'];
    $tanggal        = $_POST['tanggal'];

    mysqli_query($conn, "UPDATE pengadaan_barang SET 
        admin_id='$admin_id',
        barang_id='$barang_id',
        jumlah='$jumlah',
        harga_total='$harga_total',
        merk='$merk',
        warna='$warna',
        deskripsi_barang='$deskripsi',
        nama='$nama',
        kontak='$kontak',
        alamat='$alamat',
        tanggal='$tanggal'
        WHERE id='$id'");
}

// =============================
//  PROSES HAPUS
// =============================
if (isset($_POST['hapus_pengadaan'])) {
    $id = $_POST['id'];
    mysqli_query($conn, "DELETE FROM pengadaan_barang WHERE id='$id'");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Pengadaan Barang | Admin</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">

<!-- TOP NAVBAR -->
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="#">DigiPlan Indonesia</a>
    <button class="btn btn-link btn-sm" id="sidebarToggle"><i class="fas fa-bars"></i></button>
</nav>

<div id="layoutSidenav">

    <!-- SIDEBAR -->
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

    <!-- CONTENT -->
    <div id="layoutSidenav_content">
        <main class="container-fluid px-4">

            <h2 class="mt-4">Pengadaan Barang</h2>

            <!-- FORM INPUT -->
            <div class="card mb-4">
                <div class="card-header">Tambah Pengadaan Barang</div>
                <div class="card-body">
                    <form method="POST" action="pengadaan_barang_admin.php">
                        <div class="row">

                            <div class="col-md-3 mb-3">
                                <label>Admin ID</label>
                                <input type="number" name="admin_id" class="form-control" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Barang ID</label>
                                <input type="number" name="barang_id" class="form-control" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Jumlah</label>
                                <input type="number" name="jumlah" class="form-control" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Harga Total</label>
                                <input type="number" name="harga_total" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Merk Barang</label>
                                <input type="text" name="merk" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Warna</label>
                                <input type="text" name="warna" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Tanggal Pengadaan</label>
                                <input type="date" name="tanggal" class="form-control" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Deskripsi Barang</label>
                                <textarea name="deskripsi_barang" class="form-control"></textarea>
                            </div>

                            <h5 class="mt-4">Data Supplier</h5>

                            <div class="col-md-4 mb-3">
                                <label>Nama Supplier</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Kontak</label>
                                <input type="text" name="kontak" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Alamat</label>
                                <input type="text" name="alamat" class="form-control">
                            </div>

                        </div>
                        <button type="submit" name="simpan_pengadaan" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>

            <!-- TABLE DATA -->
            <div class="card mb-4">
                <div class="card-header">Data Pengadaan Barang</div>
                <div class="card-body p-0">

                    <table class="table table-striped table-bordered m-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Admin</th>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga Total</th>
                                <th>Merk</th>
                                <th>Warna</th>
                                <th>Deskripsi</th>
                                <th>Nama Supplier</th>
                                <th>Kontak</th>
                                <th>Alamat</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                              <tbody>
                        <?php
                        $data = mysqli_query($conn, "SELECT * FROM pengadaan_barang ORDER BY id DESC");

                        while ($row = mysqli_fetch_assoc($data)) {
                            $id = $row['id'];
                        ?>
                            <tr>
                                <td><?= $row['id']; ?></td>
                                <td><?= $row['admin_id']; ?></td>
                                <td><?= $row['barang_id']; ?></td>
                                <td><?= $row['jumlah']; ?></td>
                                <td><?= $row['harga_total']; ?></td>
                                <td><?= $row['merk']; ?></td>
                                <td><?= $row['warna']; ?></td>
                                <td><?= $row['deskripsi_barang']; ?></td>
                                <td><?= $row['nama']; ?></td>
                                <td><?= $row['kontak']; ?></td>
                                <td><?= $row['alamat']; ?></td>
                                <td><?= $row['tanggal']; ?></td>

                                <td>
                                    <!-- BUTTON EDIT -->
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit<?= $id ?>">Edit</button>

                                    <!-- BUTTON HAPUS -->
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapus<?= $id ?>">Hapus</button>
                                </td>
                            </tr>

                         <!-- ======================== -->
                            <!-- MODAL EDIT -->
                            <!-- ======================== -->

                            <div class="modal fade" id="edit<?= $id ?>">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">

                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5>Edit Pengadaan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">

                                                <input type="hidden" name="id" value="<?= $id ?>">

                                                <div class="row">

                                                    <div class="col-md-4 mb-3">
                                                        <label>Admin</label>
                                                        <input type="text" name="admin_id" class="form-control" value="<?= $row['admin_id'] ?>">
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label>Barang ID</label>
                                                        <input type="text" name="barang_id" class="form-control" value="<?= $row['barang_id'] ?>">
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label>Jumlah</label>
                                                        <input type="number" name="jumlah" class="form-control" value="<?= $row['jumlah'] ?>">
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label>Harga Total</label>
                                                        <input type="number" name="harga_total" class="form-control" value="<?= $row['harga_total'] ?>">
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label>Merk</label>
                                                        <input type="text" name="merk" class="form-control" value="<?= $row['merk'] ?>">
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label>Warna</label>
                                                        <input type="text" name="warna" class="form-control" value="<?= $row['warna'] ?>">
                                                    </div>

                                                    <div class="col-md-12 mb-3">
                                                        <label>Deskripsi</label>
                                                        <textarea name="deskripsi_barang" class="form-control"><?= $row['deskripsi_barang']; ?></textarea>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label>Nama Supplier</label>
                                                        <input type="text" name="nama" class="form-control" value="<?= $row['nama'] ?>">
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label>Kontak</label>
                                                        <input type="text" name="kontak" class="form-control" value="<?= $row['kontak'] ?>">
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label>Alamat</label>
                                                        <input type="text" name="alamat" class="form-control" value="<?= $row['alamat'] ?>">
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label>Tanggal</label>
                                                        <input type="date" name="tanggal" class="form-control" value="<?= $row['tanggal'] ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="submit" name="edit_pengadaan" class="btn btn-warning">Simpan Perubahan</button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>

                            <!-- ======================== -->
                            <!-- MODAL HAPUS -->
                            <!-- ======================== -->

                            <div class="modal fade" id="hapus<?= $id ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5>Hapus Data?</h5>
                                                <button class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus data ini?
                                                <input type="hidden" name="id" value="<?= $id ?>">
                                            </div>

                                            <div class="modal-footer">
                                                <button type="submit" name="hapus_pengadaan" class="btn btn-danger">Hapus</button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>

                        <?php } ?>
                        </tbody>

                    </table>

                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>