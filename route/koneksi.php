<?php
$host = "localhost";
$username = "root";
$password = "";
$databases = "subangoutdoor1";


$koneksi = mysqli_connect($host, $username, $password, $databases);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
