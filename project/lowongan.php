<?php

require_once __DIR__ . '/models/TabelLowongan.php';
require_once __DIR__ . '/views/ViewLowongan.php';
require_once __DIR__ . '/presenters/PresenterLowongan.php';

$model = new TabelLowongan('localhost', 'mvp_kandidat_db', 'root', '');
$view = new ViewLowongan();
$presenter = new PresenterLowongan($model, $view);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $departemen = trim($_POST['departemen'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $kuota = max(0, (int)($_POST['kuota'] ?? 0));
    $status = $_POST['status'] ?? 'Dibuka';

    if ($action === 'tambah') {
        $presenter->tambahLowongan($judul, $departemen, $lokasi, $kuota, $status);
    } elseif ($action === 'ubah' && $id > 0) {
        $presenter->ubahLowongan($id, $judul, $departemen, $lokasi, $kuota, $status);
    } elseif ($action === 'hapus' && $id > 0) {
        $presenter->hapusLowongan($id);
    }
    header("Location: lowongan.php");
    exit();
} else {
    $screen = $_GET['screen'] ?? 'list';
    if ($screen === 'add') {
        echo $presenter->tampilkanFormLowongan();
    } elseif ($screen === 'edit' && isset($_GET['id'])) {
        echo $presenter->tampilkanFormLowongan((int)$_GET['id']);
    } else {
        echo $presenter->tampilkanLowongan();
    }
}
?>