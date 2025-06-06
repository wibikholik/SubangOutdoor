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

// Notifikasi pesan
$message = '';
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == "input") {
        $message = "✅ Data berhasil ditambahkan.";
    } elseif ($_GET['pesan'] == "hapus") {
        $message = "✅ Data berhasil dihapus.";
    } elseif ($_GET['pesan'] == "update") {
        $message = "✅ Data berhasil diupdate.";
    } elseif ($_GET['pesan'] == "gagal") {
        $message = "❌ Terjadi kesalahan saat memproses data.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Subang Outdoor - Dashboard</title>

    <!-- Font & Template CSS -->
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,400,700" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="../assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include '../layout/sidebar.php'; ?>
        <!-- End of Sidebar -->

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <!-- Navbar -->
                <?php include '../layout/navbar.php'; ?>
                <!-- End of Navbar -->

                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Barang</h1>
                    </div>

                    <!-- Alert Pesan -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $message ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Data Barang -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <a class="btn btn-primary" href="tambah_barang.php" role="button">Tambah</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama</th>
                                            <th>Gambar</th>
                                            <th>Stok</th>
                                            <th>Harga</th>
                                            <th>Kategori</th>
                                            <th>Keterangan</th>
                                            <th>Unggulan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td><?= $row['id_barang'] ?></td>
                                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                                    <td>
                                                        <?php if (!empty($row['gambar'])): ?>
                                                            <img src="barang/gambar/<?= $row['gambar'] ?>" width="100" alt="gambar">
                                                        <?php else: ?>
                                                            <em>tidak ada</em>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $row['stok'] ?></td>
                                                    <td>Rp.<?= number_format($row['harga_sewa'], 0, ',', '.') ?></td>
                                                    <td><?= htmlspecialchars($row['kategori']) ?></td>
                                                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                                    <td><?= $row['unggulan'] == 1 ? 'YA' : 'TIDAK' ?></td>
                                                    <td>
                                                        <a class="btn btn-warning btn-sm" href="edit.php?id_barang=<?= $row['id_barang'] ?>">Edit</a>
                                                        <a class="btn btn-danger btn-sm" href="hapus.php?id_barang=<?= $row['id_barang'] ?>" onclick="return confirm('Yakin ingin menghapus barang ini?')">Hapus</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">Data barang belum tersedia.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> <!-- End card -->
                </div> <!-- End container -->

            </div>
        </div>

    </div>

    <!-- JS Scripts -->
    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../assets/js/sb-admin-2.min.js"></script>

    <!-- DataTables Scripts -->
    <script src="../assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#dataTable').DataTable();
        });
    </script>
</body>

</html>
