<?php
session_start();
include 'route/koneksi.php';

$message = '';

if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    header("Location: verifikasi_otp.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "Konfirmasi password tidak cocok.";
    } else {
        $email = $_SESSION['reset_email'];
        // Jika kamu pakai enkripsi lama (misal md5), ganti di sini.
        $plainPassword = $password; // Tanpa enkripsi

        $tables = ['admin', 'owner', 'penyewa'];
        foreach ($tables as $table) {
            $query = "SELECT * FROM $table WHERE email = '$email'";
            $result = mysqli_query($koneksi, $query);
            if (mysqli_num_rows($result) > 0) {
                $update = mysqli_query($koneksi, "UPDATE $table SET password = '$plainPassword' WHERE email = '$email'");
                if ($update) {
                    $message = "Password berhasil direset. Silakan login kembali.";
                    session_unset();
                    session_destroy();
                    header("Location: login.php");
                    exit;
                } else {
                    $message = "Gagal mengupdate password.";
                }
                break;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Subang Outdoor</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Reset Password</h2>
        <?php if ($message) echo "<p style='color:red;'>$message</p>"; ?>
        <form method="post">
            <input type="password" name="password" placeholder="Password Baru" required><br>
            <input type="password" name="confirm" placeholder="Konfirmasi Password" required><br>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>