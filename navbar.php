/* LARGER NAVBAR STYLES - STILL ONE LINE */
.navbar {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light)) !important;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-height: auto;
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

        /* HOME NAV ITEM - ALIGNED WITH LOGO */
        .nav-item:first-child .nav-link {
            padding-left: 15px !important;
            text-align: left;
            justify-content: flex-start;
        }

        .nav-link {
            color: white !important;
            font-weight: 600;
            margin: 0;
            transition: all 0.3s ease;
            padding: 1rem 0.5rem !important;
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

        .nav-link:hover {
            color: var(--msu-gold) !important;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            color: var(--msu-gold) !important;
            background: rgba(255, 255, 255, 0.08);
            font-weight: 700;
        }

        /* ENHANCED DROPDOWN MENUS */
        @media (min-width: 992px) {
            .navbar .dropdown-menu {
                background: var(--msu-dark);
                border: none;
                border-radius: 0 0 8px 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                padding: 0.5rem 0;
                margin-top: 0;
                border-top: 3px solid var(--msu-gold);
                display: block !important;
                opacity: 0;
                visibility: hidden;
                transform: translateY(10px);
                transition: all 0.3s ease;
                min-width: 220px;
            }

            .navbar .nav-item.dropdown:hover > .dropdown-menu {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            .dropdown-item {
                color: white !important;
                font-weight: 500;
                padding: 0.8rem 1.2rem;
                transition: all 0.3s ease;
                font-size: 0.85rem;
                border-left: 3px solid transparent;
                white-space: normal;
                word-wrap: break-word;
                position: relative;
            }

            .dropdown-item:hover {
                background: var(--msu-green);
                color: var(--msu-gold) !important;
                border-left: 3px solid var(--msu-gold);
                padding-left: 1.5rem;
            }

            .dropdown-item::before {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                height: 100%;
                width: 0;
                background: rgba(255, 215, 0, 0.1);
                transition: width 0.3s ease;
            }

            .dropdown-item:hover::before {
                width: 100%;
            }

            /* SPECIFIC FIX FOR OFFICES DROPDOWN */
            .nav-item:last-child .dropdown-menu {
                right: 0;
                left: auto;
            }

            .dropdown-submenu > .dropdown-menu {
                top: 0;
                left: 100%;
                margin-top: -0.5rem;
                opacity: 0;
                visibility: hidden;
                transform: translateX(10px);
                transition: all 0.3s ease;
                background: var(--msu-dark);
                min-width: 200px;
            }

            .dropdown-submenu:hover > .dropdown-menu {
                opacity: 1;
                visibility: visible;
                transform: translateX(0);
            }
        }

        /* MOBILE NAVIGATION IMPROVEMENTS */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: var(--msu-dark);
                margin-top: 10px;
                border-radius: 8px;
                padding: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
            
            .navbar-toggler {
                border: 2px solid white;
                color: white;
            }
            
            .navbar-toggler:focus {
                box-shadow: none;
            }
            
            .nav-link {
                border-bottom: 1px solid rgba(255,255,255,0.1);
                justify-content: flex-start !important;
                padding-left: 15px !important;
            }
            
            .dropdown-menu {
                background: rgba(0,0,0,0.2) !important;
                border: none;
                margin: 5px 0;
            }

            /* Reset alignment on mobile */
            .nav-item:first-child .nav-link {
                text-align: left;
                justify-content: flex-start;
            }
        }

        /* RESPONSIVE DESIGN IMPROVEMENTS */
        @media (max-width: 768px) {
            .portal-buttons {
                justify-content: center;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .portal-btn {
                min-width: 110px;
                font-size: 0.8rem;
                padding: 5px 12px;
            }
            
            .brand-main {
                font-size: 1.5rem;
            }
            
            .brand-address {
                font-size: 0.9rem;
            }
            
            .logo-img {
                height: 70px;
            }
            
            .nav-link {
                font-size: 0.85rem;
                padding: 0.8rem 0.3rem !important;
            }

            .logo-container {
                margin-left: 0;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .navbar-nav {
                flex-wrap: wrap;
            }
            
            .nav-item {
                flex: 1 0 50%;
            }
            
            .brand-main {
                font-size: 1.3rem;
            }
            
            .logo-img {
                height: 60px;
                margin-right: 15px;
            }
}

/* ADJUST HEADER POSITION FOR LARGER NAVBAR */
.header {
    top: 40px;
}
/* FIXED MULTI-LEVEL DROPDOWN - HINDI SABAY-SABAY LUMALABAS */
        @media (min-width: 992px) {
            /* Main dropdown container */
            .navbar .dropdown-menu {
                background: var(--msu-dark);
                border: none;
                border-radius: 15px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                padding: 0.5rem 0;
                margin-top: 0.5rem;
                border: 1px solid rgba(255,255,255,0.1);
                display: block !important;
                opacity: 0;
                visibility: hidden;
                transform: translateY(15px) scale(0.95);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                backdrop-filter: blur(20px);
            }

            /* Main dropdown - show only when hovering parent */
            .navbar .nav-item.dropdown:hover > .dropdown-menu {
                opacity: 1;
                visibility: visible;
                transform: translateY(0) scale(1);
            }

            /* Dropdown items */
            .dropdown-item {
                color: white !important;
                font-weight: 500;
                padding: 0.8rem 1.5rem;
                transition: all 0.3s ease;
                font-size: 0.85rem;
                border-left: 3px solid transparent;
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
                background: linear-gradient(90deg, transparent, rgba(255,215,0,0.1), transparent);
                transition: left 0.5s ease;
            }

            .dropdown-item:hover::before {
                left: 100%;
            }

            .dropdown-item:hover {
                background: var(--msu-green);
                color: var(--msu-gold) !important;
                border-left: 3px solid var(--msu-gold);
                padding-left: 2rem;
                transform: translateX(5px);
            }

            /* Multi-level dropdown styles - FIXED */
            .dropdown-submenu {
                position: relative;
            }

            .dropdown-submenu > .dropdown-menu {
                top: 0;
                left: 100%;
                margin-top: -0.5rem;
                margin-left: 0.1rem;
                opacity: 0;
                visibility: hidden;
                transform: translateX(15px) scale(0.95);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                background: var(--msu-dark);
                backdrop-filter: blur(20px);
                border: none;
                border-radius: 15px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                display: block !important;
            }

            /* Sub-dropdown - show only when hovering the specific submenu item */
            .dropdown-submenu:hover > .dropdown-menu {
                opacity: 1;
                visibility: visible;
                transform: translateX(0) scale(1);
            }

            /* Keep submenu open when hovering the submenu itself */
            .dropdown-submenu > .dropdown-menu:hover {
                opacity: 1;
                visibility: visible;
                transform: translateX(0) scale(1);
            }

            .dropdown-submenu .dropdown-toggle::after {
                margin-left: 0.5rem;
                float: right;
                transform: rotate(0deg);
                transition: transform 0.4s ease;
            }

            .dropdown-submenu:hover .dropdown-toggle::after {
                transform: rotate(90deg);
            }

            /* Dropdown arrow indicator */
            .dropdown-toggle::after {
                transition: transform 0.3s ease;
            }

            .nav-item.dropdown:hover .dropdown-toggle::after {
                transform: rotate(180deg);
            }

            /* Add gap between main menu and submenu to prevent accidental closing */
            .dropdown-submenu > .dropdown-menu {
                margin-left: 5px;
            }
        }

        /* Mobile responsiveness */
        @media (max-width: 991.98px) {
            .dropdown-submenu .dropdown-menu {
                position: static;
                margin-left: 1rem;
                border-left: 3px solid var(--msu-green);
                box-shadow: none;
                opacity: 1;
                visibility: visible;
                transform: none;
                display: none;
            }
            
            .dropdown-submenu:hover .dropdown-menu {
                display: none;
            }
            
            .dropdown-submenu.show .dropdown-menu {
                display: block;
            }
            
            .navbar-nav {
                text-align: center;
            }
            
            .nav-link {
                margin: 0.2rem 0;
            }

            .logo-img {
                height: 70px;
            }

            .brand-main {
                font-size: 1.4rem;
            }

            .brand-address {
                font-size: 0.95rem;
            }
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


        @media (max-width: 576px) {
            .sdg-grid-full {
                grid-template-columns: repeat(3, 1fr);
            }

            .top-bar-buttons {
                align-items: center;
                width: 100%;
            }
            
            .header {
                top: 30px;
            }
            
            .welcome-carousel,
            .portal-hero {
                margin-top: 140px;
                padding-top: 140px;
            }

            .brand-main {
                font-size: 1rem;
            }
            
            .brand-address {
                font-size: 0.8rem;
            }
            
            .logo-img {
                height: 50px;
            }

            .welcome-title {
                font-size: 2rem;
            }
            
            .carousel-btn {
                width: 40px;
                height: 40px;
            }
            
            .enter-button {
                padding: 10px 30px;
                font-size: 1rem;
            }
        }

        @media (max-width: 400px) {
            .sdg-grid-full {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        /* TOP BAR HOVER EFFECTS */
.top-bar {
    background: var(--footer-gray);
    padding: 0.3rem 0;
  
}

.top-bar a {
    color: black !important;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    transition: all 0.3s ease;
    position: relative;
    display: inline-block;
}

.top-bar a:hover {
    color: var(--msu-gold) !important;
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-1px);
   
}

/* Specific hover effects for different portals */
.top-bar a[href*="portal"]:hover,
.top-bar a[href*="login"]:hover,
.top-bar a[href*="system"]:hover {
    background: linear-gradient(135deg, rgba(255,215,0,0.2), rgba(255,165,0,0.2));
    border: 1px solid var(--msu-gold);
}

/* Icon animations */
.top-bar a:hover::before {
    content: "➜ ";
    margin-right: 5px;
    animation: bounce 0.5s ease infinite alternate;
}

@keyframes bounce {
    from { transform: translateX(0); }
    to { transform: translateX(3px); }
}

/* Optional: Add different colors for different portals */
.top-bar a[href*="student"]:hover {
    color: #4CAF50 !important;
    border-color: #4CAF50;
}

.top-bar a[href*="faculty"]:hover {
    color: #2196F3 !important;
    border-color: #2196F3;
}




.top-bar a[href*="library"]:hover {
    color: #9C27B0 !important;
    border-color: #9C27B0;
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

    <!-- HEADER -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <a class="logo-container" href="#" onclick="showSection('landing')">
                            <img src="msulogo.png" alt="MSU Buug Logo" class="logo-img">
                            <div class="brand-text">
                                <span class="brand-main">Mindanao State University - Buug</span>
                                <span class="brand-address">Datu Panas, Buug, Zamboanga Sibugay</span>
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
