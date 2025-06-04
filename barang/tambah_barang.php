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
    <meta charset="UTF-8">
    <title>Subang Outdoor - Input Barang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font & Template CSS -->
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,400,700" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
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
                        <h1 class="h3 mb-0 text-gray-800"><i ></i> Tambah Barang</h1>
                        <a href="barang.php" class="btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
                        </a>
                    </div>

                <!-- Form Input -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form action="tambah_aksi.php" method="post" enctype="multipart/form-data" autocomplete="off">
                            <div class="form-group">
                                <label for="nama_barang">Nama Barang</label>
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                            </div>

                            <div class="form-group">
                                <label for="kategori">Kategori</label>
                                <select class="form-control" id="kategori" name="kategori" required>
                                    <option value="" disabled selected>Pilih Kategori</option>
                                    <option value="tenda">Tenda</option>
                                    <option value="perlengkapan masak">Perlengkapan Masak</option>
                                    <option value="perlengkapan">Perlengkapan Camping</option>
                                </select>
                            </div>

                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="unggulan" name="unggulan" value="1">
                                <label class="form-check-label" for="unggulan">Produk Unggulan</label>
                            </div>

                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" required>
                            </div>

                            <div class="form-group">
                                <label for="gambar">Gambar</label>
                                <input type="file" class="form-control-file" id="gambar" name="gambar" accept="image/*" required>
                            </div>

                            <div class="form-group">
                                <label for="stok">Stok Barang</label>
                                <input type="number" class="form-control" id="stok" name="stok" min="0" required>
                            </div>

                            <div class="form-group">
                                <label for="harga_sewa">Harga Sewa (per hari)</label>
                                <input type="number" class="form-control" id="harga_sewa" name="harga_sewa" min="0" step="0.01" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Tambah Barang</button>
                        </form>
                    </div>
                </div>
                <!-- End Form -->

            </div>

        </div>
        <!-- End Content -->

    </div>
    <!-- End Content Wrapper -->

</div>
<!-- End Wrapper -->

<!-- JS -->
<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../assets/js/sb-admin-2.min.js"></script>

</body>
</html>
