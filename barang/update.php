<?php
include '../route/koneksi.php';

// Path upload gambar: di dalam folder barang ada folder gambar
$folder_upload = "barang/gambar/";

if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0755, true);  // buat folder jika belum ada
}

$id_barang     = $_POST['id_barang'];
$Nama_Barang   = $_POST['nama_barang'];   // disesuaikan dengan form edit
$Keterangan    = $_POST['keterangan'];
$Stok          = $_POST['stok'];
$Harga_Barang  = $_POST['harga_sewa'];    // disesuaikan dengan form edit
$Kategori      = $_POST['kategori'];
$Unggulan      = isset($_POST['unggulan']) ? 1 : 0;

// Ambil gambar lama dari database
$query = mysqli_query($koneksi, "SELECT gambar FROM barang WHERE id_barang='$id_barang'");
$data_lama = mysqli_fetch_assoc($query);
$gambar_lama = $data_lama['gambar'];

if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
    $file_tmp = $_FILES['gambar']['tmp_name'];
    $file_name = basename($_FILES['gambar']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array($file_ext, $allowed_ext)) {
        // nama file baru dengan timestamp + nama asli yang sudah difilter
        $new_file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "", $file_name);
        $target_file = $folder_upload . $new_file_name;  // gabungkan path dan nama file

        if (move_uploaded_file($file_tmp, $target_file)) {
            // Hapus gambar lama jika ada
            if (!empty($gambar_lama) && file_exists($folder_upload . $gambar_lama)) {
                unlink($folder_upload . $gambar_lama);
            }

            // Update data dengan gambar baru
            $update_query = "UPDATE barang SET 
                nama_barang='$Nama_Barang',
                keterangan='$Keterangan',
                gambar='$new_file_name',
                stok='$Stok',
                harga_sewa='$Harga_Barang',
                kategori='$Kategori',
                unggulan='$Unggulan'
                WHERE id_barang='$id_barang'";

            mysqli_query($koneksi, $update_query) or die(mysqli_error($koneksi));
            header("location:barang.php?pesan=update");
            exit;
        } else {
            echo "Gagal mengupload gambar baru.";
            exit;
        }
    } else {
        echo "Format gambar tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
        exit;
    }
} else {
    // Update data tanpa mengganti gambar
    $update_query = "UPDATE barang SET 
        nama_barang='$Nama_Barang',
        keterangan='$Keterangan',
        stok='$Stok',
        harga_sewa='$Harga_Barang',
        kategori='$Kategori',
        unggulan='$Unggulan'
        WHERE id_barang='$id_barang'";

    mysqli_query($koneksi, $update_query) or die(mysqli_error($koneksi));
    header("location:barang.php?pesan=update");
    exit;
}
?>
