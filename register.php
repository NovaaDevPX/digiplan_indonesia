<?php
session_start();
require 'function.php'; // koneksi database ($conn)

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['log']) && $_SESSION['log'] === true) {
    switch ($_SESSION['role']) {
        case 'super_admin':
            header('Location: superadmin_dashboard.php');
            break;
        case 'admin':
            header('Location: admin_dashboard.php');
            break;
        default:
            header('Location: customer_dashboard.php');
            break;
    }
    exit;
}

// Jika tombol register ditekan
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validasi form
    if (empty($name) || empty($email) || empty($password)) {
        echo "<script>alert('Semua kolom wajib diisi!');</script>";
    } elseif ($password !== $confirm) {
        echo "<script>alert('Konfirmasi password tidak cocok!');</script>";
    } else {
        // Cek apakah email sudah digunakan
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('Email sudah terdaftar!');</script>";
        } else {
            // Ambil role_id untuk customer
           $role_stmt = $conn->prepare("SELECT id FROM roles WHERE name = 'customer' LIMIT 1");
            $role_stmt->execute();
            $role_result = $role_stmt->get_result();
            $role = $role_result->fetch_assoc();

            if (!$role) {
                die("Role 'customer' tidak ditemukan di tabel roles!");
            }

            $role_id = $role['id'];

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Simpan user baru
            $insert = $conn->prepare("INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)");
            $insert->bind_param("sssi", $name, $email, $hashedPassword, $role_id);

            if ($insert->execute()) {
                echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
                exit;
            } else {
                echo "<script>alert('Terjadi kesalahan, coba lagi.');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Register</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body style="background-color: #2c3e50 !important;">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-6">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header"><h3 class="text-center font-weight-light my-4">Register</h3></div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="name" type="text" placeholder="Nama Lengkap" required />
                                            <label>Nama Lengkap</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="email" type="email" placeholder="Email" required />
                                            <label>Email</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="password" type="password" placeholder="Password" required />
                                            <label>Password</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="confirm_password" type="password" placeholder="Konfirmasi Password" required />
                                            <label>Konfirmasi Password</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <button class="btn btn-primary" name="register">Daftar</button>
                                            <a class="small" href="login.php">Sudah punya akun? Login</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
