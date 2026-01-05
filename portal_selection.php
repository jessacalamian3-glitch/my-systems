<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal Selection - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --msu-green: #8B0000;
            --msu-gold: #FFD700;
            --msu-light: #A52A2A;
            --msu-dark: #600000;
            --msu-cream: #FFF8E1;
            --footer-gray: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.7;
            overflow-x: hidden;
            background: white;
        }

        /* ENHANCED NAVBAR SCROLL EFFECT */
        .header {
            background: white;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .navbar-scrolled {
            padding: 0.5rem 0;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            transform: translateY(0);
        }

        .navbar-scrolled .navbar {
            background: transparent !important;
            padding: 0.2rem 0;
        }

        .header-top {
            background: white;
            padding: 1.2rem 0;
            border-bottom: 3px solid var(--msu-green);
            transition: all 0.4s ease;
        }

        .navbar-scrolled .header-top {
            padding: 0.8rem 0;
            transform: scale(0.95);
            opacity: 0.9;
        }

        /* MODERN NAVBAR STYLES */
        .navbar {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            padding: 0.4rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
        }

        .navbar-nav {
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav-link {
            color: white !important;
            font-weight: 600;
            margin: 0 0.3rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            padding: 0.6rem 1.2rem !important;
            border-radius: 6px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            color: var(--msu-gold) !important;
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .nav-link.active {
            color: var(--msu-gold) !important;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transform: translateY(-1px);
        }

        .contact-number {
            background: linear-gradient(135deg, var(--msu-gold), #ffed4a);
            color: var(--msu-dark) !important;
            padding: 8px 16px !important;
            border-radius: 20px;
            margin-left: 1rem !important;
            font-weight: 700;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            border: none;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .contact-number::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s ease;
        }

        .contact-number:hover::before {
            left: 100%;
        }

        .contact-number:hover {
            background: linear-gradient(135deg, #ffed4a, var(--msu-gold));
            transform: translateY(-2px) scale(1.05);
            color: var(--msu-dark) !important;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
        }

        .logo-container {
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .logo-container:hover {
            transform: translateY(-2px) scale(1.02);
        }
        
        .logo-img {
            height: 85px;
            width: auto;
            margin-right: 20px;
            transition: all 0.4s ease;
            filter: none;
            background: transparent !important;
        }
        
        .logo-img:hover {
            transform: scale(1.05) rotate(2deg);
        }
        
        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }
        
        .brand-main {
            font-weight: 800;
            font-size: 1.8rem;
            color: var(--msu-green);
            letter-spacing: -0.5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 6px;
            transition: all 0.3s ease;
        }
        
        .brand-address {
            font-size: 1.1rem;
            color: var(--msu-dark);
            font-weight: 600;
            letter-spacing: 0.3px;
            line-height: 1.4;
        }

        .logo-container:hover .brand-main {
            background: linear-gradient(135deg, var(--msu-light), var(--msu-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @media (max-width: 768px) {
            .brand-main {
                font-size: 1.2rem;
            }
            
            .brand-address {
                font-size: 0.85rem;
            }
            
            .logo-img {
                height: 60px;
            }
            
            .nav-link {
                font-size: 0.85rem;
                padding: 0.5rem 1rem !important;
            }

            .header-top {
                padding: 1rem 0;
            }
        }

        /* MAIN CONTENT STYLES */
        .main-content {
            padding-top: 180px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f9fa 0%, white 50%, #f8f9fa 100%);
        }

        .section-title {
            color: var(--msu-green);
            font-weight: 800;
            margin-bottom: 3rem;
            position: relative;
            text-align: center;
            font-size: 2.5rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--msu-gold);
            border-radius: 2px;
        }

        /* PORTAL SELECTION CARDS */
        .portal-selection {
            padding: 50px 0;
        }

        .portal-card {
            background: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            border: none;
            text-align: center;
            height: 100%;
            border-top: 5px solid var(--msu-green);
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .portal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
        }
        
        .portal-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .portal-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 3rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .portal-card:hover .portal-icon {
            transform: scale(1.1);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }

        .portal-title {
            color: var(--msu-green);
            font-weight: 800;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }

        .portal-description {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .btn-portal {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            border: none;
            color: white;
            font-weight: 700;
            padding: 15px 40px;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.3);
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 100%;
            max-width: 250px;
        }
        
        .btn-portal:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(139, 0, 0, 0.4);
            background: linear-gradient(135deg, var(--msu-light), var(--msu-green));
            color: white;
        }

        .btn-student {
            background: linear-gradient(135deg, #28a745, #20c997);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        .btn-student:hover {
            background: linear-gradient(135deg, #20c997, #28a745);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
        }

        .btn-faculty {
            background: linear-gradient(135deg, #007bff, #0056b3);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }

        .btn-faculty:hover {
            background: linear-gradient(135deg, #0056b3, #007bff);
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.4);
        }

        .btn-admin {
            background: linear-gradient(135deg, #6f42c1, #5a2d9c);
            box-shadow: 0 6px 20px rgba(111, 66, 193, 0.3);
        }

        .btn-admin:hover {
            background: linear-gradient(135deg, #5a2d9c, #6f42c1);
            box-shadow: 0 10px 25px rgba(111, 66, 193, 0.4);
        }

        /* FOOTER STYLES */
        .footer {
            background: #f8f9fa;
            color: var(--msu-dark);
            padding: 50px 0 25px;
            border-top: 3px solid #dee2e6;
        }

        .footer-column-title {
            color: var(--msu-green);
            font-weight: 800;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--msu-gold);
            border-radius: 2px;
        }

        .footer-contact-info p {
            font-size: 1.05rem;
            margin-bottom: 0.8rem;
            line-height: 1.6;
            color: #444;
        }

        .footer-contact-info strong {
            color: var(--msu-dark);
            font-weight: 700;
        }

        .systems-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .systems-list li {
            margin-bottom: 0.8rem;
            padding-left: 0;
            transition: all 0.3s ease;
        }

        .systems-list li:hover {
            transform: translateX(5px);
        }

        .systems-list a {
            color: #444;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.05rem;
            font-weight: 500;
            display: block;
            padding: 5px 0;
            border-bottom: 1px solid transparent;
        }

        .systems-list a:hover {
            color: var(--msu-green);
            border-bottom: 1px solid var(--msu-green);
            text-decoration: none;
        }

        .fb-link {
            color: var(--msu-dark);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 15px;
            padding: 8px 15px;
            background: rgba(139, 0, 0, 0.05);
            border-radius: 8px;
        }

        .fb-link:hover {
            color: var(--msu-green);
            transform: translateY(-3px);
            background: rgba(139, 0, 0, 0.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .footer-bottom {
            border-top: 2px solid #dee2e6;
            padding-top: 25px;
            margin-top: 30px;
            text-align: center;
        }

        .footer-bottom p {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--msu-dark);
            margin: 0;
        }

        .core-values-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .core-values-list li {
            margin-bottom: 0.8rem;
            color: #444;
            font-size: 1.1rem;
            font-weight: 600;
            padding: 8px 15px;
            background: rgba(139, 0, 0, 0.05);
            border-radius: 8px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--msu-green);
        }

        .core-values-list li:hover {
            background: rgba(139, 0, 0, 0.1);
            transform: translateX(5px);
        }

        @media (max-width: 768px) {
            .footer {
                text-align: center;
            }
            
            .footer-column-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .systems-list li:hover {
                transform: none;
            }
            
            .core-values-list li:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header with Logo and Navbar -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <a class="logo-container" href="index.php">
                            <img src="msulogo.png" alt="MSU Buug Logo" class="logo-img">
                            <div class="brand-text">
                                <span class="brand-main">Mindanao State University - Buug</span>
                                <span class="brand-address">Datu Panas, Buug, Zamboanga Sibugay</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a class="contact-number" href="tel:+0620000000">
                            <i class="fas fa-phone me-2"></i>(062) 000-0000
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon" style="color: white; border: 1px solid white; padding: 4px;"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Transparency
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="university-profile.php">University Profile</a></li>
                                <li><a class="dropdown-item" href="mission-vision.php">Mission,Vision,Core Values</a></li>
                                <li><a class="dropdown-item" href="campus-officials.php">Campus Officials</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Admission
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="admission-requirements.php">Requirements</a></li>
                                <li><a class="dropdown-item" href="application-process.php">Application Process</a></li>
                                <li><a class="dropdown-item" href="graduate-programs.php">Graduate programs</a></li>
                                <li><a class="dropdown-item" href="undergraduate-programs.php">Undergraduate Programs</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Academics
                            </a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">
                                        Basic Education <i class="fas fa-chevron-right float-end"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuugseniorhighschool" target="_blank">Senior High School</a></li>
                                        <li><a class="dropdown-item" href="junior-high.php">Junior High School</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">
                                        Colleges <i class="fas fa-chevron-right float-end"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuug.collegeofagriculture" target="_blank">College of Agriculture</a></li>
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuug.collegeofartsandsciences" target="_blank">College of Arts and Sciences</a></li>
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuug.collegeofeducation" target="_blank">College of Education</a></li>
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuug.collegeofpublicaffairs" target="_blank">College of Public Affairs</a></li>
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuug.collegeoffisheries" target="_blank">College of Fisheries</a></li>
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuug.collegeofforestry" target="_blank">College of Forestry & Environmental Studies</a></li>
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuug.collegeofhospitality" target="_blank">College of Hospitality Management</a></li>
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuug.collegeofinformationtechnology" target="_blank">College of Information Technology</a></li>
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuug.collegeofnursing" target="_blank">College of Nursing</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Administration
                            </a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">
                                        Office of the Vice Chancellor for Admin & Finance <i class="fas fa-chevron-right float-end"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="director-research.php">Director of Research</a></li>
                                        <li><a class="dropdown-item" href="director-extension.php">Director for Extension Services</a></li>
                                    </ul>
                                </li>
                                <li><a class="dropdown-item" href="campus-secretary.php">Office of the Campus Secretary</a></li>
                                <li><a class="dropdown-item" href="vc-admin-finance.php">Office of the Vice Chancellor for Admin & Finance</a></li>
                                <li><a class="dropdown-item" href="vc-planning-development.php">Office of the Vice Chancellor for Planning & Development</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Research & Extensions
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="vc-research-extensions.php">Office of the Vice Chancellor for Research and Extensions</a></li>
                                <li><a class="dropdown-item" href="ajamr-publication.php">AJAMR Publication</a></li>
                                <li><a class="dropdown-item" href="stream-publication.php">The Stream Publication</a></li>
                                <li><a class="dropdown-item" href="phanalam-publication.php">Phanalam Publication</a></li>
                                <li><a class="dropdown-item" href="research-colloquium.php">The Research Colloquium Publication</a></li>
                                <li><a class="dropdown-item" href="thesis-archive.php">Thesis Archive System</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="sustainability.php">Sustainability</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Offices
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="property-custodian.php">Office of the Property Custodian</a></li>
                                <li><a class="dropdown-item" href="chief-security.php">Office of the Chief Security</a></li>
                                <li><a class="dropdown-item" href="campus-registrar.php">Office of the Campus Registrar</a></li>
                                <li><a class="dropdown-item" href="student-affairs.php">Office of the Student Affairs and Services</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="portal-selection">
            <div class="container">
                <h2 class="section-title">Portal Selection</h2>
                <p class="text-center mb-5 fs-5 text-muted">Select your role to access the appropriate portal</p>
                
                <div class="row g-4">
                    <!-- Student Portal -->
                    <div class="col-md-4">
                        <div class="portal-card">
                            <div class="portal-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h3 class="portal-title">Student Portal</h3>
                            <p class="portal-description">
                                Access your academic records, grades, class schedules, and other student services. 
                                Manage your enrollment and stay updated with campus announcements.
                            </p>
                            <button class="btn btn-portal btn-student" onclick="redirectToPortal('student')">
                                <i class="fas fa-sign-in-alt me-2"></i>Student Sign In
                            </button>
                        </div>
                    </div>
                    
                    <!-- Faculty Portal -->
                    <div class="col-md-4">
                        <div class="portal-card">
                            <div class="portal-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h3 class="portal-title">Faculty Portal</h3>
                            <p class="portal-description">
                                Manage your classes, submit grades, access teaching materials, and communicate with students. 
                                Access faculty resources and research tools.
                            </p>
                            <button class="btn btn-portal btn-faculty" onclick="redirectToPortal('faculty')">
                                <i class="fas fa-sign-in-alt me-2"></i>Faculty Sign In
                            </button>
                        </div>
                    </div>
                    
                    <!-- Admin Portal -->
                    <div class="col-md-4">
                        <div class="portal-card">
                            <div class="portal-icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <h3 class="portal-title">Admin Portal</h3>
                            <p class="portal-description">
                                Access administrative tools, manage system settings, generate reports, and oversee campus operations. 
                                Administrative staff and management only.
                            </p>
                            <button class="btn btn-portal btn-admin" onclick="redirectToPortal('admin')">
                                <i class="fas fa-sign-in-alt me-2"></i>Admin Sign In
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- Column 1: Contact Information -->
                <div class="col-md-4 mb-4">
                    <h5 class="footer-column-title">Contact Information</h5>
                    <div class="footer-contact-info">
                        <p><strong>MSU Buug Campus</strong></p>
                        <p>Datu Panas, Buug, Zamboanga Sibugay</p>
                        <p>Email: info@msubuug.edu.ph</p>
                        <p>Phone: (062) 000-0000</p>
                        
                        <!-- Facebook Link -->
                        <a href="https://facebook.com/msubuug" class="fb-link" target="_blank">
                            <i class="fab fa-facebook fa-lg me-2"></i>
                            <span>Follow our Facebook Page</span>
                        </a>
                    </div>
                </div>
                
                <!-- Column 2: MSU Systems -->
                <div class="col-md-4 mb-4">
                    <h5 class="footer-column-title">MSU System Campuses</h5>
                    <ul class="systems-list">
                        <li><a href="#">MSU Main Campus - Marawi</a></li>
                        <li><a href="#">MSU-Iligan Institute of Technology</a></li>
                        <li><a href="#">MSU-General Santos</a></li>
                        <li><a href="#">MSU-Naawan</a></li>
                        <li><a href="#">MSU-Maguindanao</a></li>
                        <li><a href="#">MSU-Tawi-Tawi</a></li>
                        <li><a href="#">MSU-Sulu</a></li>
                        <li><a href="#">MSU-Buug</a></li>
                        <li><a href="#">MSU-Lanao del Norte</a></li>
                        <li><a href="#">MSU-Maigo School of Arts and Trades</a></li>
                    </ul>
                </div>
                
                <!-- Column 3: Core Values -->
                <div class="col-md-4 mb-4">
                    <h5 class="footer-column-title">Core Values</h5>
                    <ul class="core-values-list">
                        <li>Integrity</li>
                        <li>Culture Sensitivity</li>
                        <li>Accountability</li>
                        <li>Responsiveness</li>
                        <li>Excellence</li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="row">
                    <div class="col-12">
                        <p>&copy; 2025 Mindanao State University - Buug Campus. All Rights Reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.header').classList.add('navbar-scrolled');
            } else {
                document.querySelector('.header').classList.remove('navbar-scrolled');
            }
        });

        // Portal redirection function
        function redirectToPortal(portalType) {
            switch(portalType) {
                case 'student':
                    window.location.href = 'login_student.php';
                    break;
                case 'faculty':
                    window.location.href = 'faculty_login.php';
                    break;
                case 'admin':
                    window.location.href = 'admin_login.php';
                    break;
                default:
                    alert('Please select a valid portal.');
            }
        }

        // Add active class to current page in navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>