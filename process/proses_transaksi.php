<?php
require '../database/koneksi.php';

$tipe = $_POST['tipe'];
$pengutang = $_POST['pengutang'];
$penerima = $_POST['penerima'];
$jumlah = $_POST['jumlah'];

// Hilangkan Rp dan titik
$jumlah = (int) str_replace(['Rp', '.', ' '], '', $jumlah);

$tanggal = date('Y-m-d');

$stmt = $conn->prepare("INSERT INTO transaksi (tanggal, pengutang, penerima, jumlah, tipe) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$tanggal, $pengutang, $penerima, $jumlah, $tipe]);

header("Location: ../admin/index.php");
exit;
?>
