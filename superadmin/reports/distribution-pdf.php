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
  $where .= " AND d.tanggal_kirim BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

if ($status) {
  $where .= " AND d.status_distribusi = '$status'";
}

/* ================= QUERY ================= */
$query = "
SELECT 
  d.*,
  pm.kode_permintaan,
  pm.nama_barang,
  pm.jumlah,
  u.name AS nama_customer
FROM distribusi_barang d
JOIN permintaan_barang pm ON d.permintaan_id = pm.id
JOIN users u ON pm.user_id = u.id
$where
ORDER BY d.tanggal_kirim DESC
";

$result = mysqli_query($conn, $query);

/* ================= HTML PDF ================= */
$html = '
<style>
body {
  font-family: DejaVu Sans;
  font-size: 11px;
  color: #333;
}

.header {
  text-align: center;
  margin-bottom: 20px;
}

.header h2 {
  margin: 0;
  font-size: 18px;
  letter-spacing: 1px;
}

.header p {
  margin: 4px 0;
  font-size: 11px;
  color: #555;
}

.divider {
  border-top: 2px solid #444;
  margin: 12px 0 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th {
  background: #2c3e50;
  color: #fff;
  padding: 8px;
  font-size: 11px;
}

td {
  padding: 7px;
  border: 1px solid #ccc;
  font-size: 10.5px;
}

tr:nth-child(even) {
  background: #f5f5f5;
}

.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 10px;
  color: #fff;
}

.dikirim   { background: #f39c12; }
.diterima  { background: #27ae60; }
.dibatalkan{ background: #c0392b; }

.footer {
  margin-top: 40px;
  display: flex;
  justify-content: space-between;
  font-size: 10px;
}

.ttd {
  text-align: right;
}
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
<th>Kode Permintaan</th>
<th>Customer</th>
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
    <td align="center">' . $no++ . '</td>
    <td>' . $row['kode_distribusi'] . '</td>
    <td>' . $row['kode_permintaan'] . '</td>
    <td>' . $row['nama_customer'] . '</td>
    <td>' . $row['nama_barang'] . '</td>
    <td align="center">' . $row['jumlah'] . '</td>
    <td>' . $row['kurir'] . '</td>
    <td>' . $row['no_resi'] . '</td>
    <td>' . $row['tanggal_kirim'] . '</td>
    <td>' . ucfirst($row['status_distribusi']) . '</td>
  </tr>';
}

$html .= '
</tbody>
</table>

<p style="margin-top:40px;text-align:right;">
Super Admin<br><br><br>
_____________________
</p>
';

/* ================= DOMPDF ================= */
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("laporan_distribusi.pdf", ["Attachment" => false]);
