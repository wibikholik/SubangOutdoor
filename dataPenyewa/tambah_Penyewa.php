<?php
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['owner', 'admin'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css" />
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <title>Input Data Barang</title>
</head>
<body>
    <!-- sidebar -->
    <?php include('../layout/sidebar.php'); ?>
    <!-- sidebar -->

    <div style="margin-left:25%; padding:20px;">
         <?php include('../layout/navbar.php'); ?>
        <h3>Input Data User</h3>
        
        <form action="tambah_aksi.php" method="post" class="w3-container w3-card-4 w3-light-grey" enctype="multipart/form-data" autocomplete="off">
            <table>
                <tr>
                    <td>Nama Penyewa</td>
                    <td><input type="text" name="namapenyewa" required class="w3-input w3-border" /></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td><input type="text" name="alamat" required class="w3-input w3-border" /></td>
                </tr>
                <tr>
                    <td>No Hp</td>
                    <td><input type="text" name="no_hp" required class="w3-input w3-border" /></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><input type="email" name="email" required class="w3-input w3-border" /></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td><input type="password" name="password" required class="w3-input w3-border" autocomplete="new-password" /></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" class="w3-button w3-blue" value="Tambah" /></td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>
