<?php include 'inc/header.php'; include '../config/db.php';

$products = $db->query("SELECT id, name FROM products ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Lisans ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $urun_id = intval($_POST['product_id']);
  $lisanslar = explode("\n", trim($_POST['licenses']));
  $stmt = $db->prepare("INSERT INTO licenses (product_id, license_key) VALUES (?, ?)");

  foreach ($lisanslar as $key) {
    if (strlen(trim($key)) > 0) {
      $stmt->execute([$urun_id, trim($key)]);
    }
  }
  header("Location: lisanslar.php");
  exit;
}

$lisanslar = $db->query("SELECT l.id, l.license_key, l.is_sold, p.name AS product_name
                         FROM licenses l
                         JOIN products p ON l.product_id = p.id
                         ORDER BY l.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header"><h1>Lisans Yönetimi</h1></div>
<section class="content">
  <div class="container-fluid">

    <form method="post" class="card card-body mb-4">
      <h5>Yeni Lisans Ekle</h5>
      <select name="product_id" class="form-control mb-2" required>
        <?php foreach ($products as $p): ?>
          <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
        <?php endforeach; ?>
      </select>
      <textarea name="licenses" class="form-control mb-2" rows="5" placeholder="Her satıra bir lisans anahtarı girin..." required></textarea>
      <button class="btn btn-success" type="submit">Kaydet</button>
    </form>

    <table class="table table-bordered table-sm">
      <thead>
        <tr><th>ID</th><th>Ürün</th><th>Lisans</th><th>Durum</th></tr>
      </thead>
      <tbody>
        <?php foreach ($lisanslar as $l): ?>
        <tr>
          <td><?= $l['id'] ?></td>
          <td><?= htmlspecialchars($l['product_name']) ?></td>
          <td><?= htmlspecialchars($l['license_key']) ?></td>
          <td><?= $l['is_sold'] ? '<span class="badge badge-danger">Kullanıldı</span>' : '<span class="badge badge-success">Hazır</span>' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</section>

<?php include 'inc/footer.php'; ?>
