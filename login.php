<?php
session_start();

// Tampilkan pesan logout atau error jika ada
$message = '';
if (isset($_GET['message'])) {
    if ($_GET['message'] === 'logout') {
        $message = "Anda berhasil logout.";
    } elseif ($_GET['message'] === 'error') {
        $message = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: #fff;
            padding: 30px 35px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 360px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 25px;
        }

        label {
            display: block;
            text-align: left;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            margin-bottom: 20px;
            border: 1.5px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }

        button {
            background-color: #007BFF;
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
            background-color: #0056b3;
        }

        .message {
            margin-bottom: 15px;
            font-weight: 600;
        }

        .register-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .register-link a {
            color: #007BFF;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Subang Outdoor</h2>
        <?php if ($message): ?>
            <div class="message" style="color: <?= $_GET['message'] === 'error' ? 'red' : 'green' ?>;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="prosesLogin.php">
            <label>Username</label>
            <input type="text" name="username" required>
            
            <label>Password</label>
            <input type="password" name="password" required>
            
            <button type="submit" name="login">Login</button>
        </form>
        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
</body>
</html>
