<?php
require '../include/conn.php';
require '../include/auth.php';

cek_role(['customer']);

/* ==============================
   VALIDASI ID
============================== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: dashboard.php");
  exit;
}

$id = (int) $_GET['id'];

/* ==============================
   AMBIL DATA BARANG
============================== */
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

<body class="min-h-screen bg-gradient-to-b from-gray-900 to-black text-gray-100">

  <div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <?php include '../include/layouts/sidebar-customer.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="ml-64 w-full p-8 lg:p-10">
      <div class="max-w-6xl mx-auto space-y-6">

        <!-- Breadcrumb -->
        <nav class="text-sm text-white/60">
          <a href="dashboard.php" class="hover:text-white transition">
            Dashboard
          </a>
          <span class="mx-2">/</span>
          <span class="text-white font-medium">
            <?= htmlspecialchars($barang['nama_barang']); ?>
          </span>
        </nav>

        <!-- CARD DETAIL -->
        <section
          class="grid grid-cols-1 md:grid-cols-2
                 backdrop-blur-xl bg-white/10
                 border border-white/20
                 rounded-2xl shadow-2xl overflow-hidden">

          <!-- IMAGE -->
          <div class="relative aspect-[4/3] md:aspect-auto">
            <img
              src="../uploads/<?= htmlspecialchars($barang['gambar']); ?>"
              alt="<?= htmlspecialchars($barang['nama_barang']); ?>"
              class="absolute inset-0 w-full h-full object-cover">
          </div>

          <!-- DETAIL -->
          <div class="p-8 flex flex-col justify-between">

            <div class="space-y-5">
              <h1 class="text-3xl font-bold tracking-tight text-white">
                <?= htmlspecialchars($barang['nama_barang']); ?>
              </h1>

              <p class="text-white/70 leading-relaxed text-sm md:text-base">
                <?= nl2br(htmlspecialchars($barang['deskripsi'])); ?>
              </p>
            </div>

            <!-- PRICE & ACTION -->
            <div class="pt-8 space-y-6">

              <div class="text-2xl md:text-3xl font-bold text-blue-400">
                Rp <?= number_format($barang['harga'], 0, ',', '.'); ?>
              </div>

              <div class="flex flex-col sm:flex-row gap-4">

                <!-- AJUKAN -->
                <a
                  href="ajukan-permintaan.php?barang_id=<?= $barang['id']; ?>"
                  class="flex-1 inline-flex items-center justify-center
                         px-6 py-3 rounded-xl
                         bg-blue-500 hover:bg-blue-600
                         text-white font-semibold
                         transition-all duration-200">
                  Ajukan Permintaan
                </a>

                <!-- KEMBALI -->
                <a
                  href="dashboard.php"
                  class="flex-1 inline-flex items-center justify-center
                         px-6 py-3 rounded-xl
                         bg-white/10 hover:bg-white/20
                         text-white
                         transition-all duration-200">
                  Kembali
                </a>

              </div>
            </div>

          </div>
        </section>

      </div>
    </main>

  </div>

</body>

</html>