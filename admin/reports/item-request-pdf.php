<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['admin']);

require '../../vendor/autoload.php';

use Dompdf\Dompdf;

$tgl_awal  = $_GET['tgl_awal']  ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status    = $_GET['status']    ?? '';

$where = "WHERE 1=1";

if ($tgl_awal && $tgl_akhir) {
  $where .= " AND pb.created_at BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'";
}

if ($status) {
  $where .= " AND pb.status = '$status'";
}

$query = "
SELECT pb.*, u.name AS customer
FROM permintaan_barang pb
JOIN users u ON pb.user_id = u.id
$where
ORDER BY pb.created_at DESC
";

$result = mysqli_query($conn, $query);

$html = '
<style>
body { font-family: DejaVu Sans; font-size: 12px; }
table { width:100%; border-collapse: collapse; }
th, td { border:1px solid #000; padding:6px; }
th { background:#eee; }
</style>

<h2 style="text-align:center">LAPORAN PERMINTAAN BARANG</h2>

<table>
<thead>
<tr>
<th>No</th>
<th>Kode</th>
<th>Customer</th>
<th>Barang</th>
<th>Jumlah</th>
<th>Status</th>
<th>Tanggal</th>
</tr>
</thead>
<tbody>
';

$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
  $html .= "
  <tr>
    <td>{$no}</td>
    <td>{$row['kode_permintaan']}</td>
    <td>{$row['customer']}</td>
    <td>{$row['nama_barang']}</td>
    <td>{$row['jumlah']}</td>
    <td>{$row['status']}</td>
    <td>" . date('d-m-Y', strtotime($row['created_at'])) . "</td>
  </tr>";
  $no++;
}

$html .= '
</tbody>
</table>

<p style="margin-top:40px;text-align:right">
Admin Gudang<br><br><br>
_____________________
</p>
';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("laporan_permintaan_barang.pdf", ["Attachment" => false]);
