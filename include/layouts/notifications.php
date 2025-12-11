<?php
// Ambil notifikasi dari URL
$success = isset($_GET['success']) ? $_GET['success'] : null;
$error   = isset($_GET['error']) ? $_GET['error'] : null;

// Fungsi ubah kode menjadi pesan
function getMessage($key)
{
  $messages = [
    'updated'   => 'Data berhasil diperbarui!',
    'added'     => 'Data berhasil ditambahkan!',
    'stock_updated'  => 'Stok berhasil diperbarui!',
    'active'  => 'Produk berhasil diaktifkan!',
    'nonactive'  => 'Produk berhasil dinonaktifkan!',
    'deleted'   => 'Data berhasil dihapus!',
    'failed'    => 'Terjadi kesalahan, silakan coba lagi!',
    'notfound'  => 'Data tidak ditemukan!',
  ];

  return isset($messages[$key]) ? $messages[$key] : $key;
}
?>

<div id="notif-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

<script>
  function showNotification(message, type = 'success', duration = 3000) {
    const container = document.getElementById('notif-container');
    const notif = document.createElement('div');

    notif.className = `notif ${type === 'success' ? 'notif-success' : 'notif-error'}`;
    notif.textContent = message;

    // Style
    notif.style.cssText = `
        padding: 15px 22px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 500;
        color: #fff;
        margin-top: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        opacity: 0;
        transform: translateX(50px);
        transition: all 0.4s ease;
    `;

    container.appendChild(notif);

    // Slide in
    setTimeout(() => {
      notif.style.opacity = '1';
      notif.style.transform = 'translateX(0)';
    }, 10);

    // Slide out after duration
    setTimeout(() => {
      notif.style.opacity = '0';
      notif.style.transform = 'translateX(50px)';
      setTimeout(() => container.removeChild(notif), 400);
    }, duration);
  }

  // Trigger otomatis jika ada success/error
  <?php if ($success): ?>
    showNotification("<?= getMessage($success); ?>", 'success');
  <?php elseif ($error): ?>
    showNotification("<?= getMessage($error); ?>", 'error');
  <?php endif; ?>
</script>

<style>
  .notif-success {
    background: linear-gradient(135deg, #28c76f, #20a55f);
  }

  .notif-error {
    background: linear-gradient(135deg, #ff4e4e, #d93636);
  }
</style>