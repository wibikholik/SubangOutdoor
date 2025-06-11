<?php
session_start();
include 'route/koneksi.php';

$message = '';
$message_type = ''; // Untuk membedakan pesan error (merah) atau sukses (hijau)

// Jika pengguna belum verifikasi OTP, jangan izinkan masuk ke halaman ini.
if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    header("Location: verifikasi_otp.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validasi 1: Password harus cocok
    if ($password !== $confirm) {
        $message = "Konfirmasi password tidak cocok. Silakan coba lagi.";
        $message_type = 'error';
    // Validasi 2: Password minimal 8 karakter
    } elseif (strlen($password) < 8) {
        $message = "Password baru harus terdiri dari minimal 8 karakter.";
        $message_type = 'error';
    } else {
        $email = $_SESSION['reset_email'];

        // --- PENINGKATAN KEAMANAN 1: HASHING PASSWORD (SANGAT PENTING!) ---
        // Jangan pernah menyimpan password sebagai teks biasa. Gunakan password_hash().
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Cari tahu pengguna ada di tabel mana (admin, owner, atau penyewa)
        $user_data = null;
        $tables = ['admin', 'owner', 'penyewa'];
        foreach ($tables as $table) {
            // --- PENINGKATAN KEAMANAN 2: MENCEGAH SQL INJECTION ---
            $stmt_check = $koneksi->prepare("SELECT email FROM $table WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $result = $stmt_check->get_result();

            if ($result->num_rows > 0) {
                $user_data = ['table' => $table];
                break;
            }
        }

        if ($user_data) {
            // Jika pengguna ditemukan, update passwordnya
            $target_table = $user_data['table'];
            $stmt_update = $koneksi->prepare("UPDATE $target_table SET password = ? WHERE email = ?");
            $stmt_update->bind_param("ss", $hashedPassword, $email);

            if ($stmt_update->execute()) {
                // Jika berhasil, hancurkan session dan arahkan ke login dengan pesan sukses
                session_unset();
                session_destroy();

                session_start(); // Mulai session baru hanya untuk pesan sukses
                $_SESSION['success_message'] = "Password berhasil direset. Silakan login dengan password baru Anda.";
                header("Location: login.php");
                exit;
            } else {
                $message = "Gagal memperbarui password di database.";
                $message_type = 'error';
            }
        } else {
            $message = "Email tidak ditemukan di sistem kami.";
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Subang Outdoor</title>
    <style>
        /* CSS yang sama dari halaman sebelumnya untuk tampilan yang konsisten */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('assets/img/bekgrun.jpg'); /* Pastikan path ini benar */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px 35px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            width: 400px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 25px;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid transparent;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        label {
            display: block;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        input[type="password"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        button {
            background-color: #28a745; /* Warna Hijau untuk Aksi Positif */
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Atur Password Baru</h2>
        
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="reset_password.php" novalidate>
            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" name="password" id="password" placeholder="Minimal 8 karakter" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Ketik ulang password baru" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>