<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['admin']);

require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['kode'])) {
  die('Kode distribusi tidak ditemukan');
}

$kode = $_GET['kode'];

/* ambil data distribusi */
$query = "
SELECT d.*, 
       pm.kode_permintaan,
       pm.nama_barang,
       pm.jumlah,
       pb.kode_pengadaan,
       u.name AS admin_nama
FROM distribusi_barang d
JOIN permintaan_barang pm ON d.permintaan_id = pm.id
JOIN pengadaan_barang pb ON d.pengadaan_id = pb.id
JOIN users u ON d.admin_id = u.id
WHERE d.kode_distribusi = '$kode'
";

$data = mysqli_query($conn, $query);

if (!$data) {
  die('Query error: ' . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($data);


if (!$row) {
  die('Data distribusi tidak ditemukan');
}

/* HTML laporan */
$html = '
<!DOCTYPE html>
<html>
<head>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      color: #000;
    }
    .header {
      text-align: center;
      margin-bottom: 20px;
    }
    .header h2 {
      margin: 0;
    }
    .header p {
      margin: 4px 0;
      font-size: 11px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #000;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #f0f0f0;
    }
    .footer {
      margin-top: 40px;
      width: 100%;
    }
    .signature {
      width: 30%;
      float: right;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="header">
    <h2>LAPORAN DISTRIBUSI BARANG</h2>
    <p>PT DigiPlan Indonesia</p>
    <p>Tanggal Cetak: ' . date('d-m-Y') . '</p>
  </div>

  <table>
    <tr>
      <th>Kode Distribusi</th>
      <td>' . $row['kode_distribusi'] . '</td>
    </tr>
    <tr>
      <th>Kode Permintaan</th>
      <td>' . $row['kode_permintaan'] . '</td>
    </tr>
    <tr>
      <th>Kode Pengadaan</th>
      <td>' . $row['kode_pengadaan'] . '</td>
    </tr>
    <tr>
      <th>Nama Barang</th>
      <td>' . $row['nama_barang'] . '</td>
    </tr>
    <tr>
      <th>Jumlah</th>
      <td>' . $row['jumlah'] . '</td>
    </tr>
    <tr>
      <th>Alamat Pengiriman</th>
      <td>' . $row['alamat_pengiriman'] . '</td>
    </tr>
    <tr>
      <th>Kurir</th>
      <td>' . $row['kurir'] . '</td>
    </tr>
    <tr>
      <th>No Resi</th>
      <td>' . $row['no_resi'] . '</td>
    </tr>
    <tr>
      <th>Status Distribusi</th>
      <td>' . $row['status_distribusi'] . '</td>
    </tr>
  </table>

  <div class="footer">
    <div class="signature">
      <p>Admin</p>
      <br><br><br>
      <p><b>' . $row['admin_nama'] . '</b></p>
    </div>
  </div>

</body>
</html>
';

/* konfigurasi dompdf */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* tampilkan PDF */
$dompdf->stream(
  'Laporan_Distribusi_' . $kode . '.pdf',
  ['Attachment' => false]
);
exit;
