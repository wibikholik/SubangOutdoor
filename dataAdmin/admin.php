<?php
session_start();

// Cek role owner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit;
}

include '../route/koneksi.php';

$message = '';
if (isset($_GET['pesan'])) {
    $pesan = $_GET['pesan'];
    if ($pesan == "input") {
        $message = "✅ Data berhasil ditambahkan.";
    } elseif ($pesan == "hapus") {
        $message = "✅ Data berhasil dihapus.";
    } elseif ($pesan == "update") {
        $message = "✅ Data berhasil diupdate.";
    }
}

$query = "SELECT * FROM admin";
$result = mysqli_query($koneksi, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Data Admin - Owner Panel | Subang Outdoor</title>

    <!-- Custom fonts for this template-->
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css" />
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,400,700"
        rel="stylesheet"
    />

    <!-- Custom styles for this template-->
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet" />
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include '../layout/sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Navbar -->
                <?php include '../layout/navbar.php'; ?>
                <!-- End Navbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-users-cog"></i> Data Admin</h1>
                        <a href="tambah_admin.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Admin
                        </a>
                    </div>

                    <!-- Notifikasi Pesan -->
                    <?php if (!empty($message)) :
                        $color = 'alert-success';
                        $icon = '<i class="fas fa-check-circle"></i>';
                        if (isset($pesan)) {
                            if ($pesan === "hapus") {
                                $color = 'alert-danger';
                                $icon = '<i class="fas fa-trash-alt"></i>';
                            } elseif ($pesan === "update") {
                                $color = 'alert-primary';
                                $icon = '<i class="fas fa-pen"></i>';
                            }
                        }
                    ?>
                        <div class="alert <?= $color ?> alert-dismissible fade show" role="alert">
                            <?= $icon ?> <?= htmlspecialchars($message) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Data Table -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table
                                    class="table table-bordered table-hover"
                                    id="dataTable"
                                    width="100%"
                                    cellspacing="0"
                                >
                                    <thead class="thead-light">
                                        <tr>
                                            <th>ID Admin</th>
                                            <th>Username</th>
                                            <th>Nama Admin</th>
                                            <th>Alamat</th>
                                            <th>No HP</th>
                                            <th>Email</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['id_admin']) ?></td>
                                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                                    <td><?= htmlspecialchars($row['nama_admin']) ?></td>
                                                    <td><?= htmlspecialchars($row['alamat']) ?></td>
                                                    <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                                    <td>
                                                        <a href="editAdmin.php?id_admin=<?= urlencode($row['id_admin']) ?>" class="btn btn-sm btn-warning" title="Edit Data">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="hapus.php?id_admin=<?= urlencode($row['id_admin']) ?>" class="btn btn-sm btn-danger" title="Hapus Data" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada data admin.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../assets/js/sb-admin-2.min.js"></script>

    <!-- DataTables scripts -->
    <script src="../assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#dataTable').DataTable({
                // Optional config
                "order": [[0, "asc"]],
            });
        });
    </script>

</body>
</html>
