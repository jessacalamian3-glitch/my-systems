<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Offices - MSU Buug Campus</title>
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
            scroll-behavior: smooth;
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

        /* OFFICES PAGE STYLES */
        .content-section {
            padding: 120px 0 80px;
            margin-top:0px;
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
                radial-gradient(circle at 20% 80%, rgba(139, 0, 0, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 215, 0, 0.05) 0%, transparent 50%);
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

        .office-anchor {
            scroll-margin-top: 140px;
            padding-top: 80px;
            margin-top: -80px;
        }

        .info-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
            border-left: 5px solid var(--msu-green);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(139, 0, 0, 0.05), transparent);
            transition: left 0.6s ease;
        }

        .info-card:hover::before {
            left: 100%;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            padding: 1.5rem;
            border-radius: 10px 10px 0 0;
            margin: -2.5rem -2.5rem 2rem -2.5rem;
        }

        .card-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .card-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .info-card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        /* QUICK NAVIGATION */
        .quick-nav {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
            text-align: center;
        }

        .quick-nav-title {
            color: var(--msu-green);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .nav-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .nav-button {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: block;
            text-align: center;
        }

        .nav-button:hover {
            background: linear-gradient(135deg, var(--msu-light), var(--msu-green));
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }

        /* OFFICE STAFF */
        .staff-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .staff-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-top: 4px solid var(--msu-green);
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
            border: 4px solid var(--msu-green);
        }

        .staff-name {
            color: var(--msu-green);
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .staff-position {
            color: var(--msu-dark);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .staff-contact {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        /* SERVICES LIST */
        .services-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .services-list li {
            margin-bottom: 0.8rem;
            padding: 0.8rem 1rem;
            background: rgba(139, 0, 0, 0.05);
            border-radius: 8px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--msu-green);
        }

        .services-list li:hover {
            background: rgba(139, 0, 0, 0.1);
            transform: translateX(5px);
        }

        .services-list i {
            color: var(--msu-green);
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* CONTACT INFO */
        .contact-info {
            background: rgba(139, 0, 0, 0.05);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .contact-item i {
            color: var(--msu-green);
            font-size: 1.2rem;
            width: 30px;
            text-align: center;
            margin-right: 15px;
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
            .content-section {
                padding: 100px 0 50px;
                margin-top: 100px;
                min-height: calc(100vh - 100px);
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .info-card {
                padding: 2rem;
            }
            
            .card-header {
                margin: -2rem -2rem 1.5rem -2rem;
                padding: 1.2rem;
            }
            
            .card-icon {
                width: 60px;
                height: 60px;
                font-size: 1.7rem;
            }
            
            .nav-buttons {
                grid-template-columns: 1fr;
            }
            
            .staff-grid {
                grid-template-columns: 1fr;
            }
            
            .office-anchor {
                scroll-margin-top: 120px;
                padding-top: 60px;
                margin-top: -60px;
            }
            
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
            
            .services-list li:hover {
                transform: none;
            }
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
                                <li><a class="dropdown-item" href="#university-profile">University Profile</a></li>
                                <li><a class="dropdown-item" href="#mission-vision">Mission,Vision,Core Values</a></li>
                                <li><a class="dropdown-item" href="#campus-officials">Campus Officials</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Admission
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#admission-requirements">Requirements</a></li>
                                <li><a class="dropdown-item" href="#application-process">Application Process</a></li>
                                <li><a class="dropdown-item" href="#graduate-programs">Graduate programs</a></li>
                                <li><a class="dropdown-item" href="#undergraduate-programs">Undergraduate Programs</a></li>
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
                                        <li><a class="dropdown-item" href="#senior-high-school">Senior High School</a></li>
                                        <li><a class="dropdown-item" href="#junior-high-school">Junior High School</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">
                                        Colleges
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#college-agriculture">College of Agriculture</a></li>
                                        <li><a class="dropdown-item" href="#college-arts">College of Arts and Sciences</a></li>
                                        <li><a class="dropdown-item" href="#college-education">College of Education</a></li>
                                        <li><a class="dropdown-item" href="#college-public-affairs">College of Public Affairs</a></li>
                                        <li><a class="dropdown-item" href="#college-fisheries">College of Fisheries</a></li>
                                        <li><a class="dropdown-item" href="#college-forestry">College of Forestry & Environmental Studies</a></li>
                                        <li><a class="dropdown-item" href="#college-hospitality">College of Hospitality Management</a></li>
                                        <li><a class="dropdown-item" href="#college-it">College of Information Technology</a></li>
                                        <li><a class="dropdown-item" href="#college-nursing">College of Nursing</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Research & Extension
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#ajmr-publication">AJMR Publication</a></li>
                                <li><a class="dropdown-item" href="#stream-publication">The Stream Publication</a></li>
                                <li><a class="dropdown-item" href="#phanalam-publication">Phanalam Publication</a></li>
                                <li><a class="dropdown-item" href="#research-colloquium">The Research Colloquium Publication</a></li>
                                <li><a class="dropdown-item" href="#thesis-archive">Thesis and Capstone Archive System</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Administration
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#vc-research">Office of the Vice Chancellor for Research and Extensions</a></li>
                                <li><a class="dropdown-item" href="#campus-secretary">Office of the Campus Secretary</a></li>
                                <li><a class="dropdown-item" href="#vc-admin-finance">Office of the Vice Chancellor for Admin & Finance</a></li>
                                <li><a class="dropdown-item" href="#vc-planning">Office of the Vice Chancellor for Planning & Development</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#sustainability">Sustainability</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Offices
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#property-custodian">Office of the Property Custodian</a></li>
                                <li><a class="dropdown-item" href="#chief-security">Office of the Chief Security</a></li>
                                <li><a class="dropdown-item" href="#campus-registrar">Office of the Campus Registrar</a></li>
                                <li><a class="dropdown-item" href="#student-affairs">Office of the Student Affairs and Services</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- UNIVERSITY OFFICES CONTENT SECTION -->
    <section class="content-section">
        <div class="container">
            <h2 class="section-title">University Offices</h2>

            <!-- Quick Navigation -->
            <div class="quick-nav">
                <h3 class="quick-nav-title">Quick Navigation</h3>
                <div class="nav-buttons">
                    <a href="#property-custodian" class="nav-button">
                        <i class="fas fa-boxes me-2"></i>Property Custodian
                    </a>
                    <a href="#chief-security" class="nav-button">
                        <i class="fas fa-shield-alt me-2"></i>Chief Security
                    </a>
                    <a href="#campus-registrar" class="nav-button">
                        <i class="fas fa-file-alt me-2"></i>Campus Registrar
                    </a>
                    <a href="#student-affairs" class="nav-button">
                        <i class="fas fa-users me-2"></i>Student Affairs
                    </a>
                </div>
            </div>

            <!-- Office of the Property Custodian -->
            <div id="property-custodian" class="office-anchor">
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-boxes me-2"></i>Office of the Property Custodian</h3>
                    </div>
                    <div class="text-center mb-4">
                        <div class="card-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <p class="lead">Managing University Assets and Properties</p>
                    </div>
                    
                    <p class="mb-4">The Office of the Property Custodian is responsible for the proper management, safekeeping, and maintenance of all university properties, equipment, and supplies. We ensure that all assets are accounted for, properly maintained, and utilized efficiently to support the university's academic and administrative functions.</p>
                    
                    <h5 class="mb-3" style="color: var(--msu-green);">Key Responsibilities</h5>
                    <ul class="services-list">
                        <li><i class="fas fa-check"></i> Inventory Management of University Properties</li>
                        <li><i class="fas fa-check"></i> Asset Tagging and Tracking</li>
                        <li><i class="fas fa-check"></i> Property Issuance and Retrieval</li>
                        <li><i class="fas fa-check"></i> Maintenance of Property Records</li>
                        <li><i class="fas fa-check"></i> Disposal of Unserviceable Properties</li>
                        <li><i class="fas fa-check"></i> Conduct of Annual Physical Inventory</li>
                    </ul>

                    <h5 class="mt-4 mb-3" style="color: var(--msu-green);">Office Staff</h5>
                    <div class="staff-grid">
                        <div class="staff-card">
                            <img src="https://via.placeholder.com/120" alt="Property Custodian" class="staff-img">
                            <h4 class="staff-name">Mr. Juan Dela Cruz</h4>
                            <p class="staff-position">Property Custodian</p>
                            <p class="staff-contact"><i class="fas fa-envelope me-2"></i>property@msubuug.edu.ph</p>
                            <p class="staff-contact"><i class="fas fa-phone me-2"></i>(062) 000-0001</p>
                        </div>
                        
                        <div class="staff-card">
                            <img src="https://via.placeholder.com/120" alt="Assistant Property Custodian" class="staff-img">
                            <h4 class="staff-name">Ms. Maria Santos</h4>
                            <p class="staff-position">Assistant Property Custodian</p>
                            <p class="staff-contact"><i class="fas fa-envelope me-2"></i>assistant.property@msubuug.edu.ph</p>
                            <p class="staff-contact"><i class="fas fa-phone me-2"></i>(062) 000-0002</p>
                        </div>
                    </div>

                    <div class="contact-info">
                        <h5 class="mb-3" style="color: var(--msu-green);">Office Information</h5>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Location:</strong> Administration Building, Ground Floor
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Office Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Telephone:</strong> (062) 000-0001
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email:</strong> property@msubuug.edu.ph
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Office of the Chief Security -->
            <div id="chief-security" class="office-anchor">
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-shield-alt me-2"></i>Office of the Chief Security</h3>
                    </div>
                    <div class="text-center mb-4">
                        <div class="card-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <p class="lead">Ensuring Campus Safety and Security</p>
                    </div>
                    
                    <p class="mb-4">The Office of the Chief Security is dedicated to maintaining a safe and secure environment for all students, faculty, staff, and visitors. We implement comprehensive security measures and emergency response protocols to protect lives and property within the university campus.</p>
                    
                    <h5 class="mb-3" style="color: var(--msu-green);">Security Services</h5>
                    <ul class="services-list">
                        <li><i class="fas fa-check"></i> 24/7 Campus Security Patrol</li>
                        <li><i class="fas fa-check"></i> Access Control and Gate Security</li>
                        <li><i class="fas fa-check"></i> Emergency Response and Crisis Management</li>
                        <li><i class="fas fa-check"></i> Crime Prevention Programs</li>
                        <li><i class="fas fa-check"></i> Traffic and Parking Management</li>
                        <li><i class="fas fa-check"></i> Safety and Security Awareness Seminars</li>
                    </ul>

                    <h5 class="mt-4 mb-3" style="color: var(--msu-green);">Security Team</h5>
                    <div class="staff-grid">
                        <div class="staff-card">
                            <img src="https://via.placeholder.com/120" alt="Chief Security Officer" class="staff-img">
                            <h4 class="staff-name">Col. Roberto Garcia</h4>
                            <p class="staff-position">Chief Security Officer</p>
                            <p class="staff-contact"><i class="fas fa-envelope me-2"></i>security@msubuug.edu.ph</p>
                            <p class="staff-contact"><i class="fas fa-phone me-2"></i>(062) 000-0003</p>
                        </div>
                        
                        <div class="staff-card">
                            <img src="https://via.placeholder.com/120" alt="Deputy Security Officer" class="staff-img">
                            <h4 class="staff-name">Lt. Michael Tan</h4>
                            <p class="staff-position">Deputy Security Officer</p>
                            <p class="staff-contact"><i class="fas fa-envelope me-2"></i>deputy.security@msubuug.edu.ph</p>
                            <p class="staff-contact"><i class="fas fa-phone me-2"></i>(062) 000-0004</p>
                        </div>
                    </div>

                    <div class="contact-info">
                        <h5 class="mb-3" style="color: var(--msu-green);">Emergency Contacts</h5>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Security Hotline:</strong> (062) 000-0003
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>Emergency:</strong> 0917-123-4567
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Security Office:</strong> Main Gate, Administration Building
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Office of the Campus Registrar -->
            <div id="campus-registrar" class="office-anchor">
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-alt me-2"></i>Office of the Campus Registrar</h3>
                    </div>
                    <div class="text-center mb-4">
                        <div class="card-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <p class="lead">Managing Academic Records and Student Registration</p>
                    </div>
                    
                    <p class="mb-4">The Office of the Campus Registrar is responsible for maintaining the integrity and confidentiality of all academic records. We facilitate student registration, process academic credentials, and ensure compliance with university policies and educational standards.</p>
                    
                    <h5 class="mb-3" style="color: var(--msu-green);">Services Offered</h5>
                    <ul class="services-list">
                        <li><i class="fas fa-check"></i> Student Registration and Enrollment</li>
                        <li><i class="fas fa-check"></i> Academic Records Management</li>
                        <li><i class="fas fa-check"></i> Issuance of Transcript of Records and Diplomas</li>
                        <li><i class="fas fa-check"></i> Course Scheduling and Classroom Assignment</li>
                        <li><i class="fas fa-check"></i> Student Classification and Evaluation</li>
                        <li><i class="fas fa-check"></i> Graduation Processing</li>
                    </ul>

                    <h5 class="mt-4 mb-3" style="color: var(--msu-green);">Registrar Staff</h5>
                    <div class="staff-grid">
                        <div class="staff-card">
                            <img src="https://via.placeholder.com/120" alt="Campus Registrar" class="staff-img">
                            <h4 class="staff-name">Dr. Elena Rodriguez</h4>
                            <p class="staff-position">Campus Registrar</p>
                            <p class="staff-contact"><i class="fas fa-envelope me-2"></i>registrar@msubuug.edu.ph</p>
                            <p class="staff-contact"><i class="fas fa-phone me-2"></i>(062) 000-0005</p>
                        </div>
                        
                        <div class="staff-card">
                            <img src="https://via.placeholder.com/120" alt="Assistant Registrar" class="staff-img">
                            <h4 class="staff-name">Mr. Carlos Reyes</h4>
                            <p class="staff-position">Assistant Registrar</p>
                            <p class="staff-contact"><i class="fas fa-envelope me-2"></i>assistant.registrar@msubuug.edu.ph</p>
                            <p class="staff-contact"><i class="fas fa-phone me-2"></i>(062) 000-0006</p>
                        </div>
                    </div>

                    <div class="contact-info">
                        <h5 class="mb-3" style="color: var(--msu-green);">Office Information</h5>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Location:</strong> Administration Building, 2nd Floor
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Office Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM<br>
                                <small>Saturday: 8:00 AM - 12:00 PM (During Enrollment Period)</small>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Telephone:</strong> (062) 000-0005
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email:</strong> registrar@msubuug.edu.ph
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Office of the Student Affairs and Services -->
            <div id="student-affairs" class="office-anchor">
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-users me-2"></i>Office of the Student Affairs and Services</h3>
                    </div>
                    <div class="text-center mb-4">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <p class="lead">Supporting Student Development and Well-being</p>
                    </div>
                    
                    <p class="mb-4">The Office of Student Affairs and Services is committed to the holistic development of students through various programs, services, and activities that complement academic instruction. We provide support for student organizations, leadership development, counseling services, and student welfare.</p>
                    
                    <h5 class="mb-3" style="color: var(--msu-green);">Student Services</h5>
                    <ul class="services-list">
                        <li><i class="fas fa-check"></i> Student Organization Recognition and Support</li>
                        <li><i class="fas fa-check"></i> Leadership Training and Development</li>
                        <li><i class="fas fa-check"></i> Counseling and Psychological Services</li>
                        <li><i class="fas fa-check"></i> Student Discipline and Grievance Procedures</li>
                        <li><i class="fas fa-check"></i> Scholarship and Financial Assistance</li>
                        <li><i class="fas fa-check"></i> Student Housing and Residence Life</li>
                    </ul>

                    <h5 class="mt-4 mb-3" style="color: var(--msu-green);">OSAS Team</h5>
                    <div class="staff-grid">
                        <div class="staff-card">
                            <img src="https://via.placeholder.com/120" alt="Director of Student Affairs" class="staff-img">
                            <h4 class="staff-name">Dr. Anna Lim</h4>
                            <p class="staff-position">Director, Student Affairs and Services</p>
                            <p class="staff-contact"><i class="fas fa-envelope me-2"></i>student.affairs@msubuug.edu.ph</p>
                            <p class="staff-contact"><i class="fas fa-phone me-2"></i>(062) 000-0007</p>
                        </div>
                        
                        <div class="staff-card">
                            <img src="https://via.placeholder.com/120" alt="Student Activities Coordinator" class="staff-img">
                            <h4 class="staff-name">Ms. Sofia Hernandez</h4>
                            <p class="staff-position">Student Activities Coordinator</p>
                            <p class="staff-contact"><i class="fas fa-envelope me-2"></i>activities@msubuug.edu.ph</p>
                            <p class="staff-contact"><i class="fas fa-phone me-2"></i>(062) 000-0008</p>
                        </div>
                        
                        <div class="staff-card">
                            <img src="https://via.placeholder.com/120" alt="Guidance Counselor" class="staff-img">
                            <h4 class="staff-name">Mr. James Wilson</h4>
                            <p class="staff-position">Guidance Counselor</p>
                            <p class="staff-contact"><i class="fas fa-envelope me-2"></i>counseling@msubuug.edu.ph</p>
                            <p class="staff-contact"><i class="fas fa-phone me-2"></i>(062) 000-0009</p>
                        </div>
                    </div>

                    <div class="contact-info">
                        <h5 class="mb-3" style="color: var(--msu-green);">Contact Information</h5>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Location:</strong> Student Center Building, Ground Floor
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Office Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Telephone:</strong> (062) 000-0007
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email:</strong> student.affairs@msubuug.edu.ph
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back to Top Button -->
            <div class="text-center mt-4">
                <a href="#" class="nav-button" style="max-width: 200px; display: inline-block;">
                    <i class="fas fa-arrow-up me-2"></i>Back to Top
                </a>
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
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.header').classList.add('navbar-scrolled');
            } else {
                document.querySelector('.header').classList.remove('navbar-scrolled');
            }
        });

        // Close mobile navbar when clicking on dropdown links
        document.addEventListener('DOMContentLoaded', function() {
            var navbarToggler = document.querySelector('.navbar-toggler');
            var navbarCollapse = document.querySelector('.navbar-collapse');
            var dropdownItems = document.querySelectorAll('.dropdown-item');
            
            dropdownItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    if (navbarCollapse.classList.contains('show')) {
                        navbarToggler.click();
                    }
                });
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>