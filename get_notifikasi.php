<?php
require 'function.php';
$user_id = $_SESSION['user_id'];

$q = mysqli_query($conn, 
    "SELECT pesan FROM notifikasi 
     WHERE user_id='$user_id' AND dibaca=0"
);

$list = [];
while ($n = mysqli_fetch_assoc($q)) {
    $list[] = $n;
}

// tandai sudah dibaca
mysqli_query($conn, 
    "UPDATE notifikasi SET dibaca=1 WHERE user_id='$user_id'"
);

echo json_encode($list);
