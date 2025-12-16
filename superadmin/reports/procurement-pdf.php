<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['super_admin']);

require '../../vendor/autoload.php';

use Dompdf\Dompdf;

$tgl_awal  = $_GET['tgl_awal']  ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status    = $_GET['status']    ?? '';

$where = "WHERE 1=1";

if ($tgl_awal && $tgl_akhir) {
  $where .= " AND pg.tanggal_pengadaan BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

if ($status) {
  $where .= " AND pg.status_pengadaan = '$status'";
}

$query = "
SELECT pg.*, 
       pm.kode_permintaan
FROM pengadaan_barang pg
JOIN permintaan_barang pm ON pg.permintaan_id = pm.id
$where
ORDER BY pg.tanggal_pengadaan DESC
";

$result = mysqli_query($conn, $query);

$html = '
<style>
body { font-family: DejaVu Sans; font-size: 11px; }
table { width:100%; border-collapse: collapse; }
th, td { border:1px solid #000; padding:5px; }
th { background:#eee; }
</style>

<h2 style="text-align:center">LAPORAN PENGADAAN BARANG</h2>

<table>
<thead>
<tr>
<th>No</th>
<th>Kode Pengadaan</th>
<th>Kode Permintaan</th>
<th>Barang</th>
<th>Jumlah</th>
<th>Supplier</th>
<th>Harga Satuan</th>
<th>Total</th>
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
    <td>{$row['kode_pengadaan']}</td>
    <td>{$row['kode_permintaan']}</td>
    <td>{$row['nama_barang']}</td>
    <td>{$row['jumlah']}</td>
    <td>{$row['supplier']}</td>
    <td>Rp " . number_format($row['harga_satuan'], 0, ',', '.') . "</td>
    <td>Rp " . number_format($row['harga_total'], 0, ',', '.') . "</td>
    <td>{$row['status_pengadaan']}</td>
    <td>{$row['tanggal_pengadaan']}</td>
  </tr>";
  $no++;
}

$html .= '
</tbody>
</table>

<p style="margin-top:40px;text-align:right">
Super Admin <br><br><br>
_____________________
</p>
';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("laporan_pengadaan_barang.pdf", ["Attachment" => false]);
