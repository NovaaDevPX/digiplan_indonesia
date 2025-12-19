<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

include '../include/base-url.php';

/* ===============================
   PROSES TAMBAH USER
================================ */
if (isset($_POST['tambah_user'])) {
  $name     = mysqli_real_escape_string($conn, $_POST['name']);
  $email    = mysqli_real_escape_string($conn, $_POST['email']);
  $password = md5($_POST['password']);
  $role_id  = (int) $_POST['role_id'];

  mysqli_query($conn, "
    INSERT INTO users (name,email,password,role_id)
    VALUES ('$name','$email','$password','$role_id')
  ");

  header("Location: user-management.php?success=user_added");
  exit;
}

/* ===============================
   PROSES UPDATE USER
================================ */
if (isset($_POST['update_user'])) {
  $id      = (int) $_POST['id'];
  $name    = mysqli_real_escape_string($conn, $_POST['name']);
  $email   = mysqli_real_escape_string($conn, $_POST['email']);
  $role_id = (int) $_POST['role_id'];

  if (!empty($_POST['password'])) {
    $password = md5($_POST['password']);
    mysqli_query($conn, "
      UPDATE users SET 
        name='$name',
        email='$email',
        password='$password',
        role_id='$role_id'
      WHERE id='$id'
    ");
  } else {
    mysqli_query($conn, "
      UPDATE users SET 
        name='$name',
        email='$email',
        role_id='$role_id'
      WHERE id='$id'
    ");
  }

  header("Location: user-management.php?success=user_updated");
  exit;
}

/* ===============================
   SOFT DELETE USER
================================ */
if (isset($_GET['hapus'])) {
  $id = (int) $_GET['hapus'];
  mysqli_query($conn, "
    UPDATE users SET deleted_at = NOW()
    WHERE id='$id'
  ");
  header("Location: user-management.php?success=user_deleted");
  exit;
}

/* ===============================
   DATA
================================ */
$users = mysqli_query($conn, "
  SELECT u.*, r.name AS role_name
  FROM users u
  JOIN roles r ON u.role_id = r.id
  WHERE u.deleted_at IS NULL
  ORDER BY u.created_at DESC
");

$roles = mysqli_query($conn, "SELECT * FROM roles");
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>User Management | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">

  <?php include '../include/layouts/sidebar-superadmin.php'; ?>
  <?php include '../include/layouts/notifications.php'; ?>

  <main class="ml-64 p-10">

    <div class="max-w-7xl mx-auto">

      <!-- HEADER -->
      <div class="bg-white/10 border border-white/20 p-6 rounded-2xl mb-8">
        <h1 class="text-4xl font-bold mb-2">User Management</h1>
        <p class="text-white/70">Kelola akun pengguna</p>
      </div>

      <!-- TOMBOL TAMBAH -->
      <?php if (!isset($_GET['edit']) && !isset($_GET['tambah'])): ?>
        <a href="?tambah=1"
          class="inline-flex mb-6 px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg hover:scale-105 transition">
          + Tambah User
        </a>
      <?php endif; ?>

      <!-- FORM TAMBAH -->
      <?php if (isset($_GET['tambah'])): ?>
        <div class="bg-white/10 p-8 rounded-2xl mb-8">
          <h2 class="text-2xl font-semibold mb-6">Tambah User</h2>

          <form method="post" class="space-y-6">
            <input type="text" name="name" placeholder="Nama"
              class="w-full p-3 bg-white/20 rounded-xl" required>

            <input type="email" name="email" placeholder="Email"
              class="w-full p-3 bg-white/20 rounded-xl" required>

            <input type="password" name="password" placeholder="Password"
              class="w-full p-3 bg-white/20 rounded-xl" required>

            <select name="role_id" class="w-full p-3 bg-white/20 rounded-xl">
              <?php while ($r = mysqli_fetch_assoc($roles)): ?>
                <option class="text-black" value="<?= $r['id']; ?>"><?= ucfirst($r['name']); ?></option>
              <?php endwhile; ?>
            </select>

            <div class="flex gap-4">
              <button name="tambah_user"
                class="px-6 py-3 bg-green-500 rounded-xl">Simpan</button>
              <a href="user-management.php"
                class="px-6 py-3 bg-white/20 rounded-xl">Batal</a>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <!-- FORM EDIT -->
      <?php if (isset($_GET['edit'])):
        $id = (int) $_GET['edit'];
        $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$id'"));
        $roles = mysqli_query($conn, "SELECT * FROM roles");
      ?>
        <div class="bg-white/10 p-8 rounded-2xl mb-8">
          <h2 class="text-2xl font-semibold mb-6">Edit User</h2>

          <form method="post" class="space-y-6">
            <input type="hidden" name="id" value="<?= $u['id']; ?>">

            <input type="text" name="name" value="<?= $u['name']; ?>"
              class="w-full p-3 bg-white/20 rounded-xl" required>

            <input type="email" name="email" value="<?= $u['email']; ?>"
              class="w-full p-3 bg-white/20 rounded-xl" required>

            <input type="password" name="password"
              placeholder="Password baru (opsional)"
              class="w-full p-3 bg-white/20 rounded-xl">

            <select name="role_id" class="w-full p-3 bg-white/20 rounded-xl">
              <?php while ($r = mysqli_fetch_assoc($roles)): ?>
                <option class="text-black" value="<?= $r['id']; ?>" <?= $r['id'] == $u['role_id'] ? 'selected' : '' ?>>
                  <?= ucfirst($r['name']); ?>
                </option>
              <?php endwhile; ?>
            </select>

            <div class="flex gap-4">
              <button name="update_user"
                class="px-6 py-3 bg-yellow-500 rounded-xl">Update</button>
              <a href="user-management.php"
                class="px-6 py-3 bg-white/20 rounded-xl">Batal</a>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <!-- TABLE -->
      <div class="bg-white/10 p-8 rounded-2xl">
        <h2 class="text-2xl font-semibold mb-6">Daftar User</h2>

        <div class="rounded-xl overflow-hidden">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-white/20">
                <th class="p-4 text-left">Nama</th>
                <th class="p-4 text-left">Email</th>
                <th class="p-4 text-left">Role</th>
                <th class="p-4 text-left">Aksi</th>
              </tr>
            </thead>

            <tbody>
              <?php while ($u = mysqli_fetch_assoc($users)): ?>
                <tr class="border-b border-white/10 hover:bg-white/5">
                  <td class="p-4 font-medium"><?= $u['name']; ?></td>
                  <td class="p-4 text-white/70"><?= $u['email']; ?></td>
                  <td class="p-4">
                    <span class="px-3 py-1 text-xs rounded-full
                    <?= $u['role_name'] === 'super_admin' ? 'bg-purple-500/20 text-purple-300' : '' ?>
                    <?= $u['role_name'] === 'admin' ? 'bg-indigo-500/20 text-indigo-300' : '' ?>
                    <?= $u['role_name'] === 'customer' ? 'bg-emerald-500/20 text-emerald-300' : '' ?>">
                      <?= ucfirst($u['role_name']); ?>
                    </span>
                  </td>

                  <!-- BUTTON AKSI -->
                  <td class="p-4">
                    <button
                      onclick="openDropdown(event, <?= $u['id']; ?>)"
                      class="p-2 rounded-lg hover:bg-white/10 text-white/70 hover:text-white transition">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                      </svg>
                    </button>
                  </td>

                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  <!-- ===============================
     GLOBAL DROPDOWN (PORTAL)
================================ -->
  <div id="globalDropdown"
    class="hidden fixed z-[999999]
         w-48 bg-slate-900/90 backdrop-blur-xl
         border border-white/30 rounded-xl shadow-2xl">

    <a id="dropdownEdit"
      class="block px-4 py-3 text-white hover:bg-white/10 rounded-t-xl transition">
      Edit
    </a>

    <a id="dropdownDelete"
      onclick="return confirm('Hapus user?')"
      class="block px-4 py-3 text-red-400 hover:bg-white/10 rounded-b-xl transition">
      Hapus
    </a>
  </div>

  <script>
    const dropdown = document.getElementById("globalDropdown");
    const editLink = document.getElementById("dropdownEdit");
    const deleteLink = document.getElementById("dropdownDelete");

    function openDropdown(event, userId) {
      event.stopPropagation();

      const button = event.currentTarget;
      const rect = button.getBoundingClientRect();

      // tampilkan dulu supaya width kebaca
      dropdown.classList.remove("hidden");

      const dropdownWidth = dropdown.offsetWidth;

      // ✅ POSISI FIX & AKURAT
      dropdown.style.top = rect.top + "px";
      dropdown.style.left = rect.left - dropdownWidth - 8 + "px";

      editLink.href = "?edit=" + userId;
      deleteLink.href = "?hapus=" + userId;
    }

    document.addEventListener("click", () => {
      dropdown.classList.add("hidden");
    });
  </script>



</body>

</html>