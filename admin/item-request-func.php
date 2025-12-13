<?php
require '../include/conn.php';
require '../include/auth.php';
require '../include/base-url.php';

cek_role(['admin']);

$sql = "
  SELECT 
    pb.*,
    u.name AS nama_user
  FROM permintaan_barang pb
  JOIN users u ON pb.user_id = u.id
  ORDER BY pb.created_at DESC
";

$result = $conn->query($sql);
if (!$result) {
  die("Query gagal: " . $conn->error);
}


// if ($aksi == 'teruskan') {
//   mysqli_query($conn, "UPDATE permintaan_barang SET status='Menunggu Super Admin' WHERE id='$id'");

//   // Kirim notifikasi ke super admin
//   $res_sa = mysqli_query($conn, "SELECT id FROM users WHERE role='super_admin'");
//   $daftar_sa = [];
//   while ($r = mysqli_fetch_assoc($res_sa)) {
//     $daftar_sa[] = $r['id'];
//   }

//   tambahNotifikasi($daftar_sa, $id, "Admin telah meneruskan <b>permintaan barang</b> ke Super Admin.");
// }
