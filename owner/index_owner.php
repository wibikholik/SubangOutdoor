<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login dan role owner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit;
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Owner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
</main>
    

</body>
</html>
