<?php

/**
 * =====================================================
 * NOTIFICATION HELPER
 * =====================================================
 * - Support pesan admin & customer
 * - Receiver-based
 * - Aman untuk semua role
 */

/**
 * INSERT NOTIFIKASI
 *
 * @param mysqli    $conn
 * @param int       $receiver_id     Penerima notifikasi
 * @param int|null  $sender_id       Pengirim (admin/superadmin/system)
 * @param int|null  $permintaan_id   Relasi permintaan (optional)
 * @param string    $pesan_admin     Pesan untuk admin/super_admin
 * @param string|null $pesan_customer Pesan khusus customer
 *
 * @return bool
 */
function insertNotifikasi(
  mysqli $conn,
  int $receiver_id,
  ?int $sender_id,
  ?int $permintaan_id,
  string $pesan_admin,
  ?string $pesan_customer = null
) {
  $stmt = $conn->prepare("
    INSERT INTO notifikasi
      (receiver_id, sender_id, permintaan_id, pesan, pesan_customer, status_baca)
    VALUES (?, ?, ?, ?, ?, 0)
  ");

  if (!$stmt) {
    return false;
  }

  /* =========================
     HANDLE NULL INTEGER
  ========================= */
  if ($sender_id === null) {
    $sender_id = null;
  }
  if ($permintaan_id === null) {
    $permintaan_id = null;
  }

  $stmt->bind_param(
    "iisss",
    $receiver_id,
    $sender_id,
    $permintaan_id,
    $pesan_admin,
    $pesan_customer
  );

  $result = $stmt->execute();
  $stmt->close();

  return $result;
}


/**
 * =====================================================
 * HELPER AMBIL PESAN SESUAI ROLE
 * =====================================================
 */
function tampilkanPesanNotifikasi(array $notif, int $role_id): string
{
  // CUSTOMER
  if ($role_id === 1 && !empty($notif['pesan_customer'])) {
    return $notif['pesan_customer'];
  }

  // ADMIN / SUPER ADMIN
  return $notif['pesan'];
}
