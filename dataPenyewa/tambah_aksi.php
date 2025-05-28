<?php
session_start();
include '../route/koneksi.php';

// Cek session
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$nama_penyewa = $_POST['namapenyewa'];
$alamat      = $_POST['alamat'];
$no_hp       = $_POST['no_hp'];
$email       = $_POST['email'];
$password    = $_POST['password'];

// Query insert tanpa hash, langsung simpan password apa adanya
$query = "INSERT INTO penyewa (nama_penyewa, alamat, no_hp, email, password) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "sssss", $nama_penyewa, $alamat, $no_hp, $email, $password);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    header("Location: penyewa.php?pesan=input");
} else {
    echo "Gagal memasukkan data.";
}

mysqli_stmt_close($stmt);
mysqli_close($koneksi);
?>
    