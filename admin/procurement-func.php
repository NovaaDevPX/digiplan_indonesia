<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: procurement.php');
  exit;
}

/* =========================
   DATA LOGIN
========================= */
$admin_id = (int) ($_SESSION['user_id'] ?? 0);

/* =========================
   DATA FORM
========================= */
$barang_id     = !empty($_POST['barang_id']) ? (int) $_POST['barang_id'] : null;
$admin_id      = (int) ($_POST['admin_id'] ?? 0);
$permintaan_id = (int) ($_POST['permintaan_id'] ?? 0);
$jumlah        = (int) ($_POST['jumlah'] ?? -1);
$harga_satuan  = (float) ($_POST['harga_satuan'] ?? 0);

$supplier = trim($_POST['supplier'] ?? '');
$kontak   = trim($_POST['kontak_supplier'] ?? '');
$alamat   = trim($_POST['alamat_supplier'] ?? '');

/* =========================
   VALIDASI DASAR
========================= */
if (
  $admin_id <= 0 ||
  $admin_id <= 0 ||
  $permintaan_id <= 0 ||
  $jumlah < 0 ||               // ✅ boleh 0
  $harga_satuan < 0 ||         // ✅ boleh 0 jika jumlah = 0
  $supplier === '' ||
  $kontak === '' ||
  $alamat === ''
) {
  die('❌ Data tidak valid');
}

