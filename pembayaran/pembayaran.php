<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki peran admin/owner
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../login.php');
    exit;
}

include '../route/koneksi.php';

$query = "
SELECT 
    b.id_pembayaran,
    t.id_transaksi,
    p.nama_penyewa,
    t.tanggal_sewa,
    t.total_harga_sewa,
    b.bukti_pembayaran,
    b.status_pembayaran,
    b.tanggal_pembayaran
FROM pembayaran b
JOIN transaksi t ON b.id_transaksi = t.id_transaksi
JOIN penyewa p ON t.id_penyewa = p.id_penyewa
ORDER BY b.tanggal_pembayaran DESC
";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query pembayaran gagal: " . mysqli_error($koneksi));
}

$pembayaranList = [];

while ($row = mysqli_fetch_assoc($result)) {
    $pembayaranList[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Daftar Pembayaran - Subang Outdoor</title>
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css" />
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
                <h1 class="h3 mb-4 text-gray-800">Daftar Pembayaran</h1>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID Pembayaran</th>
                                        <th>ID Transaksi</th>
                                        <th>Nama Penyewa</th>
                                        <th>Tanggal Sewa</th>
                                        <th>Total Pembayaran</th>
                                        <th>Bukti</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pembayaranList as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id_pembayaran']) ?></td>
                                            <td><?= htmlspecialchars($row['id_transaksi']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_penyewa']) ?></td>
                                            <td><?= htmlspecialchars($row['tanggal_sewa']) ?></td>
                                            <td>Rp <?= number_format($row['total_harga_sewa'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php if ($row['bukti_pembayaran']): ?>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-sm btn-info btn-bukti" 
                                                        data-toggle="modal" 
                                                        data-target="#modalBukti"
                                                        data-img="../uploads/bukti/<?= htmlspecialchars($row['bukti_pembayaran']) ?>"
                                                    >
                                                        Lihat
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $status = strtolower($row['status_pembayaran']);
                                                    $badge_class = match($status) {
                                                        'menunggu konfirmasi' => 'warning',
                                                        'dikonfirmasi' => 'info',
                                                        'selesai' => 'success',
                                                        'batal' => 'danger',
                                                        default => 'secondary',
                                                    };
                                                ?>
                                                <span class="badge badge-<?= $badge_class ?>">
                                                    <?= htmlspecialchars(ucfirst($row['status_pembayaran'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Bukti Pembayaran (hanya satu modal) -->
                <div class="modal fade" id="modalBukti" tabindex="-1" aria-labelledby="modalBuktiLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modalBuktiLabel">Bukti Pembayaran</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body text-center">
                        <img src="" id="imgBukti" class="img-fluid" alt="Bukti Pembayaran" />
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

<!-- JS -->
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
