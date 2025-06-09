<?php
session_start();
include '../../route/koneksi.php';

require '../../vendor/autoload.php';  // PHPMailer autoload
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cek apakah user sudah login (session user_id ada)
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='../../login.php';</script>";
    exit;
}
$id_penyewa = $_SESSION['user_id'];

// Pastikan metode request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Akses tidak diizinkan.'); window.location.href='../page/produk.php';</script>";
    exit;
}

// Ambil data dari form POST
$id_metode = $_POST['id_metode'] ?? null;
$selected_items = $_POST['items'] ?? []; // array dengan key = cart_id dan value = jumlah (biasanya)
$tanggal_sewa = $_POST['tanggal_sewa'] ?? null;
$tanggal_kembali = $_POST['tanggal_kembali'] ?? null;

// Validasi data wajib ada
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

// Siapkan array ID carts yang dipilih (integer)
$selected_ids = array_keys($selected_items);
$selected_ids_int = array_map('intval', $selected_ids);
$ids_placeholders = implode(',', array_fill(0, count($selected_ids_int), '?'));

// Ambil data carts dan harga barang dari database
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

// Validasi apakah semua data cart ada
if (count($items_db) !== count($selected_ids_int)) {
    echo "<script>alert('Data keranjang tidak valid.'); window.history.back();</script>";
    exit;
}

// Ambil nama metode pembayaran berdasarkan id_metode
$sqlMetode = "SELECT nama_metode FROM metode_pembayaran WHERE id_metode = ?";
$stmtMetode = mysqli_prepare($koneksi, $sqlMetode);
mysqli_stmt_bind_param($stmtMetode, "i", $id_metode);
mysqli_stmt_execute($stmtMetode);
$resultMetode = mysqli_stmt_get_result($stmtMetode);
$metodeData = mysqli_fetch_assoc($resultMetode);

if (!$metodeData) {
    echo "<script>alert('Metode pembayaran tidak ditemukan.'); window.history.back();</script>";
    exit;
}
$nama_metode = trim($metodeData['nama_metode']);

// Mulai transaksi MySQL
mysqli_begin_transaction($koneksi);

try {
    $total_harga = 0;
    foreach ($selected_ids_int as $cart_id) {
        $item = $items_db[$cart_id];
        $total_harga += $item['harga'] * $item['jumlah'] * $lama_sewa;
    }

    // Tentukan status transaksi awal berdasarkan metode pembayaran
    if (strtolower($nama_metode) === 'bayar langsung') {
        $status_transaksi = 'menunggu konfirmasi pesanan';
    } else {
        $status_transaksi = 'belumbayar';
    }

    // Insert data ke tabel transaksi
    $stmtTransaksi = mysqli_prepare($koneksi, "INSERT INTO transaksi (id_penyewa, total_harga_sewa, status, id_metode, tanggal_sewa, tanggal_kembali) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmtTransaksi, "idsiss", $id_penyewa, $total_harga, $status_transaksi, $id_metode, $tanggal_sewa, $tanggal_kembali);

    if (!mysqli_stmt_execute($stmtTransaksi)) {
        throw new Exception("Gagal menyimpan transaksi.");
    }
    $id_transaksi = mysqli_insert_id($koneksi);

    // Insert detail transaksi
    $stmtDetail = mysqli_prepare($koneksi, "INSERT INTO detail_transaksi (id_transaksi, id_barang, jumlah_barang, harga_satuan) VALUES (?, ?, ?, ?)");

    foreach ($selected_ids_int as $cart_id) {
        $item = $items_db[$cart_id];
        $id_barang = (int) $item['id_barang'];
        $jumlah = (int) $item['jumlah'];
        $harga_per_hari = (float) $item['harga'];

        mysqli_stmt_bind_param($stmtDetail, "iiid", $id_transaksi, $id_barang, $jumlah, $harga_per_hari);
        if (!mysqli_stmt_execute($stmtDetail)) {
            throw new Exception("Gagal menyimpan detail transaksi untuk barang ID: $id_barang");
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

    // Commit transaksi
    mysqli_commit($koneksi);

    // Kirim email notifikasi jika metode bayar langsung
    if (strtolower($nama_metode) === 'bayar langsung') {
        try {
            $mail = new PHPMailer(true);
            // Konfigurasi SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'subangoutdoortes@gmail.com';  // Ganti dengan email pengirim Anda
            $mail->Password   = 'sbsn ajtg fgox otra';          // Ganti dengan App Password email
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('subangoutdoortes@gmail.com', 'Subang Outdoor');

            // Ambil email admin dan owner
            $emails = [];
            $admin_result = $koneksi->query("SELECT email FROM admin");
            if ($admin_result) {
                while ($row = $admin_result->fetch_assoc()) {
                    if (!empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                        $emails[] = $row['email'];
                    }
                }
            }
            $owner_result = $koneksi->query("SELECT email FROM owner");
            if ($owner_result) {
                while ($row = $owner_result->fetch_assoc()) {
                    if (!empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                        $emails[] = $row['email'];
                    }
                }
            }

            if (!empty($emails)) {
                foreach ($emails as $email) {
                    $mail->addAddress($email);
                }

                $mail->isHTML(true);
                $mail->Subject = 'Pesanan Baru Menunggu Konfirmasi Pesanan';
                $mail->Body    = "
                    <h4>Pesanan Baru dari Penyewa dengan metode pembayaran bayar langsung</h4>
                    <p><strong>ID Transaksi:</strong> {$id_transaksi}</p>
                    <p>Status saat ini: <strong>{$status_transaksi}</strong></p>
                    <p>Silakan login untuk verifikasi dan proses lebih lanjut.</p>
                ";

                $mail->send();
            }
        } catch (Exception $e) {
            error_log("Gagal mengirim email notifikasi: " . $mail->ErrorInfo);
        }
    }

    // Redirect ke halaman sesuai metode pembayaran
    if (strtolower($nama_metode) === 'bayar langsung') {
        header("Location: ../page/transaksi.php");
    } else {
        header("Location: ../page/pembayaran.php?id_transaksi=$id_transaksi");
    }
    exit;

} catch (Exception $e) {
    // Rollback jika error
    mysqli_rollback($koneksi);
    $msg = htmlspecialchars($e->getMessage());
    echo "<script>alert('Terjadi kesalahan: $msg'); window.history.back();</script>";
    exit;
}
?>
