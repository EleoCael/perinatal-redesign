<?php
// /rhusystem/system/forgot-password/reset_password.php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../module/db.config.php';

$token = $_GET['token'] ?? '';
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $token = $_POST['token'] ?? '';
  $pass1 = $_POST['password'] ?? '';
  $pass2 = $_POST['password2'] ?? '';

  if ($token==='' || $pass1==='' || $pass2==='') {
    $msg = 'Complete all fields';
    $msgType = 'warning';
  } elseif ($pass1 !== $pass2) {
    $msg = 'Passwords do not match';
    $msgType = 'danger';
  } else {
    $stmt = $conn->prepare("
      SELECT user_id FROM user
      WHERE password_reset_token = ? AND token_expiration > NOW()
      LIMIT 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if (!$row) {
      $msg = 'Invalid or expired link';
      $msgType = 'danger';
    } else {
      $new_hash = password_hash($pass1, PASSWORD_DEFAULT);
      $uid = (int)$row['user_id'];

      $u = $conn->prepare("
        UPDATE user
        SET password_hash = ?, password_reset_token = NULL, token_expiration = NULL, is_verified = 1
        WHERE user_id = ?
      ");
      $u->bind_param("si", $new_hash, $uid);
      if ($u->execute()) {
        $u->close();
        header('Location: ../login/login.php'); 
        exit;
      }
      $u->close();
      $msg = 'Something went wrong. Try again.';
      $msgType = 'danger';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perinatal Care - Reset Password</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/reset_pass.css">
</head>
<body>

<div class="container-fluid login-page">
    <div class="row w-100 g-0">
        <div class="col-lg-6 login-left">
            <div class="login-card-container">
                <div class="text-center mb-4">
                    <img src="/rhusystem/assets/img/pericare_Logo.png" alt="Perinatal Care Logo" class="login-logo mb-3">
                    <h2 class="login-title">Reset Password</h2>
                    <p class="subtitle-text">Enter your new password below</p>
                </div>
                
                <?php if ($msg): ?>
                    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="form-floating mb-3">
                        <input type="password" name="password" id="password" class="form-control" placeholder="New Password" required minlength="6">
                        <label for="password">New Password</label>
                        <div class="invalid-feedback">Password must be at least 6 characters.</div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="password" name="password2" id="password2" class="form-control" placeholder="Confirm Password" required>
                        <label for="password2">Confirm Password</label>
                        <div class="invalid-feedback">Please confirm your password.</div>
                    </div>

                    <ul class="password-requirements mb-3">
                        <li><i class="fas fa-check-circle text-muted me-1"></i> At least 6 characters long</li>
                        <li><i class="fas fa-check-circle text-muted me-1"></i> Both passwords must match</li>
                    </ul>
                    
                    <button type="submit" class="btn btn-primary w-100 login-btn mb-3">Reset Password</button>
                    
                    <div class="text-center">
                        <a href="../login/login.php" class="forgot-link">
                            <i class="fas fa-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-6 d-none d-lg-flex login-right">
            <div class="login-right-content text-center">
                <h1 class="fw-bold display-4">Perinatal Care System</h1>
                <p class="lead mt-3">Every Process is Painful <br> So We better Pass this freaking capstone.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation');
        const password = document.getElementById('password');
        const password2 = document.getElementById('password2');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity() || password.value !== password2.value) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    if (password.value !== password2.value) {
                        password2.setCustomValidity('Passwords do not match');
                    } else {
                        password2.setCustomValidity('');
                    }
                }
                form.classList.add('was-validated');
            }, false);
        });
        
        password2.addEventListener('input', function() {
            if (password.value === password2.value) {
                password2.setCustomValidity('');
            } else {
                password2.setCustomValidity('Passwords do not match');
            }
        });
    })();
</script>

</body>
</html>