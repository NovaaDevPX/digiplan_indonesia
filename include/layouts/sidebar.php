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
        <a href="<?= $base_url ?>admin/dashboard.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition 
          hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
          </svg>
          Dashboard
        </a>
      </li>

      <!-- Kelola Produk -->
      <li>
        <a href="<?= $base_url ?>admin/products.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition 
          hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              d="M20 13V7a2 2 0 00-2-2h-3m5 8v6a2 2 0 01-2 2h-3m5-8h-5m-6 8H6a2 2 0 01-2-2v-6m8 8h-4m4-16H6a2 2 0 00-2 2v6m8-8v8m0 0h4" />
          </svg>
          Kelola Produk
        </a>
      </li>

      <!-- Permintaan Barang -->
      <li>
        <a href="<?= $base_url ?>admin/item-request.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition 
          hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
          </svg>
          Permintaan Barang
        </a>
      </li>

      <!-- Pengadaan Barang -->
      <li>
        <a href="<?= $base_url ?>admin/pengadaan_barang_admin.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition
          hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              d="M3 3h18M9 3v18m6-18v18" />
          </svg>
          Pengadaan Barang
        </a>
      </li>

      <!-- Distribusi Barang -->
      <li>
        <a href="<?= $base_url ?>admin/distribusi_barang.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition
          hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              d="M3 7h18M3 12h18M3 17h18" />
          </svg>
          Distribusi Barang
        </a>
      </li>

      <!-- Stok Barang -->
      <li>
        <a href="<?= $base_url ?>admin/stok_barang.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition
          hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              d="M4 6h16M4 10h16M4 14h16M4 18h7" />
          </svg>
          Barang
        </a>
      </li>

      <!-- Laporan -->
      <li>
        <a href="<?= $base_url ?>admin/laporan.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition
          hover:bg-white/10 hover:backdrop-blur-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              d="M9 17v-6h6v6m-8 4h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          Laporan
        </a>
      </li>

      <!-- Chat -->
      <li>
        <a href="<?= $base_url ?>admin/chat.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition
          hover:bg-white/10 hover:backdrop-blur-md">
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
        <a href="<?= $base_url ?>logout.php"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition
          hover:bg-white/10 hover:backdrop-blur-md">
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