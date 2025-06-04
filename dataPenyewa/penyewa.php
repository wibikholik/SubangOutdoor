<?php
session_start();

// Batasi akses hanya untuk admin dan owner
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header("Location: ../login.php?message=access_denied");
    exit;
}

include '../route/koneksi.php';

// Handle pesan notifikasi
$message = '';
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == "input") {
        $message = "✅ Data berhasil ditambahkan.";
    } elseif ($_GET['pesan'] == "hapus") {
        $message = "✅ Data berhasil dihapus.";
    } elseif ($_GET['pesan'] == "update") {
        $message = "✅ Data berhasil diupdate.";
    }
}

// Ambil data penyewa dari database
$query = "SELECT * FROM penyewa";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subang Outdoor - Data Penyewa</title>

    <!-- Font & CSS -->
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <!-- Sidebar -->
        <?php include '../layout/sidebar.php'; ?>
        <!-- End Sidebar -->

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <?php include '../layout/navbar.php'; ?>
                <!-- End Topbar -->

                <div class="container-fluid">
                    <!-- Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Data Penyewa</h1>
                    </div>

                    <!-- Notifikasi -->
                    <?php if ($message != ''): ?>
                        <div class="alert alert-success">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tabel Data -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <a class="btn btn-primary" href="tambah_Penyewa.php" role="button">Tambah</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama</th>
                                            <th>Alamat</th>
                                            <th>No. HP</th>
                                            <th>Email</th>
                                            <th>Password</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                            <tr>
                                                <td><?= $row['id_penyewa'] ?></td>
                                                <td><?= htmlspecialchars($row['nama_penyewa']) ?></td>
                                                <td><?= htmlspecialchars($row['alamat']) ?></td>
                                                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                                <td><?= htmlspecialchars($row['email']) ?></td>
                                                <td><?= htmlspecialchars($row['password']) ?></td>
                                                <td>
                                                 <a class="btn btn-warning btn-sm" href="editPenyewa.php?id_penyewa=<?= $row['id_penyewa'] ?>">Edit</a>

                                                    <a class="btn btn-danger btn-sm" href="hapusPenyewa.php?id=<?= $row['id_penyewa'] ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- Footer bisa ditambahkan jika perlu -->
        </div>
    </div>

    <!-- JS -->
    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../assets/js/sb-admin-2.min.js"></script>
</body>
</html>
