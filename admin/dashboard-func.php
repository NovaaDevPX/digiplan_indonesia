<?php
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

$login_id   = $_SESSION['user_id'];
$login_role = 2; // admin

/* =========================
   HELPER
========================= */
function queryCheck($conn, $query)
{
  $result = mysqli_query($conn, $query);
  if (!$result) {
    die("Query error: " . mysqli_error($conn));
  }
  return $result;
}

$today        = date('Y-m-d');
$currentMonth = date('Y-m');

/* =========================
   STATISTIK DASHBOARD
========================= */

// Permintaan hari ini
$permintaan_hari_ini = mysqli_fetch_assoc(
  queryCheck($conn, "
    SELECT COUNT(*) total
    FROM permintaan_barang
    WHERE DATE(created_at) = '$today'
  ")
)['total'] ?? 0;

// Permintaan disetujui
$permintaan_diterima = mysqli_fetch_assoc(
  queryCheck($conn, "
    SELECT COUNT(*) total
    FROM permintaan_barang
    WHERE status = 'disetujui'
  ")
)['total'] ?? 0;

// Barang masuk bulan ini
$barang_masuk = mysqli_fetch_assoc(
  queryCheck($conn, "
    SELECT IFNULL(SUM(jumlah),0) total
    FROM pengadaan_barang
    WHERE DATE_FORMAT(tanggal_pengadaan,'%Y-%m') = '$currentMonth'
      AND status_pengadaan = 'selesai'
  ")
)['total'] ?? 0;

// Barang keluar bulan ini
$barang_keluar = mysqli_fetch_assoc(
  queryCheck($conn, "
    SELECT IFNULL(SUM(p.jumlah),0) total
    FROM distribusi_barang d
    JOIN pengadaan_barang p ON d.pengadaan_id = p.id
    WHERE DATE_FORMAT(d.tanggal_kirim,'%Y-%m') = '$currentMonth'
      AND d.status_distribusi IN ('dikirim','diterima')
  ")
)['total'] ?? 0;


/* =========================
   NOTIFIKASI ADMIN
========================= */

// Ambil daftar notifikasi milik admin login
$qNotif = $conn->prepare("
  SELECT 
    n.id,
    n.pesan,
    n.pesan_customer,
    n.status_baca,
    n.created_at,
    p.kode_permintaan
  FROM notifikasi n
  LEFT JOIN permintaan_barang p ON n.permintaan_id = p.id
  WHERE n.receiver_id = ?
  ORDER BY n.created_at DESC
  LIMIT 10
");
$qNotif->bind_param("i", $login_id);
$qNotif->execute();
$notifikasi = $qNotif->get_result()->fetch_all(MYSQLI_ASSOC);

// Hitung jumlah notif belum dibaca
$qUnread = $conn->prepare("
  SELECT COUNT(*) AS total
  FROM notifikasi
  WHERE receiver_id = ?
    AND status_baca = 0
");
$qUnread->bind_param("i", $login_id);
$qUnread->execute();
$total_notif_belum_dibaca = $qUnread->get_result()->fetch_assoc()['total'] ?? 0;


/* =========================
   DATA GRAFIK
========================= */

// Permintaan per status
$qPermintaanStatus = queryCheck($conn, "
  SELECT status, COUNT(*) total
  FROM permintaan_barang
  GROUP BY status
");
$permintaan_status_label = [];
$permintaan_status_data  = [];
while ($r = mysqli_fetch_assoc($qPermintaanStatus)) {
  $permintaan_status_label[] = ucfirst($r['status']);
  $permintaan_status_data[]  = (int)$r['total'];
}

// Pengadaan per status
$qPengadaanStatus = queryCheck($conn, "
  SELECT status_pengadaan, COUNT(*) total
  FROM pengadaan_barang
  GROUP BY status_pengadaan
");
$pengadaan_status_label = [];
$pengadaan_status_data  = [];
while ($r = mysqli_fetch_assoc($qPengadaanStatus)) {
  $pengadaan_status_label[] = ucfirst($r['status_pengadaan']);
  $pengadaan_status_data[]  = (int)$r['total'];
}

// Invoice vs pembayaran
$invoice_total = mysqli_fetch_assoc(
  queryCheck($conn, "
    SELECT IFNULL(SUM(total),0) total
    FROM invoice
    WHERE status != 'dibatalkan'
  ")
)['total'];

$pembayaran_total = mysqli_fetch_assoc(
  queryCheck($conn, "
    SELECT IFNULL(SUM(jumlah),0) total
    FROM pembayaran
    WHERE status = 'berhasil'
  ")
)['total'];


/* =========================
   MARK AS READ (Setelah data dipakai)
========================= */

$markRead = $conn->prepare("
  UPDATE notifikasi
  SET status_baca = 1
  WHERE receiver_id = ?
");
$markRead->bind_param("i", $login_id);
$markRead->execute();
