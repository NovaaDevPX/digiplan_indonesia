<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['admin']);

require '../../vendor/autoload.php';

use Dompdf\Dompdf;

$tgl_awal  = $_GET['tgl_awal']  ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status    = $_GET['status']    ?? '';
$customer  = $_GET['customer']  ?? '';

// ==========================
// BUILD WHERE CLAUSE
// ==========================
$where = "WHERE pb.deleted_at IS NULL";

if ($tgl_awal && $tgl_akhir) {
  $where .= " AND pb.created_at BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'";
}

if ($status) {
  $where .= " AND pb.status = '$status'";
}

if ($customer) {
  $where .= " AND u.id = '$customer'";
}

// ==========================
// GET CUSTOMER NAME
// ==========================
$customerName = "-";
if ($customer) {
  $c = mysqli_query($conn, "SELECT name FROM users WHERE id = '$customer'");
  if ($c && mysqli_num_rows($c) > 0) {
    $cx = mysqli_fetch_assoc($c);
    $customerName = $cx['name'];
  }
}

// ==========================
// QUERY MAIN DATA
// ==========================
$query = "
SELECT pb.*, u.name AS customer
FROM permintaan_barang pb
JOIN users u ON pb.user_id = u.id
$where
ORDER BY pb.created_at DESC
";

$result = mysqli_query($conn, $query);

// ==========================
// BUILD HTML
// ==========================
$html = '
<!DOCTYPE html>
<html>
<head>
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

.diajukan { background: #3498db; }
.disetujui { background: #27ae60; }
.ditolak { background: #c0392b; }
.dibatalkan { background: #7f8c8d; }
.dalam_pengadaan { background: #f39c12; }
.siap_distribusi { background: #9b59b6; }
.selesai { background: #2ecc71; }

.footer {
  margin-top: 40px;
  display: flex;
  justify-content: space-between;
  font-size: 10px;
}
</style>
</head>

<body>

<div class="header">
  <h2>LAPORAN PERMINTAAN BARANG</h2>
  <p>DigiPlan Indonesia</p>
</div>

<div class="divider"></div>

<p>
<strong>Periode:</strong> ' . ($tgl_awal ?: '-') . ' s/d ' . ($tgl_akhir ?: '-') . '<br>
<strong>Status:</strong> ' . ($status ?: 'Semua') . ' <br>
<strong>Customer:</strong> ' . $customerName . '
</p>

<table>
<thead>
<tr>
  <th>No</th>
  <th>Kode Permintaan</th>
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

  $statusClass = str_replace(' ', '_', $row['status']);

  $html .= '
  <tr>
    <td>' . $no++ . '</td>
    <td>' . $row['kode_permintaan'] . '</td>
    <td>' . $row['customer'] . '</td>
    <td>' . $row['nama_barang'] . '</td>
    <td>' . $row['jumlah'] . '</td>
    <td>
      <span class="badge ' . $statusClass . '">' . strtoupper(str_replace('_', ' ', $row['status'])) . '</span>
    </td>
    <td>' . date('d-m-Y', strtotime($row['created_at'])) . '</td>
  </tr>';
}

$html .= '
</tbody>
</table>

<div class="footer">
  <div>
    Dicetak pada:<br>
    ' . date('d M Y H:i') . '
  </div>

  <div style="text-align:right">
   Admin<br><br><br>
    <strong>_____________________</strong>
  </div>
</div>

</body>
</html>
';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("laporan_permintaan_barang.pdf", ["Attachment" => false]);
