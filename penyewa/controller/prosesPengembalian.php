<?php
include '../../route/koneksi.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='../../login.php';</script>";
    exit;
}

// Tentukan folder upload
$path_pengembalian = "../../uploads/pengembalian/";
$path_denda = "../../uploads/denda/";

// Cek dan buat folder jika belum ada
if (!is_dir($path_pengembalian)) {
    mkdir($path_pengembalian, 0777, true);
}
if (!is_dir($path_denda)) {
    mkdir($path_denda, 0777, true);
}

// Proses saat form dikirim
if (isset($_POST['submit'])) {
    $id_transaksi = intval($_POST['id_transaksi']);
    $kondisi_barang = mysqli_real_escape_string($koneksi, $_POST['kondisi_barang']);
    $total_denda = intval($_POST['total_denda']);

    // ===== Upload bukti pengembalian =====
    $bukti_pengembalian_name = $_FILES['bukti_pengembalian']['name'];
    $tmp_bukti_pengembalian = $_FILES['bukti_pengembalian']['tmp_name'];
    $bukti_pengembalian = time() . '_' . basename($bukti_pengembalian_name);
    $target_file_pengembalian = $path_pengembalian . $bukti_pengembalian;

    if (!move_uploaded_file($tmp_bukti_pengembalian, $target_file_pengembalian)) {
        die("Gagal mengunggah bukti pengembalian.");
    }

    // ===== Upload bukti denda jika ada =====
    $bukti_denda = null;
    if ($total_denda > 0 && isset($_FILES['bukti_denda']) && $_FILES['bukti_denda']['name'] != '') {
        $bukti_denda_name = $_FILES['bukti_denda']['name'];
        $tmp_bukti_denda = $_FILES['bukti_denda']['tmp_name'];
        $bukti_denda = time() . '_' . basename($bukti_denda_name);
        $target_file_denda = $path_denda . $bukti_denda;

        if (!move_uploaded_file($tmp_bukti_denda, $target_file_denda)) {
            die("Gagal mengunggah bukti pembayaran denda.");
        }
    }

    // Simpan data pengembalian ke database
    $query = "INSERT INTO pengembalian (
                id_transaksi, kondisi_barang, bukti_pengembalian, bukti_denda, 
                total_denda, tanggal_pengembalian, status_pengembalian
              ) VALUES (
                $id_transaksi, '$kondisi_barang', '$bukti_pengembalian', " .
                ($bukti_denda ? "'$bukti_denda'" : "NULL") . ",
                $total_denda, NOW(), 'Menunggu Konfirmasi Pengembalian'
              )";

    $result = mysqli_query($koneksi, $query);

    if ($result) {
        // Update status transaksi
        $update = "UPDATE transaksi SET status = 'Menunggu Konfirmasi Pengembalian' WHERE id_transaksi = $id_transaksi";
        mysqli_query($koneksi, $update);

        echo "<script>alert('Pengembalian berhasil dikirim. Menunggu konfirmasi admin.'); window.location.href='../page/transaksi.php';</script>";
    } else {
        echo "Gagal menyimpan pengembalian: " . mysqli_error($koneksi);
    }
}
?>
