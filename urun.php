<?php
session_start();
require_once '../admin/inc/db.php';

// Ürün ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = (int)$_GET['id'];
$message = '';
$message_type = '';

// Ürün bilgilerini al
try {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: index.php');
        exit;
    }
    
    // Müsait lisans sayısını kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) as available_count FROM licenses WHERE product_id = ? AND is_sold = 0");
    $stmt->execute([$product_id]);
    $available = $stmt->fetch(PDO::FETCH_ASSOC);
    $available_count = $available['available_count'];
    
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// Satın alma işlemi
if ($_POST && isset($_SESSION['user_id'])) {
    if ($available_count > 0) {
        try {
            $db->beginTransaction();
            
            // Müsait ilk lisansı al
            $stmt = $db->prepare("SELECT id, license_key FROM licenses WHERE product_id = ? AND is_sold = 0 LIMIT 1");
            $stmt->execute([$product_id]);
            $license = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($license) {
                // Lisansı satıldı olarak işaretle
                $stmt = $db->prepare("UPDATE licenses SET is_sold = 1, sold_to_user_id = ?, sold_at = NOW() WHERE id = ?");
                $stmt->execute([$_SESSION['user_id'], $license['id']]);
                
                // Sipariş oluştur
                $stmt = $db->prepare("INSERT INTO orders (user_id, product_id, license_id, status) VALUES (?, ?, ?, 'Teslim Edildi')");
                $stmt->execute([$_SESSION['user_id'], $product_id, $license['id']]);
                
                $db->commit();
                
                $message = 'Satın alma işlemi başarılı! Lisansınız: ' . $license['license_key'];
                $message_type = 'success';
                
                // Müsait lisans sayısını güncelle
                $available_count--;
            } else {
                $message = 'Üzgünüz, bu ürün için müsait lisans kalmamıştır.';
                $message_type = 'danger';
            }
            
        } catch (PDOException $e) {
            $db->rollBack();
            $message = 'Satın alma işlemi sırasında bir hata oluştu.';
            $message_type = 'danger';
        }
    } else {
        $message = 'Üzgünüz, bu ürün için müsait lisans kalmamıştır.';
        $message_type = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - AcilLVCS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-icon {
            font-size: 5rem;
            margin-bottom: 20px;
        }
        .product-card {
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
        .price-tag {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
        }
        .stock-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .btn-purchase {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 10px;
        }
        .btn-purchase:hover {
            background: linear-gradient(135deg, #218838 0%, #1a9f7a 100%);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-key me-2"></i>AcilLVCS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
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
                                <i class="fas fa-sign-out-alt me-1"></i>Çıkış Yap
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Giriş Yap
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Kayıt Ol
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-12 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
                    </ol>
                </nav>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="product-card card h-100">
                    <div class="card-body text-center">
                        <div class="product-icon">
                            <?php if ($product['category'] == 'Windows'): ?>
                                <i class="fab fa-windows text-primary"></i>
                            <?php elseif ($product['category'] == 'Office'): ?>
                                <i class="fas fa-file-word text-success"></i>
                            <?php else: ?>
                                <i class="fas fa-desktop text-secondary"></i>
                            <?php endif; ?>
                        </div>
                        <h1 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        <p class="card-text lead"><?php echo htmlspecialchars($product['description']); ?></p>
                        
                        <div class="stock-info">
                            <h6><i class="fas fa-box me-2"></i>Stok Durumu</h6>
                            <?php if ($available_count > 0): ?>
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check me-1"></i>
                                    <?php echo $available_count; ?> adet mevcut
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger fs-6">
                                    <i class="fas fa-times me-1"></i>
                                    Stokta yok
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="product-card card h-100">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="price-tag">₺<?php echo number_format($product['price'], 2); ?></div>
                            <p class="text-muted">KDV Dahil</p>
                        </div>
                        
                        <div class="mb-4">
                            <h5><i class="fas fa-info-circle me-2"></i>Ürün Özellikleri</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Orijinal lisans</li>
                                <li><i class="fas fa-check text-success me-2"></i>Anında teslimat</li>
                                <li><i class="fas fa-check text-success me-2"></i>7/24 destek</li>
                                <li><i class="fas fa-check text-success me-2"></i>Yaşam boyu geçerli</li>
                            </ul>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($available_count > 0): ?>
                                <form method="POST" onsubmit="return confirm('Bu ürünü satın almak istediğinizden emin misiniz?');">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success btn-purchase">
                                            <i class="fas fa-shopping-cart me-2"></i>
                                            Hemen Satın Al
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="d-grid">
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-times me-2"></i>
                                        Stokta Yok
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Satın almak için <a href="login.php">giriş yapın</a> veya 
                                <a href="register.php">kayıt olun</a>.
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <h6><i class="fas fa-shield-alt me-2"></i>Güvenlik Garantisi</h6>
                            <p class="text-muted small">
                                Tüm satışlarımız güvenli ödeme sistemleri üzerinden gerçekleşir. 
                                Lisanslarımız orijinal ve Microsoft tarafından onaylıdır.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kategori aynı olan diğer ürünler -->
        <div class="row mt-5">
            <div class="col-md-12">
                <h3>Benzer Ürünler</h3>
                <hr>
            </div>
            
            <?php
            try {
                $stmt = $db->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 3");
                $stmt->execute([$product['category'], $product_id]);
                $similar_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($similar_products)):
            ?>
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Bu kategoride başka ürün bulunmamaktadır.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($similar_products as $similar): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <?php if ($similar['category'] == 'Windows'): ?>
                                        <i class="fab fa-windows fa-3x text-primary"></i>
                                    <?php elseif ($similar['category'] == 'Office'): ?>
                                        <i class="fas fa-file-word fa-3x text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-desktop fa-3x text-secondary"></i>
                                    <?php endif; ?>
                                </div>
                                <h6 class="card-title"><?php echo htmlspecialchars($similar['name']); ?></h6>
                                <p class="card-text small"><?php echo htmlspecialchars(substr($similar['description'], 0, 100)) . '...'; ?></p>
                                <div class="text-center">
                                    <strong class="text-primary">₺<?php echo number_format($similar['price'], 2); ?></strong>
                                    <div class="mt-2">
                                        <a href="urun.php?id=<?php echo $similar['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            Detaylar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php
            } catch (PDOException $e) {
                // Hata durumunda sessizce devam et
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>