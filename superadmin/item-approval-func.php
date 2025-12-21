<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['super_admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

/**
 * ==================================================
 * UPDATE STATUS PERMINTAAN + NOTIFIKASI
 * ==================================================
 */
function updatePermintaanStatus(
  mysqli $conn,
  int $permintaan_id,
  string $aksi,
  int $superadmin_id,
  ?string $catatan_admin = null
): bool {

  /* =========================
     AMBIL DATA PERMINTAAN
  ========================= */
  $q = $conn->prepare("
    SELECT 
      pb.id,
      pb.nama_barang,
      pb.user_id   AS customer_id,
      u.name       AS customer_name
    FROM permintaan_barang pb
    JOIN users u ON pb.user_id = u.id
    WHERE pb.id = ?
      AND pb.deleted_at IS NULL
  ");
  $q->bind_param("i", $permintaan_id);
  $q->execute();
  $data = $q->get_result()->fetch_assoc();
  $q->close();

  if (!$data) {
    return false;
  }

  /* =========================
     STATUS & PESAN
  ========================= */
  if ($aksi === 'terima') {

    $status_baru = 'disetujui';

    $pesan_customer =
      "Halo {$data['customer_name']},\n\n" .
      "Permintaan barang Anda telah *DISETUJUI* oleh Super Admin.\n\n" .
      "Barang: {$data['nama_barang']}\n\n" .
      "Permintaan Anda akan segera diproses ke tahap berikutnya.";

    $pesan_admin =
      "Super Admin menyetujui permintaan barang.\n\n" .
      "Barang : {$data['nama_barang']}\n" .
      "Customer : {$data['customer_name']}";
  } elseif ($aksi === 'tolak') {

    $status_baru = 'ditolak';

    $pesan_customer =
      "Halo {$data['customer_name']},\n\n" .
      "Permintaan barang Anda *DITOLAK* oleh Super Admin.\n\n" .
      "Barang: {$data['nama_barang']}" .
      ($catatan_admin ? "\n\nCatatan:\n$catatan_admin" : "");

    $pesan_admin =
      "Super Admin menolak permintaan barang.\n\n" .
      "Barang : {$data['nama_barang']}\n" .
      "Customer : {$data['customer_name']}";
  } else {
    return false;
  }

  $conn->begin_transaction();

  try {

    /* =========================
       UPDATE PERMINTAAN
    ========================= */
    if ($aksi === 'tolak') {

      $up = $conn->prepare("
        UPDATE permintaan_barang
        SET status = ?,
            catatan_admin = ?,
            tanggal_verifikasi = NOW(),
            admin_id = ?
        WHERE id = ?
      ");
      $up->bind_param(
        "ssii",
        $status_baru,
        $catatan_admin,
        $superadmin_id,
        $permintaan_id
      );
    } else {

      $up = $conn->prepare("
        UPDATE permintaan_barang
        SET status = ?,
            tanggal_verifikasi = NOW(),
            admin_id = ?
        WHERE id = ?
      ");
      $up->bind_param(
        "sii",
        $status_baru,
        $superadmin_id,
        $permintaan_id
      );
    }

    $up->execute();
    $up->close();

    /* =========================
       NOTIFIKASI CUSTOMER
    ========================= */
    insertNotifikasi(
      $conn,
      $data['customer_id'], // receiver
      $superadmin_id,       // sender
      $permintaan_id,
      $pesan_admin,         // fallback admin
      $pesan_customer       // pesan customer
    );

    /* =========================
       NOTIFIKASI SUPER ADMIN (LOG)
    ========================= */
    insertNotifikasi(
      $conn,
      $superadmin_id,       // receiver
      $superadmin_id,       // sender
      $permintaan_id,
      $pesan_admin,
      null
    );

    $conn->commit();
    return true;
  } catch (Throwable $e) {
    $conn->rollback();
    return false;
  }
}

/**
 * ==================================================
 * PROSES FORM
 * ==================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['aksi'])) {

  $permintaan_id = (int) $_POST['id'];
  $aksi          = $_POST['aksi'];
  $superadmin_id = (int) $_SESSION['user_id'];

  $catatan_admin = $_POST['catatan_admin'] ?? null;
  $catatan_admin = $catatan_admin ? trim($catatan_admin) : null;

  $hasil = updatePermintaanStatus(
    $conn,
    $permintaan_id,
    $aksi,
    $superadmin_id,
    $catatan_admin
  );

  if ($hasil) {
    $success = ($aksi === 'terima') ? 'item_approv' : 'item_decline';
    header("Location: item-approval.php?success=$success");
  } else {
    header("Location: item-approval.php?error=process_failed");
  }
  exit;
}

/**
 * ==================================================
 * QUERY DATA PERMINTAAN (UNTUK VIEW)
 * ==================================================
 */
$sql = "
  SELECT 
    pb.*,
    u.name AS nama_user,
    a.name AS nama_admin
  FROM permintaan_barang pb
  JOIN users u ON pb.user_id = u.id
  LEFT JOIN users a ON pb.admin_id = a.id
  ORDER BY pb.created_at DESC
";

$result = $conn->query($sql);
if (!$result) {
  die('Query gagal: ' . $conn->error);
}
