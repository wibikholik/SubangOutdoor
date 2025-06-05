<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'owner')) {
    header('Location: ../login.php');
    exit;
}

include '../route/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $status_baru = isset($_POST['status_baru']) ? trim($_POST['status_baru']) : '';
    $target_table = isset($_POST['target_table']) ? trim($_POST['target_table']) : '';

    // Daftar status valid per tabel
    $valid_status = [
        'transaksi' => [
            'menunggu konfirmasi pembayaran',
            'Dikonfirmasi Pembayaran Silahkan AmbilBarang',
             'Ditolak Pembayaran',
             'selesai Pembayaran',
            'disewa',
            'terlambat dikembalikan',
            'menunggu konfirmasi pengembalian',
            'ditolak pengembalian',
            'selesai dikembalikan',
            'batal'
        ],
        'pembayaran' => [
            'Menunggu Konfirmasi Pembayaran',
            'Dikonfirmasi Pembayaran Silahkan AmbilBarang',
            'Ditolak Pembayaran',
            'selesai Pembayaran'
        ],
        'pengembalian' => [
            'menunggu konfirmasi pengembalian',
            'Selesai Dikembalikan',
            'Ditolak Pengembalian'
        ],
    ];

    // Validasi id dan nama tabel
    if ($id <= 0 || !array_key_exists($target_table, $valid_status)) {
        die('Input ID atau tabel tidak valid.');
    }

    // Validasi status baru
    if (!in_array($status_baru, $valid_status[$target_table], true)) {
        die('Status baru tidak valid untuk tabel ' . htmlspecialchars($target_table));
    }

    // Tentukan kolom id sesuai tabel
    $id_column = match ($target_table) {
        'transaksi' => 'id_transaksi',
        'pembayaran' => 'id_pembayaran',
        'pengembalian' => 'id_pengembalian',
        default => ''
    };

    // Tentukan kolom status sesuai tabel
    $status_column = match ($target_table) {
        'pembayaran' => 'status_pembayaran',
        'pengembalian' => 'status_pengembalian',
        default => 'status'
    };

    // Update status
    $sql = "UPDATE $target_table SET $status_column = ? WHERE $id_column = ?";
    $stmt = $koneksi->prepare($sql);
    if (!$stmt) {
        die("Gagal prepare statement: " . $koneksi->error);
    }
    $stmt->bind_param("si", $status_baru, $id);
    if (!$stmt->execute()) {
        die("Gagal update status: " . $stmt->error);
    }
    $stmt->close();

    // Jika update transaksi, sinkronkan ke pembayaran dan pengembalian
    if ($target_table === 'transaksi') {
        // Update pembayaran
        $sql_pembayaran = "UPDATE pembayaran SET status_pembayaran = ? WHERE id_transaksi = ?";
        $stmt_pembayaran = $koneksi->prepare($sql_pembayaran);
        if ($stmt_pembayaran) {
            $stmt_pembayaran->bind_param("si", $status_baru, $id);
            $stmt_pembayaran->execute();
            $stmt_pembayaran->close();
        }

        // Update pengembalian
        $sql_pengembalian = "UPDATE pengembalian SET status_pengembalian = ? WHERE id_transaksi = ?";
        $stmt_pengembalian = $koneksi->prepare($sql_pengembalian);
        if ($stmt_pengembalian) {
            $stmt_pengembalian->bind_param("si", $status_baru, $id);
            $stmt_pengembalian->execute();
            $stmt_pengembalian->close();
        }
    }

    header('Location: transaksi.php?success=updated');
    exit;
} else {
    header('Location: transaksi.php');
    exit;
}
