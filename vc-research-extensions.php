<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office of the Vice Chancellor for Research and Extension - MSU Buug Campus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --msu-green: #8B0000;
            --msu-gold: #FFD700;
            --msu-light: #A52A2A;
            --msu-dark: #600000;
            --research-purple: #8A2BE2;
            --research-light-purple: #9370DB;
            --extension-teal: #20B2AA;
            --extension-light-teal: #48D1CC;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.7;
            background: white;
            color: #333;
            padding-top: 180px; /* Adjusted for fixed header */
        }

        /* TOP BAR */
        .top-bar {
            background: #f8f9fa;
            padding: 8px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1002;
            border-bottom: 1px solid #dee2e6;
        }

        .top-bar-buttons a {
            color: #333 !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: inline-block;
            margin-left: 10px;
            border: 1px solid transparent;
        }

        .top-bar-buttons a:hover {
            background: var(--msu-gold);
            color: var(--msu-dark) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* HEADER */
        .header {
            position: fixed;
            top: 40px;
            width: 100%;
            z-index: 1001;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-top {
            padding: 15px 0;
            background: white;
        }

        .logo-container {
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logo-img {
            height: 80px;
            width: auto;
            margin-right: 20px;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-main {
            font-weight: 800;
            font-size: 1.6rem;
            color: var(--msu-green);
            margin-bottom: 5px;
        }

        .brand-address {
            font-size: 0.95rem;
            color: var(--msu-dark);
            font-weight: 500;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light)) !important;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-nav {
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        .nav-item {
            flex: 1;
            text-align: center;
        }

        .nav-link {
            color: white !important;
            font-weight: 600;
            padding: 1rem 0.5rem !important;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: var(--msu-gold);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 80%;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--msu-gold) !important;
            background: rgba(255, 255, 255, 0.1);
        }

        /* DROPDOWN MENUS */
        .dropdown-menu {
            background: var(--msu-dark);
            border: none;
            border-radius: 0 0 8px 8px;
            border-top: 3px solid var(--msu-gold);
            padding: 0.5rem 0;
        }

        .dropdown-item {
            color: white !important;
            font-weight: 500;
            padding: 0.8rem 1.2rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .dropdown-item:hover {
            background: var(--msu-green);
            color: var(--msu-gold) !important;
            border-left: 3px solid var(--msu-gold);
            padding-left: 1.5rem;
        }

        /* MOBILE RESPONSIVENESS */
        @media (max-width: 991.98px) {
            body {
                padding-top: 220px;
            }
            
            .navbar-collapse {
                background: var(--msu-dark);
                margin-top: 10px;
                border-radius: 8px;
                padding: 15px;
            }
            
            .nav-link {
                justify-content: flex-start !important;
                padding-left: 15px !important;
            }
            
            .brand-main {
                font-size: 1.3rem;
            }
            
            .brand-address {
                font-size: 0.85rem;
            }
            
            .logo-img {
                height: 60px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 200px;
            }
            
            .top-bar-buttons {
                text-align: center;
                margin-top: 10px;
            }
            
            .brand-main {
                font-size: 1.1rem;
            }
        }

        /* MAIN CONTENT */
        .content-section {
            padding: 40px 0 80px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: calc(100vh - 100px);
        }

        .section-title {
            text-align: center;
            color: var(--research-purple);
            font-weight: 700;
            margin-bottom: 50px;
            position: relative;
            padding-bottom: 15px;
            font-size: 2.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--msu-gold);
            border-radius: 2px;
        }

        /* CARDS */
        .info-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--research-purple), var(--research-light-purple));
            color: white;
            padding: 20px 30px;
            border-bottom: 4px solid var(--msu-gold);
        }

        .card-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .card-icon {
            font-size: 3rem;
            color: var(--research-purple);
            margin-bottom: 15px;
        }

        /* EXTENSION CARD */
        .extension-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid var(--extension-teal);
        }

        .extension-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .extension-header {
            background: linear-gradient(135deg, var(--extension-teal), var(--extension-light-teal));
            color: white;
            padding: 20px 30px;
            border-bottom: 4px solid var(--msu-gold);
        }

        .extension-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
        }

        /* STAFF CARDS */
        .staff-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            border-top: 4px solid var(--research-purple);
            text-align: center;
            height: 100%;
        }

        .staff-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .staff-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 4px solid var(--research-purple);
        }

        .staff-name {
            color: var(--msu-green);
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .staff-position {
            color: var(--research-purple);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* EXTENSION STAFF */
        .extension-staff-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            border-top: 4px solid var(--extension-teal);
            text-align: center;
            height: 100%;
        }

        .extension-staff-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .extension-staff-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 4px solid var(--extension-teal);
        }

        /* PROGRAM CARDS */
        .program-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            border-top: 4px solid var(--research-purple);
            height: 100%;
            text-align: center;
        }

        .program-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .program-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--research-purple), var(--research-light-purple));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .program-title {
            color: var(--research-purple);
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        /* QUICK LINKS */
        .quick-links {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }

        .quick-links-title {
            color: var(--msu-green);
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.5rem;
        }

        .quick-links-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .quick-links-list li {
            margin-bottom: 0.8rem;
        }

        .quick-links-list a {
            display: flex;
            align-items: center;
            color: #444;
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 10px 15px;
            border-radius: 8px;
            background: rgba(138, 43, 226, 0.1);
        }

        .quick-links-list a:hover {
            color: var(--msu-green);
            background: rgba(138, 43, 226, 0.2);
            transform: translateX(5px);
        }

        /* FOOTER */
        .footer {
            background: var(--msu-dark);
            color: white;
            padding: 50px 0 20px;
        }

        .footer-column-title {
            color: var(--msu-gold);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.2rem;
        }

        .fb-link {
            display: inline-flex;
            align-items: center;
            color: white !important;
            text-decoration: none;
            margin-top: 15px;
            padding: 8px 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .fb-link:hover {
            background: rgba(255,255,255,0.2);
            color: var(--msu-gold) !important;
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6"></div>
                <div class="col-md-6 text-md-end">
                    <div class="top-bar-buttons">
                        <a href="faculty_login.php">
                            <i class="fas fa-sign-in-alt me-2"></i>Faculty Portal
                        </a>
                        <a href="student_login.php">
                            <i class="fas fa-sign-in-alt me-2"></i>Student Portal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HEADER -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <a class="logo-container" href="index.html">
                    <img src="msulogo.png" alt="MSU Buug Logo" class="logo-img">
                    <div class="brand-text">
                        <span class="brand-main">Mindanao State University - Buug</span>
                        <span class="brand-address">Datu Panas, Buug, Zamboanga Sibugay</span>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- NAVIGATION -->
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.html">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Transparency
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">University Profile</a></li>
                                <li><a class="dropdown-item" href="#">Mission & Vision</a></li>
                                <li><a class="dropdown-item" href="#">Campus Officials</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Admission
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Requirements</a></li>
                                <li><a class="dropdown-item" href="#">Application Process</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Academics
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Basic Education</a></li>
                                <li><a class="dropdown-item" href="#">Colleges</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Research & Extension
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#ovcre">Office of the VC for Research & Extension</a></li>
                                <li><a class="dropdown-item" href="#">Publications</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Administration
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Office of the VC for Research</a></li>
                                <li><a class="dropdown-item" href="#">Office of the Campus Secretary</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Sustainability</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Offices
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Office of the Property Custodian</a></li>
                                <li><a class="dropdown-item" href="#">Office of the Chief Security</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- MAIN CONTENT -->
    <section id="ovcre" class="content-section">
        <div class="container">
            <h2 class="section-title">Office of the Vice Chancellor for Research and Extension</h2>

            <!-- Introduction -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-flask me-2"></i>About the Office</h3>
                </div>
                <div class="p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-flask card-icon"></i>
                        <p class="lead">Promoting Excellence in Research and Community Extension</p>
                    </div>
                    
                    <p class="mb-4">The Office of the Vice Chancellor for Research and Extension (OVCRE) is the central unit responsible for developing, implementing, and monitoring research and extension programs of MSU Buug Campus. Our office serves as the hub for knowledge creation, innovation, and community engagement.</p>
                    
                    <p class="mb-4">We are committed to fostering a culture of research excellence and meaningful community extension that addresses local, regional, and national development needs while contributing to the global knowledge economy.</p>
                    
                    <p>Through strategic partnerships and collaborative initiatives, we aim to transform research outputs into tangible solutions that benefit communities and contribute to sustainable development.</p>
                </div>
            </div>

            <!-- Mission and Vision -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-bullseye me-2"></i>Mission and Vision</h3>
                </div>
                <div class="p-4">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="program-card">
                                <div class="program-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <h4 class="program-title">Vision</h4>
                                <p>To be a center of excellence in research and extension that contributes significantly to sustainable development and community transformation.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="program-card">
                                <div class="program-icon">
                                    <i class="fas fa-rocket"></i>
                                </div>
                                <h4 class="program-title">Mission</h4>
                                <p>To promote a culture of research excellence, innovation, and community engagement that addresses societal challenges and contributes to knowledge advancement.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Director of Research -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-microscope me-2"></i>Director of Research</h3>
                </div>
                <div class="p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-microscope card-icon"></i>
                        <p class="lead">Leading Research Excellence and Innovation</p>
                    </div>
                    
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-6">
                            <div class="staff-card">
                                <img src="https://via.placeholder.com/120" alt="Research Director" class="staff-img">
                                <h4 class="staff-name">Dr. Maria Research</h4>
                                <p class="staff-position">Director of Research</p>
                                <p class="mb-3">Ph.D. in Research Management</p>
                                <p class="mb-0">mresearch@msubuug.edu.ph</p>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3" style="color: var(--research-purple);">Responsibilities:</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Develop and implement research policies and programs</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Coordinate research activities across all colleges</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Facilitate research collaborations</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Manage research grants and funding</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Oversee publication of research outputs</li>
                    </ul>
                </div>
            </div>

            <!-- Director for Extension -->
            <div class="extension-card">
                <div class="extension-header">
                    <h3><i class="fas fa-hands-helping me-2"></i>Director for Extension Services</h3>
                </div>
                <div class="p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-hands-helping card-icon" style="color: var(--extension-teal);"></i>
                        <p class="lead">Bridging the University and Community</p>
                    </div>
                    
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-6">
                            <div class="extension-staff-card">
                                <img src="https://via.placeholder.com/120" alt="Extension Director" class="extension-staff-img">
                                <h4 class="staff-name">Dr. Juan Extension</h4>
                                <p class="staff-position">Director for Extension Services</p>
                                <p class="mb-3">Ph.D. in Development Studies</p>
                                <p class="mb-0">jextension@msubuug.edu.ph</p>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3" style="color: var(--extension-teal);">Responsibilities:</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Develop community extension programs</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Coordinate with local government units</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Facilitate technology transfer programs</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Monitor extension service impacts</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Build partnerships with stakeholders</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Links and Publications -->
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="quick-links">
                        <h4 class="quick-links-title">Quick Links</h4>
                        <ul class="quick-links-list">
                            <li><a href="#"><i class="fas fa-file-alt me-2"></i>Research Guidelines</a></li>
                            <li><a href="#"><i class="fas fa-money-bill-wave me-2"></i>Grant Opportunities</a></li>
                            <li><a href="#"><i class="fas fa-calendar-alt me-2"></i>Research Calendar</a></li>
                            <li><a href="#"><i class="fas fa-book me-2"></i>Publication Guidelines</a></li>
                            <li><a href="#"><i class="fas fa-handshake me-2"></i>Partnership Opportunities</a></li>
                            <li><a href="#"><i class="fas fa-download me-2"></i>Forms and Templates</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <div class="info-card">
                        <div class="card-header">
                            <h3><i class="fas fa-newspaper me-2"></i>Research Publications</h3>
                        </div>
                        <div class="p-4">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="program-card">
                                        <div class="program-icon">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <h4 class="program-title">AJAMR Publication</h4>
                                        <p>Asian Journal of Agriculture and Multidisciplinary Research</p>
                                        <ul class="list-unstyled mt-3">
                                            <li><i class="fas fa-check-circle text-primary me-2"></i>Peer-reviewed journal</li>
                                            <li><i class="fas fa-check-circle text-primary me-2"></i>Biannual publication</li>
                                            <li><i class="fas fa-check-circle text-primary me-2"></i>International contributors</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="program-card">
                                        <div class="program-icon">
                                            <i class="fas fa-water"></i>
                                        </div>
                                        <h4 class="program-title">The Stream Publication</h4>
                                        <p>Student research and literary publication</p>
                                        <ul class="list-unstyled mt-3">
                                            <li><i class="fas fa-check-circle text-primary me-2"></i>Student research papers</li>
                                            <li><i class="fas fa-check-circle text-primary me-2"></i>Literary works</li>
                                            <li><i class="fas fa-check-circle text-primary me-2"></i>Annual publication</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-column-title">Contact Information</h5>
                    <p><strong>MSU Buug Campus</strong></p>
                    <p>Datu Panas, Buug, Zamboanga Sibugay</p>
                    <p>Email: info@msubuug.edu.ph</p>
                    <p>Phone: (062) 000-0000</p>
                    <a href="https://facebook.com/msubuug" class="fb-link" target="_blank">
                        <i class="fab fa-facebook me-2"></i>Follow our Facebook Page
                    </a>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-column-title">MSU System Campuses</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white text-decoration-none">MSU Main Campus - Marawi</a></li>
                        <li><a href="#" class="text-white text-decoration-none">MSU-Iligan Institute of Technology</a></li>
                        <li><a href="#" class="text-white text-decoration-none">MSU-General Santos</a></li>
                        <li><a href="#" class="text-white text-decoration-none">MSU-Buug</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-column-title">Core Values</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-star text-gold me-2"></i>Integrity</li>
                        <li><i class="fas fa-star text-gold me-2"></i>Culture Sensitivity</li>
                        <li><i class="fas fa-star text-gold me-2"></i>Accountability</li>
                        <li><i class="fas fa-star text-gold me-2"></i>Responsiveness</li>
                        <li><i class="fas fa-star text-gold me-2"></i>Excellence</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Mindanao State University - Buug Campus. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if(target) {
                    window.scrollTo({
                        top: target.offsetTop - 180,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.boxShadow = '0 4px 15px rgba(0,0,0,0.2)';
            } else {
                navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            }
        });
    </script>
</body>
</html>