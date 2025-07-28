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

// Slug oluşturma fonksiyonu
function createSlug($text) {
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    
    // Türkçe karakterleri dönüştür
    $search = array('ç', 'ğ', 'ı', 'ö', 'ş', 'ü');
    $replace = array('c', 'g', 'i', 'o', 's', 'u');
    $text = str_replace($search, $replace, $text);
    
    // Alfanumerik olmayan karakterleri temizle
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    
    // Boşlukları ve çoklu tireleri tek tire ile değiştir
    $text = preg_replace('/[\s-]+/', '-', $text);
    
    // Başındaki ve sonundaki tireleri temizle
    $text = trim($text, '-');
    
    return $text;
}

// Form gönderildiğinde
if ($_POST) {
    $title = trim($_POST['title']);
    $excerpt = trim($_POST['excerpt']);
    $content = trim($_POST['content']);
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    $meta_keywords = trim($_POST['meta_keywords']);
    $status = $_POST['status'];
    
    if (empty($title) || empty($content)) {
        $message = 'Başlık ve içerik alanları zorunludur.';
        $message_type = 'danger';
    } else {
        $slug = createSlug($title);
        
        // Slug benzersizliği kontrolü
        $original_slug = $slug;
        $counter = 1;
        while (true) {
            $stmt = $db->prepare("SELECT id FROM blog_posts WHERE slug = ?");
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        try {
            $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, meta_title, meta_description, meta_keywords, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $excerpt, $content, $meta_title, $meta_description, $meta_keywords, $status]);
            
            $message = 'Blog yazısı başarıyla eklendi!';
            $message_type = 'success';
            
            // Form verilerini temizle
            $title = $excerpt = $content = $meta_title = $meta_description = $meta_keywords = '';
            $status = 'draft';
            
        } catch (Exception $e) {
            $message = 'Bir hata oluştu: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
} else {
    // Varsayılan değerler
    $title = $excerpt = $content = $meta_title = $meta_description = $meta_keywords = '';
    $status = 'draft';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Blog Yazısı - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border: none; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .navbar-brand { font-weight: bold; }
        .seo-preview { background: #f8f9fa; border-radius: 10px; padding: 15px; margin-top: 10px; }
        .seo-title { color: #1a0dab; font-size: 18px; text-decoration: none; }
        .seo-url { color: #006621; font-size: 14px; }
        .seo-description { color: #545454; font-size: 13px; line-height: 1.4; }
        .char-counter { font-size: 12px; float: right; }
        .char-counter.over-limit { color: #dc3545; }
        .char-counter.optimal { color: #28a745; }
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
                    <h1 class="h2">Yeni Blog Yazısı</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="blog-yonetimi.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Geri Dön
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" id="blogForm">
                    <div class="row">
                        <!-- Sol Kolon - Ana İçerik -->
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">İçerik</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Başlık *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($title); ?>" required>
                                        <div class="form-text">
                                            Slug: <span id="slug-preview">-</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">Özet</label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" 
                                                  placeholder="Yazının kısa özeti..."><?php echo htmlspecialchars($excerpt); ?></textarea>
                                        <div class="form-text">
                                            Arama sonuçlarında ve yazı listesinde gösterilecek kısa açıklama
                                            <span class="char-counter" id="excerpt-counter">0 karakter</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="content" class="form-label">İçerik *</label>
                                        <textarea class="form-control" id="content" name="content" required><?php echo htmlspecialchars($content); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Ayarları -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-search me-2"></i>SEO Ayarları
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label">Meta Başlık</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                               value="<?php echo htmlspecialchars($meta_title); ?>" maxlength="60">
                                        <div class="form-text">
                                            Google'da görünecek başlık (boş bırakılırsa yazı başlığı kullanılır)
                                            <span class="char-counter" id="meta-title-counter">0/60 karakter</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Açıklama</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" 
                                                  rows="3" maxlength="160"><?php echo htmlspecialchars($meta_description); ?></textarea>
                                        <div class="form-text">
                                            Arama sonuçlarında görünecek açıklama
                                            <span class="char-counter" id="meta-desc-counter">0/160 karakter</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_keywords" class="form-label">Anahtar Kelimeler</label>
                                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                               value="<?php echo htmlspecialchars($meta_keywords); ?>"
                                               placeholder="windows lisans, office 365, yazılım satın al">
                                        <div class="form-text">Virgülle ayırarak yazın</div>
                                    </div>

                                    <!-- Google Önizleme -->
                                    <div class="seo-preview">
                                        <h6><i class="fab fa-google me-2"></i>Google Önizleme</h6>
                                        <div id="google-preview">
                                            <div class="seo-title" id="preview-title">Başlık buraya gelecek</div>
                                            <div class="seo-url" id="preview-url">https://acillisans.com/blog-detay.php?slug=</div>
                                            <div class="seo-description" id="preview-description">Açıklama buraya gelecek</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sağ Kolon - Yayın Ayarları -->
                        <div class="col-lg-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Yayın Ayarları</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Durum</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>
                                                <i class="fas fa-edit"></i> Taslak
                                            </option>
                                            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>
                                                <i class="fas fa-eye"></i> Yayınla
                                            </option>
                                        </select>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" name="action" value="save" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Kaydet
                                        </button>
                                        <button type="submit" name="action" value="save_and_continue" class="btn btn-success">
                                            <i class="fas fa-save me-2"></i>Kaydet ve Yeni Ekle
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO İpuçları -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-lightbulb text-warning me-2"></i>SEO İpuçları
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled small">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Başlığı 60 karakterden kısa tutun
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Meta açıklamayı 150-160 karakter yapın
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Hedef kelimelerinizi başlıkta kullanın
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            İçeriği en az 1000 kelime yapın
                                        </li>
                                        <li>
                                            <i class="fas fa-check text-success me-2"></i>
                                            H2, H3 başlıklarını kullanın
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-tr-TR.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Summernote editörü
            $('#content').summernote({
                height: 400,
                lang: 'tr-TR',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                placeholder: 'Blog yazınızı buraya yazın...',
                callbacks: {
                    onPaste: function (e) {
                        var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                        e.preventDefault();
                        document.execCommand('insertText', false, bufferText);
                    }
                }
            });

            // Slug oluşturma
            function createSlug(text) {
                return text
                    .toLowerCase()
                    .replace(/ç/g, 'c')
                    .replace(/ğ/g, 'g')
                    .replace(/ı/g, 'i')
                    .replace(/ö/g, 'o')
                    .replace(/ş/g, 's')
                    .replace(/ü/g, 'u')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/[\s-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            // Başlık değiştiğinde slug güncelle
            $('#title').on('input', function() {
                const title = $(this).val();
                const slug = createSlug(title);
                $('#slug-preview').text(slug || '-');
                updatePreview();
            });

            // Karakter sayaçları
            function updateCharCounter(fieldId, counterId, maxLength, optimalMin = 0, optimalMax = 0) {
                const field = $('#' + fieldId);
                const counter = $('#' + counterId);
                
                field.on('input', function() {
                    const length = $(this).val().length;
                    let counterText = length;
                    
                    if (maxLength > 0) {
                        counterText += '/' + maxLength + ' karakter';
                        
                        if (length > maxLength) {
                            counter.removeClass('optimal').addClass('over-limit');
                        } else if (optimalMin > 0 && length >= optimalMin && length <= optimalMax) {
                            counter.removeClass('over-limit').addClass('optimal');
                        } else {
                            counter.removeClass('over-limit optimal');
                        }
                    } else {
                        counterText += ' karakter';
                    }
                    
                    counter.text(counterText);
                    updatePreview();
                });
            }

            updateCharCounter('excerpt', 'excerpt-counter');
            updateCharCounter('meta_title', 'meta-title-counter', 60, 50, 60);
            updateCharCounter('meta_description', 'meta-desc-counter', 160, 150, 160);

            // Google önizleme güncelleme
            function updatePreview() {
                const title = $('#meta_title').val() || $('#title').val() || 'Başlık buraya gelecek';
                const description = $('#meta_description').val() || $('#excerpt').val() || 'Açıklama buraya gelecek';
                const slug = createSlug($('#title').val()) || 'slug';

                $('#preview-title').text(title);
                $('#preview-url').text('https://acillisans.com/blog-detay.php?slug=' + slug);
                $('#preview-description').text(description);
            }

            // İlk yükleme
            updatePreview();
            $('#title').trigger('input');
            $('#excerpt').trigger('input');
            $('#meta_title').trigger('input');
            $('#meta_description').trigger('input');

            // Form gönderimi kontrolü
            $('#blogForm').on('submit', function(e) {
                const title = $('#title').val().trim();
                const content = $('#content').summernote('code').trim();

                if (!title || !content || content === '<p><br></p>') {
                    e.preventDefault();
                    alert('Başlık ve içerik alanları zorunludur!');
                    return false;
                }
            });
        });
    </script>
</body>
</html>