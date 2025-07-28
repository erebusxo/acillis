<?php include 'inc/header.php'; include '../config/db.php';

$siparisler = $db->query("SELECT o.id, o.status, o.created_at, 
    u.email, p.name AS product_name, l.license_key
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    LEFT JOIN licenses l ON o.license_id = l.id
    ORDER BY o.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header"><h1>Siparişler</h1></div>
<section class="content">
  <div class="container-fluid">

    <table class="table table-bordered table-sm">
      <thead>
        <tr>
          <th>#</th><th>Kullanıcı</th><th>Ürün</th><th>Lisans</th><th>Durum</th><th>Tarih</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($siparisler as $s): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= htmlspecialchars($s['email']) ?></td>
          <td><?= htmlspecialchars($s['product_name']) ?></td>
          <td><?= $s['license_key'] ? htmlspecialchars($s['license_key']) : '-' ?></td>
          <td><?= $s['status'] ?></td>
          <td><?= $s['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</section>

<?php include 'inc/footer.php'; ?>
