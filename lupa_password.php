<?php
// Koneksi ke database
include 'route/koneksi.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "Password tidak cocok.";
    } else {
        // Mapping nama tabel dan field username-nya
        $user_tables = [
            'admin'  => 'nama_admin',
            'owner'  => 'nama_owner',
            'penyewa'=> 'nama_penyewa'
        ];

        $found = false;

        foreach ($user_tables as $table => $field) {
            $cek = mysqli_query($koneksi, "SELECT * FROM $table WHERE $field = '$username'");
            if (mysqli_num_rows($cek) > 0) {
                $update = mysqli_query($koneksi, "UPDATE $table SET password = '$password' WHERE $field = '$username'");
                if ($update) {
                    echo "<script>
                        alert('Password berhasil diubah. Anda akan diarahkan ke halaman login.');
                        setTimeout(function() {
                            window.location.href = 'login.php?message=reset_success';
                        }, 1000);
                    </script>";
                    exit;
                } else {
                    $message = "Gagal mengubah password.";
                }
                $found = true;
                break;
            }
        }

        if (!$found) {
            $message = "Username tidak ditemukan di tabel manapun.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #eef2f3;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 10px 40px 10px 14px; /* ruang kanan untuk ikon */
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
        }
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #888;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            color: red;
        }
        .back-link {
            text-align: center;
            margin-top: 10px;
        }
        .back-link a {
            text-decoration: none;
            color: #007bff;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Masukkan Nama Admin / Owner / Penyewa" required>

            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Password Baru" required>
                <span class="toggle-password" toggle="#password">👁️</span>
            </div>

            <div class="password-container">
                <input type="password" name="confirm" id="confirm" placeholder="Konfirmasi Password" required>
                <span class="toggle-password" toggle="#confirm">👁️</span>
            </div>

            <button type="submit">Ubah Password</button>
        </form>
        <div class="back-link">
            <a href="login.php">&larr; Kembali ke Login</a>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', function () {
                const targetInput = document.querySelector(this.getAttribute('toggle'));
                const type = targetInput.getAttribute('type') === 'password' ? 'text' : 'password';
                targetInput.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🙈';
            });
        });
    </script>
</body>
</html>