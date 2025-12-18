<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

/**
 * Update status permintaan dan buat notifikasi
 */
function updatePermintaanStatus($conn, $id, $aksi, $superadmin_id, $catatan_admin = null)
{
  // Ambil data permintaan
  $res = $conn->query("
    SELECT 
      pb.user_id,
      pb.nama_barang,
      u.name AS nama_user
    FROM permintaan_barang pb
    JOIN users u ON pb.user_id = u.id
    WHERE pb.id = $id
  ");

  if (!$res || $res->num_rows === 0) return false;
  $data = $res->fetch_assoc();

  $user_id      = $data['user_id'];
  $nama_barang  = $data['nama_barang'];
  $nama_user    = $data['nama_user'];

  /**
   * Tentukan status & pesan
   */
  if ($aksi === 'terima') {
    $status_baru = 'disetujui';

    $pesan_user  = "Permintaan barang \"$nama_barang\" Anda telah disetujui oleh Super Admin.";
    $pesan_admin = "Super Admin menyetujui permintaan \"$nama_barang\" dari $nama_user.";
  } elseif ($aksi === 'tolak') {
    $status_baru = 'ditolak';

    $pesan_user  = "Permintaan barang \"$nama_barang\" Anda ditolak oleh Super Admin"
      . ($catatan_admin ? " dengan catatan: $catatan_admin" : "") . ".";

    $pesan_admin = "Super Admin menolak permintaan \"$nama_barang\" dari $nama_user.";
  } else {
    return false;
  }

  /**
   * ==========================
   * UPDATE PERMINTAAN BARANG
   * ==========================
   */
  if ($aksi === 'tolak') {
    $stmt = $conn->prepare("
      UPDATE permintaan_barang 
      SET 
        status = ?, 
        catatan_admin = ?, 
        tanggal_verifikasi = NOW(), 
        admin_id = ?
      WHERE id = ?
    ");
    if (!$stmt) return false;

    $stmt->bind_param("ssii", $status_baru, $catatan_admin, $superadmin_id, $id);
  } else {
    $stmt = $conn->prepare("
      UPDATE permintaan_barang 
      SET 
        status = ?, 
        tanggal_verifikasi = NOW(), 
        admin_id = ?
      WHERE id = ?
    ");
    if (!$stmt) return false;

    $stmt->bind_param("sii", $status_baru, $superadmin_id, $id);
  }

  $hasil = $stmt->execute();
  $stmt->close();

  if (!$hasil) return false;

  /**
   * ==========================
   * NOTIFIKASI USER
   * ==========================
   */
  $stmtUser = $conn->prepare("
    INSERT INTO notifikasi (user_id, permintaan_id, pesan)
    VALUES (?, ?, ?)
  ");
  if (!$stmtUser) return false;

  $stmtUser->bind_param("iis", $user_id, $id, $pesan_user);
  $stmtUser->execute();
  $stmtUser->close();

  /**
   * ==========================
   * NOTIFIKASI SUPER ADMIN
   * ==========================
   */
  $stmtAdmin = $conn->prepare("
    INSERT INTO notifikasi (user_id, permintaan_id, pesan)
    VALUES (?, ?, ?)
  ");
  if (!$stmtAdmin) return false;

  $stmtAdmin->bind_param("iis", $superadmin_id, $id, $pesan_admin);
  $stmtAdmin->execute();
  $stmtAdmin->close();

  return true;
}

/**
 * ==========================
 * PROSES FORM
 * ==========================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['aksi'])) {

  $id_permintaan = (int) $_POST['id'];
  $aksi          = $_POST['aksi'];
  $superadmin_id = $_SESSION['user_id'];

  $catatan_admin = isset($_POST['catatan_admin'])
    ? trim($_POST['catatan_admin'])
    : null;

  $hasil = updatePermintaanStatus(
    $conn,
    $id_permintaan,
    $aksi,
    $superadmin_id,
    $catatan_admin
  );

  if ($hasil) {
    $success = ($aksi === 'terima') ? 'item_approv' : 'item_decline';
    header("Location: /digiplan_indonesia/superadmin/item-approval.php?success=$success");
  } else {
    header("Location: /digiplan_indonesia/superadmin/item-approval.php?error=process_failed");
  }
  exit;
}

/**
 * ==========================
 * QUERY DATA PERMINTAAN
 * ==========================
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
if (!$result) die("Query gagal: " . $conn->error);
