<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <title>Edit Data Admin</title>
</head>
<body>
    <?php include('../layout/sidebar.php'); ?>

    <div style="margin-left:25%; padding:20px;">
        <?php include('../layout/navbar.php'); ?>
        <h3>Edit Data Admin</h3>

        <?php
        include "../route/koneksi.php";

        // Pesan notifikasi
        if (isset($_GET['pesan'])) {
            $pesan = $_GET['pesan'];
            if ($pesan == "update") {
                echo '<div class="w3-panel w3-green w3-padding w3-round w3-margin-bottom">';
                echo '<i class="fas fa-check-circle"></i> Data berhasil diupdate.';
                echo '</div>';
            }
        }

        if (isset($_GET['id_admin'])) {
            $id_admin = intval($_GET['id_admin']);
            $stmt = mysqli_prepare($koneksi, "SELECT * FROM admin WHERE id_admin = ?");
            mysqli_stmt_bind_param($stmt, "i", $id_admin);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($data = mysqli_fetch_assoc($result)) {
        ?>
                <form action="update.php" method="post" class="w3-container w3-card-4 w3-light-grey">
                    <input type="hidden" name="id_admin" value="<?= $data['id_admin']; ?>">

                    <p>
                        <label>Username</label>
                        <input class="w3-input w3-border" type="text" name="username" value="<?= htmlspecialchars($data['username']); ?>" required />
                    </p>

                    <p>
                        <label>Nama Admin</label>
                        <input class="w3-input w3-border" type="text" name="nama_admin" value="<?= htmlspecialchars($data['nama_admin']); ?>" required />
                    </p>

                    <p>
                        <label>Alamat</label>
                        <input class="w3-input w3-border" type="text" name="alamat" value="<?= htmlspecialchars($data['alamat']); ?>" required />
                    </p>

                    <p>
                        <label>No HP</label>
                        <input class="w3-input w3-border" type="tel" name="no_hp" value="<?= htmlspecialchars($data['no_hp']); ?>" required />
                    </p>

                    <p>
                        <label>Email</label>
                        <input class="w3-input w3-border" type="email" name="email" value="<?= htmlspecialchars($data['email']); ?>" required />
                    </p>

                    <p>
                        <label>Password</label>
                        <input class="w3-input w3-border" type="text" name="password" value="<?= htmlspecialchars($data['password']); ?>" required />
                    </p>

                    <p>
                        <button class="w3-button w3-blue" type="submit">Simpan Perubahan</button>
                    </p>
                </form>
        <?php
            } else {
                echo "<p class='w3-red w3-padding'>Data tidak ditemukan.</p>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<p class='w3-red w3-padding'>ID Admin tidak ditemukan.</p>";
        }
        ?>
    </div>
</body>
</html>
