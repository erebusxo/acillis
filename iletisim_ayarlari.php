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

// Form gönderildiğinde
if ($_POST) {
    $whatsapp_number = trim($_POST['whatsapp_number']);
    $whatsapp_message = trim($_POST['whatsapp_message']);
    $telegram_username = trim($_POST['telegram_username']);
    $telegram_message = trim($_POST['telegram_message']);
    $is_whatsapp_active = isset($_POST['is_whatsapp_active']) ? 1 : 0;
    $is_telegram_active = isset($_POST['is_telegram_active']) ? 1 : 0;
    
    try {
        // Önce kontrol et, varsa güncelle yoksa ekle
        $stmt = $db->query("SELECT id FROM contact_settings WHERE id = 1");
        if ($stmt->fetch()) {
            // Güncelle
            $stmt = $db->prepare("UPDATE contact_settings SET 
                whatsapp_number = ?, 
                whatsapp_message = ?, 
                telegram_username = ?, 
                telegram_message = ?, 
                is_whatsapp_active = ?, 
                is_telegram_active = ? 
                WHERE id = 1");
            $stmt->execute([
                $whatsapp_number, 
                $whatsapp_message, 
                $telegram_username, 
                $telegram_message, 
                $is_whatsapp_active, 
                $is_telegram_active
            ]);
        } else {
            // Yeni kayıt
            $stmt = $db->prepare("INSERT INTO contact_settings 
                (whatsapp_number, whatsapp_message, telegram_username, telegram_message, is_whatsapp_active, is_telegram_active) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $whatsapp_number, 
                $whatsapp_message, 
                $telegram_username, 
                $telegram_message, 
                $is_whatsapp_active, 
                $is_telegram_active
            ]);
        }
        
        $message = 'İletişim ayarları başarıyla güncellendi!';
        $message_type = 'success';
        
    } catch (Exception $e) {
        $message = 'Bir hata oluştu: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Mevcut ayarları al
try {
    $stmt = $db->query("SELECT * FROM contact_settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        $settings = [
            'whatsapp_number' => '+905551234567',
            'whatsapp_message' => 'Merhaba! Lisans konusunda yardıma ihtiyacım var.',
            'telegram_username' => 'acillisans',
            'telegram_message' => 'Merhaba! Lisans satın almak istiyorum.',
            'is_whatsapp_active' => 1,
            'is_telegram_active' => 1
        ];
    }
} catch (Exception $e) {
    $settings = [
        'whatsapp_number' => '+905551234567',
        'whatsapp_message' => 'Merhaba! Lisans konusunda yardıma ihtiyacım var.',
        'telegram_username' => 'acillisans',
        'telegram_message' => 'Merhaba! Lisans satın almak istiyorum.',
        'is_whatsapp_active' => 1,
        'is_telegram_active' => 1
    ];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İletişim Ayarları - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border: none; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .preview-card { background: #f8f9fa; border-radius: 10px; padding: 15px; margin-top: 10px; }
        .whatsapp-preview { border-left: 4px solid #25D366; }
        .telegram-preview { border-left: 4px solid #0088CC; }
        .navbar-brand { font-weight: bold; }
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
                            <a class="nav-link" href="blog-yonetimi.php">
                                <i class="fas fa-blog me-2"></i>Blog Yönetimi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="iletisim-ayarlari.php">
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
                    <h1 class="h2">İletişim Ayarları</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <!-- WhatsApp Ayarları -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fab fa-whatsapp me-2"></i>WhatsApp Ayarları
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_whatsapp_active" 
                                               name="is_whatsapp_active" <?php echo $settings['is_whatsapp_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_whatsapp_active">
                                            WhatsApp butonunu aktif et
                                        </label>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="whatsapp_number" class="form-label">Telefon Numarası</label>
                                        <input type="text" class="form-control" id="whatsapp_number" 
                                               name="whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number']); ?>"
                                               placeholder="+905551234567">
                                        <div class="form-text">Ülke kodu ile birlikte yazın (örn: +905551234567)</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="whatsapp_message" class="form-label">Varsayılan Mesaj</label>
                                        <textarea class="form-control" id="whatsapp_message" name="whatsapp_message" 
                                                  rows="3" placeholder="Merhaba! Lisans konusunda yardıma ihtiyacım var."><?php echo htmlspecialchars($settings['whatsapp_message']); ?></textarea>
                                        <div class="form-text">Kullanıcılar WhatsApp'ta bu mesajla size ulaşacak</div>
                                    </div>
                                    
                                    <div class="preview-card whatsapp-preview">
                                        <strong>Önizleme:</strong><br>
                                        <i class="fab fa-whatsapp text-success me-2"></i>
                                        <span id="whatsapp_preview"><?php echo htmlspecialchars($settings['whatsapp_message']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Telegram Ayarları -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fab fa-telegram me-2"></i>Telegram Ayarları
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_telegram_active" 
                                               name="is_telegram_active" <?php echo $settings['is_telegram_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_telegram_active">
                                            Telegram butonunu aktif et
                                        </label>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="telegram_username" class="form-label">Kullanıcı Adı</label>
                                        <div class="input-group">
                                            <span class="input-group-text">@</span>
                                            <input type="text" class="form-control" id="telegram_username" 
                                                   name="telegram_username" value="<?php echo htmlspecialchars($settings['telegram_username']); ?>"
                                                   placeholder="acillisans">
                                        </div>
                                        <div class="form-text">@ işareti olmadan kullanıcı adınızı yazın</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="telegram_message" class="form-label">Varsayılan Mesaj</label>
                                        <textarea class="form-control" id="telegram_message" name="telegram_message" 
                                                  rows="3" placeholder="Merhaba! Lisans satın almak istiyorum."><?php echo htmlspecialchars($settings['telegram_message']); ?></textarea>
                                        <div class="form-text">Kullanıcılar Telegram'da bu mesajla size ulaşacak</div>
                                    </div>
                                    
                                    <div class="preview-card telegram-preview">
                                        <strong>Önizleme:</strong><br>
                                        <i class="fab fa-telegram text-primary me-2"></i>
                                        <span id="telegram_preview"><?php echo htmlspecialchars($settings['telegram_message']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Bağlantıları -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-link me-2"></i>Test Bağlantıları
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>WhatsApp Test</h6>
                                            <a href="#" id="whatsapp_test_link" class="btn btn-success mb-2" target="_blank">
                                                <i class="fab fa-whatsapp me-2"></i>WhatsApp'ta Test Et
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Telegram Test</h6>
                                            <a href="#" id="telegram_test_link" class="btn btn-primary mb-2" target="_blank">
                                                <i class="fab fa-telegram me-2"></i>Telegram'da Test Et
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Ayarları Kaydet
                            </button>
                            <a href="index.php" class="btn btn-secondary btn-lg ms-2">
                                <i class="fas fa-arrow-left me-2"></i>Geri Dön
                            </a>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Önizleme güncellemeleri
        document.getElementById('whatsapp_message').addEventListener('input', function() {
            document.getElementById('whatsapp_preview').textContent = this.value;
            updateTestLinks();
        });

        document.getElementById('telegram_message').addEventListener('input', function() {
            document.getElementById('telegram_preview').textContent = this.value;
            updateTestLinks();
        });

        document.getElementById('whatsapp_number').addEventListener('input', updateTestLinks);
        document.getElementById('telegram_username').addEventListener('input', updateTestLinks);

        function updateTestLinks() {
            const whatsappNumber = document.getElementById('whatsapp_number').value.replace(/[^0-9+]/g, '');
            const whatsappMessage = document.getElementById('whatsapp_message').value;
            const telegramUsername = document.getElementById('telegram_username').value.replace('@', '');
            const telegramMessage = document.getElementById('telegram_message').value;

            // WhatsApp link
            const whatsappLink = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(whatsappMessage)}`;
            document.getElementById('whatsapp_test_link').href = whatsappLink;

            // Telegram link
            const telegramLink = `https://t.me/${telegramUsername}`;
            document.getElementById('telegram_test_link').href = telegramLink;
        }

        // Sayfa yüklendiğinde test linklerini güncelle
        updateTestLinks();

        // Form validasyonu
        document.querySelector('form').addEventListener('submit', function(e) {
            const whatsappActive = document.getElementById('is_whatsapp_active').checked;
            const telegramActive = document.getElementById('is_telegram_active').checked;
            const whatsappNumber = document.getElementById('whatsapp_number').value.trim();
            const telegramUsername = document.getElementById('telegram_username').value.trim();

            if (whatsappActive && !whatsappNumber) {
                alert('WhatsApp aktifse telefon numarası gereklidir!');
                e.preventDefault();
                return;
            }

            if (telegramActive && !telegramUsername) {
                alert('Telegram aktifse kullanıcı adı gereklidir!');
                e.preventDefault();
                return;
            }

            if (whatsappNumber && !whatsappNumber.startsWith('+')) {
                alert('WhatsApp numarası + ile başlamalıdır!');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>