<?php include 'inc/header.php'; include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $desc = $_POST['description'];
  $price = floatval($_POST['price']);
  $cat = $_POST['category'];

  $stmt = $db->prepare("INSERT INTO products (name, description, price, category) VALUES (?, ?, ?, ?)");
  $stmt->execute([$name, $desc, $price, $cat]);
  header("Location: urunler.php");
  exit;
}

if (isset($_GET['sil'])) {
  $db->prepare("DELETE FROM products WHERE id = ?")->execute([$_GET['sil']]);
  header("Location: urunler.php");
  exit;
}

$products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header"><h1>Ürünler</h1></div>
<section class="content">
  <div class="container-fluid">

    <form method="post" class="card card-body mb-4">
      <h5>Yeni Ürün Ekle</h5>
      <input name="name" class="form-control mb-2" placeholder="Ürün Adı" required>
      <textarea name="description" class="form-control mb-2" placeholder="Açıklama" rows="2"></textarea>
      <input name="price" class="form-control mb-2" placeholder="Fiyat (örnek: 129.90)" required>
      <select name="category" class="form-control mb-2">
        <option>Windows</option>
        <option>Office</option>
        <option>Other</option>
      </select>
      <button class="btn btn-primary" type="submit">Kaydet</button>
    </form>

    <table class="table table-bordered table-striped">
      <thead>
        <tr><th>ID</th><th>Ad</th><th>Fiyat</th><th>Kategori</th><th>Sil</th></tr>
      </thead>
      <tbody>
        <?php foreach($products as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= $p['price'] ?> TL</td>
          <td><?= $p['category'] ?></td>
          <td><a href="?sil=<?= $p['id'] ?>" onclick="return confirm('Silinsin mi?')" class="btn btn-danger btn-sm">Sil</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</section>

<?php include 'inc/footer.php'; ?>
