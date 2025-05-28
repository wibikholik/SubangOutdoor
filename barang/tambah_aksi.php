<?php
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    // Jika bukan admin atau owner, arahkan ke halaman login
    header("Location: ../login.php");
    exit;
}

include '../route/koneksi.php'; 

$$folder_upload = "image/barang/";

if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0755, true);
}

// Ambil data dari form, sesuaikan nama inputnya
$Nama_Barang   = $_POST['nama_barang'];
$Keterangan    = $_POST['keterangan'];
$Stok          = $_POST['stok'];
$Harga_Barang  = $_POST['harga_sewa'];
$Kategori      = $_POST['kategori'];
$Unggulan     = isset($_POST['unggulan']) ? 1 : 0;

if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
    $file_tmp = $_FILES['gambar']['tmp_name'];
    $file_name = basename($_FILES['gambar']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array($file_ext, $allowed_ext)) {
        
        $new_file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "", $file_name);
        $target_file = $folder_upload . $new_file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            // Escape data sebelum query untuk keamanan
            $Nama_Barang = mysqli_real_escape_string($koneksi, $Nama_Barang);
            $Keterangan = mysqli_real_escape_string($koneksi, $Keterangan);
            $Kategori = mysqli_real_escape_string($koneksi, $Kategori);

            $query = "INSERT INTO barang (Nama_Barang, Keterangan, Gambar, Stok, Harga_Sewa, kategori, unggulan) 
                      VALUES ('$Nama_Barang', '$Keterangan', '$new_file_name', '$Stok', '$Harga_Barang', '$Kategori', '$Unggulan')";

            if (mysqli_query($koneksi, $query)) {
                header("Location: barang.php?pesan=input");
                exit;
            } else {
                echo "Error saat memasukkan data ke database: " . mysqli_error($koneksi);
                exit;
            }

        } else {
            echo "Gagal mengupload gambar.";
            exit;
        }
    } else {
        echo "Format gambar tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
        exit;
    }
} else {
    echo "Gambar belum diupload atau terjadi kesalahan saat upload.";
    exit;
}
?>
