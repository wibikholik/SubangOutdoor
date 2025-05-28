<?php
// Pastikan session sudah start dan username ada di session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'Guest';
?>

<nav class="navbar">
  <div class="navbar-logo">
    <a href="../index.php">Subang Outdoor</a>
  </div>

  <div class="navbar-user">
    <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($username) ?></span>
    <a href="../prosesLogin.php?logout=true" class="logout-btn">Logout</a>
  </div>
</nav>

<style>
  .navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #007BFF;
    padding: 10px 20px;
    color: white;
  }

  .navbar-logo a {
    color: white;
    font-size: 22px;
    font-weight: 700;
    text-decoration: none;
  }

  .navbar-user {
    display: flex;
    align-items: center;
    gap: 15px;
    font-weight: 600;
  }

  .navbar-user i {
    margin-right: 5px;
    font-size: 20px;
  }

  .logout-btn {
    background-color: #dc3545;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.3s ease;
  }

  .logout-btn:hover {
    background-color: #b02a37;
  }
</style>
