<?php
$role = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? 'Guest';
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
        <div class="sidebar-brand-icon">
            <img src="../assets/img/pinguin.jpg" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
        </div>
        <div class="sidebar-brand-text mx-3">Subang Outdoor</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <?php
    switch ($role) {
        case 'admin':
            ?>
            <!-- Menu untuk Admin -->
            <li class="nav-item">
                <a class="nav-link" href="../admin/index_admin.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../barang/barang.php">
                    <i class="fas fa-fw fa-boxes"></i>
                    <span>Barang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../dataPenyewa/penyewa.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Penyewa</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../transaksi/transaksi.php">
                    <i class="fas fa-fw fa-clipboard-list"></i>
                    <span>Transaksi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../pembayaran/pembayaran.php">
                    <i class="fas fa-fw fa-money-check"></i>
                    <span>Pembayaran</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../metode_pembayaran/metode.php">
                    <i class="fas fa-fw fa-credit-card"></i>
                    <span>Metode Pembayaran</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../pengembalian/pengembalian.php">
                    <i class="fas fa-fw fa-undo"></i>
                    <span>Pengembalian</span>
                </a>
            </li>
            <?php
            break;

        case 'owner':
            ?>
            <!-- Menu untuk Owner -->
            <li class="nav-item">
                <a class="nav-link" href="../admin/index_admin.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../barang/barang.php">
                    <i class="fas fa-fw fa-boxes"></i>
                    <span>Barang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../dataPenyewa/penyewa.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Penyewa</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../transaksi/transaksi.php">
                    <i class="fas fa-fw fa-clipboard-list"></i>
                    <span>Transaksi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../pembayaran/pembayaran.php">
                    <i class="fas fa-fw fa-money-check"></i>
                    <span>Pembayaran</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../metode_pembayaran/metode.php">
                    <i class="fas fa-fw fa-credit-card"></i>
                    <span>Metode Pembayaran</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../pengembalian/pengembalian.php">
                    <i class="fas fa-fw fa-undo"></i>
                    <span>Pengembalian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../dataAdmin/admin.php">
                    <i class="fas fa-fw fa-user-shield"></i>
                    <span>Data Admin</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../laporan/laporan.php">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <?php
            break;

        default:
            ?>
            <!-- Menu untuk Guest (opsional) -->
            <li class="nav-item">
                <a class="nav-link" href="../login.php">
                    <i class="fas fa-fw fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
            </li>
            <?php
            break;
    }
    ?>

    <hr class="sidebar-divider my-0">
</ul>
