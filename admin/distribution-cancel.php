<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if (!isset($_GET['id'])) {
  header("Location: distribution.php");
  exit;
}

$distribusi_id  = (int) $_GET['id'];
$admin_id  = (int) $_SESSION['user_id'];

$conn->begin_transaction();

try {

  /* =========================
     AMBIL DATA DISTRIBUSI
  ========================= */
  $stmt = $conn->prepare("
    SELECT 
      d.id,
      d.kode_distribusi,
      d.permintaan_id,
      d.status_distribusi,
      pm.user_id AS customer_id,
      pm.kode_permintaan,
      pm.nama_barang,
      u.name AS customer_name
    FROM distribusi_barang d
    JOIN permintaan_barang pm ON d.permintaan_id = pm.id
    JOIN users u ON pm.user_id = u.id
    WHERE d.id = ?
      AND d.deleted_at IS NULL
  ");
  $stmt->bind_param("i", $distribusi_id);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$data) {
    throw new Exception('Distribusi tidak ditemukan');
  }

  if ($data['status_distribusi'] !== 'dikirim') {
    throw new Exception('Distribusi tidak dapat dibatalkan');
  }

  /* =========================
     UPDATE DISTRIBUSI
  ========================= */
  $stmtCancel = $conn->prepare("
    UPDATE distribusi_barang
    SET status_distribusi = 'dibatalkan',
        deleted_at = NOW()
    WHERE id = ?
  ");
  $stmtCancel->bind_param("i", $distribusi_id);
  $stmtCancel->execute();
  $stmtCancel->close();

  /* =========================
     UPDATE STATUS PERMINTAAN
  ========================= */
  $stmtPM = $conn->prepare("
    UPDATE permintaan_barang
    SET status = 'siap_distribusi'
    WHERE id = ?
  ");
  $stmtPM->bind_param("i", $data['permintaan_id']);
  $stmtPM->execute();
  $stmtPM->close();

  /* =========================
     NOTIFIKASI
  ========================= */

  /* PESAN ADMIN */
  $pesan_admin =
    "Distribusi dibatalkan oleh Admin\n\n" .
    "Kode Distribusi : {$data['kode_distribusi']}\n" .
    "Kode Permintaan : {$data['kode_permintaan']}\n" .
    "Barang          : {$data['nama_barang']}\n" .
    "Status          : Dikirim → Dibatalkan";

  /* PESAN CUSTOMER */
  $pesan_customer =
    "Halo {$data['customer_name']},\n\n" .
    "Distribusi barang Anda telah dibatalkan oleh Admin.\n\n" .
    "Kode Distribusi : {$data['kode_distribusi']}\n" .
    "Barang          : {$data['nama_barang']}\n\n" .
    "Status permintaan Anda dikembalikan ke tahap *siap distribusi*.\n" .
    "Silakan menunggu informasi selanjutnya.";

  /* KIRIM KE CUSTOMER */
  insertNotifikasi(
    $conn,
    (int) $data['customer_id'], // receiver
    $admin_id,             // sender
    (int) $data['permintaan_id'],
    $pesan_admin,
    $pesan_customer
  );

  /* LOG ADMIN */
  insertNotifikasi(
    $conn,
    $admin_id,
    $admin_id,
    (int) $data['permintaan_id'],
    $pesan_admin,
    null
  );

  $conn->commit();

  header("Location: distribution.php?cancel=success");
  exit;
} catch (Exception $e) {
  $conn->rollback();
  header("Location: distribution.php?cancel=failed");
  exit;
}
