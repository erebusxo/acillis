<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Yönetim Paneli</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
      </li>
    </ul>
  </nav>
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <span class="brand-text font-weight-light">Lisans Paneli</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item"><a href="index.php" class="nav-link"><i class="nav-icon fas fa-home"></i><p>Ana Sayfa</p></a></li>
          <li class="nav-item"><a href="urunler.php" class="nav-link"><i class="nav-icon fas fa-box"></i><p>Ürünler</p></a></li>
          <li class="nav-item"><a href="lisanslar.php" class="nav-link"><i class="nav-icon fas fa-key"></i><p>Lisanslar</p></a></li>
          <li class="nav-item"><a href="siparisler.php" class="nav-link"><i class="nav-icon fas fa-receipt"></i><p>Siparişler</p></a></li>
          <li class="nav-item"><a href="musteri_listesi.php" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Müşteriler</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>
  <div class="content-wrapper p-3">
