<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perinatal Care Landing Pag</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/landing-page.css">
</head>
<body>
    <div class="main-container"> 

        <nav class="navbar navbar-expand-lg bg-light sticky-top">
            <div class="container-fluid"> 
                
                
                <a class="navbar-brand" href="#">
                    <img src="/rhusystem/assets/img/pericare_Logo.png" alt="Perinatal Care Logo" height="50"> Perinatal Care
                </a>

                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>


                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto me-4"> 
                        <li class="nav-item"><a class="nav-link" href="#">Home</a></li> 
                        <li class="nav-item"><a class="nav-link" href="#features-section">Features</a></li>
                        <li class="nav-item"><a class="nav-link" href="#about-section">About</a></li>
                    </ul>

                    <a class="btn btn-login" href="../login/login.php">Login</a>
                </div>
            </div>
        </nav>

        <section class="hero-section text-center text-lg-start">
            <div class="container"> 
                <div class="row align-items-center">
                    
                    <div class="col-lg-7 mb-4 mb-lg-0">
                        <div class="hero-content">
                            <h1 class="mb-3">Nurturing new life, one heartbeat at a time</h1> 
                            <p class="mb-4">Perinatal Care is a comprehensive web-based system designed to streamline obstetric and infant care monitoring for Rural Health Units and Barangay Health Centers in Bulakan, Bulacan.</p> <!-- Description -->
                            <a class="btn btn-login" href="#features-section">Learn More</a> 
                        </div>
                    </div>

                    
                    <div class="col-lg-5 d-none d-lg-block">
                        <img src="/rhusystem/assets/img/landingpg_img.png" class="img-fluid" alt="Baby in a Heart"> 
                    </div>
                </div>
            </div>
        </section>

        <section id="features-section" class="features-grid-section">
            <div class="container">
                <h2 class="text-center mb-5" style="font-family: 'Montserrat', sans-serif; font-weight: 700;">Key System Features</h2> 

                <div class="row g-4"> 
                    <?php
                      
                        function generateFeatureCard($icon, $title, $description) {
                            echo '<div class="col-md-6 col-lg-4">';
                            echo '     <div class="feature-card">'; 
                            echo '         <div class="feature-icon"><i class="fas fa-' . $icon . '"></i></div>'; 
                            echo '         <h3>' . $title . '</h3>'; 
                            echo '         <p>' . $description . '</p>';
                            echo '     </div>';
                            echo '</div>';
                        }
                        
                        generateFeatureCard('database', 'Centralized Patient Records', 'Manage all maternal and infant health data in one secure, digital hub, reducing paperwork and errors.');
                        generateFeatureCard('chart-bar', 'Data Visualization & Reports', 'Generate reports and charts to track immunization progress, set quotas, and ensure targets are met.');
                        generateFeatureCard('bell', 'Automated SMS Reminders', 'Patients receive text message reminders a day before their scheduled appointments to ensure timely visits and better care follow-up.');
                        generateFeatureCard('users-cog', 'User Access Control', 'Assign specific roles and permissions to ensure secure and organized data management for all health workers.');
                        generateFeatureCard('calendar-alt', 'Patient Access', 'Through their accounts, patients can view and track their child’s immunization history and ensure timely vaccinations.');
                        generateFeatureCard('search', 'Efficient Search & Filtering', 'Quickly locate and filter patient records to find the information you need, when you need it.');
                    ?>
                </div>
            </div>
        </section>

        <section id="about-section" class="about-section"> 
            <div class="container">
                <h2>Our Mission</h2> 
                <p>
                    We are dedicated to providing a powerful and effective Web-Based Management Information System that assists the Rural Health Unit and Barangay Health Centers of Bulakan, Bulacan, in streamlining and speeding up their monitoring of infant and obstetric care, ultimately leading to improved health outcomes.
                </p>
               
            </div>
        </section>

        <footer class="main-footer">
            <div class="container"> 
                <div class="row">
                    <div class="col-12 text-center"> 
                        <div class="mb-3">
                            <a href="#" class="footer-logo d-inline-block">
                                <img src="/rhusystem/assets/img/pericare_Logo.png" alt="Perinatal Care Logo" height="70"> Perinatal Care
                            </a>
                        </div>
                        <p class="text-secondary mb-4">A Web-Based Management Information System for local health units in Bulakan, Bulacan.</p> <!-- Short description -->
                        
                        <div class="d-flex justify-content-center flex-wrap gap-3">
                            <a href="#" class="nav-link-item text-white">Home</a>
                            <a href="#features-section" class="nav-link-item text-white">Features</a>
                            <a href="#about-section" class="nav-link-item text-white">About Us</a>
                        </div>

                        <p class=" mt-4 mb-0" style="color: white;">© 2025 Perinatal Care. All Rights Reserved.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>