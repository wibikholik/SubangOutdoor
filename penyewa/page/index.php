<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: ../../login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Subang Outdoor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      font-family: sans-serif;
      background-color: #fff;
    }

    .header {
      height: 400px;
      background: #d9d9d9;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 2rem;
    }

    .logo-container {
      width: 120px;
      height: 120px;
      background-color: #c9c9c9;
      margin-bottom: 1rem;
      border-radius: 50%;
    }

    .title {
      font-size: 2rem;
      font-weight: bold;
      color: #1d1d1d;
    }

    .subtitle {
      font-size: 1.5rem;
      font-weight: bold;
      color: #1d1d1d;
    }

    .tagline {
      margin-top: 1rem;
      font-size: 1.1rem;
      color: #1d1d1d;
    }

    .highlight {
      font-weight: 600;
      color: #198754;
    }

    .card-img-top {
      height: 180px;
      object-fit: cover;
    }

    .card {
      border-radius: 0.75rem;
    }
  </style>
</head>
<body>
  <?php include("../layout/navbar.php"); ?>

  <!-- Header -->
  <div class="header">
    <div class="logo-container"></div>
    <div class="title">SUBANG OUTDOOR</div>
    <div class="subtitle">BACK TO NATURE</div>
    <div class="tagline">
      Tempat Sewa Alat Camping <span class="highlight">Terpercaya Di Subang</span>
    </div>
  </div>

  <!-- Section Barang -->
  <div class="container my-5">
    <h2 class="mb-4 text-center fw-bold">Daftar Barang yang Tersedia</h2>
    <div class="row g-4">
      <?php
      include '../../route/koneksi.php';
      $query = "SELECT * FROM barang";
      $result = mysqli_query($koneksi, $query);

      if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
      ?>
          <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm">
              <img src="../../barang/barang/gambar/<?php echo $row['gambar']; ?>" class="card-img-top" alt="<?php echo $row['nama_barang']; ?>">
              <div class="card-body">
                <h5 class="card-title"><?php echo $row['nama_barang']; ?></h5>
                <p class="card-text"><?php echo $row['keterangan']; ?></p>
                <p class="card-text"><strong>Kategori:</strong> <?php echo $row['kategori']; ?></p>
                <p class="card-text"><strong>Stok:</strong> <?php echo $row['stok']; ?></p>
                <p class="card-text text-success"><strong>Rp <?php echo number_format($row['harga_sewa'], 0, ',', '.'); ?></strong></p>
                <a href="#" class="btn btn-success w-100">+ Keranjang</a>
              </div>
            </div>
          </div>
      <?php
        }
      } else {
        echo "<p class='text-center'>Tidak ada data barang.</p>";
      }
      ?>
    </div>
  </div>

  <?php include('../layout/footer.php'); ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
