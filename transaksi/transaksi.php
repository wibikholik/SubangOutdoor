<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'owner')) {
    header('Location: ../login.php');
    exit;
}

include '../route/koneksi.php';

// Update otomatis status terlambat dikembalikan
$today_str = date('Y-m-d');
$update_query = "
    UPDATE transaksi 
    SET status = 'terlambat dikembalikan' 
    WHERE status IN ('disewa', 'di ambil barang') 
      AND tanggal_kembali < '$today_str'
";
mysqli_query($koneksi, $update_query);

// Ambil data transaksi setelah update status
$query_transaksi = "
    SELECT t.*, mp.nama_metode
    FROM transaksi t
    JOIN metode_pembayaran mp ON t.id_metode = mp.id_metode
    ORDER BY t.id_transaksi DESC
";
$result_transaksi = mysqli_query($koneksi, $query_transaksi);
if (!$result_transaksi) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Admin - Daftar Transaksi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <?php include('../layout/sidebar.php'); ?>
    <!-- sidebar -->

    <div style="margin-left:25%">
        <?php include('../layout/navbar.php'); ?>
      <h3 class="my-4">Daftar Semua Transaksi</h3>
      
      <?php if (mysqli_num_rows($result_transaksi) === 0): ?>
        <div class="alert alert-info">Belum ada transaksi.</div>
      <?php else: ?>
      
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th>ID Transaksi</th>
            <th>ID Penyewa</th>
            <th>Periode Sewa</th>
            <th>Lama Sewa</th>
            <th>Metode Pembayaran</th>
            <th>Status</th>
            <th>Detail Barang</th>
            <th>Total Harga</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($transaksi = mysqli_fetch_assoc($result_transaksi)) : ?>
            <?php
              $tanggal_sewa = new DateTime($transaksi['tanggal_sewa']);
              $tanggal_kembali = new DateTime($transaksi['tanggal_kembali']);
              $lama_sewa = $tanggal_sewa->diff($tanggal_kembali)->days;

              $id_transaksi = $transaksi['id_transaksi'];
              $stmt_detail = $koneksi->prepare("
                SELECT dt.jumlah_barang, b.nama_barang, dt.harga_satuan
                FROM detail_transaksi dt
                JOIN barang b ON dt.id_barang = b.id_barang
                WHERE dt.id_transaksi = ?
              ");
              if (!$stmt_detail) {
                  die("Prepare statement gagal: " . $koneksi->error);
              }
              $stmt_detail->bind_param("i", $id_transaksi);

              if (!$stmt_detail->execute()) {
                  die("Eksekusi query gagal: " . $stmt_detail->error);
              }

              $result_detail = $stmt_detail->get_result();
              if (!$result_detail) {
                  die("Ambil hasil query gagal: " . $stmt_detail->error);
              }

              $status = strtolower(trim($transaksi['status']));

              // Badge dan label status
              switch ($status) {
                  case 'menunggu konfirmasi':
                      $badgeClass = 'bg-warning text-dark';
                      $statusLabel = 'Menunggu Konfirmasi';
                      break;
                  case 'dikonfirmasi':
                      $badgeClass = 'bg-primary';
                      $statusLabel = 'Dikonfirmasi (Silahkan Ambil Barang)';
                      break;
                  case 'disewa':
                  case 'di ambil barang':
                      $badgeClass = 'bg-info text-dark';
                      $statusLabel = 'Disewa / Di Ambil Barang';
                      break;
                  case 'terlambat dikembalikan':
                      $badgeClass = 'bg-danger';
                      $statusLabel = 'Terlambat Dikembalikan';
                      break;
                  case 'selesai':
                      $badgeClass = 'bg-success';
                      $statusLabel = 'Selesai';
                      break;
                  case 'batal':
                      $badgeClass = 'bg-secondary';
                      $statusLabel = 'Batal';
                      break;
                  default:
                      $badgeClass = 'bg-secondary';
                      $statusLabel = ucfirst($status);
                      break;
              }
            ?>
            <tr>
              <td><?= htmlspecialchars($transaksi['id_transaksi']); ?></td>
              <td><?= htmlspecialchars($transaksi['id_penyewa']); ?></td>
              <td><?= htmlspecialchars($tanggal_sewa->format('d M Y') . " - " . $tanggal_kembali->format('d M Y')); ?></td>
              <td><?= htmlspecialchars($lama_sewa . ' hari'); ?></td>
              <td><?= htmlspecialchars($transaksi['nama_metode']); ?></td>
              <td>
                <span class="badge <?= $badgeClass ?>">
                  <?= htmlspecialchars($statusLabel); ?>
                </span>
              </td>
              <td>
                <ul class="mb-0">
                  <?php while ($detail = $result_detail->fetch_assoc()) : ?>
                   <li>
                    <?= htmlspecialchars($detail['nama_barang']) . 
                        " (x" . htmlspecialchars($detail['jumlah_barang']) . ")" . 
                        " Rp" . number_format($detail['harga_satuan'], 0, ',', '.') ?>
                    </li>
                  <?php endwhile; ?>
                </ul>
              </td> 
              <td>Rp<?= number_format($transaksi['total_harga_sewa'], 0, ',', '.'); ?></td>
              <td>
                <form method="POST" action="update_status.php" class="d-inline">
                  <input type="hidden" name="id_transaksi" value="<?= htmlspecialchars($transaksi['id_transaksi']); ?>">
                  <select name="status_baru" onchange="this.form.submit()" class="form-select form-select-sm">
                    <option value="menunggu konfirmasi" <?= $status === 'menunggu konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                    <option value="dikonfirmasi" <?= $status === 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi (Silahkan Ambil Barang)</option>
                    <option value="disewa" <?= $status === 'disewa' ? 'selected' : '' ?>>Disewa / Di Ambil Barang</option>
                    <option value="di ambil barang" <?= $status === 'di ambil barang' ? 'selected' : '' ?>>Di Ambil Barang</option>
                    <option value="terlambat dikembalikan" <?= $status === 'terlambat dikembalikan' ? 'selected' : '' ?>>Terlambat Dikembalikan</option>
                    <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="batal" <?= $status === 'batal' ? 'selected' : '' ?>>Batal</option>
                  </select>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
