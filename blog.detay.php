<?php
session_start();
require_once '../admin/inc/db.php';

// Slug kontrolü
if (!isset($_GET['slug']) or empty($_GET['slug'])) {
    header('Location: blog.php');
    exit;
}

$slug = $_GET['slug'];

// Blog yazısını al
try {
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header('Location: blog.php');
        exit;
    }
    
    // Diğer yazıları al (benzer içerik)
    $stmt = $db->prepare("SELECT id, title, slug, excerpt, created_at FROM blog_posts WHERE id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$post['id']]);
    $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    header('Location: blog.php');
    exit;
}

// SEO ayarları
$page_title = !empty($post['meta_title']) ? $post['meta_title'] : $post['title'] . ' | Acil Lisans Blog';
$page_description = !empty($post['meta_description']) ? $post['meta_description'] : substr(strip_tags($post['content']), 0, 160);
$page_keywords = !empty($post['meta_keywords']) ? $post['meta_keywords'] : 'yazılım lisansı, ' . strtolower($post['title']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:type" content="article">
    <meta property="article:published_time" content="<?php echo date('c', strtotime($post['created_at'])); ?>">
    
    <!-- Schema.org -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "<?php echo htmlspecialchars($post['title']); ?>",
        "description": "<?php echo htmlspecialchars($page_description); ?>",
        "datePublished": "<?php echo date('c', strtotime($post['created_at'])); ?>",
        "dateModified": "<?php echo date('c', strtotime($post['updated_at'])); ?>",
        "author": {
            "@type": "Organization",
            "name": "Acil Lisans"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Acil Lisans"
        }
    }
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    
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
            line-height: 1.7;
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

        .article-header {
            background: var(--primary-gradient);
            padding: 150px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .article-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="25" cy="25" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="25" r="1.5" fill="rgba(255,255,255,0.05)"/></svg>') repeat;
        }

        .article-content {
            position: relative;
            z-index: 2;
        }

        .article-body {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
            margin-top: -50px;
            position: relative;
            z-index: 3;
        }

        .article-body h2 {
            color: #fff;
            font-weight: 600;
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(102, 126, 234, 0.3);
        }

        .article-body h3 {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            margin: 1.5rem 0 1rem;
        }

        .article-body p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .article-body ul, .article-body ol {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }

        .article-body li {
            margin-bottom: 0.5rem;
        }

        .article-body strong {
            color: #fff;
            font-weight: 600;
        }

        .article-body blockquote {
            background: rgba(102, 126, 234, 0.1);
            border-left: 4px solid #667eea;
            padding: 1rem 1.5rem;
            margin: 1.5rem 0;
            border-radius: 0 10px 10px 0;
        }

        .article-meta {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }

        .social-share {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .share-btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .share-whatsapp {
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
        }

        .share-telegram {
            background: linear-gradient(135deg, #0088cc, #005f8a);
            color: white;
        }

        .share-twitter {
            background: linear-gradient(135deg, #1da1f2, #0d8bd9);
            color: white;
        }

        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .related-posts {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 2rem;
        }

        .related-post-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            height: 100%;
        }

        .related-post-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
        }

        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: rgba(255, 255, 255, 0.9);
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            color: white;
        }

        @media (max-width: 768px) {
            .article-body {
                padding: 1.5rem;
            }
            
            .article-header {
                padding: 120px 0 60px;
            }
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
                        <a class="nav-link" href="blog.php">Blog</a>
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

    <!-- Article Header -->
    <header class="article-header">
        <div class="article-content">
            <div class="container">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><a href="blog.php">Blog</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($post['title']); ?></li>
                    </ol>
                </nav>
                
                <h1 class="display-5 fw-bold mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="article-meta">
                    <i class="fas fa-calendar me-2"></i>
                    <?php echo date('d F Y', strtotime($post['created_at'])); ?>
                    <span class="mx-3">•</span>
                    <i class="fas fa-clock me-2"></i>
                    <?php echo ceil(str_word_count(strip_tags($post['content'])) / 200); ?> dakika okuma
                </div>
            </div>
        </div>
    </header>

    <!-- Article Content -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <article class="article-body">
                        <?php if (!empty($post['excerpt'])): ?>
                            <div class="lead mb-4 p-4" style="background: rgba(102, 126, 234, 0.1); border-radius: 10px; border-left: 4px solid #667eea;">
                                <?php echo htmlspecialchars($post['excerpt']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="content">
                            <?php echo $post['content']; ?>
                        </div>
                        
                        <!-- Social Share -->
                        <div class="social-share">
                            <h6 class="mb-3">
                                <i class="fas fa-share-alt me-2"></i>Bu yazıyı paylaş
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' - ' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" 
                                   class="share-btn share-whatsapp">
                                    <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                </a>
                                <a href="https://t.me/share/url?url=<?php echo urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" 
                                   target="_blank" 
                                   class="share-btn share-telegram">
                                    <i class="fab fa-telegram me-2"></i>Telegram
                                </a>
                                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($post['title']); ?>&url=<?php echo urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" 
                                   class="share-btn share-twitter">
                                    <i class="fab fa-twitter me-2"></i>Twitter
                                </a>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="blog.php" class="btn-primary-modern">
                                <i class="fas fa-arrow-left me-2"></i>Tüm Yazılara Dön
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Posts -->
    <?php if (!empty($related_posts)): ?>
    <section class="py-5">
        <div class="container">
            <div class="related-posts">
                <h4 class="mb-4 text-center">
                    <i class="fas fa-newspaper me-2" style="color: #667eea;"></i>
                    İlgili Yazılar
                </h4>
                <div class="row g-4">
                    <?php foreach ($related_posts as $related): ?>
                        <div class="col-md-4">
                            <div class="related-post-card">
                                <h6 class="mb-3">
                                    <a href="blog-detay.php?slug=<?php echo $related['slug']; ?>" 
                                       class="text-white text-decoration-none">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </a>
                                </h6>
                                <p class="text-white-50 small mb-3">
                                    <?php echo htmlspecialchars(substr($related['excerpt'], 0, 100)); ?>...
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-white-50">
                                        <?php echo date('d.m.Y', strtotime($related['created_at'])); ?>
                                    </small>
                                    <a href="blog-detay.php?slug=<?php echo $related['slug']; ?>" 
                                       class="btn btn-sm btn-outline-light">
                                        Oku
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>