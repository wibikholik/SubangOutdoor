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
            'menunggu konfirmasi',
            'dikonfirmasi Silahkan AmbilBarang',
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

    // Validasi id positif dan target_table ada di daftar valid_status
    if ($id <= 0 || !array_key_exists($target_table, $valid_status)) {
        die('Input ID atau tabel tidak valid.');
    }

    // Validasi status baru sesuai tabel target
    if (!in_array($status_baru, $valid_status[$target_table], true)) {
        die('Status baru tidak valid untuk tabel ' . htmlspecialchars($target_table));
    }

    // Tentukan kolom id sesuai tabel target
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

    // Tentukan kolom status sesuai tabel target
    if ($target_table === 'pembayaran') {
        $status_column = 'status_pembayaran';
    } elseif ($target_table === 'pengembalian') {
        $status_column = 'status_pengembalian';
    } else {
        $status_column = 'status';
    }

    // Update status di tabel target
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

    // Jika update di transaksi, update juga pembayaran dan pengembalian tanpa validasi status
    if ($target_table === 'transaksi') {
        // Update status pembayaran
        $sql_pembayaran = "UPDATE pembayaran SET status_pembayaran = ? WHERE id_transaksi = ?";
        $stmt_pembayaran = $koneksi->prepare($sql_pembayaran);
        if ($stmt_pembayaran) {
            $stmt_pembayaran->bind_param("si", $status_baru, $id);
            if (!$stmt_pembayaran->execute()) {
                die("Gagal update status pembayaran: " . $stmt_pembayaran->error);
            }
            $stmt_pembayaran->close();
        } else {
            die("Gagal prepare statement pembayaran: " . $koneksi->error);
        }

        // Update status pengembalian
        $sql_pengembalian = "UPDATE pengembalian SET status_pengembalian = ? WHERE id_transaksi = ?";
        $stmt_pengembalian = $koneksi->prepare($sql_pengembalian);
        if ($stmt_pengembalian) {
            $stmt_pengembalian->bind_param("si", $status_baru, $id);
            if (!$stmt_pengembalian->execute()) {
                die("Gagal update status pengembalian: " . $stmt_pengembalian->error);
            }
            $stmt_pengembalian->close();
        } else {
            die("Gagal prepare statement pengembalian: " . $koneksi->error);
        }
    }

    header('Location: transaksi.php?success=updated');
    exit;
} else {
    header('Location: transaksi.php');
    exit;
}
