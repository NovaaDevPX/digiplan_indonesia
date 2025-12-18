<?php
require 'item-request-func.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Permintaan Barang | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          backdropBlur: {
            'xs': '2px',
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black overflow-x-hidden">

  <?php include '../include/layouts/sidebar-admin.php'; ?>

  <div class="flex min-h-screen">
    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Permintaan Barang</h1>
          <p class="text-white/80">Daftar permintaan barang dari customer.</p>
        </div>

        <!-- Table -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl shadow-2xl mb-8 p-6">
          <div class="rounded-2xl overflow-x-auto">
            <table class="table-auto w-full">
              <thead>
                <tr class="bg-white/20 text-white text-left">
                  <th class="px-6 py-4">No</th>
                  <th class="px-6 py-4">Nama Customer</th>
                  <th class="px-6 py-4">Nama Barang</th>
                  <th class="px-6 py-4">Jumlah</th>
                  <th class="px-6 py-4">Status</th>
                  <th class="px-6 py-4">Tanggal Permintaan</th>
                  <th class="px-6 py-4">Tanggal Verifikasi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/10">

                <?php $no = 1;
                while ($row = $result->fetch_assoc()): ?>
                  <tr class="hover:bg-white/5">
                    <td class="px-6 py-4 text-white"><?= $no++ ?></td>
                    <td class="px-6 py-4 text-white"><?= htmlspecialchars($row['nama_user']) ?></td>
                    <td class="px-6 py-4 text-white"><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td class="px-6 py-4 text-white"><?= $row['jumlah'] ?></td>

                    <!-- STATUS -->
                    <td class="px-6 py-4 capitalize">
                      <?php
                      switch ($row['status']) {
                        case 'diajukan':
                          echo "<span class='px-3 py-1 bg-blue-500/20 text-blue-300 rounded-lg text-xs whitespace-nowrap'>Diajukan</span>";
                          break;
                        case 'disetujui':
                          echo "<span class='px-3 py-1 bg-green-500/20 text-green-300 rounded-lg text-xs whitespace-nowrap'>Disetujui</span>";
                          break;
                        case 'ditolak':
                          echo "<span class='px-3 py-1 bg-red-500/20 text-red-300 rounded-lg text-xs whitespace-nowrap'>Ditolak</span>";
                          break;
                        case 'dibatalkan':
                          echo "<span class='px-3 py-1 bg-red-500/20 text-red-300 rounded-lg text-xs whitespace-nowrap'>Dibatalkan</span>";
                          break;
                        case 'dalam_pengadaan':
                          echo "<span class='px-3 py-1 bg-yellow-500/20 text-yellow-300 rounded-lg text-xs whitespace-nowrap'>Dalam Pengadaan</span>";
                          break;
                        case 'siap_distribusi':
                          echo "<span class='px-3 py-1 bg-purple-500/20 text-purple-300 rounded-lg text-xs whitespace-nowrap'>Siap Distribusi</span>";
                          break;
                        case 'selesai':
                          echo "<span class='px-3 py-1 bg-emerald-500/20 text-emerald-300 rounded-lg text-xs whitespace-nowrap'>Selesai</span>";
                          break;
                        default:
                          echo "-";
                      }
                      ?>
                    </td>

                    <!-- TANGGAL -->
                    <td class="px-6 py-4 text-white/70">
                      <?= date('d-m-Y', strtotime($row['created_at'])) ?>
                    </td>

                    <td class="px-6 py-4 text-white/70">
                      <?= $row['tanggal_verifikasi']
                        ? date('d-m-Y H:i', strtotime($row['tanggal_verifikasi']))
                        : 'Belum Diverifikasi'; ?>
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

</body>

</html>