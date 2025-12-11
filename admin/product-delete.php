<?php

// ---- DELETE PRODUK ----
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  $cek = mysqli_query($conn, "SELECT gambar FROM produk WHERE id='$id'");
  $g = mysqli_fetch_assoc($cek)['gambar'];
  if (file_exists("uploads/" . $g)) unlink("uploads/" . $g);

  mysqli_query($conn, "DELETE FROM produk WHERE id='$id'");
  header("Location: products.php?success=deleted");
  exit;
}
