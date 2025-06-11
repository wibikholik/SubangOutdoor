<?php
session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = $_POST['otp'];

    if (
        isset($_SESSION['reset_otp']) &&
        $_SESSION['reset_otp'] == $input_otp &&
        time() - $_SESSION['reset_time'] < 300
    ) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset_password.php");
        exit;
    } else {
        $message = "OTP salah atau sudah kedaluwarsa.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi OTP - Subang Outdoor</title>
    <link rel="stylesheet" href="../assets/css/style-login.css"> <!-- Samakan dengan login -->
</head>
<body>
    <div class="login-container">
        <h2>Verifikasi OTP</h2>
        <?php if ($message): ?>
            <div class="error-message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="otp">Masukkan Kode OTP</label>
                <input type="text" name="otp" id="otp" maxlength="6" required placeholder="6 digit OTP" autofocus>
            </div>
            <button type="submit">Verifikasi</button>
        </form>
        <p class="back-link"><a href="lupa_password.php">Kembali ke Lupa Password</a></p>
    </div>
</body>
</html>
