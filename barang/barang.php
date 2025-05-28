<?php
session_start();
include '../route/koneksi.php';

// Cek role, hanya admin dan owner yang boleh akses
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header("Location: ../login.php?message=access_denied");
    exit;
}

$username = $_SESSION['username'];

$query = "SELECT * FROM barang";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Daftar Barang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- W3.CSS -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f8;
            margin: 0; 
            padding: 0;
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar width */
        .sidebar {
            width: 25%;
        }
        /* Main content */
        main {
            flex-grow: 1;
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin: 20px;
        }
        header h1 {
            margin: 0 0 15px 0;
            color: #007BFF;
        }
        .btn-tambah {
            background-color: #007BFF;
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 15px;
            display: inline-block;
        }
        .btn-tambah:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #f4f4f4;
        }
        img {
            max-width: 100px;
            height: auto;
            border-radius: 6px;
        }
        .aksi a {
            margin-right: 10px;
            color: #333;
            cursor: pointer;
            font-size: 18px;
        }
        .aksi a:hover {
            opacity: 0.7;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <?php include('../layout/sidebar.php'); ?>
</div>

<main>
    <?php include('../layout/navbar.php'); ?>

    <header>
        <h1>Daftar Barang</h1>
        <a href="tambah_barang.php" class="btn-tambah"><i class="fas fa-plus"></i> Tambah Barang</a>
    </header>

    <?php if (isset($_GET['pesan'])): ?>
    <script>
        <?php 
        $pesan = $_GET['pesan'];
        if ($pesan == 'hapus'): ?>
            alert("✅ Barang berhasil dihapus.");
        <?php elseif ($pesan == 'gagalhapus'): ?>
            alert("❌ Gagal menghapus barang karena masih digunakan dalam transaksi atau keranjang.");
        <?php elseif ($pesan == 'gagalhapusdb'): ?>
            alert("❌ Terjadi kesalahan saat menghapus dari database.");
        <?php elseif ($pesan == 'invalid'): ?>
            alert("❌ ID Barang tidak ditemukan.");
        <?php endif; ?>
    </script>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID Barang</th>
                <th>Foto</th>
                <th>Nama Barang</th>
                <th>Keterangan</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Harga Sewa</th>
                <th>Unggulan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_barang']) ?></td>
                        <td><img src="barang/gambar/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_barang']) ?>" /></td>
                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td><?= htmlspecialchars($row['keterangan']) ?></td>
                        <td><?= htmlspecialchars($row['kategori']) ?></td>
                        <td><?= htmlspecialchars($row['stok']) ?></td>
                        <td>Rp <?= number_format($row['harga_sewa'], 0, ',', '.') ?></td>
                        <td><?= $row['unggulan'] == 1 ? '✅' : '❌' ?></td>
                        <td class="aksi">
                            <a href="hapus.php?id_barang=<?= urlencode($row['id_barang']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?');" title="Hapus"><i class="fas fa-trash" style="color:#dc3545;"></i></a>
                            <a href="edit.php?id_barang=<?= urlencode($row['id_barang']) ?>" title="Edit"><i class="fas fa-edit" style="color:#007BFF;"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" style="text-align:center;">Data barang kosong.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

</body>
</html>
