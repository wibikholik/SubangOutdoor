<?php
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['owner', 'admin'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Input Data Barang</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <!-- Sidebar -->
    <?php include('../layout/sidebar.php'); ?>
    <!-- End Sidebar -->

    <div style="margin-left:25%; padding: 20px;">
        <h3 class="w3-text-blue">Input Data Barang</h3>
        <form action="tambah_aksi.php" method="post" enctype="multipart/form-data" class="w3-container w3-card-4 w3-light-grey w3-padding">
            <p>
                <label>Nama Barang</label>
                <input class="w3-input w3-border" type="text" name="nama_barang" required />
            </p>

            <p>
                <label>Kategori</label>
                <select class="w3-select w3-border" name="kategori" required>
                    <option value="" disabled selected>Pilih Kategori</option>
                    <option value="tenda">Tenda</option>
                    <option value="perlengkapan masak">Perlengkapan Masak</option>
                    <option value="perlengkapan">Perlengkapan Camping</option>
                </select>
            </p>

            <p>
                <label>
                    <input class="w3-check" type="checkbox" name="unggulan" value="1" />
                    Produk Unggulan
                </label>
            </p>

            <p>
                <label>Keterangan</label>
                <input class="w3-input w3-border" type="text" name="keterangan" required />
            </p>

            <p>
                <label>Gambar</label>
                <input class="w3-input w3-border" type="file" name="gambar" accept="image/*" required />
            </p>

            <p>
                <label>Stok Barang</label>
                <input class="w3-input w3-border" type="number" name="stok" min="0" required />
            </p>

            <p>
                <label>Harga Sewa (per hari)</label>
                <input class="w3-input w3-border" type="number" name="harga_sewa" min="0" step="0.01" required />
            </p>

            <p>
                <button type="submit" class="w3-button w3-blue w3-round">Tambah</button>
            </p>
        </form>
    </div>
</body>
</html>
