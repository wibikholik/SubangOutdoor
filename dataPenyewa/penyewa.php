<?php
session_start();

// Batasi akses hanya admin dan owner
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header("Location: ../login.php?message=access_denied");
    exit;
}

include '../route/koneksi.php';

// Handle pesan notifikasi
$message = '';
if (isset($_GET['pesan'])) {
    $pesan = $_GET['pesan'];
    if ($pesan == "input") {
        $message = "✅ Data berhasil ditambahkan.";
    } elseif ($pesan == "hapus") {
        $message = "✅ Data berhasil dihapus.";
    } elseif ($pesan == "update") {
        $message = "✅ Data berhasil diupdate.";
    }
}

$query = "SELECT * FROM penyewa";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- CSS eksternal -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css" />
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <title>Penyewa</title>

    <style>
        <?php include('../layout/style.css'); ?>
        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: 600;
        }

        .container {
            margin-left: 25%;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f4f4f4;
        }

        a.fas {
            font-size: 18px;
            margin-right: 10px;
            cursor: pointer;
        }

        a.fas.fa-trash {
            color: #dc3545;
        }

        a.fas.fa-edit {
            color: #007bff;
        }

        a.add-user-btn {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 14px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }

        a.add-user-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php include('../layout/sidebar.php'); ?>

    <div class="container">
        <?php include('../layout/navbar.php'); ?>
        <h2>Daftar Penyewa</h2>

      <?php if (!empty($message)) : ?>
    <?php
    $color = 'w3-green';
    $icon = '<i class="fas fa-check-circle"></i>';

    if ($pesan == "hapus") {
        $color = 'w3-red';
        $icon = '<i class="fas fa-trash-alt"></i>';
    } elseif ($pesan == "update") {
        $color = 'w3-blue';
        $icon = '<i class="fas fa-pen"></i>';
    }
    ?>
    <div class="w3-panel <?= $color ?> w3-round w3-display-container w3-animate-opacity" style="margin: 16px;">
        <span onclick="this.parentElement.style.display='none'" class="w3-button w3-large w3-display-topright">&times;</span>
        <p><?= $icon ?> <?= $message ?></p>
    </div>
<?php endif; ?>


        <a href="tambah_Penyewa.php" class="add-user-btn"><i class="fas fa-plus"></i> Tambah User</a>

        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>ID Penyewa</th>
                    <th>Nama Penyewa</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id_penyewa']) ?></td>
                            <td><?= htmlspecialchars($row['nama_penyewa']) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td><?= htmlspecialchars($row['no_hp']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['password']) ?></td>
                            <td>
                                <a href="hapus.php?id_penyewa=<?= urlencode($row['id_penyewa']) ?>" class="fas fa-trash" onclick="return confirm('Apakah Anda yakin ingin menghapus penyewa ini?');" title="Hapus"></a>
                                <a href="editPenyewa.php?id_penyewa=<?= urlencode($row['id_penyewa']) ?>" class="fas fa-edit" title="Edit"></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;">Data penyewa kosong.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
