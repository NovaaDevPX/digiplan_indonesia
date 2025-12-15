<?php
require '../include/conn.php';
require '../include/midtrans-config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['invoice_id'];

$inv = $conn->query("SELECT * FROM invoice WHERE id_invoice=$id")->fetch_assoc();

$params = [
  'transaction_details' => [
    'order_id' => $inv['nomor_invoice'],
    'gross_amount' => (int)$inv['total']
  ]
];

$snapToken = \Midtrans\Snap::getSnapToken($params);

echo json_encode(['snapToken' => $snapToken]);
