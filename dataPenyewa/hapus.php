<?php
include '../route/koneksi.php';

if (isset($_GET['id_penyewa'])) {
    $id_penyewa = $_GET['id_penyewa'];

    $stmt = mysqli_prepare($koneksi, "DELETE FROM penyewa WHERE id_penyewa = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_penyewa);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        header("Location: penyewa.php?pesan=hapus");
        exit;
    } else {
        echo "Gagal menghapus data atau data tidak ditemukan.";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "ID Penyewa tidak ditemukan.";
}

mysqli_close($koneksi);
?>
