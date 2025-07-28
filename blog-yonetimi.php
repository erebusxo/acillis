<?php
session_start();
require_once 'inc/db.php';

// Admin kontrolü
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$message_type = '';

// İşlemler
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($action === 'delete' && $id > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Blog yazısı başarıyla silindi.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Silme işlemi başarısız: ' . $e->getMessage();
            $message_type = 'danger';
        }
    } elseif ($action === 'toggle_status' && $id > 0) {
        try {
            $stmt = $db->prepare("UPDATE blog_posts SET status = CASE WHEN status = 'published' THEN 'draft' ELSE 'published' END WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Yazı durumu değiştirildi.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Durum değiştirme başarısız: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Blog yazılarını al
try {
    $stmt = $db->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $posts = [];
}

// İstatistikler
$stats = [
    'total' => count($posts),
    'published' => count(array_filter($posts, function($p) { return $p['status'] === 'published'; })),
    'draft' => count(array_filter($posts, function($p) { return $p['status'] === 'draft'; }))
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Yönetimi - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border: none; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .navbar-brand { font-weight: bold; }
        .stats-card { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
        .stats-card .card-body { padding: 1.5rem; }
        .table-hover tbody tr:hover { background-color: rgba(0,123,255,.075); }
        .status-badge { font-size: 0.75rem; }
        .action-buttons .btn { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shield-keyhole me-2"></i>Admin Panel
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../site/index.php" target="_blank">
                        <i class="fas fa-external-link-alt me-1"></i>Siteyi Görüntüle
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../site/blog.php" target="_blank">
                        <i class="fas fa-blog me-1"></i>Blog Sayfası
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Çıkış
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-light sidebar py-3">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="urunler.php">
                                <i class="fas fa-box me-2"></i>Ürünler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="lisanslar.php">
                                <i class="fas fa-key me-2"></i>Lisanslar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="siparisler.php">
                                <i class="fas fa-shopping-cart me-2"></i>Siparişler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="musteri_listesi.php">
                                <i class="fas fa-users me-2"></i>Müşteriler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="blog-yonetimi.php">
                                <i class="fas fa-blog me-2"></i>Blog Yönetimi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="iletisim-ayarlari.php">
                                <i class="fas fa-comments me-2"></i>İletişim Ayarları
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="site-ayarlari.php">
                                <i class="fas fa-cogs me-2"></i>Site Ayarları
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-4 py-3">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">Blog Yönetimi</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="blog-ekle.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Yeni Yazı Ekle
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- İstatistikler -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-blog fa-2x mb-2"></i>
                                <h4><?php echo $stats['total']; ?></h4>
                                <p class="mb-0">Toplam Yazı</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body text-center">
                                <i class="fas fa-eye fa-2x mb-2"></i>
                                <h4><?php echo $stats['published']; ?></h4>
                                <p class="mb-0">Yayında</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-edit fa-2x mb-2"></i>
                                <h4><?php echo $stats['draft']; ?></h4>
                                <p class="mb-0">Taslak</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Blog Yazıları Tablosu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Blog Yazıları</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($posts)): ?>
                            <div class="text-center p-5">
                                <i class="fas fa-blog fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Henüz blog yazısı yok</h5>
                                <p class="text-muted">İlk blog yazınızı eklemek için butona tıklayın.</p>
                                <a href="blog-ekle.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Yeni Yazı Ekle
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Başlık</th>
                                            <th>Durum</th>
                                            <th>Oluşturma Tarihi</th>
                                            <th>Son Güncelleme</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                                        <?php if (!empty($post['excerpt'])): ?>
                                                            <br><small class="text-muted">
                                                                <?php echo htmlspecialchars(substr($post['excerpt'], 0, 100)); ?>...
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($post['status'] === 'published'): ?>
                                                        <span class="badge bg-success status-badge">
                                                            <i class="fas fa-eye me-1"></i>Yayında
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning status-badge">
                                                            <i class="fas fa-edit me-1"></i>Taslak
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <small><?php echo date('d.m.Y H:i', strtotime($post['updated_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <?php if ($post['status'] === 'published'): ?>
                                                            <a href="../site/blog-detay.php?slug=<?php echo $post['slug']; ?>" 
                                                               class="btn btn-sm btn-info" target="_blank" title="Görüntüle">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <a href="blog-duzenle.php?id=<?php echo $post['id']; ?>" 
                                                           class="btn btn-sm btn-primary" title="Düzenle">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <a href="?action=toggle_status&id=<?php echo $post['id']; ?>" 
                                                           class="btn btn-sm btn-<?php echo $post['status'] === 'published' ? 'warning' : 'success'; ?>" 
                                                           title="<?php echo $post['status'] === 'published' ? 'Taslağa Çevir' : 'Yayınla'; ?>"
                                                           onclick="return confirm('Durumu değiştirmek istediğinizden emin misiniz?')">
                                                            <i class="fas fa-<?php echo $post['status'] === 'published' ? 'eye-slash' : 'eye'; ?>"></i>
                                                        </a>
                                                        
                                                        <a href="?action=delete&id=<?php echo $post['id']; ?>" 
                                                           class="btn btn-sm btn-danger" title="Sil"
                                                           onclick="return confirm('Bu yazıyı silmek istediğinizden emin misiniz?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- SEO Bilgileri -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>SEO İpuçları
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-lightbulb text-warning me-2"></i>Blog Yazısı İpuçları:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>En az 1000 kelime uzunluğunda olsun</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Hedef kelimelerinizi başlıkta kullanın</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Meta açıklama 150-160 karakter olsun</li>
                                    <li><i class="fas fa-check text-success me-2"></i>İç linkleri kullanın</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-keywords text-primary me-2"></i>Popüler Anahtar Kelimeler:</h6>
                                <ul class="list-unstyled">
                                    <li><code>windows lisans satın al</code></li>
                                    <li><code>office 365 lisans</code></li>
                                    <li><code>orijinal yazılım lisansı</code></li>
                                    <li><code>adobe lisans fiyatları</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>