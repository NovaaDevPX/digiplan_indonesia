<?php
function insertNotifikasiDB($conn, $user_id, $permintaan_id, $pesan)
{
  $stmt = $conn->prepare("
    INSERT INTO notifikasi (user_id, permintaan_id, pesan)
    VALUES (?, ?, ?)
  ");

  if (!$stmt) {
    return false;
  }

  $stmt->bind_param("iis", $user_id, $permintaan_id, $pesan);
  $result = $stmt->execute();

  if (!$result) {
    return false;
  }

  $stmt->close();
  return true;
}
