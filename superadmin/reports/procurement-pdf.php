<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['super_admin']);

require '../../vendor/autoload.php';

use Dompdf\Dompdf;

$tgl_awal   = $_GET['tgl_awal']   ?? '';
$tgl_akhir  = $_GET['tgl_akhir']  ?? '';
$status     = $_GET['status']     ?? '';
$customer   = $_GET['customer']   ?? '';

$where = "WHERE pg.deleted_at IS NULL";

if ($tgl_awal && $tgl_akhir) {
  $where .= " AND pg.tanggal_pengadaan BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

if ($status) {
  $where .= " AND pg.status_pengadaan = '$status'";
}

if ($customer) {
  $where .= " AND cust.id = '$customer'";
}

$query = "
SELECT 
  pg.*, 
  pm.kode_permintaan,
  cust.name AS customer_name
FROM pengadaan_barang pg
JOIN permintaan_barang pm ON pg.permintaan_id = pm.id
JOIN users cust ON pm.user_id = cust.id
$where
ORDER BY pg.tanggal_pengadaan DESC
";

$result = mysqli_query($conn, $query);

$grandTotal = 0;

/* ================= HTML PDF ================= */
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

.text-right {
  text-align: right;
}

.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 10px;
  color: #fff;
}

.selesai { background: #27ae60; }
.diproses { background: #f39c12; }
.dibatalkan { background: #c0392b; }

.total-row td {
  background: #ecf0f1;
  font-weight: bold;
  font-size: 12px;
}

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
  <h2>LAPORAN PENGADAAN BARANG</h2>
  <p>DigiPlan Indonesia</p>
</div>

<div class="divider"></div>

<p>
<strong>Periode:</strong> ' . ($tgl_awal ?: '-') . ' s/d ' . ($tgl_akhir ?: '-') . '<br>
<strong>Status:</strong> ' . ($status ?: 'Semua') . '<br>
<strong>Customer:</strong> ' . ($customer ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE id='$customer'"))['name'] : 'Semua') . '
</p>

<table>
<thead>
<tr>
  <th>No</th>
  <th>Kode Pengadaan</th>
  <th>Permintaan</th>
  <th>Customer</th>
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

  $grandTotal += $row['harga_total'];

  $badge = strtolower($row['status_pengadaan']);

  $html .= '
  <tr>
    <td>' . $no++ . '</td>
    <td>' . $row['kode_pengadaan'] . '</td>
    <td>' . $row['kode_permintaan'] . '</td>
    <td>' . $row['customer_name'] . '</td>
    <td>' . $row['nama_barang'] . '</td>
    <td>' . $row['jumlah'] . '</td>
    <td>' . $row['supplier'] . '</td>
    <td class="text-right">Rp ' . number_format($row['harga_satuan'], 0, ',', '.') . '</td>
    <td class="text-right">Rp ' . number_format($row['harga_total'], 0, ',', '.') . '</td>
    <td>
      <span class="badge ' . $badge . '">' . strtoupper($row['status_pengadaan']) . '</span>
    </td>
    <td>' . $row['tanggal_pengadaan'] . '</td>
  </tr>';
}

$html .= '
<tr class="total-row">
  <td colspan="8" class="text-right">TOTAL KESELURUHAN</td>
  <td class="text-right">Rp ' . number_format($grandTotal, 0, ',', '.') . '</td>
  <td colspan="2"></td>
</tr>

</tbody>
</table>

<div class="footer">
  <div>
    Dicetak pada:<br>
    ' . date('d M Y H:i') . '
  </div>

  <div style="text-align:right">
    Super Admin<br><br><br>
    <strong>_____________________</strong>
  </div>
</div>

</body>
</html>
';

/* ================= DOMPDF ================= */
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("laporan_pengadaan_barang.pdf", ["Attachment" => false]);
