<?php
require '../include/conn.php';

if (isset($_GET['id'])) {
  $id = (int) $_GET['id'];
  $delete = mysqli_query($conn, "DELETE FROM barang WHERE id='$id'");

  if ($delete) {
    echo "<script>alert('Barang berhasil dihapus!'); window.location='procurement.php';</script>";
  } else {
    echo "<script>alert('Gagal hapus barang!'); window.location='procurement.php';</script>";
  }
} else {
  header('Location: procurement.php');
}