/* =========================
   VALIDASI PERMINTAAN
========================= */
$q = $conn->prepare("
  SELECT 
    pb.jumlah,
    pb.nama_barang,
    pb.merk,
    pb.warna,
    pb.user_id,
    u.name AS customer_name
  FROM permintaan_barang pb
  JOIN users u ON pb.user_id = u.id
  WHERE pb.id = ?
    AND pb.status = 'disetujui'
");
$q->bind_param("i", $permintaan_id);
$q->execute();
$permintaan = $q->get_result()->fetch_assoc();

if (!$permintaan) {
  die('❌ Permintaan tidak valid atau belum disetujui');
}

/* ==================================================
   STOK MENCUKUPI → PENGADAAN OTOMATIS (VIRTUAL)
================================================== */
if ($jumlah === 0) {

  $conn->begin_transaction();

  try {

    /* ===============================
       GENERATE KODE PENGADAAN
    =============================== */
    $r = $conn->query("SELECT COUNT(*) total FROM pengadaan_barang");
    $total = ($r->fetch_assoc()['total'] ?? 0) + 1;
    $kode_pengadaan = 'PGD-' . str_pad($total, 3, '0', STR_PAD_LEFT);

    /* ===============================
       INSERT PENGADAAN OTOMATIS
    =============================== */
    $stmt = $conn->prepare("
      INSERT INTO pengadaan_barang (
        kode_pengadaan,
        permintaan_id,
        admin_id,
        barang_id,
        nama_barang,
        merk,
        warna,
        jumlah,
        supplier,
        kontak_supplier,
        alamat_supplier,
        harga_satuan,
        harga_total,
        status_pengadaan,
        tanggal_pengadaan
      ) VALUES (
        ?, ?, ?, ?,
        ?, ?, ?, 0,
        'STOK_GUDANG ( AUTO )',
        '-',
        '-',
        0,
        0,
        'selesai',
        CURDATE()
      )
    ");

    $stmt->bind_param(
      "siiisss",
      $kode_pengadaan,
      $permintaan_id,
      $admin_id,
      $barang_id,
      $permintaan['nama_barang'],
      $permintaan['merk'],
      $permintaan['warna']
    );
    $stmt->execute();

    /* ===============================
       UPDATE STATUS PERMINTAAN
    =============================== */
    $up = $conn->prepare("
      UPDATE permintaan_barang
      SET status = 'siap_distribusi'
      WHERE id = ?
    ");
    $up->bind_param("i", $permintaan_id);
    $up->execute();

    /* ==================================================
       NOTIFIKASI 1 — CUSTOMER
    ================================================== */
    $pesan_customer =
      "Halo {$permintaan['customer_name']},\n\n" .
      "Barang yang Anda minta tersedia di gudang.\n" .
      "Tidak diperlukan pengadaan tambahan.\n\n" .
      "Status permintaan Anda kini: SIAP DIKIRIM.";

    insertNotifikasi(
      $conn,
      $permintaan['user_id'], // receiver customer
      $admin_id,              // sender admin
      $permintaan_id,
      '',                   // pesan admin (kosong)
      $pesan_customer
    );

    /* ==================================================
       NOTIFIKASI 2 — ADMIN & SUPER ADMIN
    ================================================== */
    $pesan_admin =
      "Pengadaan otomatis dibuat (STOK MENCUKUPI).\n\n" .
      "Kode Pengadaan: $kode_pengadaan\n" .
      "Kode Permintaan: {$permintaan['kode_permintaan']}\n" .
      "Barang: {$permintaan['nama_barang']}\n\n" .
      "Status Permintaan: SIAP DISTRIBUSI";

    $qStaff = $conn->query("
      SELECT id FROM users
      WHERE role_id IN (2,3)
        AND deleted_at IS NULL
    ");

    while ($staff = $qStaff->fetch_assoc()) {
      insertNotifikasi(
        $conn,
        $staff['id'],   // receiver admin / super admin
        $admin_id,      // sender (admin yang login)
        $permintaan_id,
        $pesan_admin,   // pesan internal
        null            // pesan customer kosong
      );
    }

    $conn->commit();

    header('Location: procurement.php?success=stok_mencukupi_barang_siap_dikirim');
    exit;
  } catch (Throwable $e) {
    $conn->rollback();
    die('❌ ERROR DATABASE: ' . $e->getMessage());
  }
}



/* =========================
   VALIDASI JUMLAH (PENGADAAN)
========================= */
if ($jumlah < $permintaan['jumlah']) {
  die('❌ Jumlah pengadaan tidak boleh kurang dari permintaan');
}

/* =========================
   HITUNG & KODE PENGADAAN
========================= */
$harga_total = $jumlah * $harga_satuan;

$r = $conn->query("SELECT COUNT(*) total FROM pengadaan_barang");
$total = $r->fetch_assoc()['total'] + 1;
$kode  = 'PGD-' . str_pad($total, 3, '0', STR_PAD_LEFT);

$conn->begin_transaction();

try {

  /* ===============================
     INSERT PENGADAAN
  =============================== */
  $stmt = $conn->prepare("
    INSERT INTO pengadaan_barang (
      kode_pengadaan,
      permintaan_id,
      admin_id,
      barang_id,
      nama_barang,
      merk,
      warna,
      jumlah,
      supplier,
      kontak_supplier,
      alamat_supplier,
      harga_satuan,
      harga_total,
      status_pengadaan,
      tanggal_pengadaan
    ) VALUES (
      ?, ?, ?, ?,
      ?, ?, ?, ?,
      ?, ?, ?,
      ?, ?,
      'diproses',
      CURDATE()
    )
  ");

  $stmt->bind_param(
    "siiisssisssdd",
    $kode,
    $permintaan_id,
    $admin_id,
    $barang_id,
    $permintaan['nama_barang'],
    $permintaan['merk'],
    $permintaan['warna'],
    $jumlah,
    $supplier,
    $kontak,
    $alamat,
    $harga_satuan,
    $harga_total
  );
  $stmt->execute();

  /* ===============================
     UPDATE STATUS PERMINTAAN
  =============================== */
  $up = $conn->prepare("
    UPDATE permintaan_barang
    SET status = 'dalam_pengadaan'
    WHERE id = ?
  ");
  $up->bind_param("i", $permintaan_id);
  $up->execute();

  /* ==================================================
     NOTIFIKASI 1 — CUSTOMER
  ================================================== */
  $pesan_customer =
    "Halo {$permintaan['customer_name']},\n\n" .
    "Permintaan barang Anda telah masuk tahap pengadaan.\n\n" .
    "Kode Permintaan: {$permintaan['kode_permintaan']}\n" .
    "Barang: {$permintaan['nama_barang']}\n" .
    "Kami akan menginformasikan kembali setelah proses selesai.";

  insertNotifikasi(
    $conn,
    $permintaan['user_id'], // receiver customer
    $admin_id,              // sender admin / super admin
    $permintaan_id,
    '',                     // pesan admin kosong
    $pesan_customer
  );

  /* ==================================================
     NOTIFIKASI 2 — INTERNAL (ADMIN & SUPER ADMIN)
  ================================================== */
  $pesan_admin =
    "Pengadaan barang dibuat.\n\n" .
    "Kode Pengadaan: $kode\n" .
    "Kode Permintaan: {$permintaan['kode_permintaan']}\n" .
    "Customer: {$permintaan['customer_name']}\n" .
    "Barang: {$permintaan['nama_barang']}\n" .
    "Jumlah: $jumlah\n\n" .
    "Status Permintaan: DALAM PENGADAAN";

  // Kirim SATU notifikasi internal (cukup 1 record)
  $qInternal = $conn->query("
    SELECT id FROM users
    WHERE role_id IN (2,3)
      AND deleted_at IS NULL
    LIMIT 1
  ");

  if ($qInternal && $internal = $qInternal->fetch_assoc()) {
    insertNotifikasi(
      $conn,
      $internal['id'], // receiver admin / super admin
      $admin_id,       // sender
      $permintaan_id,
      $pesan_admin,
      ''
    );
  }

  $conn->commit();

  header('Location: procurement.php?success=item_procurement_success');
  exit;
} catch (Throwable $e) {
  $conn->rollback();
  die('❌ ERROR DATABASE: ' . $e->getMessage());
}
