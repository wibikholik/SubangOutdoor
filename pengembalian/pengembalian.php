<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../login.php');
    exit;
}
include '../route/koneksi.php';

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$query = "
SELECT 
    p.id_pengembalian,
    p.id_transaksi,
    u.nama_penyewa,
    p.kondisi_barang,
    p.bukti_pengembalian,
    p.bukti_denda,
    p.total_denda,
    p.status_pengembalian,
    p.tanggal_pengembalian,
    GROUP_CONCAT(CONCAT(b.nama_barang, ' (', dt.jumlah_barang, ')') SEPARATOR ', ') AS detail_barang
FROM pengembalian p
JOIN transaksi t ON p.id_transaksi = t.id_transaksi
JOIN penyewa u ON t.id_penyewa = u.id_penyewa
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
JOIN barang b ON dt.id_barang = b.id_barang
GROUP BY p.id_pengembalian
ORDER BY p.id_pengembalian DESC
";

$result = mysqli_query($koneksi, $query);
if (!$result) {
    die("Query pengembalian gagal: " . mysqli_error($koneksi));
}
$pengembalianList = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pengembalianList[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Daftar Pengembalian - Subang Outdoor</title>
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" />
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body id="page-top">
<div id="wrapper">
<?php include('../layout/sidebar.php'); ?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php include('../layout/navbar.php'); ?>

        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Daftar Pengembalian</h1>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Transaksi</th>
                                    <th>Nama Penyewa</th>
                                    <th>Tanggal Pengembalian</th>
                                    <th>Denda</th>
                                    <th>Kondisi</th>
                                    <th>Bukti Pengembalian</th>
                                    <th>Bukti Bayar Denda</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pengembalianList as $row): ?>
                                    <?php
                                        $status = strtolower($row['status_pengembalian']);
                                        $badge_class = match($status) {
                                            'menunggu konfirmasi pengembalian' => 'warning',
                                            'Selesai Dikembalikan' => 'success',
                                            'ditolak' => 'danger',
                                            default => 'secondary',
                                        };
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id_pengembalian']) ?></td>
                                        <td><?= htmlspecialchars($row['id_transaksi']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_penyewa']) ?></td>
                                        <td><?= htmlspecialchars($row['tanggal_pengembalian']) ?></td>
                                        <td>Rp <?= number_format($row['total_denda'], 0, ',', '.') ?></td>
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
                                        <td>
                                            <span class="badge badge-<?= $badge_class ?>">
                                                <?= ucwords($row['status_pengembalian']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form action="update_status.php" method="POST" style="min-width:150px;">
                                                <!-- CSRF token -->
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <!-- ID pengembalian -->
                                                <input type="hidden" name="id_pengembalian" value="<?= htmlspecialchars($row['id_pengembalian']) ?>">
                                                <!-- ID transaksi -->
                                                <input type="hidden" name="id_transaksi" value="<?= htmlspecialchars($row['id_transaksi']) ?>">

                                                <select name="status_baru" class="form-control form-control-sm" onchange="this.form.submit()">
                                                    <option value="Menunggu Konfirmasi Pengembalian" <?= $row['status_pengembalian'] === 'Menunggu Konfirmasi Pengembalian' ? 'selected' : '' ?>>Menunggu Konfirmasi Pengembalian</option>
                                                    <option value="Selesai Dikembalikan" <?= $row['status_pengembalian'] === 'Selesai Dikembalikan' ? 'selected' : '' ?>>Selesai</option>
                                                    <option value="Ditolak Pengembalian" <?= $row['status_pengembalian'] === 'Ditolak Pengembalian' ? 'selected' : '' ?>>Ditolak Pengembalian</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Bukti Global -->
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

        </div>
    </div>
</div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/sb-admin-2.min.js"></script>

<script>
$(document).ready(function(){
    $('.btn-bukti').click(function(){
        var imgSrc = $(this).data('img');
        $('#imgBukti').attr('src', imgSrc);
    });
});
</script>

</body>
</html>
