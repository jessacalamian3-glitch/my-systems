<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Profile - MSU Buug Campus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            scroll-behavior: smooth;
            padding-top: 0 !important;
        }

        /* FIXED NAVBAR - WALANG SPACE SA TAAS */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
        }

        .navbar {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light)) !important;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            min-height: auto;
            margin-top: 0;
        }

        .navbar .container {
            max-width: 100%;
            padding: 0 15px;
        }

        .navbar-nav {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            gap: 0;
        }

        .nav-item {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .nav-link {
            color: white !important;
            font-weight: 600;
            margin: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 1.2rem 0.5rem !important;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            border-radius: 0;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,215,0,0.2), transparent);
            transition: left 0.6s ease;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 4px;
            background: var(--msu-gold);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(-50%);
            border-radius: 2px 2px 0 0;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 80%;
        }

        .nav-link:hover {
            color: var(--msu-gold) !important;
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
        }

        .nav-link.active {
            color: var(--msu-gold) !important;
            background: rgba(255, 255, 255, 0.05);
            font-weight: 700;
        }

        /* ENHANCED DROPDOWN WITH ANIMATIONS */
        @media (min-width: 992px) {
            .navbar .dropdown-menu {
                background: var(--msu-dark);
                border: none;
                border-radius: 12px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.25);
                padding: 0.8rem 0;
                margin-top: 0;
                border-top: 3px solid var(--msu-gold);
                display: block !important;
                opacity: 0;
                visibility: hidden;
                transform: translateY(15px) scale(0.95);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                backdrop-filter: blur(20px);
                min-width: 240px;
            }

            .navbar .nav-item.dropdown:hover > .dropdown-menu {
                opacity: 1;
                visibility: visible;
                transform: translateY(5px) scale(1);
            }

            .dropdown-item {
                color: white !important;
                font-weight: 500;
                padding: 0.9rem 1.5rem;
                transition: all 0.3s ease;
                font-size: 0.9rem;
                border-left: 4px solid transparent;
                position: relative;
                overflow: hidden;
            }

            .dropdown-item::before {
                content: '';
                position: absolute;
                left: -100%;
                top: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,215,0,0.15), transparent);
                transition: left 0.5s ease;
            }

            .dropdown-item:hover::before {
                left: 100%;
            }

            .dropdown-item:hover {
                background: var(--msu-green);
                color: var(--msu-gold) !important;
                border-left: 4px solid var(--msu-gold);
                padding-left: 2rem;
                transform: translateX(8px);
            }
        }

        /* CONTENT SECTION WITH SPACE FOR FIXED NAVBAR */
        .content-section {
            padding: 180px 0 80px;
            min-height: calc(100vh - 120px);
            background: linear-gradient(135deg, #f8f9fa 0%, white 50%, #f8f9fa 100%);
            position: relative;
            overflow: hidden;
        }

        .content-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(139, 0, 0, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 215, 0, 0.03) 0%, transparent 50%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .section-title {
            color: var(--msu-green);
            font-weight: 800;
            margin-bottom: 4rem;
            position: relative;
            text-align: center;
            font-size: 3rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            animation: fadeInUp 1s ease;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 5px;
            background: var(--msu-gold);
            border-radius: 3px;
            animation: slideIn 1s ease 0.3s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                width: 0;
            }
            to {
                width: 100px;
            }
        }

        /* ENHANCED INFO CARDS WITH HOVER ANIMATIONS */
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 3rem;
            border-left: 5px solid var(--msu-green);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(139, 0, 0, 0.03), transparent);
            transition: left 0.8s ease;
        }

        .info-card:hover::before {
            left: 100%;
        }

        .info-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            border-left: 5px solid var(--msu-gold);
        }

        .card-header {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            padding: 1.8rem;
            border-radius: 15px 15px 0 0;
            margin: -2.5rem -2.5rem 2rem -2.5rem;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .card-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2.2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .card-icon::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
        }

        .info-card:hover .card-icon {
            transform: scale(1.15) rotate(8deg);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
            background: linear-gradient(135deg, var(--msu-gold), #FFA500);
        }

        .info-card:hover .card-icon::before {
            transform: rotate(45deg) translate(50%, 50%);
        }

        /* TIMELINE ANIMATIONS */
        .timeline-content {
            padding: 25px 30px;
            background-color: white;
            position: relative;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            border-left: 4px solid var(--msu-green);
        }

        .timeline-content:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-left: 4px solid var(--msu-gold);
        }

        .timeline-year {
            font-weight: 700;
            color: var(--msu-green);
            margin-bottom: 10px;
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .timeline-content:hover .timeline-year {
            color: var(--msu-gold);
            transform: translateX(5px);
        }

        /* LEADER CARDS ENHANCED */
        .leader-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border-top: 4px solid var(--msu-green);
            height: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .leader-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,215,0,0.05), transparent);
            transition: left 0.6s ease;
        }

        .leader-card:hover::before {
            left: 100%;
        }

        .leader-card:hover {
            transform: translateY(-12px) scale(1.05);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            border-top: 4px solid var(--msu-gold);
        }

        .leader-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1.5rem;
            border: 5px solid var(--msu-green);
            transition: all 0.5s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .leader-card:hover .leader-img {
            border-color: var(--msu-gold);
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 12px 30px rgba(0,0,0,0.2);
        }

        .leader-name {
            color: var(--msu-green);
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .leader-card:hover .leader-name {
            color: var(--msu-gold);
            transform: translateY(-2px);
        }

        /* QUICK FACTS HOVER EFFECTS */
        .facts-list span {
            display: flex;
            align-items: center;
            color: #444;
            font-size: 1.05rem;
            font-weight: 500;
            padding: 12px 18px;
            border-radius: 12px;
            background: rgba(139, 0, 0, 0.05);
            transition: all 0.4s ease;
            border-left: 4px solid transparent;
        }

        .facts-list span:hover {
            background: rgba(139, 0, 0, 0.1);
            transform: translateX(10px) scale(1.02);
            border-left: 4px solid var(--msu-gold);
            color: var(--msu-green);
        }

        .facts-list span:hover i {
            transform: scale(1.2) rotate(10deg);
            color: var(--msu-gold);
        }

        .facts-list i {
            margin-right: 12px;
            color: var(--msu-green);
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        /* FOOTER HOVER EFFECTS */
        .systems-list a {
            color: #444;
            text-decoration: none;
            transition: all 0.4s ease;
            font-size: 1.05rem;
            font-weight: 500;
            display: block;
            padding: 8px 0;
            border-bottom: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .systems-list a::before {
            content: '';
            position: absolute;
            left: -100%;
            bottom: 0;
            width: 100%;
            height: 2px;
            background: var(--msu-gold);
            transition: left 0.4s ease;
        }

        .systems-list a:hover::before {
            left: 0;
        }

        .systems-list a:hover {
            color: var(--msu-green);
            transform: translateX(8px);
            text-decoration: none;
        }

        .core-values-list li {
            margin-bottom: 0.8rem;
            color: #444;
            font-size: 1.1rem;
            font-weight: 600;
            padding: 10px 18px;
            background: rgba(139, 0, 0, 0.05);
            border-radius: 12px;
            transition: all 0.4s ease;
            border-left: 4px solid var(--msu-green);
            cursor: pointer;
        }

        .core-values-list li:hover {
            background: rgba(139, 0, 0, 0.1);
            transform: translateX(10px) scale(1.02);
            border-left: 4px solid var(--msu-gold);
            color: var(--msu-green);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .fb-link {
            color: var(--msu-dark);
            text-decoration: none;
            transition: all 0.4s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 15px;
            padding: 10px 20px;
            background: rgba(139, 0, 0, 0.05);
            border-radius: 12px;
            border: 2px solid transparent;
        }

        .fb-link:hover {
            color: var(--msu-green);
            transform: translateY(-5px) scale(1.05);
            background: rgba(139, 0, 0, 0.1);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: var(--msu-gold);
        }

        /* TOP BAR HOVER EFFECTS ENHANCED */
        .top-bar {
            background: var(--footer-gray);
            padding: 0.4rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .top-bar a {
            color: black !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: inline-block;
            border: 2px solid transparent;
        }

        .top-bar a:hover {
            color: var(--msu-gold) !important;
            background: linear-gradient(135deg, rgba(255,215,0,0.15), rgba(255,165,0,0.15));
            transform: translateY(-3px) scale(1.05);
            border: 2px solid var(--msu-gold);
            box-shadow: 0 5px 15px rgba(255,215,0,0.3);
        }

        .top-bar a::after {
            content: "➜";
            margin-left: 8px;
            display: inline-block;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateX(-10px);
        }

        .top-bar a:hover::after {
            opacity: 1;
            transform: translateX(0);
            animation: bounce 0.5s ease infinite alternate;
        }

        @keyframes bounce {
            from { transform: translateX(0); }
            to { transform: translateX(3px); }
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .content-section {
                padding: 160px 0 50px;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .info-card {
                padding: 2rem;
            }
            
            .info-card:hover {
                transform: translateY(-5px);
            }
            
            .card-header {
                margin: -2rem -2rem 1.5rem -2rem;
                padding: 1.5rem;
            }
            
            .card-icon {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
            }
            
            .leader-card:hover {
                transform: translateY(-5px);
            }
            
            .facts-list span:hover {
                transform: translateX(5px);
            }
        }

        /* LOGO ANIMATIONS */
        .logo-container {
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 10px 0;
        }
        
        .logo-container:hover {
            transform: translateY(-3px) scale(1.02);
        }
        
        .logo-img {
            height: 85px;
            width: auto;
            margin-right: 20px;
            transition: all 0.5s ease;
            filter: none;
            background: transparent !important;
        }
        
        .logo-container:hover .logo-img {
            transform: scale(1.1) rotate(3deg);
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.2));
        }
        
        .brand-main {
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 6px;
            transition: all 0.3s ease;
        }
        
        .logo-container:hover .brand-main {
            background: linear-gradient(135deg, var(--msu-light), var(--msu-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <!-- Optional: Welcome message or other content -->
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="top-bar-buttons">
                        <a href="faculty_login.php" class="enter-portal-top">
                            <i class="fas fa-sign-in-alt me-2"></i>Faculty Portal
                        </a>
                        <a href="student_login.php" class="enter-portal-top">
                            <i class="fas fa-sign-in-alt me-2"></i>Student Portal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HEADER - FIXED AT TOP -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <a class="logo-container" href="#" onclick="showSection('landing')">
                            <img src="msulogo.png" alt="MSU Buug Logo" class="logo-img">
                            <div class="brand-text">
                                <span class="brand-main">Mindanao State University - Zamboanga Sibugay</span>
                              
                            </div>
                        </a>
                    </div>
                </div>
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
                            <a class="nav-link active" href="#" onclick="showSection('landing')">Home</a>
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
                                        Basic Education
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="https://facebook.com/msubuugseniorhighschool" target="_blank">Senior High School</a></li>
                                        <li><a class="dropdown-item" href="junior-high.php">Junior High School</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">
                                        Colleges
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="college-agriculture.php" target="_blank">College of Agriculture</a></li>
                                        <li><a class="dropdown-item" href="college-arts.php" target="_blank">College of Arts and Sciences</a></li>
                                        <li><a class="dropdown-item" href="college-education.php" target="_blank">College of Education</a></li>
                                        <li><a class="dropdown-item" href="collge-public-affairs.php" target="_blank">College of Public Affairs</a></li>
                                        <li><a class="dropdown-item" href="college-fisheries.php" target="_blank">College of Fisheries</a></li>
                                        <li><a class="dropdown-item" href="college-forestry.php" target="_blank">College of Forestry & Environmental Studies</a></li>
                                        <li><a class="dropdown-item" href="college-hospitality.php" target="_blank">College of Hospitality Management</a></li>
                                        <li><a class="dropdown-item" href="collge-it.php" target="_blank">College of Information Technology</a></li>
                                        <li><a class="dropdown-item" href="college-nursing.php" target="_blank">College of Nursing</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Research & Extension
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="https://msubuug.edu.ph/journal/">AJMR Publication</a></li>
                                <li><a class="dropdown-item" href="stream-publication.php">The Stream Publication</a></li>
                                <li><a class="dropdown-item" href="phanalam-publicatiion.php">Phanalam Publication</a></li>
                                <li><a class="dropdown-item" href="research-colloquium-publication">The Research Colloquium Publication</a></li>
                                <li><a class="dropdown-item" href="https://msubuug.edu.ph/otcas/">Thesis and Capstone Archive System</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Administration
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="vc-research-extensions.php">Office of the Vice Chancellor for Research and Extensions</a></li>
                                <li><a class="dropdown-item" href="campus-secretary">Office of the Campus Secretary</a></li>
                                <li><a class="dropdown-item" href="vc-admin-finance.php">Office of the Vice Chancellor for Admin & Finance</a></li>
                                <li><a class="dropdown-item" href="vc-planning-development.php">Office of the Vice Chancellor for Planning & Development</a></li>
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

    <!-- UNIVERSITY PROFILE CONTENT SECTION -->
    <section class="content-section">
        <div class="container">
            <h2 class="section-title">University Profile</h2>

            <!-- Introduction Card -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-university me-2"></i>About MSU Buug Campus</h3>
                </div>
                <div class="text-center mb-4">
                    <div class="card-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <p class="lead">A Premier Higher Educational Institution in Western Mindanao</p>
                </div>
                
                <p class="mb-4">The MSU Buug Campus, a premier higher educational institution in Western Mindanao, is a product of the endeavors of past and present leadership. It was established in 1971 as MSU Buug Community High School (a self-supporting unit) realized with the effort of the late Prof. Mariano G. Pagang.</p>
                
                <p class="mb-4">Its establishment was responsive to the incessant request of the local residents (through the local government unit) to uplift the educational status of the poor and underserved, especially the indigenous people of the province of Zamboanga Sibugay formerly Zamboanga del Sur.</p>
                
                <p>The MSU Buug Community High School was subsidized by the MSU Main Campus from 1974-1975, then was absorbed as a full-fledged unit by the latter in 1976 by virtue of BOR Resolution No. 1030, s.1976. Despite the meager budget given, the school continued to operate and thrive with commendable commitment of the faculty members and staff.</p>
            </div>

            <!-- Historical Timeline -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-history me-2"></i>Historical Timeline</h3>
                </div>
                <div class="text-center mb-4">
                    <div class="card-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <p class="lead">Key Milestones in the Development of MSU Buug Campus</p>
                </div>
                
                <div class="timeline">
                    <!-- 1971 -->
                    <div class="timeline-item left">
                        <div class="timeline-content">
                            <div class="timeline-year">1971</div>
                            <p>Established as MSU Buug Community High School through the efforts of the late Prof. Mariano G. Pagang</p>
                        </div>
                    </div>
                    
                    <!-- 1974-1975 -->
                    <div class="timeline-item right">
                        <div class="timeline-content">
                            <div class="timeline-year">1974-1975</div>
                            <p>Subsidized by the MSU Main Campus</p>
                        </div>
                    </div>
                    
                    <!-- 1976 -->
                    <div class="timeline-item left">
                        <div class="timeline-content">
                            <div class="timeline-year">1976</div>
                            <p>Absorbed as a full-fledged unit by MSU Main Campus by virtue of BOR Resolution No. 1030, s.1976</p>
                        </div>
                    </div>
                    
                    <!-- 1982 -->
                    <div class="timeline-item right">
                        <div class="timeline-content">
                            <div class="timeline-year">1982</div>
                            <p>Elevated into collegiate level and renamed as MSU Buug College. Authorized to offer general college courses (BOR Resolution Nos. 492 and 492-B)</p>
                        </div>
                    </div>
                    
                    <!-- 1986 -->
                    <div class="timeline-item left">
                        <div class="timeline-content">
                            <div class="timeline-year">1986</div>
                            <p>Headed by the late Prof. Mohammad-Ali T. Mariga</p>
                        </div>
                    </div>
                    
                    <!-- 1989 -->
                    <div class="timeline-item right">
                        <div class="timeline-content">
                            <div class="timeline-year">1989</div>
                            <p>Authorized to offer complete courses in Agriculture, Forestry, Education, and Liberal Arts (BOR Resolution No. 55, s.1989)</p>
                        </div>
                    </div>
                    
                    <!-- 2002 -->
                    <div class="timeline-item left">
                        <div class="timeline-content">
                            <div class="timeline-year">2002</div>
                            <p>Revitalized with transfer of supervision to Office of the Vice Chancellor for Academic Affairs (Special Order No. 581-OP)</p>
                        </div>
                    </div>
                    
                    <!-- 2007 -->
                    <div class="timeline-item right">
                        <div class="timeline-content">
                            <div class="timeline-year">2007</div>
                            <p>Granted Level II autonomous status (BOR Res. No. 223, s.2007)</p>
                        </div>
                    </div>
                    
                    <!-- 2008 -->
                    <div class="timeline-item left">
                        <div class="timeline-content">
                            <div class="timeline-year">2008</div>
                            <p>Granted sub-allocation and transfer of supervision to Office of the Vice President for Academic Affairs (BOR Res. 211 and 212)</p>
                        </div>
                    </div>
                    
                    <!-- 2010 -->
                    <div class="timeline-item right">
                        <div class="timeline-content">
                            <div class="timeline-year">2010</div>
                            <p>Granted autonomous status (BOR Resolution No. 168)</p>
                        </div>
                    </div>
                    
                    <!-- 2012 -->
                    <div class="timeline-item left">
                        <div class="timeline-content">
                            <div class="timeline-year">2012</div>
                            <p>Sultan Taha G. Sarip elected as first regular chancellor</p>
                        </div>
                    </div>
                    
                    <!-- 2018 -->
                    <div class="timeline-item right">
                        <div class="timeline-content">
                            <div class="timeline-year">2018</div>
                            <p>Dr. Pangandag M. Magolama elected as second chancellor</p>
                        </div>
                    </div>
                    
                    <!-- 2019 -->
                    <div class="timeline-item left">
                        <div class="timeline-content">
                            <div class="timeline-year">2019</div>
                            <p>College of Agriculture extension program established at Roseller T. Lim (BOR Resolution No. 283 s.2019)</p>
                        </div>
                    </div>
                    
                    <!-- 2020 -->
                    <div class="timeline-item right">
                        <div class="timeline-content">
                            <div class="timeline-year">2020</div>
                            <p>R.T. Lim Extension program administration transferred to MSU Buug</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leadership Section -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-users me-2"></i>Campus Leadership</h3>
                </div>
                <div class="text-center mb-4">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <p class="lead">Key Leaders Who Shaped MSU Buug Campus</p>
                </div>
                
                <div class="leadership-container">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="leader-card">
                                <img src="https://via.placeholder.com/120" alt="Prof. Mariano G. Pagang" class="leader-img">
                                <h4 class="leader-name">Prof. Mariano G. Pagang</h4>
                                <p class="leader-position">Founder</p>
                                <p class="leader-tenure">Established MSU Buug Community High School in 1971</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="leader-card">
                                <img src="https://via.placeholder.com/120" alt="Prof. Mohammad-Ali T. Mariga" class="leader-img">
                                <h4 class="leader-name">Prof. Mohammad-Ali T. Mariga</h4>
                                <p class="leader-position">Head (1986)</p>
                                <p class="leader-tenure">Led expansion of academic programs</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="leader-card">
                                <img src="https://via.placeholder.com/120" alt="Sultan Taha Sarip" class="leader-img">
                                <h4 class="leader-name">Sultan Taha Sarip</h4>
                                <p class="leader-position">Director (2006) & First Chancellor (2012)</p>
                                <p class="leader-tenure">Achieved autonomous status for the campus</p>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-4 mb-4">
                            <div class="leader-card">
                                <img src="chancellor.jpg" alt="Dr. Pangandag M. Magolama" class="leader-img">
                                <h4 class="leader-name">Dr. Pangandag M. Magolama</h4>
                                <p class="leader-position">Second Chancellor (2018-Present)</p>
                                <p class="leader-tenure">Expanded academic programs and established new colleges</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Facts and Campus Information -->
            <div class="row">
                <div class="col-md-4">
                    <div class="quick-facts">
                        <h4 class="quick-facts-title">Quick Facts</h4>
                        <ul class="facts-list">
                            <li><span><i class="fas fa-calendar-alt"></i> Established: 1971</span></li>
                            <li><span><i class="fas fa-graduation-cap"></i> Status: Autonomous Campus</span></li>
                            <li><span><i class="fas fa-map-marker-alt"></i> Location: Datu Panas, Buug, Zamboanga Sibugay</span></li>
                            <li><span><i class="fas fa-building"></i> Colleges: 10+</span></li>
                            <li><span><i class="fas fa-user-graduate"></i> Extension Programs: 2</span></li>
                            <li><span><i class="fas fa-users"></i> Student Population: Growing</span></li>
                            <li><span><i class="fas fa-chalkboard-teacher"></i> Faculty: Committed Professionals</span></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-8">
                    <!-- Mission and Impact -->
                    <div class="info-card">
                        <div class="card-header">
                            <h3><i class="fas fa-bullseye me-2"></i>Mission & Impact</h3>
                        </div>
                        <div class="text-center mb-4">
                            <div class="card-icon">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <p class="lead">Serving the Tri-People of Mindanao</p>
                        </div>
                        
                        <p class="mb-4">The MSU Buug Campus has relentlessly committed to becoming the leading higher educational institution committed to the intellectual, socio-economic, political, and moral development of the people of Zamboanga Sibugay.</p>
                        
                        <p class="mb-4">The MSU Buug is a true partner of the people and the concerned local government units in attaining peace and prosperity among the Tri-people of Mindanao — the Muslims, the Lumads, and the Christians.</p>
                        
                        <p class="mb-4">Peace is not far-fetched as the university becomes a melting pot as it never stops in welcoming influx among students of diverse cultures (representative of the Tri-people) and in extending peace initiatives to communities.</p>
                        
                        <p>Prosperity is not a chasing after the stars as the university inculcates dual excellence among its graduates and professionals — the human assets of academic and character excellence, who will possibly usher the economic lift of their respective tribal community.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
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
        // Add scroll animations
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('.content-section::before');
            if (parallax) {
                parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });

        // Add intersection observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, observerOptions);

        // Observe all info cards and timeline items
        document.querySelectorAll('.info-card, .timeline-content, .leader-card').forEach(el => {
            el.style.animationPlayState = 'paused';
            observer.observe(el);
        });
    </script>
</body>
</html>