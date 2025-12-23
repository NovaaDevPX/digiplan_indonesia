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
  <script src="//unpkg.com/alpinejs" defer></script>
  <?php include '../include/base-url.php'; ?>
  <style>
    [x-cloak] {
      display: none !important
    }
  </style>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black overflow-x-hidden"
  x-data="dropdownApp()">

  <div class="flex min-h-screen">
    <?php include '../include/layouts/sidebar-superadmin.php'; ?>
    <?php include '../include/layouts/notifications.php'; ?>

    <main class="ml-64 p-10 w-full">
      <div class="max-w-7xl mx-auto">

        <!-- HEADER -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Persetujuan Permintaan Barang</h1>
          <p class="text-white/80">Kelola dan setujui permintaan barang.</p>
        </div>

        <!-- TABLE -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-white/20 text-white">
                  <th class="p-4">No</th>
                  <th class="p-4">Customer</th>
                  <th class="p-4">Barang</th>
                  <th class="p-4">Jumlah</th>
                  <th class="p-4">Status</th>
                  <th class="p-4 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/10">

                <?php $no = 1;
                foreach ($data_permintaan as $row): ?>
                  <tr class="hover:bg-white/5">
                    <td class="p-4 text-white"><?= $no++ ?></td>
                    <td class="p-4 text-white"><?= htmlspecialchars($row['nama_user']) ?></td>
                    <td class="p-4 text-white"><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td class="p-4 text-white"><?= $row['jumlah'] ?></td>
                    <td class="p-4 text-white"><?= $row['status'] ?></td>

                    <!-- BUTTON -->
                    <td class="p-4 text-center">
                      <?php if ($row['status'] === 'diajukan'): ?>
                        <button
                          @click="openDropdown($event, <?= $row['id'] ?>)"
                          class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white">
                          ⋮
                        </button>
                      <?php else: ?>
                        <span class="text-gray-400">Selesai</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>

              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

  <!-- ================= FLOATING DROPDOWN (PORTAL) ================= -->
  <div x-show="dropdown.open"
    x-transition
    @click.outside="closeDropdown"
    x-cloak
    :style="`top:${dropdown.y}px; left:${dropdown.x}px`"
    class="fixed bg-slate-900/95 backdrop-blur-xl
              border border-white/20 rounded-xl shadow-2xl
              w-44 z-[99999]">

    <form action="item-approval-func.php" method="POST">
      <input type="hidden" name="id" :value="dropdown.id">
      <input type="hidden" name="aksi" value="terima">
      <button class="w-full px-4 py-2 text-left text-green-400 hover:bg-green-500/20 rounded-t-xl">
        Terima
      </button>
    </form>

    <button @click="openModalId = dropdown.id; closeDropdown()"
      class="w-full px-4 py-2 text-left text-red-400 hover:bg-red-500/20 rounded-b-xl">
      Tolak
    </button>
  </div>

  <!-- MODAL TOLAK -->
  <div x-show="openModalId !== null" x-cloak
    class="fixed inset-0 flex items-center justify-center bg-black/60 z-[100000]">
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6 w-96">
      <h3 class="text-white font-semibold mb-4">Alasan Penolakan</h3>
      <form action="item-approval-func.php" method="POST">
        <input type="hidden" name="id" :value="openModalId">
        <input type="hidden" name="aksi" value="tolak">
        <textarea name="catatan_admin" required
          class="w-full p-3 bg-white/20 text-white rounded-xl mb-4"></textarea>
        <div class="flex justify-end gap-2">
          <button type="button" @click="openModalId=null"
            class="px-4 py-2 bg-gray-500 rounded-lg">Batal</button>
          <button type="submit"
            class="px-4 py-2 bg-red-500 rounded-lg">Tolak</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= ALPINE LOGIC ================= -->
  <script>
    function dropdownApp() {
      return {
        openModalId: null,
        dropdown: {
          open: false,
          x: 0,
          y: 0,
          id: null
        },
        openDropdown(e, id) {
          const rect = e.target.getBoundingClientRect();
          this.dropdown = {
            open: true,
            id: id,
            x: rect.left - 120,
            y: rect.bottom + 8
          };
        },
        closeDropdown() {
          this.dropdown.open = false;
        }
      }
    }
  </script>

</body>

</html>