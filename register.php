<?php
session_start();
require_once '../admin/inc/db.php';

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if (empty($email) || empty($password) || empty($password_confirm)) {
        $error = 'Tüm alanlar zorunludur.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir e-posta adresi girin.';
    } elseif (strlen($password) < 6) {
        $error = 'Şifre en az 6 karakter olmalıdır.';
    } elseif ($password !== $password_confirm) {
        $error = 'Şifreler eşleşmiyor.';
    } else {
        try {
            // E-posta zaten kayıtlı mı kontrol et
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Bu e-posta adresi zaten kayıtlı.';
            } else {
                // Yeni kullanıcı kaydet
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                $stmt->execute([$email, $hashed_password]);
                
                $success = 'Kayıt başarılı! Şimdi giriş yapabilirsiniz.';
            }
        } catch (PDOException $e) {
            $error = 'Bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - AcilLVCS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 400px;
            width: 100%;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .password-strength {
            font-size: 0.875rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="register-card">
                    <div class="register-header">
                        <i class="fas fa-user-plus"></i>
                        <h2>Kayıt Ol</h2>
                        <p class="text-muted">Yeni hesap oluşturun</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$success): ?>
                        <form method="POST" id="registerForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>E-posta
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Şifre
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       minlength="6" required>
                                <div class="password-strength text-muted">
                                    En az 6 karakter olmalıdır
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Şifre Tekrar
                                </label>
                                <input type="password" class="form-control" id="password_confirm" 
                                       name="password_confirm" required>
                                <div id="password-match" class="mt-1"></div>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-user-plus me-2"></i>Kayıt Ol
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="text-center">
                        <p class="mb-0">Zaten hesabınız var mı?</p>
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i>Giriş Yap
                        </a>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="index.php" class="text-muted text-decoration-none">
                            <i class="fas fa-home me-1"></i>Ana Sayfaya Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Şifre eşleşme kontrolü
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');
        const passwordMatch = document.getElementById('password-match');
        const submitBtn = document.getElementById('submitBtn');
        
        function checkPasswordMatch() {
            if (passwordConfirm.value === '') {
                passwordMatch.innerHTML = '';
                return;
            }
            
            if (password.value === passwordConfirm.value) {
                passwordMatch.innerHTML = '<small class="text-success"><i class="fas fa-check me-1"></i>Şifreler eşleşiyor</small>';
                submitBtn.disabled = false;
            } else {
                passwordMatch.innerHTML = '<small class="text-danger"><i class="fas fa-times me-1"></i>Şifreler eşleşmiyor</small>';
                submitBtn.disabled = true;
            }
        }
        
        password.addEventListener('input', checkPasswordMatch);
        passwordConfirm.addEventListener('input', checkPasswordMatch);
    </script>
</body>
</html>