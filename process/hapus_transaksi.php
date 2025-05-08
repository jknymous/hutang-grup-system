<?php
require '../database/koneksi.php';

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM transaksi WHERE id = ?");
$stmt->execute([$id]);

header("Location: ../admin/index.php");
exit;
?>
