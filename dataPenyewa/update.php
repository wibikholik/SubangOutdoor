<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

include '../route/koneksi.php';

$id_penyewa   = $_POST['id_penyewa'];
$nama_penyewa = $_POST['namapenyewa'];
$alamat       = $_POST['alamat'];
$no_hp        = $_POST['no_hp'];
$email        = $_POST['email'];
$password     = $_POST['password'];

$query = "UPDATE penyewa 
          SET nama_penyewa = ?, 
              alamat = ?, 
              no_hp = ?, 
              email = ?, 
              password = ? 
          WHERE id_penyewa = ?";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "sssssi", $nama_penyewa, $alamat, $no_hp, $email, $password, $id_penyewa);
$execute = mysqli_stmt_execute($stmt);

if ($execute) {
    header("Location: penyewa.php?pesan=update");
} else {
    echo "Gagal update data: " . mysqli_error($koneksi);
}

mysqli_stmt_close($stmt);
mysqli_close($koneksi);
?>
