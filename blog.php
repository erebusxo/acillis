<?php
session_start();
require_once '../admin/inc/db.php';

// Sayfalama
$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$limit = 9;
$offset = ($sayfa - 1) * $limit;

// Blog yazılarını al
try {
    // Toplam yazı sayısı
    $stmt = $db->query("SELECT COUNT(*) as total FROM blog_posts WHERE status = 'published'");
    $total = $stmt->fetch()['total'];
    $toplam_sayfa = ceil($total / $limit);
    
    // Blog yazıları
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $blog_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $blog_posts = [];
    $toplam_sayfa = 1;
}

// SEO ayarları
$page_title = "Blog - Yazılım Lisansları Hakkında En Güncel Bilgiler | Acil Lisans";
$page_description = "Windows, Office, Adobe lisansları hakkında rehberler, güncel haberler ve uzman tavsiyeleri. Lisans satın alma öncesi bilmeniz gerekenler.";
$page_keywords = "yazılım lisansı rehberi, windows lisans, office lisans, adobe lisans, lisans satın alma, yazılım blog";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $page_description; ?>">
    <meta property="og:type" content="website">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.18);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a3e 100%);
            min-height: 100vh;
            color: white;
        }

        .navbar-custom {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(135deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-brand i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-right: 10px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #fff !important;
            transform: translateY(-2px);
        }

        .hero-section {
            background: var(--primary-gradient);
            padding: 150px 0 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="25" cy="25" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="25" r="1.5" fill="rgba(255,255,255,0.05)"/><circle cx="25" cy="75" r="1" fill="rgba(255,255,255,0.15)"/><circle cx="75" cy="75" r="2.5" fill="rgba(255,255,255,0.03)"/></svg>') repeat;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .blog-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            transition: all 0.4s ease;
            height: 100%;
            overflow: hidden;
        }

        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .blog-meta {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .pagination .page-link {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: white;
            backdrop-filter: blur(10px);
        }

        .pagination .page-link:hover {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: transparent;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shield-keyhole"></i>Acil Lisans
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="blog.php">Blog</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="lisanslarim.php">
                                <i class="fas fa-list me-1"></i>Lisanslarım
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Çıkış
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Giriş
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Kayıt
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="container">
                <h1 class="display-4 fw-bold mb-4">Yazılım Dünyasından Haberler</h1>
                <p class="lead mb-0">Lisanslar, güncellemeler ve uzman tavsiyeleri</p>
            </div>
        </div>
    </section>

    <!-- Blog Content -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($blog_posts)): ?>
                <div class="row">
                    <div class="col-12 text-center">
                        <div class="py-5">
                            <i class="fas fa-blog fa-5x mb-4" style="color: rgba(255,255,255,0.3);"></i>
                            <h3 class="mb-3">Henüz blog yazısı yok</h3>
                            <p class="text-white-50 mb-4">Yakında faydalı içerikler paylaşmaya başlayacağız.</p>
                            <a href="index.php" class="btn btn-primary-modern">
                                <i class="fas fa-home me-2"></i>Ana Sayfaya Dön
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($blog_posts as $post): ?>
                        <div class="col-lg-4 col-md-6">
                            <article class="blog-card">
                                <div class="p-4">
                                    <div class="blog-meta mb-3">
                                        <i class="fas fa-calendar me-2"></i>
                                        <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                                    </div>
                                    <h4 class="mb-3">
                                        <a href="blog-detay.php?slug=<?php echo $post['slug']; ?>" 
                                           class="text-white text-decoration-none">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h4>
                                    <p class="text-white-50 mb-4">
                                        <?php echo htmlspecialchars(substr($post['excerpt'], 0, 150)); ?>...
                                    </p>
                                    <a href="blog-detay.php?slug=<?php echo $post['slug']; ?>" 
                                       class="btn btn-primary-modern">
                                        <i class="fas fa-arrow-right me-2"></i>Devamını Oku
                                    </a>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($toplam_sayfa > 1): ?>
                    <div class="row mt-5">
                        <div class="col-12">
                            <nav aria-label="Blog sayfalama">
                                <ul class="pagination justify-content-center">
                                    <?php if ($sayfa > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?sayfa=<?php echo $sayfa - 1; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $sayfa - 2); $i <= min($toplam_sayfa, $sayfa + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $sayfa ? 'active' : ''; ?>">
                                            <a class="page-link" href="?sayfa=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($sayfa < $toplam_sayfa): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?sayfa=<?php echo $sayfa + 1; ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>