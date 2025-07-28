<?php include 'inc/header.php'; include '../config/db.php'; ?>

<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0">Yönetim Paneline Hoş Geldiniz</h1>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="row">

      <?php
      $urunSayisi = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
      $lisansSayisi = $db->query("SELECT COUNT(*) FROM licenses")->fetchColumn();
      $kullaniciSayisi = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
      $siparisSayisi = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
      ?>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner"><h3><?= $urunSayisi ?></h3><p>Toplam Ürün</p></div>
          <div class="icon"><i class="fas fa-box"></i></div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner"><h3><?= $lisansSayisi ?></h3><p>Toplam Lisans</p></div>
          <div class="icon"><i class="fas fa-key"></i></div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
          <div class="inner"><h3><?= $kullaniciSayisi ?></h3><p>Kullanıcı</p></div>
          <div class="icon"><i class="fas fa-users"></i></div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
          <div class="inner"><h3><?= $siparisSayisi ?></h3><p>Sipariş</p></div>
          <div class="icon"><i class="fas fa-receipt"></i></div>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include 'inc/footer.php'; ?>
