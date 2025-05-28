<?php
include '../../route/koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Akses tidak valid.");
}

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='../../login.php';</script>";
    exit;
}
$id_penyewa = $_SESSION['user_id'];
$id_transaksi = $_POST['id_transaksi'] ?? null;


if (!$id_transaksi || !$id_penyewa) {
    die("Data tidak lengkap.");
}

// Cek transaksi ada dan milik penyewa
$stmt = $koneksi->prepare("SELECT id_metode FROM transaksi WHERE id_transaksi = ? AND id_penyewa = ?");
$stmt->bind_param("ii", $id_transaksi, $id_penyewa);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Transaksi tidak ditemukan.");
}
$data = $result->fetch_assoc();
$id_metode = $data['id_metode'];

// Validasi file upload bukti pembayaran
if (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK) {
    die("Gagal mengunggah bukti.");
}

$file = $_FILES['bukti_pembayaran'];
$allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_ext)) {
    die("Format file tidak diizinkan.");
}

$max_size = 5 * 1024 * 1024; // 5 MB
if ($file['size'] > $max_size) {
    die("Ukuran file terlalu besar.");
}

$upload_dir = '../../uploads/bukti/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$new_filename = 'bukti_' . $id_transaksi . '_' . time() . '.' . $ext;
$target_file = $upload_dir . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $target_file)) {
    die("Gagal memindahkan file ke server.");
}

$tanggal_pembayaran = date('Y-m-d H:i:s');
$status_pembayaran = 'Menunggu Konfirmasi';

// Update status transaksi jadi menunggu konfirmasi
$update_status = $koneksi->prepare("UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
$update_status->bind_param("si", $status_pembayaran, $id_transaksi);
$update_status->execute();
$update_status->close();

// Update stok barang berdasarkan detail transaksi
$stmtDetail = $koneksi->prepare("SELECT id_barang, jumlah_barang FROM detail_transaksi WHERE id_transaksi = ?");
$stmtDetail->bind_param("i", $id_transaksi);
$stmtDetail->execute();
$resultDetail = $stmtDetail->get_result();

while ($row = $resultDetail->fetch_assoc()) {
    $id_barang = $row['id_barang'];
    $jumlah = $row['jumlah_barang'];

    $stmtUpdateStok = $koneksi->prepare("UPDATE barang SET stok = stok - ? WHERE id_barang = ?");
    if (!$stmtUpdateStok) {
        die("Prepare update stok gagal: " . $koneksi->error);
    }

    $stmtUpdateStok->bind_param("ii", $jumlah, $id_barang);

    if (!$stmtUpdateStok->execute()) {
        die("Gagal update stok barang ID $id_barang: " . $stmtUpdateStok->error);
    }
    $stmtUpdateStok->close();
}
$stmtDetail->close();

// Simpan data pembayaran ke tabel pembayaran
$stmt_insert = $koneksi->prepare("
    INSERT INTO pembayaran (id_transaksi, id_metode, tanggal_pembayaran, bukti_pembayaran, status_pembayaran)
    VALUES (?, ?, ?, ?, ?)
");
$stmt_insert->bind_param("iisss", $id_transaksi, $id_metode, $tanggal_pembayaran, $new_filename, $status_pembayaran);

if ($stmt_insert->execute()) {
    echo "<script>alert('Bukti berhasil diupload. Menunggu konfirmasi admin.'); window.location.href='../page/transaksi.php';</script>";
} else {
    echo "Gagal menyimpan data pembayaran: " . $stmt_insert->error;
}

$stmt_insert->close();
$koneksi->close();
?>
