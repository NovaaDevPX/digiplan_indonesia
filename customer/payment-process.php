<?php
header('Content-Type: application/json');

require '../include/conn.php';
require '../include/auth.php';
require '../include/midtrans-config.php';

cek_role(['customer'], true);

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['invoice_id'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid request']);
  exit;
}

$invoice_id = (int)$data['invoice_id'];

$inv = $conn->query("
  SELECT * FROM invoice 
  WHERE id_invoice = $invoice_id
")->fetch_assoc();

if (!$inv) {
  http_response_code(404);
  echo json_encode(['error' => 'Invoice not found']);
  exit;
}

/* PENTING: Order ID HARUS UNIK */
$order_id = $inv['nomor_invoice'] . '-' . time();

try {
  $params = [
    'transaction_details' => [
      'order_id' => $order_id,
      'gross_amount' => (int)$inv['total']
    ],
    'customer_details' => [
      'first_name' => $_SESSION['user']['nama'] ?? 'Customer',
      'email' => $_SESSION['user']['email'] ?? 'customer@mail.com'
    ]
  ];

  $snapToken = \Midtrans\Snap::getSnapToken($params);

  /* SIMPAN order_id BARU */
  $conn->query("
    UPDATE invoice
    SET nomor_invoice = '$order_id'
    WHERE id_invoice = $invoice_id
  ");

  echo json_encode(['snapToken' => $snapToken]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Midtrans error',
    'message' => $e->getMessage()
  ]);
}
