<?php
include '../../route/koneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='../../login.php';</script>";
    exit;
}

$id_penyewa = $_SESSION['user_id'];
$id_penyewa_esc = mysqli_real_escape_string($koneksi, $id_penyewa);

$query_transaksi = "
    SELECT t.*, mp.nama_metode, mp.gambar_metode, mp.nomor_rekening
    FROM transaksi t
    JOIN metode_pembayaran mp ON t.id_metode = mp.id_metode
    WHERE t.id_penyewa = ?
    ORDER BY t.id_transaksi DESC
";

$stmt = $koneksi->prepare($query_transaksi);
if (!$stmt) {
    die("Prepare query transaksi gagal: " . $koneksi->error);
}
$stmt->bind_param("i", $id_penyewa);
$stmt->execute();
$result_transaksi = $stmt->get_result();

if (!$result_transaksi) {
    die("Query gagal: " . $koneksi->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Histori Transaksi</title>
  <link rel="stylesheet" href="css/linearicons.css">
  <link rel="stylesheet" href="css/owl.carousel.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/themify-icons.css">
  <link rel="stylesheet" href="css/nice-select.css">
  <link rel="stylesheet" href="css/nouislider.min.css">
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/main.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<?php include("../layout/navbar1.php"); ?>
<section class="banner-area organic-breadcrumb">
        <div class="container">
            <div class="breadcrumb-banner d-flex flex-wrap align-items-center justify-content-end">
                <div class="col-first">
                    <h1>Subang Outdoor</h1>
                    <nav class="d-flex align-items-center">
                        <a href="#">Histori Penyewaan</a>
                    </nav>
                </div>
            </div>
        </div>
    </section>

<div class="container mt-4 ">
  <h4>Histori Transaksi Anda</h4>

  <?php if ($result_transaksi->num_rows < 1): ?>
    <div class="alert alert-info">Belum ada transaksi.</div>
  <?php endif; ?>

  <div class="d-flex flex-wrap gap-3">
    <?php while ($transaksi = $result_transaksi->fetch_assoc()) : ?>
      <div class="card mb-4 shadow-sm" style="min-width: 350px; max-width: 500px; flex: 1;">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <strong>ID Transaksi: <?= htmlspecialchars($transaksi['id_transaksi']); ?></strong><br>
            Status: 
            <span class="badge <?= ($transaksi['status'] === 'Belum Dibayar') ? 'bg-warning text-dark' : 'bg-success'; ?>">
              <?= htmlspecialchars($transaksi['status']); ?>
            </span><br>
            <small>Metode: <?= htmlspecialchars($transaksi['nama_metode']); ?></small>
          </div>
        </div>
        <div class="card-body">
          <?php
          $id_transaksi = $transaksi['id_transaksi'];
          $tanggal_sewa = new DateTime($transaksi['tanggal_sewa']);
          $tanggal_kembali = new DateTime($transaksi['tanggal_kembali']);
          $lama_sewa = $tanggal_sewa->diff($tanggal_kembali)->days;

          $query_detail = "
            SELECT dt.*, b.nama_barang, b.gambar 
            FROM detail_transaksi dt
            JOIN barang b ON dt.id_barang = b.id_barang
            WHERE dt.id_transaksi = ?
          ";
          $stmt_detail = $koneksi->prepare($query_detail);
          if (!$stmt_detail) {
              echo "<div class='alert alert-danger'>Query detail gagal: " . htmlspecialchars($koneksi->error) . "</div>";
          } else {
              $stmt_detail->bind_param("i", $id_transaksi);
              $stmt_detail->execute();
              $result_detail = $stmt_detail->get_result();
          }
          ?>

          <div><strong>Periode Sewa:</strong> <?= $tanggal_sewa->format('d M Y'); ?> - <?= $tanggal_kembali->format('d M Y'); ?></div>
          <div><strong>Lama Sewa:</strong> <?= $lama_sewa; ?> hari</div>

          <?php if (!empty($result_detail) && $result_detail->num_rows > 0): ?>
            <table class="table mt-2">
              <thead>
                <tr>
                  <th>Gambar</th>
                  <th>Nama Barang</th>
                  <th>Jumlah</th>
                  <th>Harga Satuan</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $result_detail->fetch_assoc()) : ?>
                  <tr>
                    <td><img src="../../barang/barang/gambar/<?= htmlspecialchars($row['gambar']); ?>" alt="Barang" style="width: 80px;"></td>
                    <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td><?= htmlspecialchars($row['jumlah_barang']); ?></td>
                    <td>Rp<?= number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="text-muted">Tidak ada detail barang.</div>
          <?php endif; ?>

          <div class="d-flex justify-content-between mt-3 align-items-center">
            <div><strong>Total:</strong> Rp<?= number_format($transaksi['total_harga_sewa'], 0, ',', '.'); ?></div>
            <div>
              <?php if (strtolower(str_replace(' ', '', $transaksi['status'])) === 'belumbayar') : ?>
                <form action="pembayaran.php" method="GET" class="d-inline">
                  <input type="hidden" name="id_transaksi" value="<?= $transaksi['id_transaksi']; ?>">
                  <button type="submit" class="btn btn-primary btn-sm">Bayar Sekarang</button>
                </form>
              <?php else : ?>
                <span class="text-success">Sudah dibayar</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script src="js/vendor/jquery-2.2.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="js/vendor/bootstrap.min.js"></script>
<script src="js/jquery.ajaxchimp.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src="js/jquery.sticky.js"></script>
<script src="js/nouislider.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/gmaps.min.js"></script>
<script src="js/main.js"></script>
<?php include ('../layout/footer.php'); ?>
</html>
