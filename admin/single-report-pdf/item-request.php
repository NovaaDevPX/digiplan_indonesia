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
    font-family: Arial, sans-serif;
    font-size: 12px;
    color: #333;
  }

  .container {
    width: 100%;
  }

  /* HEADER */
  .header {
    border-bottom: 3px solid #2c3e50;
    padding-bottom: 10px;
    margin-bottom: 25px;
  }

  .header h1 {
    margin: 0;
    font-size: 18px;
    text-align: center;
    letter-spacing: 1px;
  }

  .header p {
    margin: 3px 0;
    text-align: center;
    font-size: 11px;
  }

  /* INFO */
  .info {
    margin-bottom: 20px;
  }

  .info table {
    width: 100%;
    border-collapse: collapse;
  }

  .info td {
    padding: 6px 8px;
    vertical-align: top;
  }

  .info .label {
    width: 30%;
    font-weight: bold;
    color: #555;
  }

  /* TABLE */
  table.data {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  table.data th {
    background-color: #2c3e50;
    color: #fff;
    padding: 8px;
    text-align: left;
    font-size: 11px;
  }

  table.data td {
    border: 1px solid #ccc;
    padding: 8px;
  }

  /* STATUS */
  .status {
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 11px;
    color: #fff;
    display: inline-block;
  }

  .status-pending {
    background-color: #f39c12;
  }

  .status-disetujui {
    background-color: #2ecc71;
  }

  .status-ditolak {
    background-color: #e74c3c;
  }

  /* FOOTER */
  .footer {
    margin-top: 40px;
    font-size: 11px;
    color: #555;
    text-align: center;
  }
</style>
</head>

<body>
<div class="container">

  <div class="header">
    <h1>LAPORAN PERMINTAAN BARANG</h1>
    <p><b>PT DigiPlan Indonesia</b></p>
    <p>Tanggal Cetak: ' . date('d-m-Y') . '</p>
  </div>

  <div class="info">
    <table>
      <tr>
        <td class="label">Kode Permintaan</td>
        <td>: ' . $data['kode_permintaan'] . '</td>
        <td class="label">Customer</td>
        <td>: ' . $data['customer'] . '</td>
      </tr>
      <tr>
        <td class="label">Tanggal Permintaan</td>
        <td>: ' . date('d-m-Y', strtotime($data['created_at'])) . '</td>
        <td class="label">Status</td>
        <td>
          <span class="status status-' . strtolower($data['status']) . '">
            ' . strtoupper(str_replace('_', ' ', $data['status'])) . '
          </span>
        </td>
      </tr>
    </table>
  </div>

  <table class="data">
    <thead>
      <tr>
        <th>Nama Barang</th>
        <th>Jumlah</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>' . $data['nama_barang'] . '</td>
        <td>' . $data['jumlah'] . '</td>
      </tr>
    </tbody>
  </table>

  <div class="footer">
    Dokumen ini dicetak secara otomatis oleh sistem DigiPlan Indonesia
  </div>

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
