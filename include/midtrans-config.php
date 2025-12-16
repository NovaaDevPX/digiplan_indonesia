<?php
require_once __DIR__ . '/../vendor/midtrans/midtrans-php/Midtrans.php';
require_once __DIR__ . '/env.php';

\Midtrans\Config::$serverKey = getenv('MIDTRANS_SERVER_KEY');
\Midtrans\Config::$isProduction = getenv('MIDTRANS_IS_PRODUCTION') === 'true';
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
