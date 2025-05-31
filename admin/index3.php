<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
//STOP DISINI
require '../database/koneksi.php';
$nama_list = ['Agus', 'Aseng', 'Cinok', 'Erik', 'Jerry', 'Leo', 'Maik', 'Mawang', 'Redd','Rizz', 'Viktor'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Hutang</title>
    <style>
        * {margin:0;padding:0;box-sizing:border-box;}
        body {font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background-color:#fff;color:#000;transition:background-color .3s,color .3s;}
        body.dark-mode {background-color:#0d1b2a;color:#fff;}
        .main-header {display:flex;align-items:center;justify-content:space-between;padding:15px 25px;background:#fff;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
        body.dark-mode .main-header {background-color:#0d1b2a;}
        .profile-pic {width:60px;height:60px;border-radius:8px;object-fit:cover;}
        .header-title {font-size:1.5rem;font-weight:bold;color:#0d6efd;text-align:center;flex:1;}
        body.dark-mode .header-title {color:#FFD700;}
        .header-buttons {display:flex;align-items:center;gap:10px;}
        .toggle-switch {position: relative;width: 60px;height: 30px;}
        .toggle-switch input {display: none;}
        .slider {position: absolute;top: 0;left: 0;right: 0;bottom: 0;background-color: #ccc;border-radius: 30px;cursor: pointer;transition: background-color 0.3s;}
        .slider:before {content: "";position: absolute;width: 26px;height: 26px;border-radius: 50%;background-color: white;top: 2px;left: 2px;transition: transform 0.3s, background-image 0.3s;background-image: url('https://cdn-icons-png.flaticon.com/512/1164/1164954.png');background-size: 60%;background-repeat: no-repeat;background-position: center;}
        input:checked+.slider {background-color: #555;}
        input:checked+.slider:before {transform: translateX(30px);background-image: url('https://cdn-icons-png.flaticon.com/512/1164/1164949.png');}
        .logout-btn {padding:8px 16px;border:none;background:#f44336;color:#fff;border-radius:6px;cursor:pointer;font-weight:bold;}
        .content {display:flex;justify-content:center;gap:25px;padding:20px 200px;}
        .form-box {width:450px;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.1);}
        body.dark-mode .form-box {background:#1b263b;}
        .tabs {display:flex;}
        .tab {flex:1;padding:12px;background:#f1f1f1;font-weight:bold;border:none;cursor:pointer;}
        .tab.active {background:#fff;border-bottom:2px solid dodgerblue;}
        body.dark-mode .tab {background:#415a77;color:#fff;}
        body.dark-mode .tab.active {background:#1b263b;color:#FFD700;}
        .form-content {padding: 24px;display: flex;flex-direction: column;justify-content: space-between;height: 90%;}
        .form-row {display: flex;gap: 12px;margin-bottom: 10px;}
        .form-row select {flex-basis: 40%;}
        .form-row input {flex-basis: 60%;}
        .form-row select,.form-row input {flex:1;padding:8px 12px;border-radius:20px;border:2px solid #00bcd4;outline:none;font-size:.9rem;}
        .arrow {text-align:center;margin-bottom:10px;font-size:40px;}
        .submit-btn {width:100%;padding:10px;background:#00bcd4;color:#fff;font-weight:bold;border:none;border-radius:20px;cursor:pointer;}
        body.dark-mode .form-row select,
        body.dark-mode .form-row input {border: 2px solid #FFD700;}
        body.dark-mode .submit-btn {background-color: #FFD700;color: #000;}
        .history-box {flex: 1;background: #fff;border-radius: 10px;overflow: hidden;box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);display: flex;flex-direction: column;}
        body.dark-mode .history-box {background:#1b263b;}
        .history-title {font-weight: bold;font-size: 1.1rem;padding: 15px;border-bottom: 1px solid #ccc;background-color: inherit;flex-shrink: 0;z-index: 1;}
        .history-scroll {overflow-y: auto;max-height: 300px;padding: 10px 15px;flex: 1;}
        .history-item {display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #ccc;}
        .history-item .buttons {display:flex;gap:8px;}
        .tanggal-title {font-weight: bold;font-size: 0.95rem;margin: 12px 0 4px;color: #555;}
        body.dark-mode .tanggal-title {color: #ccc;}
        .btn-edit {background:#ffc107;color:black;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;}
        .btn-delete {background:#e53935;color:white;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;}
        .badge {display: inline-block;font-size: 0.7rem;font-weight: bold;padding: 2px 6px;border-radius: 10px;margin-left: 6px;color: white;}
        .badge-hutang {background-color: #e53935;}
        .badge-bayar {background-color: #4caf50;}
        .total-box {margin:10px 200px;background:#fff;border-radius:10px;padding:15px;box-shadow:0 4px 12px rgba(0,0,0,0.1);}
        body.dark-mode .total-box {background:#1b263b;}
        .total-box strong {font-size:1.1rem;}
        .netting-line {display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #ddd;}
    </style>
</head>

<body>
<header class="main-header">
    <img src="../assets/foto/agus.png" class="profile-pic" alt="Agus" />
    <h1 class="header-title">Sistem Pencatatan Hutang</h1>
    <div class="header-buttons">
        <label class="toggle-switch">
            <input type="checkbox" id="darkModeToggle">
            <span class="slider"></span>
        </label>
        <button class="logout-btn" onclick="location.href='logout.php'">Logout</button>
    </div>
</header>

<div class="content">
    <div class="form-box">
        <div class="tabs">
            <button id="tab-hutang" class="tab active" onclick="switchForm('hutang')">Hutang</button>
            <button id="tab-bayar" class="tab" onclick="switchForm('bayar')">Bayar</button>
        </div>
        <form class="form-content" action="../process/proses_transaksi.php" method="POST">
            <input type="hidden" name="tipe" id="tipeInput" value="hutang">
            <div class="form-row">
                <select name="pengutang" required>
                    <?php foreach ($nama_list as $nama): ?>
                        <option value="<?= $nama ?>"><?= $nama ?></option>
                    <?php endforeach; ?>
                </select>
                <input name="jumlah" id="nominal1" type="text" placeholder="Rp ...." required />
            </div>
            <div class="arrow">↓</div>
            <div class="form-row">
                <select name="penerima" required>
                    <?php foreach ($nama_list as $nama): ?>
                        <option value="<?= $nama ?>"><?= $nama ?></option>
                    <?php endforeach; ?>
                </select>
                <input id="nominal2" type="text" placeholder="Rp ....." disabled />
            </div>
            <button class="submit-btn" type="submit" id="submit-btn">Hutang</button>
        </form>
    </div>

    <div class="history-box">
        <div class="history-title">Riwayat Transaksi</div>
        <div class="history-scroll">
            <?php
                $query = $conn->query("SELECT * FROM transaksi ORDER BY tanggal DESC, id DESC");
                $data = [];
                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    $data[$row['tanggal']][] = $row;
                }
                foreach ($data as $tanggal => $transaksis) {
                    echo "<div class='tanggal-title'>" . date('d M Y', strtotime($tanggal)) . "</div>";
                    foreach ($transaksis as $row) {
                        $jumlahFormatted = "Rp " . number_format($row['jumlah'], 0, ',', '.');
                        $badge = $row['tipe'] === 'hutang' 
                            ? "<span class='badge badge-hutang'>H</span>" 
                            : "<span class='badge badge-bayar'>B</span>";
                        echo "<div class='history-item'>
                            <span>$badge {$row['pengutang']} → {$row['penerima']}: $jumlahFormatted</span>
                            <div class='buttons'>
                                <a href='../process/edit_transaksi.php?id={$row['id']}'><button class='btn-edit'>Edit</button></a>
                                <a href='../process/hapus_transaksi.php?id={$row['id']}' onclick=\"return confirm('Yakin hapus?')\"><button class='btn-delete'>Hapus</button></a>
                            </div>
                        </div>";
                    }
                }
            ?>
        </div>
    </div>
</div>

<div class="total-box">
    <strong>Total Netting</strong><br><br>
    <?php
    $net = [];
    $query = $conn->query("SELECT * FROM transaksi");
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $dari = $row['pengutang'];
        $ke = $row['penerima'];
        $jumlah = $row['jumlah'];
        // Jika tipe bayar, dibalik karena mengurangi hutang
        if ($row['tipe'] === 'bayar') {
            $dari = $row['penerima'];
            $ke = $row['pengutang'];
        }
        if (!isset($net[$dari][$ke])) $net[$dari][$ke] = 0;
        $net[$dari][$ke] += $jumlah;
    }
    // Proses netting dua arah
    $result = [];
    $processed = [];
    foreach ($net as $from => $list) {
        foreach ($list as $to => $val) {
            if (isset($processed[$from][$to]) || isset($processed[$to][$from])) {
                continue;
            }
            $val2 = $net[$to][$from] ?? 0;
            if ($val > $val2) {
                $result[$from][$to] = $val - $val2;
            } elseif ($val2 > $val) {
                $result[$to][$from] = $val2 - $val;
            }
            $processed[$from][$to] = true;
            $processed[$to][$from] = true;
        }
    }
    // Tampilkan hasil akhir
    $hasilAkhir = [];
    foreach ($result as $dari => $list) {
        foreach ($list as $ke => $jumlah) {
            if ($jumlah > 0) {
                $hasilAkhir[] = ['from' => $dari, 'to' => $ke, 'jumlah' => $jumlah];
            }
        }
    }
    // Urut berdasarkan penerima
    usort($hasilAkhir, fn($a, $b) => strcmp($a['to'], $b['to']));
    foreach ($hasilAkhir as $row) {
        $format = "Rp " . number_format($row['jumlah'], 0, ',', '.');
        echo "<div class='netting-line'><span>{$row['from']} hutang {$row['to']}</span><span>$format</span></div>";
    }
    ?>
</div>

<script>
    const toggleSwitch = document.getElementById('darkModeToggle');
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
            toggleSwitch.checked = true;
        }
        toggleSwitch.addEventListener('change', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
        });

    const nominal1 = document.getElementById('nominal1');
    const nominal2 = document.getElementById('nominal2');
    nominal1.addEventListener('input', () => {
        const raw = nominal1.value.replace(/[^0-9]/g, '');
        const formatted = formatRupiah(raw);
        nominal1.value = formatted;
        nominal2.value = formatted;
    });

    function formatRupiah(angka) {
        if (!angka) return '';
        return 'Rp ' + angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function switchForm(type) {
        document.getElementById('tipeInput').value = type;
        document.getElementById('tab-hutang').classList.remove('active');
        document.getElementById('tab-bayar').classList.remove('active');
        document.getElementById('tab-' + type).classList.add('active');
        document.getElementById('submit-btn').textContent = capitalize(type);
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
</script>
</body>
</html>
