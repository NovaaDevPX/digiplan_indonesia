<?php
require '../include/conn.php';
require '../include/auth.php';

cek_role(['customer']);

// ==============================
// VALIDASI ID
// ==============================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: dashboard.php");
  exit;
}

$id = (int) $_GET['id'];

// ==============================
// AMBIL DATA BARANG
// ==============================
$q = $conn->query("
  SELECT 
    id,
    nama_barang,
    deskripsi,
    harga,
    gambar
  FROM barang
  WHERE id = $id
    AND gambar IS NOT NULL
    AND gambar != ''
  LIMIT 1
");

if ($q->num_rows === 0) {
  header("Location: dashboard.php");
  exit;
}

$barang = $q->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($barang['nama_barang']); ?> | DigiPlan Indonesia</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          backdropBlur: {
            xs: '2px',
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black min-h-screen">

  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-customer.php'; ?>

    <!-- MAIN -->
    <main class="ml-64 p-10 w-full">
      <div class="max-w-5xl mx-auto space-y-8">

        <!-- Breadcrumb -->
        <div class="text-white/70 text-sm">
          <a href="dashboard.php" class="hover:text-white">Dashboard</a>
          <span class="mx-2">/</span>
          <span class="text-white"><?= htmlspecialchars($barang['nama_barang']); ?></span>
        </div>

        <!-- Card -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">

          <!-- Gambar -->
          <div class="h-full">
            <img src="../uploads/<?= htmlspecialchars($barang['gambar']); ?>"
              alt="<?= htmlspecialchars($barang['nama_barang']); ?>"
              class="w-full h-full object-cover">
          </div>

          <!-- Detail -->
          <div class="p-8 space-y-6">
            <h1 class="text-3xl font-bold text-white">
              <?= htmlspecialchars($barang['nama_barang']); ?>
            </h1>

            <p class="text-white/70 leading-relaxed">
              <?= nl2br(htmlspecialchars($barang['deskripsi'])); ?>
            </p>

            <div class="text-2xl font-bold text-blue-400">
              Rp <?= number_format($barang['harga'], 0, ',', '.'); ?>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4">

              <!-- Ajukan Permintaan -->
              <a href="ajukan-permintaan.php?barang_id=<?= $barang['id']; ?>"
                class="flex-1 text-center px-6 py-3
                      bg-blue-500 hover:bg-blue-600
                      text-white rounded-xl
                      font-semibold transition">
                Ajukan Permintaan
              </a>

              <!-- Kembali -->
              <a href="dashboard.php"
                class="flex-1 text-center px-6 py-3
                      bg-white/10 hover:bg-white/20
                      text-white rounded-xl
                      transition">
                Kembali
              </a>

            </div>

          </div>
        </div>

      </div>
    </main>

  </div>

</body>

</html>