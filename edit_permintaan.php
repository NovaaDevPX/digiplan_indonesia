<?php
require 'function.php';
require 'cek.php';
session_start();

if (!isset($_GET['id'])) {
    header('Location: riwayat_permintaan.php');
    exit;
}

$id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Ambil data permintaan yang dimiliki user
$stmt = $conn->prepare("SELECT * FROM permintaan_barang WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$permintaan = $result->fetch_assoc();

if (!$permintaan) {
    die("Data tidak ditemukan atau bukan milik Anda!");
}

// Jika form disubmit
if (isset($_POST['update'])) {
    $nama_barang = $_POST['nama_barang'];
    $merk = $_POST['merk'];
    $warna = $_POST['warna'];
    $deskripsi = $_POST['deskripsi'];
    $jumlah = $_POST['jumlah'];

    $update = $conn->prepare("
        UPDATE permintaan_barang 
        SET nama_barang=?, merk=?, warna=?, deskripsi=?, jumlah=? 
        WHERE id=? AND user_id=?
    ");
    $update->bind_param("sssssii", $nama_barang, $merk, $warna, $deskripsi, $jumlah, $id, $user_id);

    if ($update->execute()) {
        echo "<script>alert('Permintaan berhasil diperbarui!'); window.location='riwayat_permintaan.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal memperbarui data!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Permintaan</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">
<div class="container mt-4">
    <h3>Edit Permintaan Barang</h3>
    <form method="POST">
        <div class="mb-3">
            <label>Nama Barang</label>
            <input type="text" name="nama_barang" value="<?= htmlspecialchars($permintaan['nama_barang']); ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Merk</label>
            <input type="text" name="merk" value="<?= htmlspecialchars($permintaan['merk']); ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Warna</label>
            <input type="text" name="warna" value="<?= htmlspecialchars($permintaan['warna']); ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control"><?= htmlspecialchars($permintaan['deskripsi']); ?></textarea>
        </div>
        <div class="mb-3">
            <label>Jumlah</label>
            <input type="number" name="jumlah" value="<?= $permintaan['jumlah']; ?>" class="form-control" required>
        </div>
        <button type="submit" name="update" class="btn btn-success">Simpan Perubahan</button>
        <a href="riwayat_permintaan.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>
</body>
</html>
