<?php
session_start();
include '../../route/koneksi.php';

// Cek session user_id (id_penyewa)
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='../../login.php';</script>";
    exit;
}
$id_penyewa = $_SESSION['user_id'];

// Cek metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Akses tidak diizinkan.'); window.location.href='../page/produk.php';</script>";
    exit;
}

// Ambil data dari form
$id_metode = $_POST['id_metode'] ?? null;
$selected_items = $_POST['items'] ?? []; // array keyed by cart_id
$tanggal_sewa = $_POST['tanggal_sewa'] ?? null;
$tanggal_kembali = $_POST['tanggal_kembali'] ?? null;

// Validasi data lengkap
if (!$id_metode || empty($selected_items) || !$tanggal_sewa || !$tanggal_kembali) {
    echo "<script>alert('Data tidak lengkap. Silakan pilih barang, metode pembayaran, dan isi tanggal sewa & kembali.'); window.history.back();</script>";
    exit;
}

// Validasi tanggal sewa dan kembali
$ts_sewa = strtotime($tanggal_sewa);
$ts_kembali = strtotime($tanggal_kembali);
if (!$ts_sewa || !$ts_kembali || $ts_kembali <= $ts_sewa) {
    echo "<script>alert('Tanggal kembali harus lebih dari tanggal sewa.'); window.history.back();</script>";
    exit;
}

$lama_sewa = ceil(($ts_kembali - $ts_sewa) / (60 * 60 * 24));

// Ambil semua ID carts yang dipilih
$selected_ids = array_keys($selected_items);
$selected_ids_int = array_map('intval', $selected_ids);
$ids_placeholders = implode(',', array_fill(0, count($selected_ids_int), '?'));

// Ambil data carts dan barang
$sql = "SELECT c.id AS cart_id, c.id_barang, c.jumlah, b.harga_sewa AS harga 
        FROM carts c
        JOIN barang b ON c.id_barang = b.id_barang
        WHERE c.id_penyewa = ? AND c.id IN ($ids_placeholders)";
$stmt = mysqli_prepare($koneksi, $sql);

$params = array_merge([$id_penyewa], $selected_ids_int);
$types = str_repeat('i', count($params));
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items_db = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items_db[$row['cart_id']] = $row;
}

if (count($items_db) !== count($selected_ids_int)) {
    echo "<script>alert('Data keranjang tidak valid.'); window.history.back();</script>";
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    $total_harga = 0;
    foreach ($selected_ids_int as $cart_id) {
        $item = $items_db[$cart_id];
        $total_harga += $item['harga'] * $item['jumlah'] * $lama_sewa;
    }

    $stmtTransaksi = mysqli_prepare($koneksi, "INSERT INTO transaksi (id_penyewa, total_harga_sewa, status, id_metode, tanggal_sewa, tanggal_kembali) VALUES (?, ?, 'belumbayar', ?, ?, ?)");
    mysqli_stmt_bind_param($stmtTransaksi, "idiss", $id_penyewa, $total_harga, $id_metode, $tanggal_sewa, $tanggal_kembali);

    if (!mysqli_stmt_execute($stmtTransaksi)) {
        throw new Exception("Gagal menyimpan transaksi.");
    }
    $id_transaksi = mysqli_insert_id($koneksi);

    $stmtDetail = mysqli_prepare($koneksi, "INSERT INTO detail_transaksi (id_transaksi, id_barang, jumlah_barang, harga_satuan) VALUES (?, ?, ?, ?)");
    $stmtUpdateStok = mysqli_prepare($koneksi, "UPDATE barang SET stok = stok - ? WHERE id_barang = ?");

    foreach ($selected_ids_int as $cart_id) {
        $item = $items_db[$cart_id];
        $id_barang = (int) $item['id_barang'];
        $jumlah = (int) $item['jumlah'];
        $harga_per_hari = (float) $item['harga'];

        mysqli_stmt_bind_param($stmtDetail, "iiid", $id_transaksi, $id_barang, $jumlah, $harga_per_hari);
        if (!mysqli_stmt_execute($stmtDetail)) {
            throw new Exception("Gagal menyimpan detail transaksi untuk barang ID: $id_barang");
        }

        mysqli_stmt_bind_param($stmtUpdateStok, "ii", $jumlah, $id_barang);
        if (!mysqli_stmt_execute($stmtUpdateStok)) {
            throw new Exception("Gagal update stok untuk barang ID: $id_barang");
        }
    }

    // Hapus item dari keranjang
    $hapusSQL = "DELETE FROM carts WHERE id_penyewa = ? AND id IN ($ids_placeholders)";
    $hapusStmt = mysqli_prepare($koneksi, $hapusSQL);
    $hapusParams = array_merge([$id_penyewa], $selected_ids_int);
    $hapusTypes = str_repeat('i', count($hapusParams));
    mysqli_stmt_bind_param($hapusStmt, $hapusTypes, ...$hapusParams);
    if (!mysqli_stmt_execute($hapusStmt)) {
        throw new Exception("Gagal menghapus item dari keranjang.");
    }

    mysqli_commit($koneksi);

    // Redirect ke halaman pembayaran
    header("Location: ../page/pembayaran.php?id_transaksi=$id_transaksi");
    exit;

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $msg = htmlspecialchars($e->getMessage());
    echo "<script>alert('Terjadi kesalahan: $msg'); window.history.back();</script>";
    exit;
}
?>
