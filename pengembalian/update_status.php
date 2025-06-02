<?php
session_start();
include '../route/koneksi.php';

// Validasi akses (optional)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'owner')) {
    header('Location: ../login.php');
    exit;
}

// Validasi request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pengembalian.php');
    exit;
}

// Validasi CSRF token
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token tidak valid.');
}

// Ambil input
$id_pengembalian = intval($_POST['id_pengembalian']);
$id_transaksi = intval($_POST['id_transaksi']);
$status_baru = $_POST['status_baru'] ?? '';

// Validasi status baru
$allowed_status = [
    'Menunggu Konfirmasi Pengembalian',
    'Selesai Dikembalikan',
    'Ditolak'
];

if (!in_array($status_baru, $allowed_status)) {
    header('Location: pengembalian.php?error=invalid_status');
    exit;
}

// Update status_pengembalian
$status_baru_esc = mysqli_real_escape_string($koneksi, $status_baru);
$update_pengembalian = "UPDATE pengembalian SET status_pengembalian = '$status_baru_esc' WHERE id_pengembalian = $id_pengembalian";

if (!mysqli_query($koneksi, $update_pengembalian)) {
    header('Location: pengembalian.php?error=update_failed');
    exit;
}

// Mapping status_pengembalian ke status_transaksi
$status_transaksi_map = [
    'Menunggu Konfirmasi Pengembalian' => 'Menunggu Konfirmasi Pengembalian',
    'Selesai Dikembalikan' => 'Selesai Dikembalikan',
    'Ditolak' => 'Ditolak Pengembalian',
];

$status_transaksi_baru = $status_transaksi_map[$status_baru];
$status_transaksi_baru_esc = mysqli_real_escape_string($koneksi, $status_transaksi_baru);

// Update status transaksi
$update_transaksi = "UPDATE transaksi SET status = '$status_transaksi_baru_esc' WHERE id_transaksi = $id_transaksi";
mysqli_query($koneksi, $update_transaksi);

header('Location: pengembalian.php?success=updated');
exit;
