<?php
require '../include/conn.php';

/* Tolak akses selain POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo "Midtrans Callback Endpoint";
  exit;
}

/* Ambil JSON */
$json = file_get_contents("php://input");
$data = json_decode($json, true);

/* Logging biar yakin */
file_put_contents(
  __DIR__ . '/callback-log.txt',
  date('Y-m-d H:i:s') . " " . $json . PHP_EOL,
  FILE_APPEND
);

/* Ambil data */
$order_id      = $data['order_id'] ?? '';
$status_code   = $data['status_code'] ?? '';
$gross_amount  = $data['gross_amount'] ?? '';
$signature_key = $data['signature_key'] ?? '';
$transaction_status = $data['transaction_status'] ?? '';

/* Server Key */
$serverKey = getenv('MIDTRANS_SERVER_KEY');

/* Validasi signature */
$expectedSignature = hash(
  "sha512",
  $order_id . $status_code . $gross_amount . $serverKey
);

if ($signature_key !== $expectedSignature) {
  http_response_code(403);
  exit('Invalid signature');
}

if (in_array($transaction_status, ['cancel', 'expire', 'deny'])) {
  mysqli_query($conn, "
    UPDATE invoice
    SET status = 'dibatalkan'
    WHERE nomor_invoice = '$order_id'
  ");
}


/* Jika sukses */
if (in_array($transaction_status, ['capture', 'settlement'])) {

  mysqli_query($conn, "
    UPDATE invoice
    SET status = 'lunas'
    WHERE nomor_invoice = '$order_id'
  ");

  mysqli_query($conn, "
    INSERT INTO pembayaran
    (id_invoice, metode, jumlah, tanggal_bayar, status)
    SELECT id_invoice, 'Midtrans', total, NOW(), 'berhasil'
    FROM invoice
    WHERE nomor_invoice = '$order_id'
  ");
}

http_response_code(200);
echo 'OK';
