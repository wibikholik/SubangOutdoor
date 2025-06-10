<?php
session_start();
include '../route/koneksi.php';

// Akses hanya admin dan owner
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['owner'])) {
    header("Location: ../login.php?message=access_denied");
    exit;
}

// Fungsi bantu untuk ambil total
function getTotalByPeriod($koneksi, $interval) {
    $query = "SELECT SUM(t.total_harga_sewa) AS total 
              FROM transaksi t
              JOIN pengembalian p ON t.id_transaksi = p.id_transaksi
              WHERE t.status = 'Selesai Dikembalikan' 
              AND p.tanggal_pengembalian >= DATE_SUB(CURDATE(), INTERVAL $interval)";
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}


// Data ringkasan
$total_harian = getTotalByPeriod($koneksi, "1 DAY");
$total_mingguan = getTotalByPeriod($koneksi, "7 DAY");
$total_bulanan = getTotalByPeriod($koneksi, "1 MONTH");
$total_tahunan = getTotalByPeriod($koneksi, "1 YEAR");

// Data tabel transaksi selesai
$transaksi = mysqli_query($koneksi, "SELECT * FROM transaksi 
    JOIN metode_pembayaran ON transaksi.id_metode = metode_pembayaran.id_metode 
    WHERE status = 'Selesai Dikembalikan' 
    ORDER BY transaksi.id_transaksi DESC");


// Data sumber penghasilan untuk chart
$chart = mysqli_query($koneksi, "SELECT metode_pembayaran.nama_metode, SUM(transaksi.total_harga_sewa) AS total 
                                 FROM transaksi 
                                 JOIN metode_pembayaran ON transaksi.id_metode = metode_pembayaran.id_metode 
                                 WHERE status = 'Selesai Dikembalikan' 
                                 GROUP BY transaksi.id_metode");
$chart_labels = [];
$chart_data = [];
while ($row = mysqli_fetch_assoc($chart)) {
    $chart_labels[] = $row['nama_metode'];
    $chart_data[] = $row['total'];
}

// Data laporan pengembalian (join dengan transaksi untuk info penyewa dan tanggal)
$pengembalian = mysqli_query($koneksi, "SELECT p.*, t.tanggal_sewa, t.tanggal_kembali, t.id_penyewa 
                                        FROM pengembalian p
                                        JOIN transaksi t ON p.id_transaksi = t.id_transaksi
                                        ORDER BY p.tanggal_pengembalian DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi & Pengembalian - Subang Outdoor</title>
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body id="page-top">

<div id="wrapper">
    <?php include '../layout/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include '../layout/navbar.php'; ?>

            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Laporan Transaksi & Pengembalian</h1>

                <!-- Ringkasan Kartu -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Hari Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp.<?= number_format($total_harian, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Minggu Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp.<?= number_format($total_mingguan, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Bulan Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp.<?= number_format($total_bulanan, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tahun Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp.<?= number_format($total_tahunan, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafik Sumber Penghasilan -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Grafik Sumber Penghasilan</h6>
                    </div>
                   <div class="card-body" style="max-width: 450px; margin: auto;">
                       <canvas id="chartMetode"></canvas>
                   </div>
                </div>

                <!-- Tabel Transaksi -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Riwayat Transaksi Selesai</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal Sewa</th>
                                        <th>Tanggal Kembali</th>
                                        <th>Penyewa</th>
                                        <th>Metode</th>
                                        <th>Total Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($transaksi)): ?>
                                        <tr>
                                            <td><?= $row['id_transaksi'] ?></td>
                                            <td><?= $row['tanggal_sewa'] ?></td>
                                            <td><?= $row['tanggal_kembali'] ?></td>
                                            <td><?= $row['id_penyewa'] ?></td>
                                            <td><?= $row['nama_metode'] ?></td>
                                            <td>Rp.<?= number_format($row['total_harga_sewa'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tabel Laporan Pengembalian -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Laporan Pengembalian Barang</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTablePengembalian" width="100%">
                                <thead>
                                    <tr>
                                        <th>ID Pengembalian</th>
                                        <th>ID Transaksi</th>
                                        <th>Kondisi Barang</th>
                                        <th>Bukti Pengembalian</th>
                                        <th>Bukti Denda</th>
                                        <th>Total Denda</th>
                                        <th>Tanggal Pengembalian</th>
                                        <th>Status Pengembalian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($pengembalian)): ?>
                                        <tr>
                                            <td><?= $row['id_pengembalian'] ?></td>
                                            <td><?= $row['id_transaksi'] ?></td>
                                            <td><?= htmlspecialchars($row['kondisi_barang']) ?></td>
                                           <td>
                                            <?php if ($row['bukti_pengembalian']): ?>
                                                <button 
                                                    class="btn btn-sm btn-info btn-bukti" 
                                                    data-toggle="modal" 
                                                    data-target="#modalBukti" 
                                                    data-img="../uploads/pengembalian/<?= htmlspecialchars($row['bukti_pengembalian']) ?>">
                                                    Lihat
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                            <td>
                                            <?php if ($row['bukti_denda']): ?>
                                                <button 
                                                    class="btn btn-sm btn-info btn-bukti" 
                                                    data-toggle="modal" 
                                                    data-target="#modalBukti" 
                                                    data-img="../uploads/denda/<?= htmlspecialchars($row['bukti_denda']) ?>">
                                                    Lihat
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                            <td>Rp.<?= number_format($row['total_denda'], 2, ',', '.') ?></td>
                                            <td><?= $row['tanggal_pengembalian'] ?></td>
                                            <td><?= htmlspecialchars($row['status_pengembalian']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
 <div class="modal fade" id="modalBukti" tabindex="-1" role="dialog" aria-labelledby="modalBuktiLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalBuktiLabel">Pratinjau Bukti</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <img id="imgBukti" src="" class="img-fluid" alt="Bukti Gambar">
                        </div>
                    </div>
                </div>
            </div>
            </div> <!-- /.container-fluid -->
        </div> <!-- End of Main Content -->

       
    </div> <!-- End of Content Wrapper -->
</div> <!-- End of Page Wrapper -->

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

<!-- Page level plugins -->
<script src="../assets/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Page level custom scripts -->
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
        $('#dataTablePengembalian').DataTable();
    });

    const ctx = document.getElementById('chartMetode').getContext('2d');
    const chartMetode = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Total Pendapatan',
                data: <?= json_encode($chart_data) ?>,
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b',
                    '#858796'
                ],
                hoverOffset: 4
            }]
        }
    });
</script>

</body>
</html>
