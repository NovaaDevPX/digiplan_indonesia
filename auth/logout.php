<?php
require '../include/conn.php';
require '../include/base-url.php';
session_destroy();
header('Location: ' . $base_url . 'auth/index.php');
exit;
