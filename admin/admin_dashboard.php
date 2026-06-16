<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {

    header("Location: ../system/login/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/midwife_dashboard.css">
    <!--<link rel="stylesheet" href="../assets/css/style.css">-->
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/report.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg main-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" onclick="loadHomePage();">
                Perinatal Care
            </a>
        </div>
    </nav>

    <div class="sidebar-container d-none d-lg-block">
        <nav class="sidebar-nav">

            <div class="sidebar-section-title">Dashboard</div>

            <a href="#" class="sidebar-nav-link active" onclick="loadHomePage();">
                <i class="bi bi-house-door text-white"></i>
                <span>Home</span>
            </a>

            <div class="sidebar-section-title">Midwife Management</div>

            <a href="#" class="sidebar-nav-link" onclick="loadPage('manage_midwives.php')">
                <i class="bi bi-people text-white"></i>
                <span>Manage Midwives</span>
            </a>

            <a href="#" class="sidebar-nav-link" onclick="loadPage('create_midwife.php')">
                <i class="bi bi-person-plus text-white"></i>
                <span>Create Midwife Account</span>
            </a>

            <div class="sidebar-section-title">Reports</div>

            <a href="#" class="sidebar-nav-link" onclick="loadPage('report_dashboard.php')">
                <i class="bi bi-bar-chart text-white"></i>
                <span>View All Reports</span>
            </a>

            <div class="sidebar-section-title">Account</div>
            <a href="#" class="sidebar-nav-link" onclick="confirmLogout()">
                <i class="bi bi-box-arrow-right text-white"></i>
                <span>Logout </span>
            </a>
        </nav>
    </div>

    <main class="main-content">
        <div id="main-content" class="fade-in">
        </div>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/admin_script.js"></script>
    <script src="../assets/js/report_admin.js"></script>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of the system!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../module/logout.php';
                }
            });
        }
    </script>

</body>

</html>