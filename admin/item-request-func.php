<?php
require '../include/conn.php';
require '../include/auth.php';
require '../include/base-url.php';

cek_role(['admin']);

$sql = "SELECT pb.*, du.name AS nama_user 
        FROM permintaan_barang pb
        JOIN users du ON pb.user_id = du.id
        ORDER BY pb.tanggal_permintaan DESC";

$result = $conn->query($sql);
if (!$result) die("Query gagal: " . $conn->error);

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
