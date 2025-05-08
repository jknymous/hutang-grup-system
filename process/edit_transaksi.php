<?php
require '../database/koneksi.php';

$id = $_GET['id'];
$query = $conn->prepare("SELECT * FROM transaksi WHERE id = ?");
$query->execute([$id]);
$data = $query->fetch();

$nama_list = ['Agus', 'Aseng', 'Cinok', 'Erik', 'Jerry', 'Leo', 'Maik', 'Mawang', 'Redd', 'Rizz', 'Viktor'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipe = $_POST['tipe'];
    $pengutang = $_POST['pengutang'];
    $penerima = $_POST['penerima'];
    $jumlah = (int) str_replace(['Rp', '.', ' '], '', $_POST['jumlah']);
    $update = $conn->prepare("UPDATE transaksi SET pengutang=?, penerima=?, jumlah=?, tipe=? WHERE id=?");
    $update->execute([$pengutang, $penerima, $jumlah, $tipe, $id]);
    header("Location: ../admin/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Transaksi</title>
    <style>
        * {
            box-sizing: border-box;
            padding: 0;
            margin: 0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #ffffff;
            color: #000000;
            transition: background-color 0.3s, color 0.3s;
        }

        body.dark-mode {
            background-color: #0d1b2a;
            color: #ffffff;
        }

        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 25px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        body.dark-mode .main-header {
            background-color: #0d1b2a;
        }

        .profile-pic {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
            text-align: center;
            flex: 1;
        }

        body.dark-mode .header-title {
            color: #FFD700;
        }

        .header-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mode-toggle {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
        }

        .logout-btn {
            padding: 8px 16px;
            border: none;
            background-color: #f44336;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .edit-container {
            max-width: 500px;
            background: #fff;
            margin: 40px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        body.dark-mode .edit-container {
            background-color: #1b263b;
        }

        .edit-container h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .edit-container label {
            font-weight: bold;
            display: block;
            margin: 10px 0 5px;
        }

        .edit-container input,
        .edit-container select {
            width: 100%;
            padding: 10px;
            border: 2px solid #0d6efd;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .edit-container button {
            width: 100%;
            padding: 10px;
            background: #0d6efd;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            cursor: pointer;
        }

        body.dark-mode input,
        body.dark-mode select {
            background-color: #324a64;
            color: white;
            border-color: #FFD700;
        }

        body.dark-mode button {
            background-color: #FFD700;
            color: black;
        }
        .btn-save {
            flex: 1;
            padding: 10px;
            background: #0d6efd;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            cursor: pointer;
        }

        .btn-cancel {
            width: 100%;
            padding: 10px;
            background: #e53935;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            cursor: pointer;
        }

        body.dark-mode .btn-save {
            background: #FFD700;
            color: black;
        }

        body.dark-mode .btn-cancel {
            background: #ff6b6b;
            color: white;
        }

    </style>
</head>
<body>
    <header class="main-header">
        <img src="agus.png" alt="Agus" class="profile-pic">
        <h1 class="header-title">Sistem Pencatatan Hutang</h1>
        <div class="header-buttons">
            <button class="mode-toggle" onclick="toggleMode()">☀️</button>
            <a href="../admin/logout.php"><button class="logout-btn">Logout</button></a>
        </div>
    </header>

    <div class="edit-container">
        <h2>Edit Transaksi</h2>
        <form method="POST">
            <label>Pengutang:</label>
            <select name="pengutang">
                <?php foreach ($nama_list as $nama): ?>
                    <option <?= $data['pengutang'] == $nama ? 'selected' : '' ?>><?= $nama ?></option>
                <?php endforeach; ?>
            </select>
            <label>Penerima:</label>
            <select name="penerima">
                <?php foreach ($nama_list as $nama): ?>
                    <option <?= $data['penerima'] == $nama ? 'selected' : '' ?>><?= $nama ?></option>
                <?php endforeach; ?>
            </select>
            <label>Jumlah:</label>
            <input type="text" name="jumlah" id="inputRupiah" value="Rp <?= number_format($data['jumlah'], 0, ',', '.') ?>">
            <label>Tipe:</label>
            <select name="tipe">
                <option value="hutang" <?= $data['tipe'] == 'hutang' ? 'selected' : '' ?>>Hutang</option>
                <option value="bayar" <?= $data['tipe'] == 'bayar' ? 'selected' : '' ?>>Bayar</option>
            </select>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-save">Simpan</button>
                <a href="../admin/index.php" style="flex:1;"><button type="button" class="btn-cancel">Batal</button></a>
            </div>
        </form>
    </div>

    <script>
        // Format input jumlah otomatis
        document.querySelector('input[name="jumlah"]').addEventListener('input', function () {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = formatRupiah(value);
        });

        function formatRupiah(angka) {
            if (!angka) return '';
            return 'Rp ' + angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Dark Mode persist via localStorage
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark-mode');
                document.querySelector('.mode-toggle').textContent = '🌙';
            }
        });

        function toggleMode() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            document.querySelector('.mode-toggle').textContent = isDark ? '🌙' : '☀️';
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
    </script>
</body>
</html>
