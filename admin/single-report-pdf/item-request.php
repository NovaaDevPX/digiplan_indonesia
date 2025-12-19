<?php
require '../../include/conn.php';
require '../../include/auth.php';
require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

cek_role(['admin']);

$id = $_GET['id'] ?? null;
if (!$id) {
  die('ID permintaan tidak ditemukan');
}

/* =========================
   AMBIL DATA PERMINTAAN
========================= */
$query = "
SELECT 
  pb.*,
  u.name AS customer
FROM permintaan_barang pb
JOIN users u ON pb.user_id = u.id
WHERE pb.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
  die('Data permintaan tidak ditemukan');
}

/* =========================
   TEMPLATE HTML PDF
========================= */
$html = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #000;
    }
    h2 {
      text-align: center;
      margin-bottom: 5px;
    }
    .subtitle {
      text-align: center;
      font-size: 11px;
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      border: 1px solid #000;
      padding: 8px;
      text-align: left;
    }
    th {
      background: #eee;
      width: 35%;
    }
    .footer {
      margin-top: 50px;
      text-align: right;
      font-size: 11px;
    }
  </style>
</head>
<body>

<h2>LAPORAN PERMINTAAN BARANG</h2>
<div class="subtitle">PT DigiPlan Indonesia</div>

<table>
  <tr>
    <th>Kode Permintaan</th>
    <td>' . $data['kode_permintaan'] . '</td>
  </tr>
  <tr>
    <th>Customer</th>
    <td>' . $data['customer'] . '</td>
  </tr>
  <tr>
    <th>Nama Barang</th>
    <td>' . $data['nama_barang'] . '</td>
  </tr>
  <tr>
    <th>Jumlah</th>
    <td>' . $data['jumlah'] . '</td>
  </tr>
  <tr>
    <th>Status</th>
    <td>' . strtoupper(str_replace('_', ' ', $data['status'])) . '</td>
  </tr>
  <tr>
    <th>Tanggal Permintaan</th>
    <td>' . date('d-m-Y', strtotime($data['created_at'])) . '</td>
  </tr>
</table>

<div class="footer">
  <p>Dicetak oleh Admin</p>
</div>

</body>
</html>
';

/* =========================
   GENERATE PDF
========================= */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream(
  'Permintaan-Barang-' . $data['kode_permintaan'] . '.pdf',
  ['Attachment' => false]
);
exit;
