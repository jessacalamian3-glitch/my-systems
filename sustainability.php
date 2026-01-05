<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>17 Sustainable Development Goals - MSU Buug Campus</title>
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
   
        /* SUSTAINABLE DEVELOPMENT GOALS STYLES */
        .content-section {
            padding: 120px 0 80px;
            margin-top: 0px;
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

        .section-anchor {
            scroll-margin-top: 140px;
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

        /* SDG GRID STYLES */
        .sdg-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .sdg-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(139, 0, 0, 0.1);
            position: relative;
        }

        .sdg-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .sdg-number {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0,0,0,0.7);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            z-index: 2;
        }

        .sdg-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: all 0.4s ease;
        }

        .sdg-card:hover .sdg-image {
            transform: scale(1.05);
        }

        .sdg-content {
            padding: 1.5rem;
        }

        .sdg-title {
            color: var(--msu-green);
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            line-height: 1.4;
        }

        .sdg-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .sdg-button {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
            font-size: 0.9rem;
        }

        .sdg-button:hover {
            background: linear-gradient(135deg, var(--msu-light), var(--msu-green));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }

        /* SDG CATEGORIES */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .category-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-top: 4px solid var(--msu-green);
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .category-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--msu-gold), #ffed4a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--msu-dark);
            font-size: 1.8rem;
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.3);
        }

        .category-title {
            color: var(--msu-green);
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .category-count {
            color: var(--msu-dark);
            font-weight: 600;
            font-size: 1.1rem;
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
            
            .sdg-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
            
            .categories-grid {
                grid-template-columns: 1fr;
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
        }

        @media (max-width: 480px) {
            .sdg-grid {
                grid-template-columns: 1fr;
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


    <!-- SUSTAINABLE DEVELOPMENT GOALS CONTENT SECTION -->
    <section class="content-section">
        <div class="container">
            <h2 class="section-title">17 Sustainable Development Goals</h2>

            <!-- Introduction Card -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-globe-americas me-2"></i>About the Sustainable Development Goals</h3>
                </div>
                <div class="text-center mb-4">
                    <div class="card-icon">
                        <i class="fas fa-globe-americas"></i>
                    </div>
                    <p class="lead">Transforming Our World: The 2030 Agenda for Sustainable Development</p>
                </div>
                
                <p class="mb-4">The Sustainable Development Goals (SDGs) are a universal call to action to end poverty, protect the planet, and ensure that by 2030 all people enjoy peace and prosperity. These 17 interconnected goals were adopted by all United Nations Member States in 2015 as part of the 2030 Agenda for Sustainable Development.</p>
                
                <p class="mb-4">MSU Buug Campus is committed to contributing to these global goals through education, research, community engagement, and sustainable campus operations. We believe that universities play a crucial role in achieving the SDGs by fostering innovation, promoting sustainability, and educating future leaders.</p>
                
                <p>Explore each of the 17 goals below to learn more about how MSU Buug is working towards a sustainable future for all.</p>
            </div>

            <!-- SDG Categories Overview -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-layer-group me-2"></i>SDG Categories</h3>
                </div>
                <div class="text-center mb-4">
                    <div class="card-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <p class="lead">The Five Pillars of Sustainable Development</p>
                </div>
                
                <div class="categories-grid">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="category-title">People</h4>
                        <p class="category-count">5 Goals</p>
                        <p>Ending poverty and hunger, ensuring dignity and equality</p>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h4 class="category-title">Planet</h4>
                        <p class="category-count">5 Goals</p>
                        <p>Protecting natural resources and climate for future generations</p>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4 class="category-title">Prosperity</h4>
                        <p class="category-count">4 Goals</p>
                        <p>Ensuring prosperous and fulfilling lives in harmony with nature</p>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-peace"></i>
                        </div>
                        <h4 class="category-title">Peace</h4>
                        <p class="category-count">2 Goals</p>
                        <p>Fostering peaceful, just and inclusive societies</p>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <h4 class="category-title">Partnership</h4>
                        <p class="category-count">1 Goal</p>
                        <p>Implementing the agenda through global partnership</p>
                    </div>
                </div>
            </div>

            <!-- 17 SDGs Grid -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-list-ol me-2"></i>The 17 Sustainable Development Goals</h3>
                </div>
                <div class="text-center mb-4">
                    <div class="card-icon">
                        <i class="fas fa-list-ol"></i>
                    </div>
                    <p class="lead">Explore Each Goal and Our Commitment</p>
                </div>
                
                <div class="sdg-grid">
                    <!-- Goal 1 -->
                    <div class="sdg-card">
                        <div class="sdg-number">1</div>
                        <img src="1.png" alt="No Poverty" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">No Poverty</h4>
                            <p class="sdg-description">End poverty in all its forms everywhere by 2030.</p>
                     
                        </div>
                    </div>

                    <!-- Goal 2 -->
                    <div class="sdg-card">
                        <div class="sdg-number">2</div>
                        <img src="2.png" alt="Zero Hunger" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Zero Hunger</h4>
                            <p class="sdg-description">End hunger, achieve food security and improved nutrition.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 3 -->
                    <div class="sdg-card">
                        <div class="sdg-number">3</div>
                        <img src="3.png" alt="Good Health and Well-being" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Good Health & Well-being</h4>
                            <p class="sdg-description">Ensure healthy lives and promote well-being for all.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 4 -->
                    <div class="sdg-card">
                        <div class="sdg-number">4</div>
                        <img src="4.png" alt="Quality Education" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Quality Education</h4>
                            <p class="sdg-description">Ensure inclusive and equitable quality education.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 5 -->
                    <div class="sdg-card">
                        <div class="sdg-number">5</div>
                        <img src="5.png" alt="Gender Equality" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Gender Equality</h4>
                            <p class="sdg-description">Achieve gender equality and empower all women and girls.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 6 -->
                    <div class="sdg-card">
                        <div class="sdg-number">6</div>
                        <img src="6.png" alt="Clean Water and Sanitation" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Clean Water & Sanitation</h4>
                            <p class="sdg-description">Ensure availability of water and sanitation for all.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 7 -->
                    <div class="sdg-card">
                        <div class="sdg-number">7</div>
                        <img src="7.png" alt="Affordable and Clean Energy" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Affordable & Clean Energy</h4>
                            <p class="sdg-description">Ensure access to affordable, reliable, sustainable energy.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 8 -->
                    <div class="sdg-card">
                        <div class="sdg-number">8</div>
                        <img src="8.png" alt="Decent Work and Economic Growth" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Decent Work & Economic Growth</h4>
                            <p class="sdg-description">Promote sustained, inclusive and sustainable economic growth.</p>

                        </div>
                    </div>

                    <!-- Goal 9 -->
                    <div class="sdg-card">
                        <div class="sdg-number">9</div>
                        <img src="9.png" alt="Industry, Innovation and Infrastructure" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Industry, Innovation & Infrastructure</h4>
                            <p class="sdg-description">Build resilient infrastructure, promote sustainable industrialization.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 10 -->
                    <div class="sdg-card">
                        <div class="sdg-number">10</div>
                        <img src="10.png" alt="Reduced Inequality" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Reduced Inequality</h4>
                            <p class="sdg-description">Reduce inequality within and among countries.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 11 -->
                    <div class="sdg-card">
                        <div class="sdg-number">11</div>
                        <img src="11.png" alt="Sustainable Cities and Communities" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Sustainable Cities & Communities</h4>
                            <p class="sdg-description">Make cities and human settlements inclusive, safe, resilient and sustainable.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 12 -->
                    <div class="sdg-card">
                        <div class="sdg-number">12</div>
                        <img src="12.png" alt="Responsible Consumption and Production" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Responsible Consumption & Production</h4>
                            <p class="sdg-description">Ensure sustainable consumption and production patterns.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 13 -->
                    <div class="sdg-card">
                        <div class="sdg-number">13</div>
                        <img src="13.png" alt="Climate Action" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Climate Action</h4>
                            <p class="sdg-description">Take urgent action to combat climate change and its impacts.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 14 -->
                    <div class="sdg-card">
                        <div class="sdg-number">14</div>
                        <img src="14.png" alt="Life Below Water" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Life Below Water</h4>
                            <p class="sdg-description">Conserve and sustainably use the oceans, seas and marine resources.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 15 -->
                    <div class="sdg-card">
                        <div class="sdg-number">15</div>
                        <img src="15.png" alt="Life on Land" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Life on Land</h4>
                            <p class="sdg-description">Protect, restore and promote sustainable use of terrestrial ecosystems.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 16 -->
                    <div class="sdg-card">
                        <div class="sdg-number">16</div>
                        <img src="16.png" alt="Peace, Justice and Strong Institutions" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Peace, Justice & Strong Institutions</h4>
                            <p class="sdg-description">Promote peaceful and inclusive societies for sustainable development.</p>
                            
                        </div>
                    </div>

                    <!-- Goal 17 -->
                    <div class="sdg-card">
                        <div class="sdg-number">17</div>
                        <img src="17.png" alt="Partnerships for the Goals" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">Partnerships for the Goals</h4>
                            <p class="sdg-description">Strengthen the means of implementation and revitalize global partnership.</p>

                        </div>
                    </div>

                    <!-- Additional Image (18.png) -->
                    <div class="sdg-card">
                        <div class="sdg-number">SDG</div>
                        <img src="18.png" alt="Sustainable Development Goals" class="sdg-image">
                        <div class="sdg-content">
                            <h4 class="sdg-title">All Sustainable Development Goals</h4>
                            <p class="sdg-description">The complete set of 17 goals working together for a sustainable future.</p>

                        </div>
                    </div>
                </div>
            </div>

            <!-- MSU Buug Commitment -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-university me-2"></i>MSU Buug's Commitment to SDGs</h3>
                </div>
                <div class="text-center mb-4">
                    <div class="card-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <p class="lead">Our Role in Achieving the Sustainable Development Goals</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-center mb-3" style="color: var(--msu-green);">Our Approach</h5>
                        <ul class="volume-features">
                            <li><i class="fas fa-graduation-cap"></i> Integrating SDGs into curriculum</li>
                            <li><i class="fas fa-flask"></i> Research addressing global challenges</li>
                            <li><i class="fas fa-leaf"></i> Sustainable campus operations</li>
                            <li><i class="fas fa-hands-helping"></i> Community engagement and outreach</li>
                            <li><i class="fas fa-handshake"></i> Partnerships for sustainable development</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-center mb-3" style="color: var(--msu-green);">Key Focus Areas</h5>
                        <ul class="volume-features">
                            <li><i class="fas fa-book"></i> Quality Education (Goal 4)</li>
                            <li><i class="fas fa-seedling"></i> Climate Action (Goal 13)</li>
                            <li><i class="fas fa-hand-holding-heart"></i> Reduced Inequalities (Goal 10)</li>
                            <li><i class="fas fa-peace"></i> Peace and Justice (Goal 16)</li>
                            <li><i class="fas fa-globe"></i> Partnerships (Goal 17)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="mb-3">MSU Buug Campus is dedicated to being a catalyst for sustainable development in our community and beyond.</p>
                    <a href="#" class="sdg-button" style="max-width: 300px;">Download Our Sustainability Report</a>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="info-card text-center">
                <div class="card-header">
                    <h3><i class="fas fa-hands-helping me-2"></i>Get Involved</h3>
                </div>
                <div class="text-center mb-4">
                    <div class="card-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <p class="lead">Join Us in Building a Sustainable Future</p>
                </div>
                
                <p class="mb-4">Everyone has a role to play in achieving the Sustainable Development Goals. Whether you're a student, faculty member, staff, or community partner, there are many ways to contribute to this global effort.</p>
                
                <div class="row justify-content-center">
                    <div class="col-md-4 mb-3">
                        <a href="#" class="sdg-button">Student Opportunities</a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="#" class="sdg-button" style="background: var(--msu-dark);">Research Projects</a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="#" class="sdg-button" style="background: var(--msu-light);">Community Programs</a>
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
        });
    </script>
</body>
</html>