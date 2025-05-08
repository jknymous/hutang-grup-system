<?php
require 'database/koneksi.php';

// Sorting logic
$sortKey = $_GET['sort'] ?? 'to';
$query = $conn->query("SELECT * FROM transaksi");
$data = $query->fetchAll();

// Netting logic
$net = [];
foreach ($data as $d) {
    $dari = $d['pengutang'];
    $ke = $d['penerima'];
    $jumlah = $d['jumlah'];
    if ($d['tipe'] === 'hutang') {
        $net[$dari][$ke] = ($net[$dari][$ke] ?? 0) + $jumlah;
    } else {
        $dari = $d['penerima'];
        $ke = $d['pengutang'];
        $net[$dari][$ke] = ($net[$dari][$ke] ?? 0) + $jumlah;
    }
}

// Netting dua arah
$result = [];
$processed = [];
foreach ($net as $from => $list) {
    foreach ($list as $to => $val) {
        if (isset($processed[$from][$to]) || isset($processed[$to][$from])) continue;
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

$hasil_netting = [];
foreach ($result as $dari => $tujuan) {
    foreach ($tujuan as $ke => $jumlah) {
        if ($jumlah > 0) {
            $hasil_netting[] = ['from' => $dari, 'to' => $ke, 'jumlah' => $jumlah];
        }
    }
}

// Sort
usort($hasil_netting, fn($a, $b) => strcmp($a[$sortKey], $b[$sortKey]));

// Menghitung total hutang
$total_hutang = 0;
foreach ($hasil_netting as $item) {
    $total_hutang += $item['jumlah']; // Menjumlahkan jumlah hutang
}

// Menghitung total hutang per orang
$totalHutang = [];
foreach ($hasil_netting as $item) {
    $from = $item['from'];
    $jumlah = $item['jumlah'];
    
    if (!isset($totalHutang[$from])) {
        $totalHutang[$from] = 0;
    }
    $totalHutang[$from] += $jumlah;
}

// Menentukan orang dengan hutang terbanyak
$pemenangHutang = '';
$totalHutangTerbanyak = 0;
foreach ($totalHutang as $orang => $jumlahHutang) {
    if ($jumlahHutang > $totalHutangTerbanyak) {
        $pemenangHutang = $orang;
        $totalHutangTerbanyak = $jumlahHutang;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Catatan Hutang Publik</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #ffffff;
            color: #000000;
            transition: background-color 0.3s, color 0.3s;
        }

        body.dark-mode {
            background-color: #0d1b2a;
            color: white;
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

        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            border-radius: 10px;
            padding: 20px 30px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        body.dark-mode .container {
            background-color: #1b263b;
        }

        .container-header {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 20px;
        }

        .container-header h2 {
            margin: 0 auto;
        }

        .sort-toggle {
            background: none;
            border: none;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .sort-toggle svg {
            width: 24px;
            height: 24px;
            fill: currentColor;
        }

        body.dark-mode .sort-toggle svg {
            color: white;
        }

        .netting-line {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid transparent;
        }

        .separator-line {
            border-bottom: 1px solid #ccc;
            margin: 8px 0;
        }

        body.dark-mode .separator-line {
            border-color: #555;
        }

        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
        }
        .toggle-switch input {
            display: none;
        }
        .slider {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 30px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .slider:before {
            content: "";
            position: absolute;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background-color: white;
            top: 2px;
            left: 2px;
            transition: transform 0.3s, background-image 0.3s;
            background-image: url('https://cdn-icons-png.flaticon.com/512/1164/1164954.png');
            background-size: 60%;
            background-repeat: no-repeat;
            background-position: center;
        }
        input:checked + .slider {
            background-color: #555;
        }
        input:checked + .slider:before {
            transform: translateX(30px);
            background-image: url('https://cdn-icons-png.flaticon.com/512/1164/1164949.png');
        }
        .music-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: none;
            border: none;
            cursor: pointer;
            color: inherit;
            z-index: 999;
        }
        .music-toggle svg {
            width: 32px;
            height: 32px;
            fill: currentColor;
        }
        .pemenang-hutang-box {
            background-color: #1e1e1e;
            border-radius: 12px;
            padding: 15px;
            margin-top: 20px;
            display: flex;
            justify-content: center; /* Ini memastikan kontennya di tengah */
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
        }
        body.dark-mode .pemenang-hutang-box {
            background-color: #0d1b2a;
            color: #ffd700;
        }
        /* Menambahkan sedikit efek visual untuk box */
        .pemenang-hutang-box:hover {
            background-color: #333;
            cursor: pointer;
            transform: scale(1.02);
            transition: all 0.3s ease;
        }
        /* Konten di dalam box */
        .pemenang-hutang-content {
            text-align: center; /* Agar semua konten di dalam box rata tengah */
        }
        .pemenang-hutang-box .pemenang-title {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 8px;
        }
        .pemenang-hutang-box .pemenang-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }

        .dropdown-container {
    margin-bottom: 15px;
}

.name-dropdown {
    background-color: #e6f0ff;
    border-radius: 8px;
    padding: 15px;
    cursor: pointer;
    margin-bottom: 10px;
    width: 100%;
    display: block;
    box-sizing: border-box; /* supaya padding dihitung */
}

body.dark-mode .name-dropdown {
    background-color: #374a64;
}

.name-summary {
    font-weight: bold;
    text-align: center;
    padding: 10px;
    font-size: 1.1rem;
    color: #333;
}

body.dark-mode .name-summary {
    color: #fff;
}

.info-display {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    gap: 8px; /* jarak antara input dan tombol */
}

.info-display label {
    display: block;
    font-size: 0.9rem;
    font-weight: normal;
    margin-bottom: 5px;
}

.info-display input {
    flex: 1;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

body.dark-mode .info-display input {
    background-color: #2a3a54;
    color: #fff;
    border-color: #555;
}

.copy-button {
    padding: 6px 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    white-space: nowrap;
}

.copy-button:hover {
    background-color: #0056b3;
}

body.dark-mode .copy-button {
    background-color: #6a9e6b;
}

.copy-button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.notification {
    display: none;
    background-color: #4CAF50;
    color: white;
    padding: 5px 8px;
    border-radius: 5px;
    font-size: 0.8rem;
    position: absolute;
    top: -30px; /* naik ke atas tombol */
    left: 0;
    white-space: nowrap;
    z-index: 1;
}

body.dark-mode .notification {
    background-color: #3b6b4c;
}
.name-dropdown[open] .content {
    max-height: 500px; /* bebas, kira2 seberapa panjang isinya */
    transition: max-height 0.5s ease, padding 0.5s ease;
    padding-top: 10px;
}

.content {
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.5s ease, padding 0.5s ease;
    padding-top: 0;
}
.name-summary {
    list-style: none; /* hilangin bawaan bullet */
}

.name-dropdown summary::-webkit-details-marker {
    display: none; /* hilangin panah di Chrome/Safari */
}

.name-dropdown summary::marker {
    display: none; /* hilangin panah di Firefox */
}
.foto-pemenang {
    width: 200px;
    height: 200px;
    border-radius: 12px;
    object-fit: cover;
    margin: 10px auto;
    display: block;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

    </style>
</head>
<body>
<header class="main-header">
    <img src="assets/foto/agus.png" class="profile-pic" alt="Agus">
    <h1 class="header-title">Catatan Hutang Grup Agus King of King New Era</h1>
    <label class="toggle-switch">
        <input type="checkbox" id="darkModeToggle">
        <span class="slider"></span>
    </label>
</header>

<div class="container">
    <div class="container-header">
        <h2>Total Netting</h2>
        <form method="get">
            <input type="hidden" name="sort" value="<?= $sortKey === 'from' ? 'to' : 'from' ?>">
            <button type="submit" class="sort-toggle" aria-label="Urutkan">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 7l4-4 4 4H8zm0 10h8l-4 4-4-4z"/>
                </svg>
            </button>
        </form>
    </div>

    <?php if (empty($hasil_netting)): ?>
        <p style="text-align:center;">Tidak ada hutang saat ini.</p>
    <?php else: ?>
        <?php
        $lastGroup = null;
        foreach ($hasil_netting as $item):
            $currentGroup = $item[$sortKey];
            if ($lastGroup !== null && $currentGroup !== $lastGroup) {
                echo '<div class="separator-line"></div>';
            }
            $lastGroup = $currentGroup;
            ?>
            <div class="netting-line">
                <span><?= htmlspecialchars($item['from']) ?> hutang <?= htmlspecialchars($item['to']) ?></span>
                <span>Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Menampilkan Total Hutang -->
    <div class="separator-line"></div>
    <div class="netting-line">
        <span><strong>Total Hutang</strong></span>
        <span><strong>Rp <?= number_format($total_hutang, 0, ',', '.') ?></strong></span>
    </div>

    <!-- Box untuk Pemenang Hutang Terbanyak -->
    <div class="separator-line"></div>
    <div class="pemenang-hutang-box">
        <div class="pemenang-hutang-content">
            <span class="pemenang-title">Pemenang Hutang Terbanyak dengan Total Hutang Rp <?= number_format($totalHutangTerbanyak, 0, ',', '.') ?> jatuh kepada</span>
            <span class="pemenang-name"><strong><?= htmlspecialchars($pemenangHutang) ?></strong></span>
            <img src="assets/foto/<?= urlencode($pemenangHutang) ?>.jpg" alt="Foto <?= htmlspecialchars($pemenangHutang) ?>" class="foto-pemenang">
        </div>
    </div>
</div>

<div class="container">
    <h2>Informasi Bank dan pembayaran lainnya</h2>
    <form action="#" method="POST">
        <!-- Dropdown untuk Agus -->
        <div class="dropdown-container">
            <details class="name-dropdown">
                <summary class="name-summary">Agus</summary>
                <div class="content">
                    <div class="info-display">
                        <label for="bankNumberAgus">Bank BCA :</label>
                        <input type="text" id="bankNumberAgus" value="8890785957" readonly>
                        <button type="button" class="copy-button" onclick="copyToClipboard('bankNumberAgus')">Copy</button>
                    </div>
                    <div class="info-display">
                        <label for="phoneNumberAgus">GOPAY :</label>
                        <input type="text" id="phoneNumberAgus" value="082172040214" readonly>
                        <button type="button" class="copy-button" onclick="copyToClipboard('phoneNumberAgus')">Copy</button>
                    </div>
                </div>
            </details>
        </div>
        <!-- Dropdown untuk Jerry -->
        <div class="dropdown-container">
            <details class="name-dropdown">
                <summary class="name-summary">Jerry</summary>
                <div class="content">
                    <div class="info-display">
                        <label for="phoneNumberJerry">OVO/GOPAY/DANA :</label>
                        <input type="text" id="phoneNumberJerry" value="081277447620" readonly>
                        <button type="button" class="copy-button" onclick="copyToClipboard('phoneNumberJerry')">Copy</button>
                    </div>
                </div>
            </details>
        </div>
        <!-- Dropdown untuk Maik -->
        <div class="dropdown-container">
            <details class="name-dropdown">
                <summary class="name-summary">Maik</summary>
                <div class="content">
                    <div class="info-display">
                        <label for="bankNumberMaik">Bank BCA :</label>
                        <input type="text" id="bankNumberMaik" value="5271880716" readonly>
                        <button type="button" class="copy-button" onclick="copyToClipboard('bankNumberMaik')">Copy</button>
                    </div>
                    <div class="info-display">
                        <label for="phoneNumberMaik">OVO/GOPAY/SHOPEEPAY :</label>
                        <input type="text" id="phoneNumberMaik" value="085938543221" readonly>
                        <button type="button" class="copy-button" onclick="copyToClipboard('phoneNumberMaik')">Copy</button>
                    </div>
                </div>
            </details>
        </div>
        <!-- Dropdown untuk Rizz -->
        <div class="dropdown-container">
            <details class="name-dropdown">
                <summary class="name-summary">Rizz</summary>
                <div class="content">
                    <div class="info-display">
                        <label for="bankNumberRizz">Bank BCA :</label>
                        <input type="text" id="bankNumberRizz" value="8210860321" readonly>
                        <button type="button" class="copy-button" onclick="copyToClipboard('bankNumberRizz')">Copy</button>
                    </div>
                    <div class="info-display">
                        <label for="phoneNumberRizz">OVO/GOPAY/DANA :</label>
                        <input type="text" id="phoneNumberRizz" value="081277645283" readonly>
                        <button type="button" class="copy-button" onclick="copyToClipboard('phoneNumberRizz')">Copy</button>
                    </div>
                </div>
            </details>
        </div>
    </form>
</div>

<audio id="bg-music" autoplay loop>
    <source src="assets/music/rickroll.mp3" type="audio/mpeg">
    Browser tidak mendukung pemutar audio.
</audio>

<button class="music-toggle" onclick="toggleMusic()" id="musicToggleBtn" aria-label="Toggle Music">
    <svg id="icon-sound" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M3 10v4h4l5 5V5L7 10H3zm13.5 2c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.74 2.5-2.26 2.5-4.02z"/>
    </svg>
    <svg id="icon-muted" style="display:none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v1.71l4.95 4.95c.36-.74.55-1.55.55-2.63zm-9.9-7.68L4.27 2 3 3.27l5 5V15h4l5 5 1.27-1.27L6.6 4.32z"/>
    </svg>
</button>


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

    const audio = document.getElementById('bg-music');
    const iconSound = document.getElementById('icon-sound');
    const iconMuted = document.getElementById('icon-muted');

    // Set muted dulu untuk bypass autoplay
    audio.muted = true;
    audio.play().then(() => {
        // Nyalain suara setelah delay (biar aman)
        setTimeout(() => {
            audio.muted = false;
        }, 500);
    }).catch(err => {
        console.warn("Autoplay dicekal, nunggu interaksi user");
    });

    function toggleMusic() {
        if (audio.muted) {
            audio.muted = false;
            iconSound.style.display = 'inline';
            iconMuted.style.display = 'none';
        } else {
            audio.muted = true;
            iconSound.style.display = 'none';
            iconMuted.style.display = 'inline';
        }
    }

    document.querySelectorAll('.name-dropdown').forEach((details) => {
        details.addEventListener('toggle', function () {
            if (this.open) {
                document.querySelectorAll('.name-dropdown').forEach((other) => {
                    if (other !== this) {
                        other.removeAttribute('open');
                    }
                });
                this.classList.add('open');
            } else {
                this.classList.remove('open');
            }
        });
    });

    // Copy function tetap sama kayak tadi
    function copyToClipboard(elementId) {
        const input = document.getElementById(elementId);
        input.select();
        input.setSelectionRange(0, 99999); // for mobile devices
        document.execCommand('copy');
        const button = document.querySelector(`button[onclick="copyToClipboard('${elementId}')"]`);
        let notification = button.querySelector('.notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = 'Sudah di Copy';
            button.appendChild(notification);
        }

        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 2000);
    }
</script>
</body>
</html>
