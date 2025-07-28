<?php
session_start();
require_once '../admin/inc/db.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcının sipariş ve lisanslarını al
try {
    $stmt = $db->prepare("
        SELECT 
            o.id as order_id,
            o.status,
            o.created_at,
            p.name as product_name,
            p.category,
            p.price,
            l.license_key
        FROM orders o
        JOIN products p ON o.product_id = p.id
        LEFT JOIN licenses l ON o.license_id = l.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisanslarım - AcilLVCS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .license-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .license-key {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            border: 2px dashed #dee2e6;
        }
        .status-badge {
            font-size: 0.9rem;
        }
        .copy-btn {
            cursor: pointer;
        }
        .copy-success {
            color: #28a745;
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
                    <li class="nav-item">
                        <a class="nav-link active" href="lisanslarim.php">
                            <i class="fas fa-list me-1"></i>Lisanslarım
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Çıkış Yap
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-list me-2"></i>Lisanslarım</h2>
                    <span class="badge bg-primary fs-6"><?php echo count($orders); ?> Sipariş</span>
                </div>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-5x text-muted mb-4"></i>
                        <h4 class="text-muted">Henüz bir siparişiniz bulunmamaktadır</h4>
                        <p class="text-muted">Lisans satın almak için ürünlerimize göz atın</p>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i>Ürünleri İncele
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="license-card card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <?php if ($order['category'] == 'Windows'): ?>
                                        <i class="fab fa-windows text-primary me-2"></i>
                                    <?php elseif ($order['category'] == 'Office'): ?>
                                        <i class="fas fa-file-word text-success me-2"></i>
                                    <?php else: ?>
                                        <i class="fas fa-desktop text-secondary me-2"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($order['product_name']); ?>
                                </h6>
                                
                                <?php if ($order['status'] == 'Teslim Edildi'): ?>
                                    <span class="badge bg-success status-badge">
                                        <i class="fas fa-check me-1"></i>Teslim Edildi
                                    </span>
                                <?php elseif ($order['status'] == 'Beklemede'): ?>
                                    <span class="badge bg-warning status-badge">
                                        <i class="fas fa-clock me-1"></i>Beklemede
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger status-badge">
                                        <i class="fas fa-times me-1"></i>İptal
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <strong class="text-primary">
                                        ₺<?php echo number_format($order['price'], 2); ?>
                                    </strong>
                                </div>
                                
                                <?php if ($order['status'] == 'Teslim Edildi' && $order['license_key']): ?>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">
                                            <i class="fas fa-key me-1"></i>Lisans Anahtarı:
                                        </label>
                                        <div class="license-key position-relative">
                                            <span id="key-<?php echo $order['order_id']; ?>">
                                                <?php echo htmlspecialchars($order['license_key']); ?>
                                            </span>
                                            <button class="btn btn-sm btn-outline-secondary copy-btn float-end" 
                                                    onclick="copyToClipboard('key-<?php echo $order['order_id']; ?>', this)"
                                                    title="Kopyala">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info alert-sm">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <small>Bu lisans anahtarını güvenli bir yerde saklayın.</small>
                                    </div>
                                <?php elseif ($order['status'] == 'Beklemede'): ?>
                                    <div class="alert alert-warning alert-sm">
                                        <i class="fas fa-clock me-1"></i>
                                        <small>Siparişiniz işleme alınıyor. Kısa süre içinde lisansınız teslim edilecektir.</small>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-danger alert-sm">
                                        <i class="fas fa-times me-1"></i>
                                        <small>Bu sipariş iptal edilmiştir.</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <small class="text-muted">
                                    Sipariş No: #<?php echo $order['order_id']; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- İstatistikler -->
            <div class="row mt-5">
                <div class="col-md-12">
                    <h4>Sipariş İstatistikleri</h4>
                    <hr>
                </div>
                
                <?php
                $stats = [
                    'Teslim Edildi' => 0,
                    'Beklemede' => 0,
                    'İptal' => 0
                ];
                $total_spent = 0;
                
                foreach ($orders as $order) {
                    $stats[$order['status']]++;
                    if ($order['status'] == 'Teslim Edildi') {
                        $total_spent += $order['price'];
                    }
                }
                ?>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h5><?php echo $stats['Teslim Edildi']; ?></h5>
                            <small class="text-muted">Teslim Edildi</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h5><?php echo $stats['Beklemede']; ?></h5>
                            <small class="text-muted">Beklemede</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                            <h5><?php echo $stats['İptal']; ?></h5>
                            <small class="text-muted">İptal</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-lira-sign fa-2x text-primary mb-2"></i>
                            <h5>₺<?php echo number_format($total_spent, 2); ?></h5>
                            <small class="text-muted">Toplam Harcama</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(elementId, button) {
            const element = document.getElementById(elementId);
            const text = element.textContent.trim();
            
            // Clipboard API kullan
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopySuccess(button);
                });
            } else {
                // Fallback - eski tarayıcılar için
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    showCopySuccess(button);
                } catch (err) {
                    console.error('Kopyalama başarısız:', err);
                }
                
                document.body.removeChild(textArea);
            }
        }
        
        function showCopySuccess(button) {
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.add('copy-success');
            
            setTimeout(function() {
                button.innerHTML = originalIcon;
                button.classList.remove('copy-success');
            }, 2000);
        }
    </script>
</body>
</html>