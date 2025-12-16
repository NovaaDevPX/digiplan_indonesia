<?php
require '../include/conn.php';
require '../include/midtrans-config.php';

/* ==========================
   VALIDASI REQUEST
   ========================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo "Midtrans Callback Endpoint";
  exit;
}

/* ==========================
   AMBIL JSON CALLBACK
   ========================== */
$json = file_get_contents("php://input");
$data = json_decode($json, true);

/* LOG CALLBACK */
file_put_contents(
  __DIR__ . '/callback-log.txt',
  date('Y-m-d H:i:s') . ' ' . $json . PHP_EOL,
  FILE_APPEND
);

/* ==========================
   DATA CALLBACK
   ========================== */
$order_id = $data['order_id'] ?? '';
$status_code = $data['status_code'] ?? '';
$gross_amount = $data['gross_amount'] ?? '';
$signature_key = $data['signature_key'] ?? '';
$transaction_status = $data['transaction_status'] ?? '';

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
   STATUS GAGAL
   ========================== */
if (in_array($transaction_status, ['deny', 'cancel', 'expire'])) {

  $conn->query("
    UPDATE invoice
    SET status = 'dibatalkan'
    WHERE nomor_invoice = '$order_id'
  ");

  http_response_code(200);
  echo 'OK';
  exit;
}

/* ==========================
   STATUS BERHASIL
   ========================== */
if (in_array($transaction_status, ['capture', 'settlement'])) {

  /* UPDATE INVOICE */
  $conn->query("
    UPDATE invoice
    SET status = 'lunas'
    WHERE nomor_invoice = '$order_id'
  ");

  /* INSERT PEMBAYARAN */
  $conn->query("
    INSERT INTO pembayaran
    (id_invoice, metode, jumlah, tanggal_bayar, status)
    SELECT id_invoice, 'Midtrans', total, NOW(), 'berhasil'
    FROM invoice
    WHERE nomor_invoice = '$order_id'
  ");

  /* ==========================
     AMBIL CUSTOMER & PERMINTAAN
     ========================== */
  $res = $conn->query("
    SELECT pb.user_id, pb.id AS permintaan_id
    FROM invoice i
    JOIN distribusi_barang db ON i.distribusi_id = db.id
    JOIN permintaan_barang pb ON db.permintaan_id = pb.id
    WHERE i.nomor_invoice = '$order_id'
    LIMIT 1
  ");

  if ($res && $res->num_rows > 0) {

    $row = $res->fetch_assoc();
    $user_id = $row['user_id'];
    $permintaan_id = $row['permintaan_id'];

    $pesan = "Pembayaran invoice $order_id berhasil. Terima kasih.";

    /* INSERT NOTIFIKASI */
    $conn->query("
      INSERT INTO notifikasi
      (user_id, permintaan_id, pesan)
      VALUES
      ($user_id, $permintaan_id, '$pesan')
    ");
  }
}

/* ==========================
   RESPONSE KE MIDTRANS
   ========================== */
http_response_code(200);
echo 'OK';
