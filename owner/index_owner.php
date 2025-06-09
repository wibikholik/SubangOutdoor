<?php
session_start();
include '../route/koneksi.php';

// Query barang dengan stok menipis
$barang_menipis = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok <= 5 ORDER BY stok ASC LIMIT 10");

// Query penyewa top
$penyewa_top = mysqli_query($koneksi, "
    SELECT p.id_penyewa, p.nama_penyewa, COUNT(t.id_transaksi) AS total_transaksi
    FROM penyewa p
    LEFT JOIN transaksi t ON p.id_penyewa = t.id_penyewa
    GROUP BY p.id_penyewa
    ORDER BY total_transaksi DESC
    LIMIT 5
");

// Grafik penyewaan (sewa selesai)
$grafik_penyewaan = mysqli_query($koneksi, "
    SELECT DATE_FORMAT(tanggal_sewa, '%Y-%m') AS bulan, COUNT(*) AS total_penyewaan
    FROM transaksi
    WHERE status = 'Selesai Dikembalikan'
    GROUP BY bulan
    ORDER BY bulan ASC
");

// Grafik pengembalian (tanggal_kembali dengan status selesai)
$grafik_pengembalian = mysqli_query($koneksi, "
    SELECT DATE_FORMAT(tanggal_kembali, '%Y-%m') AS bulan, COUNT(*) AS total_pengembalian
    FROM transaksi
    WHERE status = 'Selesai Dikembalikan' AND tanggal_kembali IS NOT NULL
    GROUP BY bulan
    ORDER BY bulan ASC
");

$labels_bulan = [];
$data_penyewaan = [];
$data_pengembalian = [];

// Ambil data penyewaan
while ($row = mysqli_fetch_assoc($grafik_penyewaan)) {
    $labels_bulan[] = $row['bulan'];
    $data_penyewaan[] = (int)$row['total_penyewaan'];
}

// Ambil data pengembalian ke array asosiasi per bulan
$pengembalian_tmp = [];
while ($row = mysqli_fetch_assoc($grafik_pengembalian)) {
    $pengembalian_tmp[$row['bulan']] = (int)$row['total_pengembalian'];
}

// Sesuaikan data pengembalian berdasarkan label bulan (jika tidak ada data, 0)
foreach ($labels_bulan as $bulan) {
    $data_pengembalian[] = $pengembalian_tmp[$bulan] ?? 0;
}

// Ringkasan
$total_penyewa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM penyewa"))['total'];
$total_admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM admin"))['total'];
$total_barang = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM barang"))['total'];
$total_barang_disewa = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(dt.jumlah_barang) AS total 
    FROM detail_transaksi dt 
    JOIN transaksi t ON dt.id_transaksi = t.id_transaksi 
    WHERE t.status = 'Disewa'
"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Subang Outdoor</title>
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body id="page-top">
<div id="wrapper">
    <?php include '../layout/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include '../layout/navbar.php'; ?>

            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

                <div class="row">
                    <!-- Kartu Ringkasan -->
                    <?php
                    $ringkasan = [
                        ['title' => 'Penyewa Terdaftar', 'total' => $total_penyewa, 'icon' => 'fa-users', 'color' => 'primary'],
                        ['title' => 'Admin Terdaftar', 'total' => $total_admin, 'icon' => 'fa-user-shield', 'color' => 'success'],
                        ['title' => 'Barang Tersedia', 'total' => $total_barang, 'icon' => 'fa-boxes', 'color' => 'warning'],
                        ['title' => 'Total Barang Disewa', 'total' => $total_barang_disewa, 'icon' => 'fa-shopping-cart', 'color' => 'danger'],
                    ];
                    foreach ($ringkasan as $data): ?>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-<?= $data['color'] ?> shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-<?= $data['color'] ?> text-uppercase mb-1"><?= $data['title'] ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['total'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas <?= $data['icon'] ?> fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Stok Menipis -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Barang dengan Stok Menipis (≤ 5)</h6></div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>ID Barang</th><th>Nama Barang</th><th>Stok</th></tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($barang_menipis) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($barang_menipis)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id_barang']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                            <td><?= (int)$row['stok'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center">Tidak ada barang dengan stok menipis.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Penyewa Top -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-success">Penyewa Teratas</h6></div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>ID Penyewa</th><th>Nama Penyewa</th><th>Total Transaksi</th></tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($penyewa_top) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($penyewa_top)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id_penyewa']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_penyewa']) ?></td>
                                            <td><?= (int)$row['total_transaksi'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center">Belum ada data penyewa.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grafik Penyewaan dan Pengembalian -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-info">Grafik Penyewaan & Pengembalian per Bulan</h6></div>
                    <div class="card-body">
                       <canvas id="grafikPenyewaan" height="100"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../assets/js/sb-admin-2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('grafikPenyewaan').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels_bulan) ?>,
        datasets: [
            {
                label: 'Jumlah Penyewaan',
                data: <?= json_encode($data_penyewaan) ?>,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                fill: true,
                tension: 0.3
            },
            {
                label: 'Jumlah Pengembalian',
                data: <?= json_encode($data_pengembalian) ?>,
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                fill: true,
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                precision: 0
            }
        }
    }
});
</script>

</body>
</html>
