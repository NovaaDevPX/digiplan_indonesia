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

  /* INFO BOX */
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

  /* TABLE DATA */
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

  /* STATUS BADGE */
  .status {
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 11px;
    color: #fff;
    display: inline-block;
  }

  .status-dikirim {
    background-color: #3498db;
  }

  .status-diterima {
    background-color: #2ecc71;
  }

  /* FOOTER */
  .footer {
    margin-top: 50px;
    width: 100%;
  }

  .ttd {
    width: 35%;
    float: right;
    text-align: center;
  }

  .ttd p {
    margin: 4px 0;
  }

  .clear {
    clear: both;
  }
</style>
</head>

<body>
<div class="container">

  <div class="header">
    <h1>LAPORAN DISTRIBUSI BARANG</h1>
    <p><b>PT DigiPlan Indonesia</b></p>
    <p>Tanggal Cetak: ' . date('d-m-Y') . '</p>
  </div>

  <div class="info">
    <table>
      <tr>
        <td class="label">Kode Distribusi</td>
        <td>: ' . $row['kode_distribusi'] . '</td>
        <td class="label">Kode Pengadaan</td>
        <td>: ' . $row['kode_pengadaan'] . '</td>
      </tr>
      <tr>
        <td class="label">Kode Permintaan</td>
        <td>: ' . $row['kode_permintaan'] . '</td>
        <td class="label">Kurir</td>
        <td>: ' . $row['kurir'] . '</td>
      </tr>
      <tr>
        <td class="label">No Resi</td>
        <td>: ' . $row['no_resi'] . '</td>
        <td class="label">Alamat Kirim</td>
        <td>: ' . $row['alamat_pengiriman'] . '</td>
      </tr>
    </table>
  </div>

  <table class="data">
    <thead>
      <tr>
        <th>Nama Barang</th>
        <th>Jumlah</th>
        <th>Status Distribusi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>' . $row['nama_barang'] . '</td>
        <td>' . $row['jumlah'] . '</td>
        <td>
          <span class="status status-dikirim">' . strtoupper($row['status_distribusi']) . '</span>
        </td>
      </tr>
    </tbody>
  </table>

  <div class="footer">
    <div class="ttd">
      <p>Mengetahui,</p>
      <p><b>Admin</b></p>
      <br><br><br>
      <p><u>' . $row['admin_nama'] . '</u></p>
    </div>
    <div class="clear"></div>
  </div>

</div>
</body>
</html>
';

/* DOMPDF */
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
