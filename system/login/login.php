<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/login.css">
    <title>Login</title>
</head>

<body>
    <div class="container-fluid login-page">
        <div class="row w-100 g-0">
            <div class="col-lg-6 login-left">
                <div class="login-card-container">
                    <div class="text-center mb-4">
                        <a href="../landing-page/system_LandingPg.php">
                         <img src="/rhusystem/assets/img/pericare_Logo.png" alt="Perinatal Care Logo" class="login-logo mb-3">
                       </a>
                        <h2 class="login-title">Log In</h2>
                    </div>

                    <div>
                        <?php
                        if (isset($_SESSION['errorMessage1'])) {
                        ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['errorMessage1'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php
                            unset($_SESSION['errorMessage1']);
                        }
                        ?>

                    </div>
                    <div>
                        <?php
                        if (isset($_SESSION['errorMessage2'])) {
                        ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['errorMessage2'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php
                            unset($_SESSION['errorMessage2']);
                        }
                        ?>
                    </div>

                    <div>
                        <?php
                        if (isset($_SESSION['errorMessage3'])) {
                        ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['errorMessage3'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php
                            unset($_SESSION['errorMessage3']);
                        }
                        ?>
                    </div>

                    <form action="login_process.php" method="post" class="needs-validation" novalidate>
                        <div class="form-floating mb-3">
                            <input type="email" name="email" id="email" class="form-control" placeholder="Email address" required>
                            <label for="email">Email Address</label>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="form-floating mb-3 position-relative">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                            <label for="password">Password</label>

                            <i class="bi bi-eye-fill fa-lg text-secondary position-absolute top-50 end-0 translate-middle-y me-3"
                                id="eyeIcon" style="cursor: pointer;"></i>

                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe">
                                <label class="form-check-label text-muted" for="rememberMe">Remember me</label>
                            </div>
                            <a href="../forgot-password/forgot_password_trial.php" class="forgot-link">Forgot Password?</a>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 login-btn">Login</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-flex login-right">
                <div class="login-right-content text-center">
                    <h1 class="fw-bold display-4">Perinatal Care System</h1>
                   
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    <script>
        (function() {
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

        //show/hide password
        let eyeIcon = document.getElementById("eyeIcon");
        let password = document.getElementById("password");

        eyeIcon.onclick = function (){
            if (password.type == "password") {
                password.type = "text";
                eyeIcon.classList.remove("bi-eye-fill");
                eyeIcon.classList.add("bi-eye-slash-fill");
                
            } else {
                password.type = ("password");
                eyeIcon.classList.remove("bi-eye-slash-fill");
                eyeIcon.classList.add("bi-eye-fill");
            }
        }
    </script>


</body>

</html>