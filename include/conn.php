<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "digiplan_indonesia");

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function tambahNotifikasi($user_id, $permintaan_id, $pesan) {
    global $conn;
    if (is_array($user_id)) {
        foreach ($user_id as $uid) {
            $stmt = $conn->prepare("INSERT INTO notifikasi (user_id, permintaan_id, pesan, status_baca) VALUES (?, ?, ?, 'belum')");
            $stmt->bind_param("iis", $uid, $permintaan_id, $pesan);
            $stmt->execute();
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO notifikasi (user_id, permintaan_id, pesan, status_baca) VALUES (?, ?, ?, 'belum')");
        $stmt->bind_param("iis", $user_id, $permintaan_id, $pesan);
        $stmt->execute();
    }
}

?>