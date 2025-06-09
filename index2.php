<?php
session_start();
require 'database/koneksi.php';

// ======================
// Ambil dan hitung netting
$query = $conn->query("SELECT * FROM transaksi");
$dataTransaksi = [];
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $dataTransaksi[] = $row;
}

$net = [];
foreach ($dataTransaksi as $d) {
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

$resultNetting = [];
$processed = [];
foreach ($net as $from => $list) {
    foreach ($list as $to => $val) {
        if (isset($processed[$from][$to]) || isset($processed[$to][$from])) continue;
        $val2 = $net[$to][$from] ?? 0;
        if ($val > $val2) {
            $resultNetting[$from][$to] = $val - $val2;
        } elseif ($val2 > $val) {
            $resultNetting[$to][$from] = $val2 - $val;
        }
        $processed[$from][$to] = true;
        $processed[$to][$from] = true;
    }
}

$hasil_netting = [];
foreach ($resultNetting as $dari => $tujuanArr) {
    foreach ($tujuanArr as $ke => $jumlah) {
        if ($jumlah > 0) {
            $hasil_netting[] = [
                'from'   => $dari,
                'to'     => $ke,
                'jumlah' => $jumlah
            ];
        }
    }
}

// Total hutang keseluruhan
$totalKeseluruhan = array_reduce($hasil_netting, function($carry, $item) {
    return $carry + $item['jumlah'];
}, 0);

// Cari pemenang (creditor dengan jumlah tertinggi)
$sumByCreditor = [];
foreach ($hasil_netting as $r) {
    $creditor = $r['to'];
    $sumByCreditor[$creditor] = ($sumByCreditor[$creditor] ?? 0) + $r['jumlah'];
}
$namaMaxCreditor = null;
$maxCreditorAmount = 0;
foreach ($sumByCreditor as $nama => $sum) {
    if ($sum > $maxCreditorAmount) {
        $maxCreditorAmount = $sum;
        $namaMaxCreditor = $nama;
    }
}

// JSON untuk JS
$jsonNetting = json_encode($hasil_netting, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

// Data rekening/Ewallet
$rekening = [
  'Agus'   => [
      ['bank' => 'Bank BCA', 'noRek' => '8890785957'],
      ['bank' => 'GOPAY', 'noRek' => '082172040214']
  ],
  'Erik'   => [
      ['bank' => 'Bank BCA', 'noRek' => '8520308475']
  ],
  'Jerry'  => [
      ['bank' => 'OVO GOPAY', 'noRek' => '081277447620']
  ],
  'Maik'   => [
      ['bank' => 'Bank BCA', 'noRek' => '5271880716']
  ],
  'Rizz'   => [
      ['bank' => 'Bank BCA', 'noRek' => '8210860321'],
      ['bank' => 'OVO GOPAY DANA', 'noRek' => '081277645283']
  ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Buku Nikah Rizz</title>

  <!-- Tailwind CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"/>

  <style>
    /* ===== Mode Light / Dark ===== */
    .light-mode {
      background-color: #e8f3ff;
      color: #000000;
    }
    .light-mode header {
      background-color: white;
      color: #1e40af;
    }
    .light-mode header h1 {
      color: #1e40af;
    }
    .light-mode #toggleMode {
      border: 1px solid #1e40af;
      color: #1e40af;
      cursor: pointer;
    }
    .light-mode .box {
      background-color: white;
      color: #000000;
    }
    .dark-mode {
      background-color: #0b1423;
      color: #ffffff;
    }
    .dark-mode header {
      background-color: #0b1423;
      color: #bfa742;
    }
    .dark-mode header h1 {
      color: #bfa742;
    }
    .dark-mode #toggleMode {
      border: 1px solid #bfa742;
      color: #bfa742;
      cursor: pointer;
    }
    .dark-mode .box {
      background-color: #1e293b;
      color: #ffffff;
      box-shadow: 0 3px 12px rgb(255 215 0 / 0.3);
    }
    button {
      border-radius: 0.375rem;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0.5rem;
      outline: none;
      border: none;
    }

    /* Transisi warna */
    body, header, #toggleMode, h1 {
      transition: background-color 0.4s ease, color 0.4s ease;
    }

    /* ===== Header ===== */
    header {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 20;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    header img {
      width: 40px; height: 40px;
      object-fit: cover;
    }
    header h1 {
      font-weight: 700;
      font-size: 1.25rem;
      flex-grow: 1;
      text-align: center;
      user-select: none;
    }

    /* ===== Main Container (Stacked Vertically) ===== */
    main {
      margin-top: 4.5rem; /* ruang untuk header */
      padding: 1rem;
      max-width: 1200px;
      margin-left: auto;
      margin-right: auto;
    }

    /* ===== Box Umum ===== */
    .box {
      border-radius: 0.5rem;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      padding: 1.5rem;
      margin-bottom: 2rem;
      background-color: white;
      color: #000000;
    }
    .dark-mode .box {
      background-color: #1e293b;
      color: #f3f4f6;
    }

    /* ===== Box Data Hutang ===== */
    #dataHutangBox h2 {
      display: flex;
      align-items: center;
      justify-content: center;
      color: #1e40af;
    }
    .dark-mode #dataHutangBox h2 {
      color: #bfa742;
    }
    #sortIcon {
      cursor: pointer;
      width: 1.5rem; /* Adjust size if needed */
      height: 1.5rem;
      stroke-width: 2;
      stroke: currentColor;
      transition: transform 0.3s ease, color 0.3s ease;
    }
    #sortIcon:hover {
      color: #3b82f6; /* Change color on hover */
      transform: scale(1.1); /* Slightly enlarge on hover */
    }

    /* Judul "Netting Total" */
    .judul-netting {
      font-size: 1.5rem;
      font-weight: 600;
    }

    /* Hapus scroll pada data, agar full tampil */
    #dataHutangList {
      padding-right: 0.5rem;
    }
    .hutang-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.5rem 0;
    }
    .hutang-row .text-debtor,
    .hutang-row .text-creditor {
      flex: 2;
      color: #111827;
    }
    .dark-mode .hutang-row .text-debtor,
    .dark-mode .hutang-row .text-creditor {
      color: #f3f4f6;
    }
    .hutang-row .text-amount {
      flex: 1;
      text-align: right;
      font-weight: 600;
      color: #111827;
    }
    .dark-mode .hutang-row .text-amount {
      color: #bfa742;
    }
    .hutang-separator {
      border-top: 2px solid #e5e7eb;
      margin: 0.5rem 0;
    }
    .dark-mode .hutang-separator {
      border-color: #475569;
    }
    
    /* Total hutang bold */
    #totalHutang {
      margin-top: 1rem;
      text-align: right;
      font-size: 1.25rem;
      font-weight: 700;
      color: #1e40af;
    }
    .dark-mode #totalHutang {
      color: #bfa742;
    }

    /* ===== Container WINNER ===== */
    .winning-text {
      background-color: #f3f4f6;
    }
    .dark-mode .winning-text {
      background-color: #334155;
    }
    .highlight-text {
      color: #1e40af;
    }
    .dark-mode .highlight-text {
      color: #bfa742;
    }
    .winning-text:hover {
      transform: scale(1.01);
      transition: all 0.3s ease;
    }

    /* ===== Box No Rek (Rekening/E-Wallet) ===== */
    #rekeningBox h2 {
      color: #1e40af;
    }
    .dark-mode #rekeningBox h2 {
      color: #bfa742;
    }
    .rekening-item {
      margin-bottom: 0.75rem;
    }
    .rekening-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 1rem;
      background-color: #f3f4f6;
      cursor: pointer;
      border: 1px solid #cbd5e1;
      border-radius: 0.375rem;
      transition: background-color 0.3s;
    }
    .rekening-header:hover {
      background-color: #e2e8f0;
    }
    .dark-mode .rekening-header {
      background-color: #334155;
      border-color: #475569;
      color: #e0e0e0;
    }
    .rekening-content {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease;
      background-color: #ffffff;
      border: 1px solid #cbd5e1;
      border-top: none;
      border-radius: 0 0 0.375rem 0.375rem;
    }
    .dark-mode .rekening-content {
      background-color: #1e293b;
      border-color: #475569;
      color: #f3f4f6;
    }
    .rekening-content.open {
      max-height: 200px;
    }
    .rekening-num {
      font-family: monospace;
      font-size: 1rem;
      color: #111827;
    }
    .dark-mode .rekening-num {
      color: #f3f4f6;
    }
    .copy-btn {
      background-color: #1e40af;
      color: #ffffff;
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 0.375rem;
      cursor: pointer;
      transition: opacity 0.3s;
    }
    .copy-btn:hover {
      opacity: 0.9;
    }
    .dark-mode .copy-btn {
      background-color: #bfa742;
      color: #0b1423;
    }

    /* ===== Data rek ===== */
    .rekening-num {
      background-color: #e2e8f0;
      padding: 0.5rem; 
      border-radius: 0.375rem; 
      display: inline-block; 
      margin-right: 1rem; 
      flex: 1; 
    }
    .dark-mode .rekening-num {
      background-color: #475569; 
      color: #f3f4f6; 
    }
    .copy-btn {
      background-color: #1e40af;
      color: #ffffff; 
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 0.375rem; 
      cursor: pointer; 
      transition: opacity 0.3s; 
    }
    .copy-btn:hover {
      opacity: 0.9; 
    }
    .rekening-item {
      margin-bottom: 1rem;
    }

    @media  (max-width: 350px) {
      .judul-netting {
        font-size: 1.2rem;
      }
      #sortIcon {
        width: 1.1rem;
        height: 1.1rem;
      }
      .hutang-row {
        font-size: 0.8rem;
      }
      #totalHutang {
        font-size: 1rem;
      }
      .winning-text p {
        font-size: 0.8rem;
        font-weight: 400; 
      }
      #rekeningBox h2 {
        font-size: 1.2rem;
      }
      .rekening-header {
        font-size: 0.9rem;
      }
      .rekening-content.open {
        font-size: 0.8rem;
      }
      .rekening-num {
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body class="light-mode">
  <!-- HEADER -->
  <header>
    <img src="assets/foto/agus.png" alt="Logo" class="w-10 h-10 object-cover"/>
    <h1>Buku Nikah Rizz</h1>
    <button id="toggleMode" aria-label="Toggle Dark/Light Mode" title="Toggle Mode" type="button">
      <svg id="modeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"
            aria-hidden="true" focusable="false" class="w-6 h-6">
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
  </header>

  <main>
    <!-- BOX DATA HUTANG (di atas) -->
    <section id="dataHutangBox" class="box">
      <h2 class="flex justify-between items-center">
        <span class="judul-netting">Netting Total</span>
        <svg id="sortIcon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"
          aria-hidden="true" focusable="false" class="w-6 h-6 cursor-pointer">
          <path d="M8 9l4-4 4 4M8 15l4 4 4-4" />
        </svg>
      </h2>

      <!-- Daftar Hutang penuh tanpa scroll -->
      <div id="dataHutangList"></div>

      <!-- Total Hutang -->
      <div id="totalHutang">Total Hutang Rp <?= number_format($totalKeseluruhan, 0, ',', '.') ?></div>

      <!-- Hutang Terbanyak di Paling Bawah -->
      <?php if ($namaMaxCreditor !== null): ?>
        <div class="winning-text mt-6 p-4 rounded text-center">
          <p class="text-xl font-bold"> <!-- Increased font size to text-xl and made it bold -->
            Hutang terbanyak dipegang oleh 
            <span class="highlight-text"><?= htmlspecialchars($namaMaxCreditor) ?></span>
            dengan Jumlah 
            <span class="highlight-text">Rp <?= number_format($maxCreditorAmount, 0, ',', '.') ?></span>
          </p>
          <img src="<?php
            $pathFoto = "assets/foto/" . strtolower($namaMaxCreditor) . ".jpg";
            if (!file_exists($pathFoto)) {
              $pathFoto = "assets/foto/agus.png";
            }
            echo $pathFoto;
          ?>" alt="<?= htmlspecialchars($namaMaxCreditor) ?>" class="winner-photo w-44 h-44 mx-auto mt-2 rounded-lg"/> <!-- Added rounded-lg for slight border radius -->
        </div>
      <?php endif; ?>
    </section>

    <!-- BOX NO REK / E-WALLET (di bawah) -->
    <section id="rekeningBox" class="box">
      <h2 class="text-2xl font-semibold mb-4 text-center">Nomor Rekening / E-Wallet</h2>
      <div id="rekeningContainer">
      <?php foreach ($rekening as $nama => $dataList): ?>
  <div class="rekening-item">
    <div class="rekening-header" data-nama="<?= htmlspecialchars($nama) ?>">
      <span><?= htmlspecialchars($nama) ?></span>
      <svg class="w-5 h-5 transform transition-transform" data-icon="<?= htmlspecialchars($nama) ?>"
            xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"
            aria-hidden="true" focusable="false">
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>
    </div>
    <div class="rekening-content" data-content="<?= htmlspecialchars($nama) ?>">
      <div class="p-4 flex flex-col">
        <?php foreach ($dataList as $data): ?>
          <div class="flex items-center justify-between mb-2"> <!-- Tambahkan margin bawah -->
            <span class="text-gray-600 mr-2"><?= htmlspecialchars($data['bank']) ?></span> <!-- Keterangan bank di sebelah kiri -->
            <span class="rekening-num"><?= htmlspecialchars($data['noRek']) ?></span> <!-- Hanya nomor rekening -->
            <button class="copy-btn" data-copy="<?= htmlspecialchars($data['noRek']) ?>">Copy</button>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>

      </div>
    </section>
  </main>

  <script>
    // ----------- Toggle Light / Dark Mode -------------
    const toggleModeBtn = document.getElementById('toggleMode');
    const bodyEl = document.body;
    if (localStorage.getItem('theme') === 'dark') {
      bodyEl.classList.replace('light-mode', 'dark-mode');
      setIconDark();
    } else {
      bodyEl.classList.add('light-mode');
      setIconLight();
    }
    function setIconDark() {
      toggleModeBtn.innerHTML = '<svg id="modeIcon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false" class="w-6 h-6"><path d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z"/></svg>';
    }
    function setIconLight() {
      toggleModeBtn.innerHTML = '<svg id="modeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" focusable="false" class="w-6 h-6"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
    }
    function toggleMode() {
      if (bodyEl.classList.contains('light-mode')) {
        bodyEl.classList.replace('light-mode', 'dark-mode');
        localStorage.setItem('theme', 'dark');
        setIconDark();
      } else {
        bodyEl.classList.replace('dark-mode', 'light-mode');
        localStorage.setItem('theme', 'light');
        setIconLight();
      }
    }
    toggleModeBtn.addEventListener('click', toggleMode);

    // ----------- Render & Sort Data Hutang --------------
    const nettingData = <?= $jsonNetting ?>;
    let sortField = 'from';
    let sortDirection = 'asc';

    // Tombol sorting di judul
    const sortIcon = document.getElementById('sortIcon');
    sortIcon.addEventListener('click', () => {
      sortField = (sortField === 'from') ? 'to' : 'from'; // Toggle sort field
      sortNetting(sortField);
    });

    // Render Data Hutang
    function renderDataHutang(data, groupByField) {
      const container = document.getElementById('dataHutangList');
      container.innerHTML = '';
      let currentGroupKey = null;

      data.forEach(row => {
        const groupKey = row[groupByField]; // either 'from' or 'to'
        const debtor = row.from;
        const creditor = row.to;
        const amount = row.jumlah;

        // Insert separator if the grouping key changes (and not first item)
        if (currentGroupKey !== null && currentGroupKey !== groupKey) {
          const sep = document.createElement('div');
          sep.className = 'hutang-separator';
          container.appendChild(sep);
        }
        currentGroupKey = groupKey;

        const divRow = document.createElement('div');
        divRow.className = 'hutang-row';
    
        // Update the text content to include "hutang"
        const debtText = document.createElement('div');
        debtText.className = 'text-debtor';
        debtText.textContent = `${debtor} hutang ${creditor}`; // Updated line

        const amountText = document.createElement('div');
        amountText.className = 'text-amount';
        amountText.textContent = 'Rp ' + amount.toLocaleString('id-ID');
    
        divRow.appendChild(debtText);
        divRow.appendChild(amountText); // Removed creditor text
        container.appendChild(divRow);
      });

      const totalElem = document.getElementById('totalHutang');
      totalElem.textContent = 'Total Hutang Rp ' + data.reduce((sum, r) => sum + r.jumlah, 0).toLocaleString('id-ID');
    }

    // Sorting Netting
    function sortNetting(field) {
      const sorted = [...nettingData].sort((a, b) => {
        let valA = a[field].toLowerCase();
        let valB = b[field].toLowerCase();
        return valA.localeCompare(valB); // Always sort in ascending order
      });
      renderDataHutang(sorted, field); // Pass the field to renderDataHutang
    }

    // Render awal default by 'from'
    sortNetting('from');

    // ----------- Dropdown Rekening / E-Wallet --------------
    document.querySelectorAll('.rekening-header').forEach(header => {
      header.addEventListener('click', () => {
        const nama = header.getAttribute('data-nama');
        const content = document.querySelector(`.rekening-content[data-content="${nama}"]`);
        const icon = header.querySelector('svg');
        document.querySelectorAll('.rekening-content').forEach(div => {
          if (div !== content) {
            div.classList.remove('open');
            const nm = div.getAttribute('data-content');
            const ic = document.querySelector(`.rekening-header[data-nama="${nm}"] svg`);
            if (ic) ic.style.transform = 'rotate(0deg)';
          }
        });
        if (content.classList.contains('open')) {
          content.classList.remove('open');
          icon.style.transform = 'rotate(0deg)';
        } else {
          content.classList.add('open');
          icon.style.transform = 'rotate(180deg)';
        }
      });
    });

    // ----------- Copy ke Clipboard -------------- 
    document.querySelectorAll('.copy-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const nomor = btn.getAttribute('data-copy');
        navigator.clipboard.writeText(nomor).then(() => {
          btn.textContent = 'Copied';
          setTimeout(() => {
            btn.textContent = 'Copy';
          }, 3000);
        }).catch(err => console.error('Gagal copy:', err));
      });
    });
  </script>
</body>
</html>
