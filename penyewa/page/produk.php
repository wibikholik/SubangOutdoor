<!DOCTYPE html>
<html lang="zxx" class="no-js">

<head>
  <style>
    .produk-unggulan {
      flex: 1;
      min-width: 250px;
    }

    .produk-unggulan h3 {
      border-bottom: 2px solid #000;
      padding-bottom: 5px;
      margin-bottom: 15px;
    }

    .unggulan-item {
      display: flex;
      align-items: center;
      
      padding: 10px;
      margin-bottom: 10px;
     
    }

    .unggulan-item img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      margin-right: 10px;
      border-radius: 5px;
    }

    .harga {
      font-weight: bold;
    }

    @media (max-width: 768px) {
      .produk-unggulan {
        order: -1;
        margin-bottom: 20px;
      }
    }
  </style>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="shortcut icon" href="img/fav.png">
  <meta name="author" content="CodePixar">
  <meta name="description" content="">
  <meta name="keywords" content="">
  <meta charset="UTF-8">
  <title>Product - Subang Outdoor</title>
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

<body id="category">

  <?php include("../layout/navbar1.php")?>

  <section class="banner-area organic-breadcrumb">
    <div class="container">
      <div class="breadcrumb-banner d-flex flex-wrap align-items-center justify-content-end">
        <div class="col-first">
          <h1>Subang Outdoor</h1>
          <nav class="d-flex align-items-center">
            <a href="#">Product</span></a>
          </nav>
        </div>
      </div>
    </div>
  </section>

  <div class="container mt-4">
    <div class="row">
      <!-- Katalog Produk -->
      <div class="col-xl-9 col-lg-8 col-md-7">
        <div class="filter-bar d-flex flex-wrap align-items-center">
          <div class="sorting">
            <select>
              <option value="1">Default sorting</option>
              <option value="2">Price: Low to High</option>
              <option value="3">Price: High to Low</option>
            </select>
          </div>
          <div class="sorting mr-auto">
            <select>
              <option value="12">Show 12</option>
              <option value="24">Show 24</option>
              <option value="36">Show 36</option>
            </select>
          </div>
          <div class="pagination">
            <a href="#" class="prev-arrow"><i class="fa fa-long-arrow-left" aria-hidden="true"></i></a>
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#" class="dot-dot"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></a>
            <a href="#">6</a>
            <a href="#" class="next-arrow"><i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
          </div>
        </div>

        <section class="lattest-product-area pb-40 category-list">
          <div class="row">
            <?php
            include '../../route/koneksi.php';
            $query = "SELECT * FROM barang";
            $result = mysqli_query($koneksi, $query);

            if (mysqli_num_rows($result) > 0) {
              while ($row = mysqli_fetch_assoc($result)) {
            ?>
              <div class="col-lg-4 col-md-6">
                <div class="single-product">
                  <img class="img-fluid" src="../../barang/barang/gambar/<?php echo $row['gambar']; ?>" alt="<?php echo $row['nama_barang']; ?>">
                  <div class="product-details">
                    <h6><?php echo $row['nama_barang']; ?></h6>
                    <div class="price">
                      <h6>Rp <?php echo number_format($row['harga_sewa'], 0, ',', '.'); ?></h6>
                    </div>
                    <button type="button" class="btn btn-dark w-100" data-bs-toggle="modal" data-bs-target="#keranjangModal<?php echo $row['id_barang']; ?>">
                      Tambah ke Keranjang
                    </button>
                  </div>
                </div>
              </div>
              <?php include('../layout/modal.php'); ?>
            <?php
              }
            } else {
              echo "<p class='text-center'>Tidak ada data barang.</p>";
            }
            ?>
          </div>
        </section>
      </div>

      <!-- Produk Unggulan di Samping -->
      <div class="col-xl-3 col-lg-4 col-md-5 ps-3">
        <div class="produk-unggulan">
          <h3>Produk Unggulan</h3>
          <?php
          $unggulanQuery = "SELECT * FROM barang WHERE unggulan = 1";
          $unggulanResult = mysqli_query($koneksi, $unggulanQuery);

          if (mysqli_num_rows($unggulanResult) > 0) {
            while ($row = mysqli_fetch_assoc($unggulanResult)) {
          ?>
            <div class="unggulan-item">
              <img src="../../barang/barang/gambar/<?php echo $row['gambar']; ?>" alt="<?php echo $row['nama_barang']; ?>">
              <div>
                <p class="mb-1 fw-semibold"><?php echo $row['nama_barang']; ?></p>
                <p class="harga ">Rp <?php echo number_format($row['harga_sewa'], 0, ',', '.'); ?></p>
              </div>
            </div>
          <?php
            }
          } else {
            echo "<p>Tidak ada produk unggulan.</p>";
          }
          ?>
        </div>
      </div>
    </div>
  </div>

  <section class="related-product-area section_gap">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
          <div class="section-title">
            <h1>About Us</h1>
            <p>Subang Outdoor merupakan mitra yang menyediakan perlengkapan camping dan kegiatan luar ruang yang berada di wilayah Subang. Sebagai UMKM yang fokus pada penyewaan alat-alat outdoor seperti tenda, matras, kompor portable, dan perlengkapan lainnya, Subang Outdoor berkomitmen untuk mendukung kegiatan alam yang aman, nyaman, dan terjangkau. Dengan semangat kolaborasi, kami bersama Subang Outdoor menghadirkan solusi digital untuk mempermudah proses pemesanan dan meningkatkan layanan bagi para pecinta alam.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include("../layout/footer.php")?>

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

</body>

</html>
