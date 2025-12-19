<?php
require 'item-approval-func.php';

$data_permintaan = [];
while ($row = $result->fetch_assoc()) {
  $data_permintaan[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Persetujuan Barang | DigiPlan Indonesia</title>
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
  <script src="//unpkg.com/alpinejs" defer></script>
  <?php include '../include/base-url.php'; ?>
  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black overflow-x-hidden" x-data="{ openModalId: null }">
  <div class="flex min-h-screen">
    <?php include '../include/layouts/sidebar-superadmin.php'; ?>
    <?php include '../include/layouts/notifications.php'; ?>

    <!-- Main Content -->
    <main class="ml-64 p-10 w-full flex-1">
      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Persetujuan Permintaan Barang</h1>
          <p class="text-white/80">Kelola dan setujui permintaan barang dari customer dengan efisien.</p>
        </div>

        <div class="space-y-8">

          <!-- Tabel Persetujuan -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-4">Daftar Permintaan</h2>

            <div class="overflow-x-auto">
              <table class="w-full border-collapse rounded-xl overflow-hidden">
                <thead>
                  <tr class="bg-white/20 text-white">
                    <th class="p-4 text-left">No</th>
                    <th class="p-4 text-left">Customer</th>
                    <th class="p-4 text-left">Admin</th>
                    <th class="p-4 text-left">Barang</th>
                    <th class="p-4 text-left">Jumlah</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-left">Tanggal</th>
                    <th class="p-4 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                  <?php $no = 1;
                  foreach ($data_permintaan as $row): ?>
                    <tr class="hover:bg-white/5 transition-colors duration-200">
                      <td class="p-4 text-white/90"><?= $no++ ?></td>
                      <td class="p-4 text-white/90 max-w-xs truncate"><?= htmlspecialchars($row['nama_user']); ?></td>
                      <td class="p-4 text-white/90 max-w-xs truncate">
                        <?= $row['nama_admin'] ?: '<i class="text-gray-400">Belum diverifikasi</i>'; ?>
                      </td>
                      <td class="p-4 text-white/90 max-w-sm truncate"><?= htmlspecialchars($row['nama_barang']); ?></td>
                      <td class="p-4 text-white/90"><?= $row['jumlah']; ?></td>
                      <td class="p-4 capitalize">
                        <?php
                        switch ($row['status']) {
                          case 'diajukan':
                            echo "<span class='px-3 py-1 bg-yellow-500/20 text-yellow-500 rounded-lg text-xs whitespace-nowrap font-semibold'>
        Diajukan
      </span>";
                            break;

                          case 'ditolak':
                            echo "<span class='px-3 py-1 bg-red-500/20 text-red-500 rounded-lg text-xs whitespace-nowrap font-semibold'>
        Ditolak
      </span>";
                            break;
                          case 'dibatalkan':
                            echo "<span class='px-3 py-1 bg-red-500/20 text-red-500 rounded-lg text-xs whitespace-nowrap font-semibold'>
        Dibatalkan
      </span>";
                            break;

                          case 'disetujui':
                            echo "<span class='px-3 py-1 bg-green-500/20 text-green-500 rounded-lg text-xs whitespace-nowrap font-semibold'>
        Disetujui
      </span>";
                            break;

                          case 'dalam_pengadaan':
                            echo "<span class='px-3 py-1 bg-blue-500/20 text-blue-500 rounded-lg text-xs whitespace-nowrap font-semibold'>
        Dalam Pengadaan
      </span>";
                            break;

                          case 'siap_distribusi':
                            echo "<span class='px-3 py-1 bg-sky-500/20 text-sky-500 rounded-lg text-xs whitespace-nowrap font-semibold'>
        Siap Distribusi
      </span>";
                            break;

                          case 'selesai':
                            echo "<span class='px-3 py-1 bg-emerald-500/20 text-emerald-500 rounded-lg text-xs whitespace-nowrap font-semibold'>
        Selesai
      </span>";
                            break;

                          default:
                            echo "<span class='px-3 py-1 bg-gray-500/20 text-gray-500 rounded-lg text-xs whitespace-nowrap font-semibold'>
        Tidak Diketahui
      </span>";
                        }
                        ?>
                      </td>

                      <td class="p-4 text-white/90"><?= date('d-m-Y', strtotime($row['created_at'])); ?></td>
                      <td class="p-4 text-center">
                        <?php if ($row['status'] === 'diajukan'): ?>
                          <div x-data="{ open: false }" class="relative inline-block text-left">
                            <button @click="open = !open"
                              class="inline-flex justify-center items-center w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white transition-colors">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3z" />
                              </svg>
                            </button>

                            <div x-show="open" @click.outside="open = false"
                              class="absolute right-0 mt-2 w-32 w-48 bg-slate-900/50 backdrop-blur-xl border border-white/30 rounded-xl shadow-2xl z-50">
                              <!-- Form Terima -->
                              <form action="item-approval-func.php" method="POST">
                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                <input type="hidden" name="aksi" value="terima">
                                <button type="submit" class="block w-full text-left px-4 py-2 text-green-400 hover:bg-green-500/20 rounded">Terima</button>
                              </form>
                              <!-- Button Tolak buka modal -->
                              <button @click="openModalId = <?= $row['id']; ?>; open=false"
                                class="block w-full text-left px-4 py-2 text-red-400 hover:bg-red-500/20 rounded">Tolak</button>
                            </div>
                          </div>
                        <?php else: ?>
                          <span class="text-gray-400 text-sm">Selesai</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>

      </div>
    </main>
  </div>

  <!-- Modal Tolak -->
  <div x-show="openModalId !== null" x-cloak
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl p-6 w-96">
      <h3 class="text-lg font-semibold text-white mb-4">Alasan Penolakan</h3>
      <form action="item-approval-func.php" method="POST">
        <input type="hidden" name="id" :value="openModalId">
        <input type="hidden" name="aksi" value="tolak">
        <textarea name="catatan_admin" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-red-500 mb-4" placeholder="Masukkan catatan..." required></textarea>
        <div class="flex justify-end gap-2">
          <button type="button" @click="openModalId = null" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors">Batal</button>
          <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">Tolak</button>
        </div>
      </form>
    </div>
  </div>

</body>

</html>