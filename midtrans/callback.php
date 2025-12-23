<?php
require '../include/conn.php';
require '../include/midtrans-config.php';
require '../include/notification-func-db.php';

use Midtrans\Notification;

/* ==========================
   VALIDASI METHOD
========================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo "Midtrans Callback Endpoint";
  exit;
}

/* ==========================
   LOG CALLBACK (DEBUG)
========================== */
file_put_contents(
  __DIR__ . '/midtrans_log.txt',
  date('Y-m-d H:i:s') . "\n" . file_get_contents("php://input") . "\n\n",
  FILE_APPEND
);

/* ==========================
   AMBIL DATA DARI MIDTRANS
   (AUTO VALIDASI SIGNATURE)
========================== */
$notif = new Notification();

$order_id           = $notif->order_id;
$transaction_status = $notif->transaction_status;
$payment_type       = $notif->payment_type;
$gross_amount       = $notif->gross_amount;

/* ==========================
   NORMALISASI METODE BAYAR
========================== */
$metode = strtoupper($payment_type);

switch ($payment_type) {
  case 'bank_transfer':
    if (!empty($notif->va_numbers[0]->bank)) {
      $metode = strtoupper($notif->va_numbers[0]->bank) . ' VA';
    } elseif (!empty($notif->permata_va_number)) {
      $metode = 'PERMATA VA';
    } elseif (($notif->bank ?? '') === 'mandiri') {
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
    if (($notif->store ?? '') === 'alfamart') {
      $metode = 'ALFAMART';
    } elseif (($notif->store ?? '') === 'indomaret') {
      $metode = 'INDOMARET';
    }
    break;

  case 'akulaku':
    $metode = 'AKULAKU';
    break;

  case 'kredivo':
    $metode = 'KREDIVO';
    break;
}

/* ==========================
   AMBIL DATA INVOICE
========================== */
$stmt = $conn->prepare("
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
  WHERE i.nomor_invoice = ?
  LIMIT 1
");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();

if (!$inv) {
  http_response_code(404);
  exit('Invoice not found');
}

/* ==========================
   TRANSAKSI GAGAL
========================== */
if (in_array($transaction_status, ['deny', 'cancel', 'expire'])) {

  if ($inv['status'] !== 'dibatalkan') {
    $stmt = $conn->prepare("
      UPDATE invoice SET status = 'dibatalkan'
      WHERE id_invoice = ?
    ");
    $stmt->bind_param("i", $inv['id_invoice']);
    $stmt->execute();
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
    $stmt = $conn->prepare("
      UPDATE invoice SET status = 'lunas'
      WHERE id_invoice = ?
    ");
    $stmt->bind_param("i", $inv['id_invoice']);
    $stmt->execute();

    /* INSERT PEMBAYARAN */
    $stmt = $conn->prepare("
      INSERT INTO pembayaran
      (id_invoice, metode, jumlah, tanggal_bayar, status)
      VALUES (?, ?, ?, NOW(), 'berhasil')
    ");
    $stmt->bind_param(
      "isd",
      $inv['id_invoice'],
      $metode,
      $inv['total']
    );
    $stmt->execute();

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

    /* 🔔 NOTIFIKASI ADMIN */
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
