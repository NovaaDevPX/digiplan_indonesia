<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

/* =========================
   DATA DISTRIBUSI SIAP INVOICE
========================= */
$distribusi = $conn->query("
  SELECT 
    d.id,
    d.kode_distribusi,
    d.tanggal_terima,
    d.harga_satuan,
    d.harga_total,
    d.sumber_harga,
    p.kode_permintaan,
    p.nama_barang,
    p.jumlah,
    u.name AS customer,
    i.id_invoice,
    i.status AS status_invoice
  FROM distribusi_barang d
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  JOIN users u ON p.user_id = u.id
  LEFT JOIN invoice i 
    ON i.distribusi_id = d.id
    AND i.deleted_at IS NULL
  WHERE d.status_distribusi = 'diterima'
  ORDER BY d.tanggal_terima DESC
");

/* =========================
   GENERATE NOMOR INVOICE
========================= */
$q = $conn->query("SELECT MAX(nomor_invoice) AS maxInv FROM invoice");
$data = $q->fetch_assoc();
$no = (int) substr($data['maxInv'] ?? 'INV-000', 4, 3);
$nomor_invoice = 'INV-' . str_pad($no + 1, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Invoice | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <?php include '../include/base-url.php'; ?>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">
  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-superadmin.php'; ?>

    <main class="ml-64 p-10 w-full">
      <div class="max-w-7xl mx-auto">

        <!-- HEADER -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-3xl font-bold">Pembuatan Invoice</h1>
          <p class="text-white/70">
            Buat invoice untuk distribusi yang telah selesai
          </p>
        </div>

        <!-- TABEL -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <h2 class="text-xl font-bold text-white mb-4">Distribusi Siap Invoice</h2>
          <div class="overflow-x-auto rounded-2xl">

            <table class="w-full border-collapse rounded-2xl">
              <thead class="bg-white/20">
                <tr>
                  <th class="p-3 text-left">Kode Distribusi</th>
                  <th class="p-3 text-left">Customer</th>
                  <th class="p-3 text-left">Barang</th>
                  <th class="p-3 text-left">Total</th>
                  <th class="p-3 text-center">Status</th>
                  <th class="p-3 text-center">Sumber Harga</th>
                  <th class="p-3 text-center">Aksi</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-white/10">
                <?php while ($row = $distribusi->fetch_assoc()): ?>
                  <tr>
                    <td class="p-3"><?= $row['kode_distribusi'] ?></td>
                    <td class="p-3"><?= $row['customer'] ?></td>
                    <td class="p-3">
                      <?= $row['nama_barang'] ?> (<?= $row['jumlah'] ?>)
                    </td>
                    <td class="p-3">
                      Rp <?= number_format($row['harga_total'], 0, ',', '.') ?>
                    </td>

                    <!-- STATUS -->
                    <td class="p-3 text-center">
                      <?php if (!$row['id_invoice']): ?>
                        <span class="px-3 py-1 bg-gray-500/20 text-gray-300 rounded-full text-xs">
                          Belum Dibuat
                        </span>

                      <?php elseif ($row['status_invoice'] === 'belum bayar'): ?>
                        <span class="px-3 py-1 bg-yellow-500/20 text-yellow-300 rounded-full text-xs">
                          Belum Bayar
                        </span>

                      <?php elseif ($row['status_invoice'] === 'dibatalkan'): ?>
                        <span class="px-3 py-1 bg-red-500/20 text-red-300 rounded-full text-xs">
                          Dibatalkan
                        </span>

                      <?php elseif ($row['status_invoice'] === 'lunas'): ?>
                        <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 rounded-full text-xs">
                          Lunas
                        </span>

                      <?php else: ?>
                        <span class="px-3 py-1 bg-gray-400/20 text-gray-300 rounded-full text-xs">
                          Tidak Diketahui
                        </span>
                      <?php endif; ?>
                    </td>

                    <td class="p-3 text-center">
                      <?= strtoupper($row['sumber_harga']) ?>
                    </td>

                    <!-- AKSI -->
                    <td class="p-4 relative text-center">
                      <!-- BUTTON TITIK TIGA -->
                      <button onclick="toggleDropdown(<?= $row['id']; ?>, event)"
                        class="text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                          <path
                            d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z">
                          </path>
                        </svg>
                      </button>

                      <!-- DROPDOWN MENU -->
                      <div id="dropdown-<?= $row['id']; ?>"
                        class="hidden fixed right-[60px] z-[99999] w-48 bg-slate-900/50 backdrop-blur-xl border border-white/30 rounded-xl shadow-2xl text-left">

                        <!-- DETAIL (SELALU ADA) -->
                        <a href="invoice-detail.php?id=<?= $row['id'] ?>"
                          class="block px-4 py-3 text-white hover:bg-white/10 rounded-t-xl transition-colors duration-200">
                          Detail
                        </a>

                        <?php if (!$row['id_invoice']): ?>

                          <!-- BELUM ADA INVOICE -->
                          <a href="invoice-create.php?id=<?= $row['id'] ?>"
                            class="block px-4 py-3 text-white hover:bg-white/10 rounded-t-xl transition-colors duration-200">
                            Buat Invoice
                          </a>

                        <?php elseif ($row['status_invoice'] === 'dibatalkan'): ?>

                          <!-- INVOICE DIBATALKAN -->
                          <a href="invoice-create.php?id=<?= $row['id'] ?>&retry=1"
                            class="block px-4 py-3 text-white hover:bg-white/10 rounded-t-xl transition-colors duration-200">
                            Buat Ulang
                          </a>

                        <?php endif; ?>

                      </div>
                    </td>


                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script>
    function toggleDropdown(id, event) {
      event.stopPropagation();

      const current = document.getElementById("dropdown-" + id);

      // Tutup dropdown lain
      document.querySelectorAll("[id^='dropdown-']").forEach(d => {
        if (d !== current) d.classList.add("hidden");
      });

      // Toggle dropdown sekarang
      current.classList.toggle("hidden");
    }

    // Tutup jika klik di luar
    document.addEventListener("click", function() {
      document.querySelectorAll("[id^='dropdown-']").forEach(d => {
        d.classList.add("hidden");
      });
    });
  </script>

</body>

</html>