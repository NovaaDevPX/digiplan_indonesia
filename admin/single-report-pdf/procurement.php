<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['admin']);

require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Validasi parameter
if (!isset($_GET['kode'])) {
  die('Kode pengadaan tidak ditemukan');
}

$kode = mysqli_real_escape_string($conn, $_GET['kode']);

// Ambil data pengadaan
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

// Konfigurasi Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// HTML PDF
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #111;
    }

    h1 {
      text-align: center;
      margin-bottom: 5px;
    }

    .subtitle {
      text-align: center;
      margin-bottom: 20px;
      font-size: 11px;
      color: #555;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      border: 1px solid #999;
      padding: 8px;
    }

    th {
      background: #f3f4f6;
      text-align: left;
    }

    .status {
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: bold;
      display: inline-block;
    }

    .diproses { background: #fef3c7; color: #92400e; }
    .selesai { background: #d1fae5; color: #065f46; }
    .dibatalkan { background: #fee2e2; color: #991b1b; }

    .footer {
      margin-top: 40px;
      font-size: 10px;
      text-align: right;
      color: #555;
    }
  </style>
</head>
<body>

  <h1>LAPORAN PENGADAAN BARANG</h1>
  <div class="subtitle">DigiPlan Indonesia</div>

  <table>
    <tr>
      <th>Kode Pengadaan</th>
      <td>' . htmlspecialchars($data['kode_pengadaan']) . '</td>
    </tr>
    <tr>
      <th>Kode Permintaan</th>
      <td>' . htmlspecialchars($data['kode_permintaan'] ?? '-') . '</td>
    </tr>
    <tr>
      <th>Nama Barang</th>
      <td>' . htmlspecialchars($data['nama_barang']) . '</td>
    </tr>
    <tr>
      <th>Jumlah</th>
      <td>' . htmlspecialchars($data['jumlah']) . '</td>
    </tr>
    <tr>
      <th>Supplier</th>
      <td>' . htmlspecialchars($data['supplier']) . '</td>
    </tr>
    <tr>
      <th>Customer</th>
      <td>' . htmlspecialchars($data['nama_customer'] ?? '-') . '</td>
    </tr>
    <tr>
      <th>Tanggal Pengadaan</th>
      <td>' . htmlspecialchars($data['tanggal_pengadaan']) . '</td>
    </tr>
    <tr>
      <th>Status</th>
      <td>
        <span class="status ' . $data['status_pengadaan'] . '">
          ' . ucfirst($data['status_pengadaan']) . '
        </span>
      </td>
    </tr>
  </table>

  <div class="footer">
    Dicetak pada: ' . date('d-m-Y H:i') . '
  </div>

</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output PDF
$dompdf->stream(
  'Laporan_Pengadaan_' . $data['kode_pengadaan'] . '.pdf',
  ['Attachment' => false]
);
exit;
