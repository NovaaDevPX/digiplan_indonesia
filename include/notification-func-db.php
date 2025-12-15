<?php
function insertNotifikasiDB($conn, $user_id, $permintaan_id, $pesan)
{
  $stmt = $conn->prepare("
        INSERT INTO notifikasi (user_id, permintaan_id, pesan)
        VALUES (?, ?, ?)
    ");
  $stmt->bind_param("iis", $user_id, $permintaan_id, $pesan);
  $stmt->execute();
}
