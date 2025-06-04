<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}
include "../route/koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Data Penyewa</title>

    <!-- SB Admin 2 Assets -->
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,400,700" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <!-- Sidebar -->
    <?php include('../layout/sidebar.php'); ?>
    <!-- End of Sidebar -->

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Navbar -->
            <?php include('../layout/navbar.php'); ?>
            <!-- End of Navbar -->

            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Edit Data Penyewa</h1>

                <?php
                if (isset($_GET['id_penyewa'])) {
                    $id_penyewa = intval($_GET['id_penyewa']);
                    $stmt = mysqli_prepare($koneksi, "SELECT * FROM penyewa WHERE id_penyewa = ?");
                    mysqli_stmt_bind_param($stmt, "i", $id_penyewa);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    if ($data = mysqli_fetch_assoc($result)) {
                ?>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form action="update.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id_penyewa" value="<?= $data['id_penyewa']; ?>">

                                <div class="form-group">
                                    <label>Nama Penyewa</label>
                                    <input type="text" name="namapenyewa" class="form-control" value="<?= htmlspecialchars($data['nama_penyewa']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Alamat</label>
                                    <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($data['alamat']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>No HP</label>
                                    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($data['no_hp']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control" value="<?= htmlspecialchars($data['password']); ?>" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </form>
                        </div>
                    </div>
                <?php
                    } else {
                        echo '<div class="alert alert-danger">Data penyewa tidak ditemukan.</div>';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    echo '<div class="alert alert-warning">ID Penyewa tidak tersedia.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- SB Admin 2 Scripts -->
<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/sb-admin-2.min.js"></script>
</body>
</html>
