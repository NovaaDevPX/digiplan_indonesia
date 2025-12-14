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
  $where .= " AND d.tanggal_kirim BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

if ($status) {
  $where .= " AND d.status_distribusi = '$status'";
}

$query = "
SELECT d.*, 
       pm.kode_permintaan,
       pm.nama_barang,
       pm.jumlah
FROM distribusi_barang d
JOIN permintaan_barang pm ON d.permintaan_id = pm.id
$where
ORDER BY d.tanggal_kirim DESC
";

$result = mysqli_query($conn, $query);

/* ================= HTML PDF ================= */
$html = '
<style>
body { font-family: DejaVu Sans; font-size: 12px; }
h2 { text-align:center; }
table { width:100%; border-collapse: collapse; }
th, td { border:1px solid #000; padding:6px; }
th { background:#eee; }
</style>

<h2>LAPORAN DISTRIBUSI BARANG</h2>
<p>
Periode: ' . ($tgl_awal ?: '-') . ' s/d ' . ($tgl_akhir ?: '-') . '<br>
Status: ' . ($status ?: 'Semua') . '
</p>

<table>
<thead>
<tr>
<th>No</th>
<th>Kode Distribusi</th>
<th>Permintaan</th>
<th>Barang</th>
<th>Jumlah</th>
<th>Kurir</th>
<th>No Resi</th>
<th>Tanggal Kirim</th>
<th>Status</th>
</tr>
</thead>
<tbody>
';

$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
  $html .= '
  <tr>
    <td>' . $no++ . '</td>
    <td>' . $row['kode_distribusi'] . '</td>
    <td>' . $row['kode_permintaan'] . '</td>
    <td>' . $row['nama_barang'] . '</td>
    <td>' . $row['jumlah'] . '</td>
    <td>' . $row['kurir'] . '</td>
    <td>' . $row['no_resi'] . '</td>
    <td>' . $row['tanggal_kirim'] . '</td>
    <td>' . $row['status_distribusi'] . '</td>
  </tr>';
}

$html .= '
</tbody>
</table>

<p style="margin-top:40px;text-align:right;">
Admin Gudang<br><br><br>
_____________________
</p>
';

/* ================= DOMPDF ================= */
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("laporan_distribusi.pdf", ["Attachment" => false]);
