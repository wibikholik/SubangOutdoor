<?php
include '../../route/koneksi.php';
session_start();

require '../../vendor/autoload.php';  // Autoload PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// Validasi file bukti pembayaran
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

// Update status transaksi
$update_status = $koneksi->prepare("UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
$update_status->bind_param("si", $status_pembayaran, $id_transaksi);
$update_status->execute();
$update_status->close();

// Update stok barang
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

// Simpan ke tabel pembayaran
$stmt_insert = $koneksi->prepare("
    INSERT INTO pembayaran (id_transaksi, id_metode, tanggal_pembayaran, bukti_pembayaran, status_pembayaran)
    VALUES (?, ?, ?, ?, ?)
");
$stmt_insert->bind_param("iisss", $id_transaksi, $id_metode, $tanggal_pembayaran, $new_filename, $status_pembayaran);

if ($stmt_insert->execute()) {
    // Kirim notifikasi email
    $mail = new PHPMailer(true);

    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'subangoutdoortes@gmail.com';  // Email pengirim
        $mail->Password   = 'gpwk ldps fdgt yrhc';           // Ganti dengan App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('subangoutdoortes@gmail.com', 'Subang Outdoor');

        // Ambil email admin
        $emails = [];
        $admin_result = $koneksi->query("SELECT email FROM admin");
        if ($admin_result) {
            while ($row = $admin_result->fetch_assoc()) {
                if (!empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $row['email'];
                }
            }
        } else {
            die("Query admin gagal: " . $koneksi->error);
        }

        // Ambil email owner
        $owner_result = $koneksi->query("SELECT email FROM owner");
        if ($owner_result) {
            while ($row = $owner_result->fetch_assoc()) {
                if (!empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $row['email'];
                }
            }
        } else {
            die("Query owner gagal: " . $koneksi->error);
        }

        if (empty($emails)) {
            die("Tidak ada email admin atau owner yang valid ditemukan.");
        }

        // Tambahkan penerima yang valid
        foreach ($emails as $email) {
            $mail->addAddress($email);
        }

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = 'Pembayaran Baru dari Penyewa';
        $mail->Body    = "
            <h4>Pembayaran Baru Diterima</h4>
            <p><strong>ID Transaksi:</strong> {$id_transaksi}</p>
            <p>Status saat ini: <strong>{$status_pembayaran}</strong></p>
            <p>Silakan login untuk verifikasi dan pemrosesan lebih lanjut.</p>
        ";

        $mail->send();

    } catch (Exception $e) {
        error_log("Gagal mengirim email notifikasi: " . $mail->ErrorInfo);
    }

    echo "<script>alert('Bukti berhasil diupload. Menunggu konfirmasi admin.'); window.location.href='../page/transaksi.php';</script>";
} else {
    echo "Gagal menyimpan data pembayaran: " . $stmt_insert->error;
}

$stmt_insert->close();
$koneksi->close();
?>
