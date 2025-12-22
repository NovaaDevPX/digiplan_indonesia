<?php
require '../include/conn.php';
require '../include/midtrans-config.php';
require '../include/notification-func-db.php';

/* ==========================
   VALIDASI METHOD
========================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo "Midtrans Callback Endpoint";
  exit;
}

/* ==========================
   AMBIL CALLBACK JSON
========================== */
$json = file_get_contents("php://input");
$data = json_decode($json, true);

/* ==========================
   DATA CALLBACK
========================== */
$order_id           = $data['order_id'] ?? '';
$status_code        = $data['status_code'] ?? '';
$gross_amount       = $data['gross_amount'] ?? '';
$signature_key      = $data['signature_key'] ?? '';
$transaction_status = $data['transaction_status'] ?? '';
$payment_type       = $data['payment_type'] ?? '';

/* ==========================
   VALIDASI SIGNATURE
========================== */
$serverKey = \Midtrans\Config::$serverKey;
$expectedSignature = hash(
  'sha512',
  $order_id . $status_code . $gross_amount . $serverKey
);

if ($signature_key !== $expectedSignature) {
  http_response_code(403);
  exit('Invalid signature');
}

/* ==========================
   NORMALISASI METODE PEMBAYARAN
========================== */
$metode = strtoupper($payment_type);

switch ($payment_type) {

  case 'bank_transfer':
    if (isset($data['va_numbers'][0]['bank'])) {
      $metode = strtoupper($data['va_numbers'][0]['bank']) . ' VA';
    } elseif (isset($data['permata_va_number'])) {
      $metode = 'PERMATA VA';
    } elseif (($data['bank'] ?? '') === 'mandiri') {
      $metode = 'MANDIRI E-CHANNEL';
    }
    break;

  case 'credit_card':
    $metode = 'KARTU KREDIT';
    break;

  case 'gopay':
    $metode = 'GOPAY';
    break;

  case 'shopeepay':
    $metode = 'SHOPEEPAY';
    break;

  case 'qris':
    $metode = 'QRIS';
    break;

  case 'cstore':
    if (($data['store'] ?? '') === 'alfamart') {
      $metode = 'ALFAMART';
    } elseif (($data['store'] ?? '') === 'indomaret') {
      $metode = 'INDOMARET';
    }
    break;

  case 'akulaku':
    $metode = 'AKULAKU';
    break;

  case 'kredivo':
    $metode = 'KREDIVO';
    break;

  case 'ewallet':
    $metode = strtoupper($data['issuer'] ?? 'E-WALLET');
    break;
}

/* ==========================
   AMBIL DATA INVOICE
========================== */
$inv = $conn->query("
  SELECT 
    i.id_invoice,
    i.status,
    i.total,
    p.user_id AS customer_id,
    p.id AS permintaan_id,
    u.name AS customer_name
  FROM invoice i
  JOIN distribusi_barang d ON i.distribusi_id = d.id
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  JOIN users u ON p.user_id = u.id
  WHERE i.nomor_invoice = '$order_id'
  LIMIT 1
")->fetch_assoc();

if (!$inv) {
  http_response_code(404);
  exit('Invoice not found');
}

/* ==========================
   TRANSAKSI GAGAL
========================== */
if (in_array($transaction_status, ['deny', 'cancel', 'expire'])) {

  if ($inv['status'] !== 'dibatalkan') {
    $conn->query("
      UPDATE invoice
      SET status = 'dibatalkan'
      WHERE id_invoice = {$inv['id_invoice']}
    ");
  }

  http_response_code(200);
  echo 'OK';
  exit;
}

/* ==========================
   TRANSAKSI BERHASIL
========================== */
if (in_array($transaction_status, ['capture', 'settlement'])) {

  // Anti double callback
  if ($inv['status'] === 'lunas') {
    http_response_code(200);
    echo 'Already processed';
    exit;
  }

  $conn->begin_transaction();

  try {

    /* UPDATE INVOICE */
    $conn->query("
      UPDATE invoice
      SET status = 'lunas'
      WHERE id_invoice = {$inv['id_invoice']}
    ");

    /* INSERT PEMBAYARAN */
    $conn->query("
      INSERT INTO pembayaran
      (id_invoice, metode, jumlah, tanggal_bayar, status)
      VALUES
      (
        {$inv['id_invoice']},
        '$metode',
        {$inv['total']},
        NOW(),
        'berhasil'
      )
    ");

    /* 🔔 NOTIFIKASI CUSTOMER */
    insertNotifikasi(
      $conn,
      $inv['customer_id'],
      null,
      $inv['permintaan_id'],
      '',
      "Pembayaran berhasil 🎉\n\n" .
        "Invoice: $order_id\n" .
        "Metode: $metode\n" .
        "Total: Rp " . number_format($inv['total'], 0, ',', '.')
    );

    /* 🔔 NOTIFIKASI ADMIN  / SUPER ADMIN */
    insertNotifikasi(
      $conn,
      2,
      null,
      $inv['permintaan_id'],
      "Pembayaran berhasil.\n\n" .
        "Invoice: $order_id\n" .
        "Customer: {$inv['customer_name']}",
      null
    );

    $conn->commit();
    http_response_code(200);
    echo 'OK';
  } catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo 'Database Error';
  }
}
