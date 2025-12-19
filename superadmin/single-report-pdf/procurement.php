<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['super_admin']);

require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================
   VALIDASI PARAMETER
========================= */

if (!isset($_GET['kode'])) {
  die('Kode pengadaan tidak ditemukan');
}

$kode = mysqli_real_escape_string($conn, $_GET['kode']);

/* =========================
   AMBIL DATA PENGADAAN
========================= */
$query = "
SELECT 
  pb.*,
  pm.kode_permintaan,
  pm.nama_barang,
  pm.jumlah AS jumlah_permintaan,
  u.name AS nama_customer
FROM pengadaan_barang pb
LEFT JOIN permintaan_barang pm ON pb.permintaan_id = pm.id
LEFT JOIN users u ON pm.user_id = u.id
WHERE pb.kode_pengadaan = '$kode'
LIMIT 1
";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
  die('Data pengadaan tidak ditemukan');
}

$data = mysqli_fetch_assoc($result);

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

  .status-diproses {
    background-color: #f39c12;
  }

  .status-selesai {
    background-color: #2ecc71;
  }

  .status-dibatalkan {
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
    <h1>LAPORAN PENGADAAN BARANG</h1>
    <p><b>PT DigiPlan Indonesia</b></p>
    <p>Tanggal Cetak: ' . date('d-m-Y') . '</p>
  </div>

  <div class="info">
    <table>
      <tr>
        <td class="label">Kode Pengadaan</td>
        <td>: ' . htmlspecialchars($data['kode_pengadaan']) . '</td>
        <td class="label">Kode Permintaan</td>
        <td>: ' . htmlspecialchars($data['kode_permintaan'] ?? '-') . '</td>
      </tr>
      <tr>
        <td class="label">Nama Barang</td>
        <td>: ' . htmlspecialchars($data['nama_barang']) . '</td>
        <td class="label">Jumlah</td>
        <td>: ' . htmlspecialchars($data['jumlah']) . '</td>
      </tr>
      <tr>
        <td class="label">Supplier</td>
        <td>: ' . htmlspecialchars($data['supplier']) . '</td>
        <td class="label">Customer</td>
        <td>: ' . htmlspecialchars($data['nama_customer'] ?? '-') . '</td>
      </tr>
      <tr>
        <td class="label">Tanggal Pengadaan</td>
        <td>: ' . date('d-m-Y', strtotime($data['tanggal_pengadaan'])) . '</td>
        <td class="label">Status Pengadaan</td>
        <td>
          <span class="status status-' . $data['status_pengadaan'] . '">
            ' . strtoupper($data['status_pengadaan']) . '
          </span>
        </td>
      </tr>
    </table>
  </div>

  <table class="data">
    <thead>
      <tr>
        <th>Nama Barang</th>
        <th>Jumlah Permintaan</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>' . htmlspecialchars($data['nama_barang']) . '</td>
        <td>' . htmlspecialchars($data['jumlah_permintaan'] ?? '-') . '</td>
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

/* OUTPUT PDF */
$dompdf->stream(
  'Laporan_Pengadaan_' . $data['kode_pengadaan'] . '.pdf',
  ['Attachment' => false]
);
exit;
