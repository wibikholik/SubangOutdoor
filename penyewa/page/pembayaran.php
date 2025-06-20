<?php
include '../../route/koneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='../../login.php';</script>";
    exit;
}
$id_penyewa = $_SESSION['user_id'];

$id_transaksi = isset($_GET['id_transaksi']) ? intval($_GET['id_transaksi']) : null;
if (!$id_transaksi) {
    die("ID transaksi tidak valid.");
}

$stmt = $koneksi->prepare("
    SELECT t.*, mp.nama_metode, mp.nomor_rekening, mp.gambar_metode
    FROM transaksi t
    JOIN metode_pembayaran mp ON t.id_metode = mp.id_metode
    WHERE t.id_transaksi = ? AND t.id_penyewa = ?
");
if (!$stmt) {
    die("Prepare failed: " . $koneksi->error);
}
$stmt->bind_param("ii", $id_transaksi, $id_penyewa);
$stmt->execute();
$result_transaksi = $stmt->get_result();
if (!$result_transaksi || $result_transaksi->num_rows === 0) {
    die("Transaksi tidak ditemukan atau bukan milik Anda.");
}
$transaksi = $result_transaksi->fetch_assoc();

$stmt_detail = $koneksi->prepare("
    SELECT dt.*, b.nama_barang, b.gambar 
    FROM detail_transaksi dt
    JOIN barang b ON dt.id_barang = b.id_barang
    WHERE dt.id_transaksi = ?
");
if (!$stmt_detail) {
    die("Prepare failed: " . $koneksi->error);
}
$stmt_detail->bind_param("i", $id_transaksi);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

$stmt_penyewa = $koneksi->prepare("SELECT nama_penyewa, no_hp, alamat FROM penyewa WHERE id_penyewa = ?");
$stmt_penyewa->bind_param("s", $id_penyewa);
$stmt_penyewa->execute();
$result_penyewa = $stmt_penyewa->get_result();
$penyewa = $result_penyewa->fetch_assoc();
$stmt_penyewa->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Pembayaran - Subang Outdoor<?= htmlspecialchars($id_transaksi) ?></title>
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css" />
  <link rel="stylesheet" href="css/linearicons.css">
  <link rel="stylesheet" href="css/owl.carousel.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/themify-icons.css">
  <link rel="stylesheet" href="css/nice-select.css">
  <link rel="stylesheet" href="css/nouislider.min.css">
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/main.css">
   <link rel="shortcut icon" href="../../assets/img/logo.jpg">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .barang-img {
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
    }
  </style>
</head>
<body>

<?php include("../layout/navbar1.php"); ?>
<section class="banner-area organic-breadcrumb">
  <div class="container">
    <div class="breadcrumb-banner d-flex flex-wrap align-items-center justify-content-end">
      <div class="col-first">
        <h1>Subang Outdoor</h1>
        <nav class="d-flex align-items-center">
          <p>Pembayaran:</p>
          <p>Nomor Transaksi: <strong><?= htmlspecialchars($id_transaksi) ?></strong></p>
        </nav>
      </div>
    </div>
  </div>
</section>

<div class="w3-container w3-padding-32">
  <div class="w3-padding-32 w3-round-large w3-margin-bottom">

    <?php if (isset($error_upload)) : ?>
      <div class="w3-panel w3-red w3-round"><?= htmlspecialchars($error_upload) ?></div>
    <?php elseif (isset($success_upload)) : ?>
      <div class="w3-panel w3-green w3-round"><?= htmlspecialchars($success_upload) ?></div>
    <?php endif; ?>

    <div class="w3-row-padding w3-margin-top">
      <!-- Informasi Transaksi -->
      <div class="w3-half">
        <div class="w3-card w3-padding w3-round-large">
          <h4>Informasi Transaksi</h4>
          <p><strong>Total Bayar:</strong><br> Rp <?= number_format($transaksi['total_harga_sewa'], 0, ',', '.') ?></p>
          <p><strong>Status:</strong> <?= htmlspecialchars($transaksi['status']) ?></p>

          <h4>Metode Pembayaran</h4>
          <img src="../../metode_pembayaran/metode/gambar/<?= htmlspecialchars($transaksi['gambar_metode']) ?>" 
               alt="<?= htmlspecialchars($transaksi['nama_metode']) ?>" 
               style="height: 50px;" class="w3-margin-bottom">
          <p>
            <strong>Nama Penyewa:</strong> <?= htmlspecialchars($penyewa['nama_penyewa']) ?><br>
            <strong>No. HP:</strong> <?= htmlspecialchars($penyewa['no_hp']) ?><br>
            <strong>Alamat:</strong> <small><?= htmlspecialchars($penyewa['alamat']) ?></small>
          </p>
        </div>
      </div>

      <!-- Daftar Barang -->
      <div class="w3-half">
        <div class="w3-card w3-padding w3-round-large">
          <h4>Daftar Barang</h4>
          <table class="w3-table w3-striped w3-bordered">
            <thead>
              <tr class="w3-light-grey">
                <th>Gambar</th>
                <th>Nama</th>
                <th>Jumlah</th>
                <th>Harga</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result_detail->fetch_assoc()) : ?>
              <tr>
                <td><img src="../../barang/barang/gambar/<?= htmlspecialchars($row['gambar']) ?>" class="barang-img" alt="<?= htmlspecialchars($row['nama_barang']) ?>"></td>
                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                <td><?= (int)$row['jumlah_barang'] ?></td>
                <td>Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Upload Bukti -->
    <div class="w3-card w3-padding w3-margin-top w3-round-large">
      <h4>Upload Bukti Pembayaran</h4>
      <div class="alert alert-warning"><h5>Silakan transfer ke rekening berikut:</h5>
      <p>Nomor Rekening: <?= htmlspecialchars($transaksi['nomor_rekening']) ?></p>
    </div>
      
      <form action="../controller/prosesPembayaran.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_transaksi" value="<?= htmlspecialchars($id_transaksi) ?>">
        <input class="w3-input w3-border w3-margin-bottom" type="file" name="bukti_pembayaran" required accept="image/*,application/pdf" />
        <button type="submit" class="w3-button w3-green w3-round">Upload</button>
      </form>
    </div>
  </div>
</div>

<?php include ('../layout/footer.php');?>

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
</html>
