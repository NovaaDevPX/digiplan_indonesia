<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

/**
 * Update status permintaan dan buat notifikasi ke user
 *
 * @param mysqli $conn
 * @param int $id
 * @param string $aksi ('terima'|'tolak')
 * @param int $superadmin_id
 * @param string|null $catatan_admin
 * @return bool
 */
function updatePermintaanStatus($conn, $id, $aksi, $superadmin_id, $catatan_admin = null)
{
  // Ambil user_id pemohon
  $res = $conn->query("SELECT user_id, nama_barang FROM permintaan_barang WHERE id = $id");
  if (!$res || $res->num_rows === 0) return false;
  $permintaan = $res->fetch_assoc();
  $user_id = $permintaan['user_id'];
  $nama_barang = $permintaan['nama_barang'];

  if ($aksi === 'terima') {
    $status_baru = 'disetujui';
    $pesan = "Permintaan $nama_barang Anda telah disetujui oleh Super Admin.";
  } elseif ($aksi === 'tolak') {
    $status_baru = 'ditolak';
    $pesan = "Permintaan $nama_barang Anda ditolak oleh Super Admin" . ($catatan_admin ? " dengan catatan: $catatan_admin" : "") . ".";
  } else {
    return false;
  }

  // Update permintaan_barang dan set admin_id
  if ($aksi === 'tolak') {
    $stmt = $conn->prepare("
            UPDATE permintaan_barang 
            SET status = ?, catatan_admin = ?, tanggal_verifikasi = NOW(), admin_id = ? 
            WHERE id = ?
        ");
    if (!$stmt) die("Prepare update gagal: " . $conn->error);
    $stmt->bind_param("siii", $status_baru, $catatan_admin, $superadmin_id, $id);
  } else { // terima
    $stmt = $conn->prepare("
            UPDATE permintaan_barang 
            SET status = ?, tanggal_verifikasi = NOW(), admin_id = ? 
            WHERE id = ?
        ");
    if (!$stmt) die("Prepare update gagal: " . $conn->error);
    $stmt->bind_param("sii", $status_baru, $superadmin_id, $id);
  }

  $hasil = $stmt->execute();
  $stmt->close();

  if ($hasil) {
    // Buat notifikasi untuk user
    $stmt2 = $conn->prepare("
            INSERT INTO notifikasi (user_id, permintaan_id, pesan) 
            VALUES (?, ?, ?)
        ");
    if (!$stmt2) die("Prepare notifikasi gagal: " . $conn->error);
    $stmt2->bind_param("iis", $user_id, $id, $pesan);
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
