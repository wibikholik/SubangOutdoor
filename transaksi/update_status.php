<?php
session_start();

// Cek apakah user sudah login dan role-nya admin atau owner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'owner')) {
    header('Location: ../login.php');
    exit;
}

include '../route/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form dengan filter dan sanitasi sederhana
    $id_transaksi = isset($_POST['id_transaksi']) ? (int) $_POST['id_transaksi'] : null;
    $status_baru = isset($_POST['status_baru']) ? trim($_POST['status_baru']) : null;

    // Daftar status yang valid
    $status_valid = [
        'menunggu konfirmasi',
        'dikonfirmasi',
        'disewa',
        'di ambil barang',
        'terlambat dikembalikan',
        'batal'
    ];

    // Validasi input
    if (!$id_transaksi || !in_array($status_baru, $status_valid, true)) {
        // Redirect kembali dengan parameter error jika input tidak valid
        header('Location: transaksi.php?error=invalid_input');
        exit;
    }

    // Prepare statement untuk update status transaksi
    $stmt = $koneksi->prepare("UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
    if (!$stmt) {
        die("Prepare statement gagal: " . $koneksi->error);
    }

    $stmt->bind_param("si", $status_baru, $id_transaksi);

    if (!$stmt->execute()) {
        die("Update status gagal: " . $stmt->error);
    }

    $stmt->close();

    // Redirect ke halaman daftar transaksi dengan pesan sukses
    header('Location: transaksi.php?success=updated');
    exit;
} else {
    // Jika bukan POST request, langsung redirect ke daftar transaksi
    header('Location: transaksi.php');
    exit;
}
?>
