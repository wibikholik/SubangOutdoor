<?php
session_start();

// Cek login dan role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'owner')) {
    header('Location: ../login.php');
    exit;
}

include '../route/koneksi.php';

// Ambil data pengembalian dengan join transaksi, penyewa, dan detail barang
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
    GROUP_CONCAT(CONCAT(b.nama_barang, ' (', dt.jumlah_barang, ')') SEPARATOR ', ') AS detail_barang
FROM pengembalian p
JOIN transaksi t ON p.id_transaksi = t.id_transaksi
JOIN penyewa u ON t.id_penyewa = u.id_penyewa
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
JOIN barang b ON dt.id_barang = b.id_barang
GROUP BY p.id_pengembalian
ORDER BY p.id_pengembalian DESC
";

$result_pengembalian = mysqli_query($koneksi, $query);

if (!$result_pengembalian) {
    die('Query Error: ' . mysqli_error($koneksi));
}

// Generate CSRF token untuk form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Subang Outdoor - Daftar Pengembalian</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
  <?php include('../layout/sidebar.php'); ?>
  <div style="margin-left:25%">
    <?php include('../layout/navbar.php'); ?>
    <div class="container mt-4">
      <h3 class="mb-4">Daftar Pengembalian Barang</h3>

      <?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
        <div class="alert alert-success">Status pengembalian berhasil diperbarui.</div>
      <?php elseif (isset($_GET['error']) && $_GET['error'] === 'invalid_input'): ?>
        <div class="alert alert-danger">Input tidak valid.</div>
      <?php elseif (isset($_GET['error']) && $_GET['error'] === 'update_failed'): ?>
        <div class="alert alert-danger">Gagal memperbarui status pengembalian.</div>
      <?php endif; ?>

      <?php if (mysqli_num_rows($result_pengembalian) === 0): ?>
        <div class="alert alert-info">Belum ada data pengembalian.</div>
      <?php else: ?>
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>ID Pengembalian</th>
              <th>ID Transaksi</th>
              <th>Nama Penyewa</th>
              <th>Detail Barang</th>
              <th>Kondisi Barang</th>
              <th>Bukti Pengembalian</th>
              <th>Bukti Denda</th>
              <th>Total Denda</th>
              <th>Status Pengembalian</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($result_pengembalian)) : ?>
              <?php
                $status = $row['status_pengembalian'];
                switch (strtolower($status)) {
                    case 'menunggu konfirmasi pengembalian':
                        $badgeClass = 'bg-warning text-dark';
                        $statusLabel = 'Menunggu Konfirmasi Pengembalian';
                        break;
                    case 'selesai dikembalikan':
                        $badgeClass = 'bg-success';
                        $statusLabel = 'Selesai Dikembalikan';
                        break;
                    case 'ditolak':
                        $badgeClass = 'bg-danger';
                        $statusLabel = 'Ditolak';
                        break;
                    default:
                        $badgeClass = 'bg-secondary';
                        $statusLabel = ucfirst($status);
                        break;
                }
              ?>
              <tr>
                <td><?= htmlspecialchars($row['id_pengembalian']); ?></td>
                <td><?= htmlspecialchars($row['id_transaksi']); ?></td>
                <td><?= htmlspecialchars($row['nama_penyewa']); ?></td>
                <td><?= htmlspecialchars($row['detail_barang']); ?></td>
                <td><?= htmlspecialchars($row['kondisi_barang']); ?></td>
                <td>
                  <?php if ($row['bukti_pengembalian']) : ?>
                    <button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#modalPengembalian<?= $row['id_pengembalian'] ?>">Lihat</button>
                    <div class="modal fade" id="modalPengembalian<?= $row['id_pengembalian'] ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Bukti Pengembalian</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body text-center">
                            <img src="../uploads/pengembalian/<?= htmlspecialchars($row['bukti_pengembalian']); ?>" alt="Bukti Pengembalian" class="img-fluid" />
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if ($row['bukti_denda']) : ?>
                    <button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#modalDenda<?= $row['id_pengembalian'] ?>">Lihat</button>
                    <div class="modal fade" id="modalDenda<?= $row['id_pengembalian'] ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Bukti Denda</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body text-center">
                            <img src="../uploads/denda/<?= htmlspecialchars($row['bukti_denda']); ?>" alt="Bukti Denda" class="img-fluid" />
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>

                <td>Rp<?= number_format($row['total_denda'], 0, ',', '.'); ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= $statusLabel; ?></span></td>
                <td>
                  <form method="post" action="update_status.php" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="id_pengembalian" value="<?= $row['id_pengembalian']; ?>">
                    <input type="hidden" name="id_transaksi" value="<?= $row['id_transaksi']; ?>">
                    <select name="status_baru" class="form-select form-select-sm" onchange="this.form.submit()" required>
                      <option disabled selected>Pilih Status</option>
                      <option value="Menunggu Konfirmasi Pengembalian" <?= strtolower($status) === 'menunggu konfirmasi pengembalian' ? 'disabled' : '' ?>>Menunggu Konfirmasi Pengembalian</option>
                      <option value="Selesai Dikembalikan" <?= strtolower($status) === 'selesai dikembalikan' ? 'disabled' : '' ?>>Selesai Dikembalikan</option>
                      <option value="Ditolak" <?= strtolower($status) === 'ditolak' ? 'disabled' : '' ?>>Ditolak</option>
                    </select>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
