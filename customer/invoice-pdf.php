<?php
require '../include/conn.php';
require '../include/auth.php';

use Dompdf\Dompdf;
use Dompdf\Options;

cek_role(['customer']);

require '../vendor/autoload.php';

$id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

/**
 * Ambil data invoice
 * Pastikan invoice milik customer yang sedang login
 */
$data = mysqli_query($conn, "
  SELECT 
    i.*,
    p.nama_barang,
    p.jumlah,
    p.merk,
    p.warna,
    u.name AS customer,
    d.alamat_pengiriman,
    d.kurir,
    d.no_resi
  FROM invoice i
  JOIN distribusi_barang d ON i.distribusi_id = d.id
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  JOIN users u ON p.user_id = u.id
  WHERE i.id_invoice = $id
  AND p.user_id = $user_id
")->fetch_assoc();

if (!$data) {
  die('Invoice tidak ditemukan atau tidak memiliki akses');
}

/**
 * HTML untuk PDF
 * Gunakan CSS sederhana (domPDF tidak support Tailwind)
 */
$html = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #333;
    }
    .header {
      text-align: center;
      margin-bottom: 30px;
    }
    .header h1 {
      margin: 0;
      font-size: 24px;
    }
    .info-table, .item-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    .info-table td {
      padding: 6px;
    }
    .item-table th, .item-table td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }
    .item-table th {
      background-color: #f5f5f5;
    }
    .total {
      text-align: right;
      font-size: 14px;
      font-weight: bold;
    }
    .footer {
      margin-top: 40px;
      font-size: 11px;
      text-align: center;
      color: #777;
    }
  </style>
</head>
<body>

  <div class="header">
    <h1>INVOICE</h1>
    <p>No: ' . $data['nomor_invoice'] . '</p>
  </div>

  <table class="info-table">
    <tr>
      <td width="50%">
        <strong>Customer</strong><br>
        ' . $data['customer'] . '<br><br>

        <strong>Alamat Pengiriman</strong><br>
        ' . $data['alamat_pengiriman'] . '<br><br>

        <strong>Kurir</strong><br>
        ' . $data['kurir'] . ' (' . $data['no_resi'] . ')
      </td>
      <td width="50%">
        <strong>Tanggal Invoice</strong><br>
        ' . $data['tanggal_invoice'] . '<br><br>

        <strong>Jatuh Tempo</strong><br>
        ' . $data['jatuh_tempo'] . '<br><br>

        <strong>Status</strong><br>
        ' . strtoupper($data['status']) . '
      </td>
    </tr>
  </table>

  <table class="item-table">
    <thead>
      <tr>
        <th>Barang</th>
        <th>Merk</th>
        <th>Warna</th>
        <th>Jumlah</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>' . $data['nama_barang'] . '</td>
        <td>' . $data['merk'] . '</td>
        <td>' . $data['warna'] . '</td>
        <td>' . $data['jumlah'] . '</td>
        <td>Rp ' . number_format($data['total'], 0, ',', '.') . '</td>
      </tr>
    </tbody>
  </table>

  <p class="total">
    Total Bayar: Rp ' . number_format($data['total'], 0, ',', '.') . '
  </p>

  <div class="footer">
    Invoice ini dibuat secara otomatis oleh sistem Digiplan Indonesia.
  </div>

</body>
</html>
';

/**
 * Konfigurasi domPDF
 */
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/**
 * Force download PDF
 */
$filename = 'Invoice-' . $data['nomor_invoice'] . '.pdf';
$dompdf->stream($filename, ['Attachment' => false]);
exit;
