<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Officials - MSU Buug Campus</title>
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
            padding-top: 0 !important;
        }

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
        /* CONTENT SECTION - SAME AS UNIVERSITY PROFILE */
        .content-section {
            padding: 180px 0 80px;
            min-height: calc(100vh - 120px);
            background: linear-gradient(135deg, #f8f9fa 0%, white 50%, #f8f9fa 100%);
            position: relative;
            overflow: hidden;
            margin top: 0px;
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
        }

        .section-title {
            color: var(--msu-green);
            font-weight: 800;
            margin-bottom: 4rem;
            position: relative;
            text-align: center;
            font-size: 3rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
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
        }

        /* CAMPUS OFFICIALS SPECIFIC STYLES */
        .unit-section {
            margin-bottom: 4rem;
        }

        .unit-title {
            color: var(--msu-green);
            font-weight: 800;
            margin-bottom: 2.5rem;
            text-align: center;
            font-size: 2.2rem;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--msu-gold);
            display: inline-block;
            position: relative;
        }

        .official-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            border-left: 5px solid var(--msu-green);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .official-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(139, 0, 0, 0.05), transparent);
            transition: left 0.6s ease;
        }

        .official-card:hover::before {
            left: 100%;
        }

        .official-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .official-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 4px solid var(--msu-green);
            transition: all 0.3s ease;
        }

        .official-card:hover .official-img {
            border-color: var(--msu-gold);
            transform: scale(1.1);
        }

        .official-name {
            color: var(--msu-dark);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .official-position {
            color: var(--msu-green);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0;
            line-height: 1.4;
        }

        .chancellor-card {
            background: linear-gradient(135deg, white, #fff8f8);
            border-left: 5px solid var(--msu-gold);
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
        }

        .chancellor-card .official-name {
            color: var(--msu-green);
            font-size: 1.5rem;
        }

        .chancellor-card .official-position {
            color: var(--msu-light);
            font-size: 1.2rem;
        }

        /* FOOTER - SAME AS UNIVERSITY PROFILE */
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
            .content-section {
                padding: 160px 0 50px;
                min-height: calc(100vh - 100px);
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .unit-title {
                font-size: 1.8rem;
            }
            
            .official-card {
                padding: 1.5rem;
            }
            
            .official-name {
                font-size: 1.2rem;
            }
            
            .official-position {
                font-size: 1rem;
            }
            
            .footer {
                text-align: center;
            }
            
            .footer-column-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
        }
    </style>
</head>
<body>
    <!-- TOP BAR - SAME AS UNIVERSITY PROFILE -->
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

    <!-- HEADER - SAME AS UNIVERSITY PROFILE -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <a class="logo-container" href="#" onclick="showSection('landing')">
                            <img src="msulogo.png" alt="MSU Buug Logo" class="logo-img">
                            <div class="brand-text">
                                <span class="brand-main">Mindanao State University - Zamboanga Sibugay</span>
                                <span class="brand-address">Datu Panas, Buug, Zamboanga Sibugay</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- NAVIGATION - SAME AS UNIVERSITY PROFILE -->
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index_new.php">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Transparency
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="university-profile.php">University Profile</a></li>
                                <li><a class="dropdown-item" href="mission-vision.php">Mission,Vision,Core Values</a></li>
                             
                            
                                <li><a class="dropdown-item active" href="campus-officials.php">Campus Officials</a></li>
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

    <!-- CAMPUS OFFICIALS CONTENT SECTION -->
    <section class="content-section">
        <div class="container">
            <h2 class="section-title">Campus Officials</h2>
            
            <!-- Heads of Administrative Units -->
            <div class="unit-section">
                <h3 class="unit-title text-center w-100">Heads of Administrative Units</h3>
                <div class="row">
                    <!-- Chancellor - Special Card -->
                    <div class="col-lg-6 mx-auto mb-4">
                        <div class="official-card chancellor-card">
                            <img src="sheila.webp" alt="DR. SHEILA G. MAGOLAMA" class="official-img">
                            <div class="official-name">DR. SHEILA G. MAGOLAMA</div>
                            <div class="official-position">Chancellor</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="grace.jpg" alt="DR. GRACE F. EMPALMADO" class="official-img">
                            <div class="official-name">DR. GRACE F. EMPALMADO</div>
                            <div class="official-position">Vice Chancellor for Admin. & Finance</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="rovy.jpg" alt="DR. ROVY M. BANGUIS" class="official-img">
                            <div class="official-name">DR. ROVY M. BANGUIS</div>
                            <div class="official-position">Vice Chancellor for Research and Extension</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="jether.jpg" alt="ASSOC. PROF. JETHER P. SUMPO" class="official-img">
                            <div class="official-name">ASSOC. PROF. JETHER P. SUMPO</div>
                            <div class="official-position">Vice Chancellor for Planning and Development</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="carol.jpg" alt="ASST. PROF. CAROL JANE C. SIOSE" class="official-img">
                            <div class="official-name">ASST. PROF. CAROL JANE C. SIOSE</div>
                            <div class="official-position">Campus Secretary</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="rowin.jpg" alt="ASST.PROF. ROWIN O. BORONGAN" class="official-img">
                            <div class="official-name">ASST. PROF. ROWIN O. BORONGAN</div>
                            <div class="official-position">Director, Budget Office</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="" alt="MS. TAHANIE S. MLA" class="official-img">
                            <div class="official-name">MS. TAHANIE S. MLA</div>
                            <div class="official-position">Human Resource Officer</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="noralain.jpg" alt="MS. NORALAIN D. CAMSA " class="official-img">
                            <div class="official-name">MS. NORALAIN D. CAMSA</div>
                            <div class="official-position">Campus Accountant</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="jecellou.jpg" alt="ASST. PROF. JECELLOU F. ALAJAS" class="official-img">
                            <div class="official-name">ASST. PROF. JECELLOU F. ALAJAS</div>
                            <div class="official-position">Head, RT Lim Extension Campus</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Heads of Academic Units -->
            <div class="unit-section">
                <h3 class="unit-title text-center w-100">Heads of Academic Units</h3>
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="ivy.jpg" alt="DR. IVY F. AMANTE" class="official-img">
                            <div class="official-name">DR. IVY F. AMANTE</div>
                            <div class="official-position">Vice Chancellor for Academic Affairs</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="jennifer.jpg" alt="ASSOC. PROF. JENNIFER CASTILLANO" class="official-img">
                            <div class="official-name">ASSOC. PROF. JENNIFER CASTILLANO</div>
                            <div class="official-position">Dean, College of Arts & Sciences</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="mario.jpg" alt="DR. MARIO B. TORRED" class="official-img">
                            <div class="official-name">DR. MARIO B. TORRED</div>
                            <div class="official-position">Dean, College of Agriculture</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="alden.jpg" alt="DR. ALDEN PAUL S. GENOVIA" class="official-img">
                            <div class="official-name">DR. ALDIN PAUL S. GENOVIA</div>
                            <div class="official-position">Dean, College of Education</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="bryan.jpg" alt="DR. BRYAN L. BITANTOS" class="official-img">
                            <div class="official-name">DR. BRYAN L. BITANTOS</div>
                            <div class="official-position">Dean, College of Forestry and Envi. Studies</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="jecellou.jpg" alt="ASST. PROF. JECELLOU F. ALAJAS" class="official-img">
                            <div class="official-name">ASST. PROF. JECELLOU F. ALAJAS</div>
                            <div class="official-position">Dean, College of Fisheries</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="" alt="ASST.PROF. RUTHCELLE A. TAN" class="official-img">
                            <div class="official-name">ASST. PROF. RUTHCELLE A. TAN</div>
                            <div class="official-position">Dean, College of Hospitality Management</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="dennis.jpg" alt="ENGR. DENNIS C. TENERIFE" class="official-img">
                            <div class="official-name">ENGR. DENNIS C. TENERIFE</div>
                            <div class="official-position">Dean, College of Information Technology</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="mary.jpg" alt="ASST. PROF. MARY ANNE M. POLESTINA" class="official-img">
                            <div class="official-name">ASST. PROF. MARY ANNE M. POLESTINA</div>
                            <div class="official-position">Dean, College of Public Affairs</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <img src="mark.jpg" alt="ASST. PROF. MARK ANTHONY A. DALUMPINES" class="official-img">
                            <div class="official-name">ASST. PROF. MARK ANTHONY A. DALUMPINES</div>
                            <div class="official-position">Principal, Senior High School Department</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="official-card">
                            <div class="official-name">MS. JAMERAIDAH HADJI JAMEL</div>
                            <div class="official-position">Principal, Junior High School Department</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER - SAME AS UNIVERSITY PROFILE -->
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
        // Navbar scroll effect - SAME AS UNIVERSITY PROFILE
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.header').classList.add('navbar-scrolled');
            } else {
                document.querySelector('.header').classList.remove('navbar-scrolled');
            }
        });
    </script>
</body>
</html>