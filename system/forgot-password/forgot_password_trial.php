<?php
// /rhusystem/system/forgot-password/forgot_password.php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../module/db.config.php'; // 2 levels up

if (isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    $msgType = $_SESSION['flash_type'];
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
}else{
    $msg = '';
$msgType = '';

}


if ($_SERVER['REQUEST_METHOD']==='POST') {
  $email = trim($_POST['email'] ?? '');
  if ($email==='') {
    $msg = 'Please enter your email/username.';
    $msgType = 'warning';
  } else {
    // Find patient user by email/username
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE user_email = ? AND role = 'Patient' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    // Always show a generic message to avoid account enumeration
    $msg = 'If the account exists, you will be redirected to set a new password.';
    $msgType = 'info';

    if ($row) {
      $uid = (int)$row['user_id'];
      $token = bin2hex(random_bytes(32));
      $exp   = (new DateTime('+48 hours'))->format('Y-m-d H:i:s');

      $u = $conn->prepare("UPDATE user SET password_reset_token = ?, token_expiration = ? WHERE user_id = ?");
      $u->bind_param("ssi", $token, $exp, $uid);
      if ($u->execute()) {
        $u->close();
        header('Location: ./reset_password.php?token='.$token);
        exit;
      }
      $u->close();
      $msg = 'Something went wrong. Please try again.';
      $msgType = 'danger';
    } else {
      // Small delay to reduce timing difference
      usleep(300000);
    }
  }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perinatal Care - Forgot Password Trial</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/forgot_pass.css">
 
</head>
<body>

<div class="container-fluid login-page">
    <div class="row w-100 g-0">
        <div class="col-lg-6 login-left">
            <div class="login-card-container">
                <div class="text-center mb-4">
                    <img src="/rhusystem/assets/img/pericare_Logo.png" alt="Perinatal Care Logo" class="login-logo mb-3">
                    <h2 class="login-title">Forgot Password</h2>
                    <p class="subtitle-text">Enter your email address and we'll help you reset your password</p>
                </div>
                
                <?php if ($msg): ?>
                    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="send_reset_password.php" method="post" class="needs-validation" novalidate>
                    <div class="form-floating mb-3">
                        <input type="text" name="email" id="email" class="form-control" placeholder="Email or Username" required>
                        <label for="email">Email or Username</label>
                        <div class="invalid-feedback">Please enter your email or username.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 login-btn mb-3">Continue</button>
                    
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
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>

</body>
</html>