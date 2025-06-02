<?php
$role = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? 'Guest';
?>

<div class="w3-sidebar w3-bar-block w3-white w3-border-right" style="width:350px; border-top-right-radius: 30px;">
  <div class="w3-center w3-padding-16">
    <img src="gunung.jpg" style="border-radius:10px;" alt="Foto Profil">
    
  </div>

  <?php if ($role === 'admin'): ?>
    <a href="../admin/index_admin.php" class="w3-bar-item w3-button">
      <i class="fas fa-tachometer-alt w3-margin-right"></i>Dashboard Admin
    </a>
    <a href="../barang/barang.php" class="w3-bar-item w3-button">
      <i class="fas fa-box-open w3-margin-right"></i>Barang
    </a>
    <a href="../DataPenyewa/penyewa.php" class="w3-bar-item w3-button">
      <i class="fas fa-users w3-margin-right"></i>Penyewa
    </a>
    <a href="../transaksi/transaksi.php" class="w3-bar-item w3-button">
      <i class="fas fa-receipt w3-margin-right"></i>Transaksi
    </a>
    <a href="../pengembalian/pengembalian.php" class="w3-bar-item w3-button">
      <i class="fas fa-receipt w3-margin-right"></i>Pengembalian
    </a>

  <?php elseif ($role === 'owner'): ?>
    <a href="../owner/index_owner.php" class="w3-bar-item w3-button">
      <i class="fas fa-tachometer-alt w3-margin-right"></i>Dashboard Owner
    </a>
    <a href="../dataAdmin/admin.php" class="w3-bar-item w3-button">
      <i class="fas fa-user-shield w3-margin-right"></i>Data Admin
    </a>
    <a href="../DataPenyewa/penyewa.php" class="w3-bar-item w3-button">
      <i class="fas fa-users w3-margin-right"></i>Data Penyewa
    </a>
     <a href="../barang/barang.php" class="w3-bar-item w3-button">
      <i class="fas fa-box-open w3-margin-right"></i>Barang
    </a>
    <a href="../transaksi/transaksi.php" class="w3-bar-item w3-button">
      <i class="fas fa-receipt w3-margin-right"></i>Transaksi
    </a>
      <a href="../pengembalian/pengembalian.php" class="w3-bar-item w3-button">
      <i class="fas fa-receipt w3-margin-right"></i>Pengembalian
    </a>
    <a href="../laporan/laporan.php" class="w3-bar-item w3-button">
      <i class="fas fa-file-alt w3-margin-right"></i>Data Laporan
    </a>
    
    

  <?php elseif ($role === 'penyewa'): ?>
    <a href="../penyewa/home.php" class="w3-bar-item w3-button">
      <i class="fas fa-tachometer-alt w3-margin-right"></i>Dashboard Penyewa
    </a>
    <a href="../barang/barang.php" class="w3-bar-item w3-button">
      <i class="fas fa-box-open w3-margin-right"></i>Barang
    </a>
    <a href="../transaksi/transaksi.php" class="w3-bar-item w3-button">
      <i class="fas fa-receipt w3-margin-right"></i>Transaksi
    </a>

  <?php else: ?>
    <a href="../login.php" class="w3-bar-item w3-button">
      <i class="fas fa-sign-in-alt w3-margin-right"></i>Login
    </a>
  <?php endif; ?>

  <div class="w3-bottom w3-padding-small">
    <i class="fas fa-copyright"></i> <b>Subang Outdoor</b>
  </div>
</div>
