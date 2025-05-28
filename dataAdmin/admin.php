<?php
session_start();

// Cek apakah sudah login dan role adalah 'owner'
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Hanya salah satu versi W3CSS yang perlu digunakan -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Admin</title>
    <style>
        <?php include('../layout/style.css'); ?>
    </style>
</head>
<body>

<?php
$message = '';
if (isset($_GET['pesan'])) {
    $pesan = $_GET['pesan'];
    if ($pesan == "input") {
        $message = "✅ Data berhasil ditambahkan.";
    } elseif ($pesan == "hapus") {
        $message = "✅ Data berhasil dihapus.";
    } elseif ($pesan == "update") {
        $message = "✅ Data berhasil diupdate.";
    }
}
?>

<?php
include '../route/koneksi.php';
$query = "SELECT * FROM admin";
$result = mysqli_query($koneksi, $query);
?>

<!-- sidebar -->
<?php include('../layout/sidebar.php'); ?>
<!-- end sidebar -->

<div style="margin-left:25%">
     <?php include('../layout/navbar.php'); ?>
     <?php if (!empty($message)) : ?>
    <?php
    $color = 'w3-green';
    $icon = '<i class="fas fa-check-circle"></i>';

    if ($pesan == "hapus") {
        $color = 'w3-red';
        $icon = '<i class="fas fa-trash-alt"></i>';
    } elseif ($pesan == "update") {
        $color = 'w3-blue';
        $icon = '<i class="fas fa-pen"></i>';
    }
    ?>
    <div class="w3-panel <?= $color ?> w3-round w3-display-container w3-animate-opacity" style="margin: 16px;">
        <span onclick="this.parentElement.style.display='none'" class="w3-button w3-large w3-display-topright">&times;</span>
        <p><?= $icon ?> <?= $message ?></p>
    </div>
<?php endif; ?>

    <a href="tambah_admin.php" class="w3-button w3-blue w3-margin">Tambah Admin</a>

    <div class="card-body">
        <table class="w3-table-all w3-hoverable w3-small">
            <thead>
                <tr class="w3-light-grey">
                    <th>ID admin</th>
                    <th>Username</th>
                    <th>Nama Admin</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['id_admin']; ?></td>
                    <td><?= $row['username']; ?></td>
                    <td><?= $row['nama_admin']; ?></td>
                    <td><?= $row['alamat']; ?></td>
                    <td><?= $row['no_hp']; ?></td>
                    <td><?= $row['email']; ?></td>
                    <td><?= $row['password']; ?></td>
                    <td>
                        <a href="hapus.php?id_admin=<?= $row['id_admin']; ?>" 
                           class="fas fa-trash" style="color:red; font-size:20px;" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');"></a>
                        <a href="editAdmin.php?id_admin=<?= $row['id_admin']; ?>" 
                           class="fas fa-edit" style="color:blue; font-size:20px; margin-left:10px;"></a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
