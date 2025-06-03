<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'owner')) {
    header('Location: ../login.php');
    exit;
}

include '../route/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : null;
    $status_baru = isset($_POST['status_baru']) ? trim($_POST['status_baru']) : null;
    $target_table = isset($_POST['target_table']) ? trim($_POST['target_table']) : null;

    // Daftar status valid per tabel
    $valid_status = [
        'transaksi' => [
            'menunggu konfirmasi',
            'dikonfirmasi',
            'disewa',
            'di ambil barang',
            'terlambat dikembalikan',
            'batal',
            'Menunggu Konfirmasi Pengembalian',
            'Selesai Dikembalikan',
            'Ditolak Pengembalian'
        ],
        'pembayaran' => [
            'Menunggu Konfirmasi Pembayaran',
            'lunas',
            'Ditolak Pembayaran'
        ],
        'pengembalian' => [
            'Menunggu Konfirmasi Pengembalian',
            'Selesai Dikembalikan',
            'Ditolak Pengembalian'
        ],
    ];

    // Validasi input id dan tabel
    if (!$id || !array_key_exists($target_table, $valid_status)) {
        die('Input ID atau tabel tidak valid.');
    }

    // Validasi status baru sesuai tabel
    if (!in_array($status_baru, $valid_status[$target_table], true)) {
        die('Status baru tidak valid untuk tabel ' . htmlspecialchars($target_table));
    }

    // Tentukan nama kolom id sesuai tabel
    $id_column = '';
    switch ($target_table) {
        case 'transaksi':
            $id_column = 'id_transaksi';
            break;
        case 'pembayaran':
            $id_column = 'id_pembayaran';
            break;
        case 'pengembalian':
            $id_column = 'id_pengembalian';
            break;
    }

    // Siapkan query update dengan prepared statement
    $sql = "UPDATE $target_table SET status = ? WHERE $id_column = ?";
    $stmt = $koneksi->prepare($sql);

    if (!$stmt) {
        die("Gagal prepare statement: " . $koneksi->error);
    }

    $stmt->bind_param("si", $status_baru, $id);

    if (!$stmt->execute()) {
        die("Gagal update status: " . $stmt->error);
    }

    $stmt->close();

    // Redirect setelah sukses update
    header('Location: transaksi.php?success=updated');
    exit;
} else {
    header('Location: transaksi.php');
    exit;
}
?>
