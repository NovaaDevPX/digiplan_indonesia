<?php
require 'function.php'; // pastikan file ini berisi koneksi ke database ($conn)

echo "<h2>🔧 Setup Roles & Users</h2>";

// 1️⃣ Pastikan tabel roles ada
$createRoles = "
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
)";
mysqli_query($conn, $createRoles);

// 2️⃣ Pastikan tabel users ada
$createUsers = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
)";
mysqli_query($conn, $createUsers);

// 3️⃣ Tambah role jika belum ada
$roles = ['super_admin', 'admin', 'customer'];
foreach ($roles as $role) {
    $check = mysqli_query($conn, "SELECT * FROM roles WHERE name='$role'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO roles (name) VALUES ('$role')");
        echo "✅ Role '$role' berhasil ditambahkan.<br>";
    } else {
        echo "ℹ️ Role '$role' sudah ada.<br>";
    }
}

// Ambil role_id masing-masing
$get_super = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM roles WHERE name='super_admin'"))['id'];
$get_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM roles WHERE name='admin'"))['id'];
$get_customer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM roles WHERE name='customer'"))['id'];

// 4️⃣ Tambah akun default jika belum ada
$accounts = [
    ['Super Admin', 'superadmin@gmail.com', 'super123', $get_super],
    ['Admin', 'admin@gmail.com', 'admin123', $get_admin],
    ['Customer', 'customer@gmail.com', 'customer123', $get_customer],
];

foreach ($accounts as $acc) {
    [$name, $email, $password, $role_id] = $acc;
    $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($checkUser) == 0) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (name, email, password, role_id) VALUES ('$name', '$email', '$hashed', $role_id)");
        echo "✅ Akun '$name' ($email) berhasil dibuat.<br>";
    } else {
        echo "ℹ️ Akun '$email' sudah ada.<br>";
    }
}

echo "<hr><strong>🎉 Setup selesai!</strong><br>";
echo "Kamu bisa login pakai salah satu akun berikut:<br><br>";

echo "<table border='1' cellpadding='5' cellspacing='0'>
<tr><th>Role</th><th>Email</th><th>Password</th></tr>
<tr><td>Super Admin</td><td>superadmin@gmail.com</td><td>super123</td></tr>
<tr><td>Admin</td><td>admin@gmail.com</td><td>admin123</td></tr>
<tr><td>Customer</td><td>customer@gmail.com</td><td>customer123</td></tr>
</table>";

echo "<br><a href='login.php'>👉 Kembali ke Halaman Login</a>";
?>
