<?php
// Pastikan $base_url tersedia
if (!isset($base_url)) {
  $base_url = "/digiplan_indonesia/";
}
?>

<aside class="w-64 min-h-screen bg-gradient-to-b from-gray-900 to-black text-gray-200 p-6 fixed shadow-xl">

  <!-- Logo -->
  <h1 class="text-2xl font-bold mb-5 tracking-wide text-white">DigiPlan Indonesia</h1>

  <!-- NAVIGATION -->
  <nav>
    <ul class="space-y-3">

      <!-- Dashboard -->
      <li>
        <a href="<?= $base_url ?>customer/dashboard.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
          </svg>
          Dashboard
        </a>
      </li>

      <!-- Permintaan Barang -->
      <li>
        <a href="<?= $base_url ?>customer/form-item-request.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition hover:bg-white/10 hover:backdrop-blur-md">
          <!-- Cart / Request Icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 3h11.5M7 13l1.5 3M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2" />
          </svg>
          Permintaan Barang
        </a>
      </li>

      <!-- Riwayat Permintaan -->
      <li>
        <a href="<?= $base_url ?>customer/history-item-request.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition hover:bg-white/10 hover:backdrop-blur-md">
          <!-- History / Clock Icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              d="M12 8v4l3 3M3 12a9 9 0 1018 0 9 9 0 00-18 0z" />
          </svg>
          Riwayat Permintaan
        </a>
      </li>

      <!-- Invoice -->
      <li>
        <a href="<?= $base_url ?>customer/invoice.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              d="M9 7h6M9 11h6M9 15h4M6 3h12a1 1 0 011 1v17l-3-2-3 2-3-2-3 2V4a1 1 0 01-1-1z" />
          </svg>
          Invoice
        </a>
      </li>

      <!-- Chat -->
      <li>
        <a href="<?= $base_url ?>admin/chat.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M8 10h8M8 14h5m-5 6l-4-4V6a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H8z" />
          </svg>
          Chat
        </a>
      </li>

      <!-- Logout -->
      <li>
        <a href="<?= $base_url ?>auth/logout.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1" />
          </svg>
          Logout
        </a>
      </li>

    </ul>
  </nav>

  <!-- USER -->
  <div class="absolute bottom-5 left-6 text-sm opacity-90 text-gray-300">
    Logged in as <br>
    <span class="font-semibold text-white"><?= htmlspecialchars($_SESSION['name']); ?></span>
  </div>

</aside>