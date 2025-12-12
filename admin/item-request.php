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

    <!-- Content -->
    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Permintaan Barang</h1>
          <p class="text-white/80">Daftar permintaan barang dari customer yang perlu diverifikasi.</p>
        </div>

        <!-- Table -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl shadow-2xl overflow-hidden">
          <div class="overflow-x-auto">
            <table class="table-auto w-full">
              <thead>
                <tr class="bg-white/20 text-white">
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">No</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Nama Customer</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Nama Barang</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Jumlah</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Status</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Tanggal</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Tanggal Verifikasi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/10">
                <?php $no = 1;
                while ($row = $result->fetch_assoc()): ?>
                  <tr class="hover:bg-white/5 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap text-white/90"><?= $no++ ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-white/90 font-medium"><?= htmlspecialchars($row['nama_user']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-white/90"><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-white/90"><?= $row['jumlah']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap capitalize">
                      <?php
                      switch ($row['status']) {
                        case 'proses':
                          echo "<span class='px-3 py-1 rounded-lg bg-blue-500/20 text-blue-300 text-xs font-semibold border border-blue-500/30'>Menunggu verifikasi </span>";
                          break;
                        case 'accepted_by_superadmin':
                          echo "<span class='px-3 py-1 rounded-lg bg-green-500/20 text-green-300 text-xs font-semibold border border-green-500/30'>Sudah diverifikasi</span>";
                          break;
                        case 'reject':
                          echo "<span class='px-3 py-1 rounded-lg bg-red-500/20 text-red-300 text-xs font-semibold border border-red-500/30'>Ditolak </span>";
                          break;
                        default:
                          echo "<span class='px-3 py-1 rounded-lg bg-yellow-500/20 text-yellow-300 text-xs font-semibold border border-yellow-500/30'>Proses</span>";
                      }
                      ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-white/70"><?= date('d-m-Y', strtotime($row['tanggal_permintaan'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-white/70">
                      <?=
                      !empty($row['tanggal_verifikasi'])
                        ? date('d-m-Y H:i', strtotime($row['tanggal_verifikasi']))
                        : 'Belum Diverifikasi';
                      ?>
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