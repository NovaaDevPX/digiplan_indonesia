<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

if (isset($_POST['simpan'])) {

  $admin_id = $_POST['admin_id'];
  $barang_id = $_POST['barang_id'];
  $permintaan_id = $_POST['permintaan_id'];
  $jumlah = $_POST['jumlah'];
  $harga_total = $_POST['harga_total'];
  $merk = $_POST['merk'];
  $warna = $_POST['warna'];
  $deskripsi_barang = $_POST['deskripsi_barang'];
  $nama = $_POST['nama'];
  $kontak = $_POST['kontak'];
  $alamat = $_POST['alamat'];
  $tanggal = $_POST['tanggal'];

  $stmt = $conn->prepare("
        INSERT INTO pengadaan_barang
        (admin_id, barang_id, permintaan_id, jumlah, harga_total, merk, warna, deskripsi_barang, nama, kontak, alamat, tanggal)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
    ");

  $stmt->bind_param(
    "iiidssssssss",
    $admin_id,
    $barang_id,
    $permintaan_id,
    $jumlah,
    $harga_total,
    $merk,
    $warna,
    $deskripsi_barang,
    $nama,
    $kontak,
    $alamat,
    $tanggal
  );

  if ($stmt->execute()) {
    header("Location: procurement.php?status=success");
    exit;
  } else {
    header("Location: procurement.php?error=failed");
    exit;
  }
}
