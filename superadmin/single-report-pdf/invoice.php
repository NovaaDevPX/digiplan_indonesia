<?php
require '../../include/conn.php';
require '../../include/auth.php';
require '../../vendor/autoload.php';

use Dompdf\Dompdf;

cek_role(['super_admin']);

$id = $_GET['id'] ?? null;
if (!$id) {
  die('Invoice tidak valid');
}

/* =========================
   AMBIL DATA INVOICE
========================= */
$stmt = $conn->prepare("
  SELECT
    i.id_invoice,
    i.nomor_invoice,
    i.tanggal_invoice,
    i.jatuh_tempo,
    i.total,
    COALESCE(i.status, 'belum bayar') AS status_invoice,

    u.name AS customer,

    p.nama_barang,
    p.merk,
    p.warna,
    p.jumlah,

    pg.harga_satuan,
    pg.harga_total
  FROM distribusi_barang d
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  JOIN users u ON p.user_id = u.id
  JOIN pengadaan_barang pg ON d.pengadaan_id = pg.id
  LEFT JOIN invoice i 
    ON i.distribusi_id = d.id 
    AND i.deleted_at IS NULL
  WHERE d.id = ?
");


$stmt->bind_param('i', $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
  die('Data invoice tidak ditemukan');
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
    h1 {
      text-align: center;
      margin-bottom: 5px;
    }
    .subtitle {
      text-align: center;
      font-size: 11px;
      margin-bottom: 20px;
    }
    .info {
      margin-bottom: 20px;
    }
    .info table {
      width: 100%;
    }
    .info td {
      padding: 4px 0;
    }
    table.items {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    table.items th, table.items td {
      border: 1px solid #000;
      padding: 8px;
    }
    table.items th {
      background: #eee;
      text-align: center;
    }
    .right {
      text-align: right;
    }
    .total {
      margin-top: 20px;
      text-align: right;
      font-size: 14px;
      font-weight: bold;
    }
    .footer {
      margin-top: 50px;
      font-size: 11px;
    }
  </style>
</head>
<body>

<h1>INVOICE</h1>
<div class="subtitle">DigiPlan Indonesia</div>

<div class="info">
  <table>
    <tr>
      <td width="50%">
        <strong>Nomor Invoice:</strong><br>
        ' . $data['nomor_invoice'] . '
      </td>
      <td width="50%">
        <strong>Customer:</strong><br>
        ' . $data['customer'] . '
      </td>
    </tr>
    <tr>
      <td>
        <strong>Tanggal Invoice:</strong><br>
        ' . $data['tanggal_invoice'] . '
      </td>
      <td>
        <strong>Status:</strong><br>
        ' . strtoupper($data['status_invoice']) . '
      </td>
    </tr>
  </table>
</div>

<table class="items">
  <thead>
    <tr>
      <th>Nama Barang</th>
      <th>Jumlah</th>
      <th>Harga Satuan</th>
      <th>Subtotal</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        ' . $data['nama_barang'] . '<br>
        <small>' . $data['merk'] . ' - ' . $data['warna'] . '</small>
      </td>
      <td class="right">' . $data['jumlah'] . '</td>
      <td class="right">Rp ' . number_format($data['harga_satuan'], 0, ',', '.') . '</td>
      <td class="right">Rp ' . number_format($data['harga_total'], 0, ',', '.') . '</td>
    </tr>
  </tbody>
</table>

<div class="total">
  Total: Rp ' . number_format($data['total'], 0, ',', '.') . '
</div>

<div class="footer">
  <p>Invoice ini dibuat secara otomatis oleh sistem DigiPlan Indonesia.</p>
</div>

</body>
</html>';

/* =========================
   GENERATE PDF
========================= */
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream(
  'Invoice-' . $data['nomor_invoice'] . '.pdf',
  ['Attachment' => false]
);
exit;
