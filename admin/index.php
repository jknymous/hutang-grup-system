<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Database connection
require '../database/koneksi.php';

// List of names
$nama_list = ['Agus', 'Aseng', 'Cinok', 'Erik', 'Jerry', 'Leo', 'Maik', 'Mawang', 'Redd', 'Rizz', 'Viktor'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Buku Besar Agus Yanto</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="light-mode">
    <header>
        <img src="../assets/foto/agus.png" alt="Logo" />
        <h1>Buku Besar Agus Yanto</h1>
        <div class="header-buttons">
            <button id="toggleMode" aria-label="Toggle Dark/Light Mode" title="Toggle Mode" type="button">
                <svg id="modeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"
                    focusable="false">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
            </button>
            <button id="logout" type="button" onclick="location.href='logout.php'">Logout</button>
        </div>
    </header>

    <main>
        <div class="top-boxes">
            <section class="box hutang" aria-labelledby="hutangLabel" role="region">
                <div class="tabs-container">
                    <button class="tab-btn active" role="tab" aria-selected="true" aria-controls="hutangPanel"
                        id="hutangTab">Hutang</button>
                    <button class="tab-btn" role="tab" aria-selected="false" aria-controls="bayarPanel"
                        id="bayarTab">Bayar</button>
                </div>
                <form id="formHutang" aria-labelledby="hutangTab" class="form-container" autocomplete="off"
                    action="../process/proses_transaksi.php" method="POST">
                    <input type="hidden" name="tipe" id="tipeInput" value="hutang">
                    <div class="form-row">
                        <select name="pengutang" id="namaSelect1" required>
                            <?php foreach ($nama_list as $nama): ?>
                                <option value="<?= $nama ?>"><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" id="amountInput" name="jumlah" aria-label="Masukkan jumlah" placeholder="Rp 10.000"
                            required inputmode="numeric" />
                    </div>
                    <div class="arrow-center" aria-hidden="true">⇅</div>
                    <div class="form-row">
                        <select name="penerima" id="namaSelect2" required>
                            <?php foreach ($nama_list as $nama): ?>
                                <option value="<?= $nama ?>"><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" id="autoAmount" aria-label="Jumlah yang di atas" readonly
                            placeholder="Rp 0" />
                    </div>
                    <button type="submit" class="submit-btn" id="submitBtn">Submit Hutang</button>
                </form>
            </section>
            <section class="box history" aria-labelledby="historyLabel" role="region">
                <h2 id="historyLabel" class="font-bold text-lg mb-3 select-none">History Transaksi</h2>
                <div id="historyList" role="list" class="overflow-y-auto max-h-[460px] pr-2">
                    <?php
                    // Fetch transaction history
                    $query = $conn->query("SELECT * FROM transaksi ORDER BY tanggal DESC, id DESC");
                    $data = [];
                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                        $data[$row['tanggal']][] = $row;
                    }
                    foreach ($data as $tanggal => $transaksis) {
                        echo "<div class='history-group' role='group' aria-labelledby='date-label-{$tanggal}'>";
                        echo "<div class='history-date' id='date-label-{$tanggal}'>" . date('d/m/Y', strtotime($tanggal)) . "</div>";
                        foreach ($transaksis as $row) {
                            $jumlahFormatted = "Rp " . number_format($row['jumlah'], 0, ',', '.');
                            $transactionType = $row['tipe'] === 'hutang' ? 'hutang' : 'bayar';
                            $colorClass = $row['tipe'] === 'hutang' ? 'text-red-500' : 'text-green-500'; // Red for Hutang, Green for Bayar
                            echo "<div class='history-item' role='listitem'>
                                <div class='history-desc'>{$row['pengutang']} <span class='{$colorClass}'>{$transactionType}</span> {$row['penerima']}: $jumlahFormatted</div>
                                <div class='history-buttons'>
                                    <a href='../process/edit_transaksi.php?id={$row['id']}'><button class='btn-edit'>Edit</button></a>
                                    <a href='../process/hapus_transaksi.php?id={$row['id']}' onclick=\"return confirm('Yakin hapus?')\"><button class='btn-delete'>Hapus</button></a>
                                </div>
                            </div>";
                        }
                        echo "</div>";
                    }
                    ?>
                </div>
            </section>
        </div>

        <section class="netting" aria-labelledby="nettingLabel" role="region">
            <h2 id="nettingLabel">
                Hasil Netting
                <span id="sortNetting" class="sort-icon" title="Sort Netting Data">⇅</span>
            </h2>
            <div id="nettingList" class="netting-list"></div>
            <div id="nettingTotal" class="netting-total">Total Hutang: Rp 0</div>
        </section>
    </main>

    <script>
        function formatRupiah(number) {
            return "Rp " + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Fetch netting data
        const nettingData = [];
        <?php
        // Calculate netting
        $net = [];
        $query = $conn->query("SELECT * FROM transaksi");
        $data = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row; // Store all transactions in an array
        }

        // Netting logic
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

        // Prepare final result for output
        $hasil_netting = [];
        foreach ($result as $dari => $tujuan) {
            foreach ($tujuan as $ke => $jumlah) {
                if ($jumlah > 0) {
                    $hasil_netting[] = ['from' => $dari, 'to' => $ke, 'jumlah' => $jumlah];
                }
            }
        }

        // Output netting data
        foreach ($hasil_netting as $row) {
            echo "nettingData.push({ penghutang: '{$row['from']}', yangDihutang: '{$row['to']}', nominal: {$row['jumlah']} });";
        }
        ?>

        const nettingList = document.getElementById("nettingList");
        const nettingTotal = document.getElementById("nettingTotal");

        function renderNetting(data) {
            nettingList.innerHTML = "";
            let total = 0;
            data.forEach(item => {
                total += item.nominal;
                const row = document.createElement("div");
                row.className = "netting-row";
                row.innerHTML = `
                    <span class="names">${item.penghutang} hutang ${item.yangDihutang}</span>
                    <span class="nominal">${formatRupiah(item.nominal)}</span>
                `;
                nettingList.appendChild(row);

                // Create a separator line
                const separator = document.createElement("div");
                separator.className = "separator h-px bg-gray-300 my-1"; // Add a horizontal line
                nettingList.appendChild(separator);
            });
            nettingTotal.textContent = `Total Hutang: ${formatRupiah(total)}`;
        }

        renderNetting(nettingData);

        // Sorting
        let sortField = 'penghutang'; // Default sort by debtor
        let sortDirection = 'asc'; // Default sort direction

        function sortNettingData(field) {
            if (sortField === field) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'; // Toggle direction
            } else {
                sortField = field; // Set new field
                sortDirection = 'asc'; // Reset to ascending
            }

            const sorted = [...nettingData].sort((a, b) => {
                let valA = a[field].toLowerCase();
                let valB = b[field].toLowerCase();
                if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
                if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
                return 0;
            });

            renderNetting(sorted);
            updateSortIcon();
        }

        function updateSortIcon() {
            const sortIcon = document.getElementById('sortNetting');
            sortIcon.textContent = sortDirection === 'asc' ? '⇅' : '⇅'; // You can change the icon if needed
        }

        // Add event listener to the sorting icon
        document.getElementById('sortNetting').addEventListener('click', () => {
            sortNettingData(sortField === 'penghutang' ? 'yangDihutang' : 'penghutang');
        });

        // Initial render
        renderNetting(nettingData);

        // Input and tab functionality
        const amountInput = document.getElementById('amountInput');
        const autoAmount = document.getElementById('autoAmount');
        const namaSelect1 = document.getElementById('namaSelect1');
        const namaSelect2 = document.getElementById('namaSelect2');
        const hutangTab = document.getElementById('hutangTab');
        const bayarTab = document.getElementById('bayarTab');

        amountInput.addEventListener('input', function (e) {
            let input = e.target.value.replace(/[^0-9,]/g, '');
            e.target.value = formatRupiah(input);
            autoAmount.value = e.target.value;
        });

        hutangTab.addEventListener('click', () => {
            hutangTab.classList.add('active');
            hutangTab.setAttribute('aria-selected', 'true');
            bayarTab.classList.remove('active');
            bayarTab.setAttribute('aria-selected', 'false');
            document.getElementById('tipeInput').value = 'hutang';
            document.getElementById('submitBtn').textContent = "Submit Hutang";
        });

        bayarTab.addEventListener('click', () => {
            bayarTab.classList.add('active');
            bayarTab.setAttribute('aria-selected', 'true');
            hutangTab.classList.remove('active');
            hutangTab.setAttribute('aria-selected', 'false');
            document.getElementById('tipeInput').value = 'bayar';
            document.getElementById('submitBtn').textContent = "Submit Bayar";
        });

        // Mode toggle functionality
        const toggleModeBtn = document.getElementById('toggleMode');
        const body = document.body;

        // Check localStorage for theme preference
        if (localStorage.getItem('theme') === 'dark') {
            body.classList.add('dark-mode');
            setIconDark();
        } else {
            body.classList.add('light-mode');
            setIconLight();
        }

        function setIconDark() {
            toggleModeBtn.innerHTML = '<svg id="modeIcon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z"/></svg>';
        }

        function setIconLight() {
            toggleModeBtn.innerHTML = '<svg id="modeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
        }

        function toggleMode() {
            if (body.classList.contains('light-mode')) {
                body.classList.replace('light-mode', 'dark-mode');
                setIconDark();
                localStorage.setItem('theme', 'dark'); // Save theme preference
            } else {
                body.classList.replace('dark-mode', 'light-mode');
                setIconLight();
                localStorage.setItem('theme', 'light'); // Save theme preference
            }
        }

        toggleModeBtn.addEventListener('click', toggleMode);
    </script>
</body>

</html>
