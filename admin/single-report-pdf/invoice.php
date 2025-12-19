<?php
require '../../include/conn.php';
require '../../include/auth.php';
require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

cek_role(['admin']);

$id = $_GET['id'] ?? null;
if (!$id) {
  die('Invoice tidak valid');
}

/* =========================
   AMBIL DATA INVOICE LENGKAP
========================= */
$stmt = $conn->prepare("
SELECT
  i.nomor_invoice,
  i.tanggal_invoice,
  i.jatuh_tempo,
  i.total,
  i.status AS status_invoice,

  u.name AS customer,
  u.email AS email_customer,

  p.kode_permintaan,
  p.nama_barang,
  p.merk,
  p.warna,
  p.jumlah,

  pg.kode_pengadaan,
  pg.harga_satuan,
  pg.harga_total,
  pg.supplier,
  pg.kontak_supplier,
  pg.alamat_supplier,

  d.kode_distribusi,
  d.alamat_pengiriman,
  d.kurir,
  d.no_resi,
  d.tanggal_kirim,
  d.tanggal_terima,

  py.metode,
  py.tanggal_bayar,
  py.status AS status_pembayaran

FROM invoice i
JOIN distribusi_barang d ON i.distribusi_id = d.id
JOIN permintaan_barang p ON d.permintaan_id = p.id
JOIN pengadaan_barang pg ON d.pengadaan_id = pg.id
JOIN users u ON p.user_id = u.id
LEFT JOIN pembayaran py ON py.id_invoice = i.id_invoice
WHERE d.id = ?
AND i.deleted_at IS NULL
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
  body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
  .header { border-bottom: 3px solid #2c3e50; margin-bottom: 25px; padding-bottom: 10px; }
  .header h1 { text-align: center; margin: 0; font-size: 18px; }
  .header p { text-align: center; margin: 3px 0; font-size: 11px; }

  .info table { width:100%; border-collapse:collapse; margin-bottom:15px; }
  .info td { padding:6px 8px; }
  .label { font-weight:bold; width:25%; color:#555; }

  table.data { width:100%; border-collapse:collapse; margin-top:10px; }
  table.data th { background:#2c3e50; color:#fff; padding:8px; }
  table.data td { border:1px solid #ccc; padding:8px; }

  .right { text-align:right; }

  .status { padding:4px 10px; border-radius:10px; font-size:11px; color:#fff; }
  .lunas { background:#2ecc71; }
  .belum { background:#e67e22; }

  .section-title { margin-top:25px; font-weight:bold; color:#2c3e50; }

  .footer { margin-top:40px; font-size:11px; text-align:center; color:#555; }
</style>
</head>
<body>

<div class="header">
  <h1>INVOICE</h1>
  <p><b>PT DigiPlan Indonesia</b></p>
  <p>Tanggal Cetak: ' . date('d-m-Y') . '</p>
</div>

<div class="info">
<table>
<tr>
  <td class="label">Nomor Invoice</td><td>: ' . $data['nomor_invoice'] . '</td>
  <td class="label">Status</td>
  <td>
    <span class="status ' . ($data['status_invoice'] == 'lunas' ? 'lunas' : 'belum') . '">
      ' . strtoupper($data['status_invoice']) . '
    </span>
  </td>
</tr>
<tr>
  <td class="label">Tanggal Invoice</td><td>: ' . date('d-m-Y', strtotime($data['tanggal_invoice'])) . '</td>
  <td class="label">Jatuh Tempo</td><td>: ' . date('d-m-Y', strtotime($data['jatuh_tempo'])) . '</td>
</tr>
<tr>
  <td class="label">Customer</td><td>: ' . $data['customer'] . '</td>
  <td class="label">Email</td><td>: ' . $data['email_customer'] . '</td>
</tr>
</table>
</div>

<div class="section-title">Detail Barang</div>
<table class="data">
<tr>
  <th>Barang</th><th>Jumlah</th><th>Harga Satuan</th><th>Subtotal</th>
</tr>
<tr>
  <td>' . $data['nama_barang'] . '<br><small>' . $data['merk'] . ' - ' . $data['warna'] . '</small></td>
  <td class="right">' . $data['jumlah'] . '</td>
  <td class="right">Rp ' . number_format($data['harga_satuan'], 0, ',', '.') . '</td>
  <td class="right">Rp ' . number_format($data['harga_total'], 0, ',', '.') . '</td>
</tr>
</table>

<div class="section-title">Informasi Pengiriman</div>
<table class="data">
<tr><td>Kode Distribusi</td><td>' . $data['kode_distribusi'] . '</td></tr>
<tr><td>Kurir</td><td>' . $data['kurir'] . '</td></tr>
<tr><td>No Resi</td><td>' . $data['no_resi'] . '</td></tr>
<tr><td>Alamat</td><td>' . $data['alamat_pengiriman'] . '</td></tr>
</table>

<div class="section-title">Informasi Supplier</div>
<table class="data">
<tr><td>Supplier</td><td>' . $data['supplier'] . '</td></tr>
<tr><td>Kontak</td><td>' . $data['kontak_supplier'] . '</td></tr>
<tr><td>Alamat</td><td>' . $data['alamat_supplier'] . '</td></tr>
</table>

<div class="section-title">Pembayaran</div>
<table class="data">
<tr><td>Metode</td><td>' . ($data['metode'] ?? '-') . '</td></tr>
<tr><td>Tanggal Bayar</td><td>' . ($data['tanggal_bayar'] ?? '-') . '</td></tr>
<tr><td>Status</td><td>' . strtoupper($data['status_pembayaran'] ?? '-') . '</td></tr>
</table>

<table style="width:100%; margin-top:15px;">
<tr>
<td style="text-align:right; font-weight:bold;">TOTAL PEMBAYARAN :</td>
<td style="text-align:right; font-size:14px; font-weight:bold;">
Rp ' . number_format($data['total'], 0, ',', '.') . '
</td>
</tr>
</table>

<div class="footer">
Invoice ini dihasilkan secara otomatis oleh sistem DigiPlan Indonesia
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
  'Invoice-' . $data['nomor_invoice'] . '.pdf',
  ['Attachment' => false]
);
exit;
