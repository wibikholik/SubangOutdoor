<?php
include '../../route/koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'Guest';
$id_penyewa = $_SESSION['user_id'] ?? 0;

// Ambil jumlah item di keranjang
$jumlah_cart = 0;
if ($id_penyewa) {
  $query_cart = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total FROM carts WHERE id_penyewa = '$id_penyewa'");
  $data_cart = mysqli_fetch_assoc($query_cart);
  $jumlah_cart = $data_cart['total'] ?? 0;
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
  }

  nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 30px;
    background-color: transparent;
  }

  .nav-left,
  .nav-center,
  .nav-right {
    display: flex;
    align-items: center;
  }

  .nav-left a {
    margin-right: 20px;
    text-decoration: none;
    color: black;
    font-weight: bold;
    font-size: 14px;
  }

  .nav-left a.active {
    border-bottom: 2px solid black;
    padding-bottom: 3px;
  }

  .nav-center input[type="search"] {
    padding: 8px;
    font-size: 14px;
    width: 300px;
    border: 1px solid #aaa;
    border-radius: 4px 0 0 4px;
    outline: none;
  }

  .nav-center button {
    padding: 8px 12px;
    border: 1px solid #aaa;
    background-color: white;
    border-left: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
  }

  .nav-right {
    gap: 15px;
  }

  .nav-right a {
    position: relative;
    display: inline-block;
  }

  .nav-right i {
    font-size: 18px;
    color: black;
    cursor: pointer;
  }

  .nav-right .username {
    display: flex;
    align-items: center;
    font-weight: bold;
    font-size: 14px;
    gap: 5px;
  }

  /* Badge tepat di atas ikon */
  .cart-badge {
    position: absolute;
    top: -6px;
    right: -8px;
    background: red;
    color: white;
    font-size: 10px;
    font-weight: bold;
    border-radius: 50%;
    padding: 2px 6px;
    line-height: 1;
    min-width: 16px;
    text-align: center;
  }
</style>
</head>
<body>

<nav>
  <div class="nav-left">
    <a href="../page/Home.php">BERANDA</a>
    <a href="../page/produk.php">SEWA</a>
    <a href="../page/transaksi.php">PENYEWAAN</a>
    <a href="../page/Bantuan.php">BANTUAN</a>
  </div>

  <div class="nav-center">
    <input type="search" placeholder="Cari...">
    <button><i class="fas fa-search"></i></button>
  </div>

  <div class="nav-right">
    <a href="../page/keranjang.php" title="Keranjang">
      <i class="fas fa-shopping-cart"></i>
      <?php if ($jumlah_cart > 0): ?>
        <span class="cart-badge"><?= $jumlah_cart ?></span>
      <?php endif; ?>
    </a>
   <div class="navbar-user">
    <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($username) ?></span>
    <a href="../../prosesLogin.php?logout=true" class="logout-btn">Logout</a>
  </div>
  </div>
</nav>
