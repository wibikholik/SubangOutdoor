<?php
session_start();

// Cek apakah sudah login dan role adalah admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (isset($_SESSION['success_message'])) {
    echo "<script>alert('" . addslashes($_SESSION['success_message']) . "');</script>";
    unset($_SESSION['success_message']);
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Admin</title>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- W3.CSS -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css" />
    <style>
         body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f8;
            margin: 0; 
            padding: 0;
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar width */
        .sidebar {
            width: 25%;
        }
        /* Main content */
        main {
            flex-grow: 1;
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin: 20px;
        }
        header {
            margin-bottom: 20px;
        }
        header h1 {
            margin: 0;
            color: #007BFF;
        }
        .welcome {
            font-size: 18px;
            margin-bottom: 20px;
        }
        a.logout-btn {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        a.logout-btn:hover {
            background-color: #b02a37;
        }
    </style>
</head>
<body>

<?php include '../layout/sidebar.php'; ?>

<main style="margin-left:25%">
    <?php include '../layout/navbar.php'; ?>
    <header>
        <h1>Dashboard Admin</h1>
    </header>
    <div class="welcome">
        Selamat datang, <strong><?= htmlspecialchars($username) ?></strong>!
    </div>
    <a href="../prosesLogin.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
</main>

</body>
</html>
