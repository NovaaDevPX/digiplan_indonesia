<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

// Fungsi untuk update status (Terima/Tolak)
function updatePermintaanStatus($conn, $id, $aksi, $superadmin_id, $catatan_admin = null)
{
  if ($aksi === 'terima') {
    $status_baru = 'accepted_by_superadmin';
    $aksi_log = "Permintaan ID $id disetujui oleh Super Admin";
  } elseif ($aksi === 'tolak') {
    $status_baru = 'reject';
    $aksi_log = "Permintaan ID $id ditolak oleh Super Admin" . ($catatan_admin ? " dengan catatan: $catatan_admin" : "");
  } else {
    return false;
  }

  // Update permintaan_barang
  if ($aksi === 'tolak') {
    $stmt = $conn->prepare("UPDATE permintaan_barang SET status = ?, catatan_admin = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status_baru, $catatan_admin, $id);
  } else {
    $stmt = $conn->prepare("UPDATE permintaan_barang SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status_baru, $id);
  }
  $hasil = $stmt->execute();
  $stmt->close();

  if ($hasil) {
    // Audit trail
    $stmt2 = $conn->prepare("INSERT INTO audit_trail (user_id, aksi, tabel_yang_diubah) VALUES (?, ?, 'permintaan_barang')");
    $stmt2->bind_param("is", $superadmin_id, $aksi_log);
    $stmt2->execute();
    $stmt2->close();
    return true;
  }

  return false;
}

// Jika ada POST (aksi Terima/Tolak)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['aksi'])) {
  $id_permintaan = intval($_POST['id']);
  $aksi = $_POST['aksi'];
  $superadmin_id = $_SESSION['user_id'];
  $catatan_admin = $_POST['catatan_admin'] ?? null;

  updatePermintaanStatus($conn, $id_permintaan, $aksi, $superadmin_id, $catatan_admin);

  header("Location: /digiplan_indonesia/superadmin/item-approval.php");
  exit;
}

// Query untuk menampilkan data permintaan
$sql = "SELECT pb.*, du.name AS nama_user, a.name AS nama_admin
        FROM permintaan_barang pb
        JOIN users du ON pb.user_id = du.id
        LEFT JOIN users a ON pb.admin_id = a.id
        ORDER BY pb.tanggal_permintaan DESC";

$result = $conn->query($sql);
if (!$result) die("Query gagal: " . $conn->error);
