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

// Menangani pesan notifikasi dari session
$notification_message = '';
if (isset($_SESSION['notification'])) {
    $notification_message = $_SESSION['notification'];
    unset($_SESSION['notification']);
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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet" />
    <link href="../assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
<?php include('../layout/sidebar.php'); ?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php include('../layout/navbar.php'); ?>

        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Daftar Pengembalian</h1>

            <?php if ($notification_message): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($notification_message) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Transaksi</th>
                                    <th>Penyewa</th>
                                    <th>Tanggal</th>
                                    <th>Denda</th>
                                    <th>Kondisi</th>
                                    <th>Bukti Kembali</th>
                                    <th>Bukti Denda</th>
                                    <th>Status</th>
                                    <th style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pengembalianList as $row): ?>
                                    <?php
                                        $status = strtolower($row['status_pengembalian']);
                                        $badge_class = match($status) {
                                            'menunggu konfirmasi pengembalian' => 'warning',
                                            'selesai dikembalikan' => 'success',
                                            'ditolak' => 'danger',
                                            default => 'secondary',
                                        };
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id_pengembalian']) ?></td>
                                        <td><?= htmlspecialchars($row['id_transaksi']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_penyewa']) ?></td>
                                        <td><?= date('d M Y', strtotime($row['tanggal_pengembalian'])) ?></td>
                                        <td>Rp <?= number_format($row['total_denda'], 0, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($row['kondisi_barang']) ?></td>
                                        <td>
                                            <?php if ($row['bukti_pengembalian']): ?>
                                                <button 
                                                    class="btn btn-sm btn-info btn-bukti" 
                                                    data-toggle="modal" 
                                                    data-target="#modalBukti" 
                                                    data-img="../uploads/pengembalian/<?= htmlspecialchars($row['bukti_pengembalian']) ?>"
                                                    data-title="Bukti Pengembalian"
                                                    data-toggle="tooltip" title="Lihat Bukti Pengembalian">
                                                    <i class="fas fa-eye"></i>
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
                                                    data-img="../uploads/denda/<?= htmlspecialchars($row['bukti_denda']) ?>"
                                                    data-title="Bukti Pembayaran Denda"
                                                    data-toggle="tooltip" title="Lihat Bukti Denda">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $badge_class ?>">
                                                <?= ucwords(str_replace('_', ' ', $status)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($status == 'menunggu konfirmasi pengembalian'): ?>
                                                <a href="update_pengembalian.php?id=<?= $row['id_pengembalian'] ?>&status=Selesai Dikembalikan" class="btn btn-sm btn-success" data-toggle="tooltip" title="Setujui Pengembalian">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="update_pengembalian.php?id=<?= $row['id_pengembalian'] ?>&status=Ditolak" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Tolak Pengembalian">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <form action="hapus_pengembalian.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data pengembalian ini?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="id_pengembalian" value="<?= htmlspecialchars($row['id_pengembalian']) ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Hapus Permanen">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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

        </div>
    </div>
</div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../assets/js/sb-admin-2.min.js"></script>
<script src="../assets/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function(){
    // Inisialisasi DataTable
    $('#dataTable').DataTable({
        "order": [[0, "desc"]], // Urutkan berdasarkan ID Pengembalian (index 0)
        "columnDefs": [
            // Menonaktifkan pengurutan untuk kolom-kolom tertentu
            { "orderable": false, "targets": [4, 5, 6, 7, 9] } 
        ]
    });

    // Inisialisasi Tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // Fungsi untuk menampilkan gambar di modal
    $('.btn-bukti').on('click', function(){
        var imgSrc = $(this).data('img');
        var imgTitle = $(this).data('title'); // Ambil judul dari tombol
        var modal = $('#modalBukti');
        modal.find('#imgBukti').attr('src', imgSrc);
        modal.find('.modal-title').text(imgTitle); // Set judul modal
    });
});
</script>

</body>
</html>