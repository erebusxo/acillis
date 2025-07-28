<?php include 'inc/header.php'; include '../config/db.php';

$kullanicilar = $db->query("SELECT id, email, created_at FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header"><h1>Kullanıcılar</h1></div>
<section class="content">
  <div class="container-fluid">

    <table class="table table-bordered table-sm">
      <thead>
        <tr><th>#</th><th>Email</th><th>Kayıt Tarihi</th></tr>
      </thead>
      <tbody>
        <?php foreach ($kullanicilar as $k): ?>
        <tr>
          <td><?= $k['id'] ?></td>
          <td><?= htmlspecialchars($k['email']) ?></td>
          <td><?= $k['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</section>

<?php include 'inc/footer.php'; ?>
