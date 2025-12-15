<?php
require_once __DIR__ . '/../vendor/midtrans/midtrans-php/Midtrans.php';

\Midtrans\Config::$serverKey = getenv('MIDTRANS_SERVER_KEY');
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
