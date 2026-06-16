<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Midwife") {

    header("Location: ../system/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Midwife Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <!-- Navbar  -->
    <nav style="background-color: #1f2937;" class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">

            <!-- This is to hide sidebar -->
            <button class="navbar-toggler me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!--This is to hide sidebar-->

            <a style="color: #f3f4f6;" class="navbar-brand fw-bold text-uppercase me-auto " href="midwife_dashboard.php">Perinatal Care</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav  ms-auto my-3 my-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle " href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i style="color: #f3f4f6;" class="bi bi-person-fill"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">

                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../module/logout.php" name="login">Logout</a></li>


                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <!-- Sidebar  -->
    <div style="background-color: #1f2937;" class="offcanvas offcanvas-start sidebar-nav" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">

        <div class="offcanvas-body p-0">
            <nav class="navbar-dark">
                <ul class="navbar-nav">
                    <li>
                        <div style="color: #f3f4f6;" class="small  mt-2 fw-bold text-uppercase px-3">
                            Home</div>
                        <a href="#" class="nav-link px-3 active"  onclick="
                          
                            ">
                            <span class="me-2">
                                <i style="color: #f3f4f6;" class="bi bi-house"></i>
                            </span>
                            <span style="color: #f3f4f6;" class="span">
                                Dashboard
                            </span>
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li>
                        <div style="color: #f3f4f6;" class="small  mt-2 fw-bold text-uppercase px-3">
                            Maternal Patients</div>

                    </li>
                    <li>
                        <a href="#" class="nav-link px-3 active .sidebar-item">
                            <span class="me-2">
                                <i class="bi bi-file-earmark-person-fill"></i>
                            </span>
                            <span style="color: #f3f4f6;" class="span">
                                My Patients
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link px-3 active"
                            onclick="
                            loadPage('patient/maternal/add_maternal.php');
                            ">
                            <span class="me-2">
                                <i class="bi bi-person-plus"></i>
                            </span>
                            <span style="color: #f3f4f6;" class="span">
                                Add Patient
                            </span>
                        </a>
                    </li>
                    <li class="my-4">
                        <hr class="dropdown-divider" />
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li>
                        <div style="color: #f3f4f6;" class="small  mt-2 fw-bold text-uppercase px-3">
                            Infant Patients</div>

                    </li>
                    <li>
                        <a href="#" class="nav-link px-3 active">
                            <span class="me-2">
                                <i class="bi bi-file-earmark-person-fill"></i>
                            </span>
                            <span style="color: #f3f4f6;" class="span">
                                My Patients
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link px-3 active">
                            <span class="me-2">
                                <i class="bi bi-person-plus"></i>
                            </span>
                            <span style="color: #f3f4f6;" class="span">
                                Add Patient
                            </span>
                        </a>
                    </li>
                    <li class="my-4">
                        <hr class="dropdown-divider" />
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li>
                        <div style="color: #f3f4f6;" class="small  mt-2 fw-bold text-uppercase px-3">
                            Reports</div>

                    </li>
                    <li>
                        <a href="#" class="nav-link px-3 active">
                            <span class="me-2">
                                <i class="bi bi-folder2-open"></i>
                            </span>
                            <span style="color: #f3f4f6;" class="span">
                                Reports
                            </span>
                        </a>
                    </li>

                    <li class="my-4">
                        <hr class="dropdown-divider" />
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Main Page -->
    <div class="main-content ms-auto" id="main-content">
        <h2>This is the main page</h2>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    <script>
        function loadPage(page) {
            fetch(page)
                .then(response => response.text())
                .then(data => {

                    document.getElementById("main-content").innerHTML = data;
                })
                .catch(error => {
                    document.getElementById("main-content").innerHTML = "<p>Error loading page.</p>";
                    console.error("Error:", error);
                });
        }
    </script>

</body>

</html>