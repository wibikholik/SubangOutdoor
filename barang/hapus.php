<?php
include '../route/koneksi.php';

if (isset($_GET['id_barang'])) {
    $id_barang = intval($_GET['id_barang']); // amankan input ID

    // Cek apakah barang masih dipakai di detail_transaksi
    $cek_detail = mysqli_query($koneksi, "SELECT COUNT(*) as jumlah FROM detail_transaksi WHERE id_barang = '$id_barang'");
    $data_detail = mysqli_fetch_assoc($cek_detail);

    // Cek apakah barang masih dipakai di carts
    $cek_cart = mysqli_query($koneksi, "SELECT COUNT(*) as jumlah FROM carts WHERE id_barang = '$id_barang'");
    $data_cart = mysqli_fetch_assoc($cek_cart);

    if ($data_detail['jumlah'] > 0 || $data_cart['jumlah'] > 0) {
        // Jika masih digunakan, kirim pesan gagal via query string
        header("location: barang.php?pesan=gagalhapus");
        exit;
    }

    // Ambil nama file gambar dari database sebelum hapus
    $result = mysqli_query($koneksi, "SELECT Gambar FROM barang WHERE id_barang = '$id_barang'");
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $folder_upload = "barang/gambar/";
        $file_gambar = $folder_upload . $row['Gambar'];

        // Cek jika file gambar ada, hapus file gambar
        if (file_exists($file_gambar)) {
            unlink($file_gambar);
        }
    }

    // Jika aman, hapus data barang
    $query = mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang = '$id_barang'");

    if ($query) {
        header("location: barang.php?pesan=hapus");
        exit;
    } else {
        header("location: barang.php?pesan=gagalhapusdb");
        exit;
    }
} else {
    header("location: barang.php?pesan=invalid");
    exit;
}
?>
