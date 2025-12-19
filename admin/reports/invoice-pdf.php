<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['admin']);

require '../../vendor/autoload.php';

use Dompdf\Dompdf;

$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status    = $_GET['status'] ?? '';

$where = "WHERE i.deleted_at IS NULL";

if ($tgl_awal && $tgl_akhir) {
  $where .= " AND i.tanggal_invoice BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

if ($status) {
  $where .= " AND i.status = '$status'";
}

$query = "
SELECT 
  i.nomor_invoice,
  i.tanggal_invoice,
  i.total,
  i.status,
  u.name AS customer,
  pm.kode_permintaan,
  d.kode_distribusi,
  p.metode
FROM invoice i
JOIN distribusi_barang d ON i.distribusi_id = d.id
JOIN permintaan_barang pm ON d.permintaan_id = pm.id
JOIN users u ON pm.user_id = u.id
LEFT JOIN pembayaran p ON p.id_invoice = i.id_invoice
$where
ORDER BY i.tanggal_invoice DESC
";

$result = mysqli_query($conn, $query);

$grandTotal = 0; // 🔥 TOTAL KESELURUHAN

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
}

td {
  padding: 7px;
  border: 1px solid #ccc;
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

.lunas { background: #27ae60; }
.belum { background: #f39c12; }
.batal { background: #c0392b; }

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
  <h2>LAPORAN INVOICE & PEMBAYARAN</h2>
  <p>DigiPlan Indonesia</p>
</div>

<div class="divider"></div>

<p>
<strong>Periode:</strong> ' . ($tgl_awal ?: '-') . ' s/d ' . ($tgl_akhir ?: '-') . '<br>
<strong>Status:</strong> ' . ($status ?: 'Semua') . '
</p>

<table>
<thead>
<tr>
  <th>No</th>
  <th>No Invoice</th>
  <th>Customer</th>
  <th>Permintaan</th>
  <th>Distribusi</th>
  <th>Tanggal</th>
  <th>Total</th>
  <th>Metode</th>
  <th>Status</th>
</tr>
</thead>
<tbody>
';

$no = 1;
while ($r = mysqli_fetch_assoc($result)) {

  $grandTotal += $r['total']; // 🔥 AKUMULASI TOTAL

  $badge = 'belum';
  if ($r['status'] == 'lunas') $badge = 'lunas';
  if ($r['status'] == 'dibatalkan') $badge = 'batal';

  $html .= '
  <tr>
    <td>' . $no++ . '</td>
    <td>' . $r['nomor_invoice'] . '</td>
    <td>' . $r['customer'] . '</td>
    <td>' . $r['kode_permintaan'] . '</td>
    <td>' . $r['kode_distribusi'] . '</td>
    <td>' . $r['tanggal_invoice'] . '</td>
    <td class="text-right">Rp ' . number_format($r['total'], 0, ',', '.') . '</td>
    <td>' . ($r['metode'] ?? '-') . '</td>
    <td><span class="badge ' . $badge . '">' . strtoupper($r['status']) . '</span></td>
  </tr>';
}

$html .= '
<tr class="total-row">
  <td colspan="6" class="text-right">TOTAL KESELURUHAN</td>
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
$dompdf->stream("laporan_invoice_pembayaran.pdf", ["Attachment" => false]);
