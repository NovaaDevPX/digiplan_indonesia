<?php
require '../include/conn.php';

if (isset($_GET['id'])) {
  $id = (int) $_GET['id'];

  $delete = mysqli_query($conn, "DELETE FROM barang WHERE id='$id'");

  if ($delete) {
    header("Location: item.php?success=item_deleted");
    exit;
  } else {
    header("Location: item.php?error=delete_failed");
    exit;
  }
} else {
  header("Location: item.php");
  exit;
}
