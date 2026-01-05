 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindanao State University - Buug Campus</title>
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

/* COLLEGES SECTION - ONE COLUMN HORIZONTAL, NO SCROLL */
.colleges-section {
    padding: 15px 0;
    background: transparent !important;
    min-height: auto;
    position: relative;
    overflow: hidden;
    border: 3px solid var(--msu-green);
    border-radius: 10px;
    margin: 10px 0;
    animation: fadeInUp 0.8s ease-out; /* FADE IN ANIMATION */
}

.colleges-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--msu-green);
    margin-bottom: 0;
    padding: 10px 0 5px 0;
    text-transform: uppercase;
    letter-spacing: 1px;
    animation: bounceIn 1s ease-out; /* BOUNCE ANIMATION */
}

.colleges-subtitle {
    font-size: 1rem;
    color: var(--msu-dark);
    font-weight: 600;
    margin-bottom: 0;
    padding: 0;
    animation: fadeIn 1.2s ease-out; /* FADE ANIMATION */
}

.colleges-horizontal-no-scroll {
    display: flex;
    justify-content: space-between;
    align-items: stretch;
    width: 100%;
    flex-wrap: nowrap;
    padding: 0;
    overflow: hidden;
    margin: 0 auto;
    height: 150px;
}

.college-item {
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    position: relative;
    flex-shrink: 0;
    padding: 0 !important;
    margin: 0 !important;
    min-width: 0;
    flex: 1;
    height: 100%;
    animation: zoomIn 0.6s ease-out; /* ZOOM ANIMATION */
}

.college-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: all 0.3s ease;
    filter: grayscale(0.3);
    opacity: 0.9;
    background: transparent !important;
    border: none !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

.college-item:hover .college-img {
    transform: scale(1.15);
    filter: grayscale(0);
    opacity: 1;
    z-index: 1;
    animation: pulse 0.5s ease-in-out; /* PULSE ANIMATION ON HOVER */
}

/* Facebook hover indicator */
.college-item::after {
    content: '\f39e';
    font-family: 'Font Awesome 5 Brands';
    position: absolute;
    bottom: 15px;
    right: 15px;
    background: var(--msu-green);
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s ease;
    z-index: 2;
}

.college-item:hover::after {
    opacity: 0.9;
    transform: scale(1);
    animation: bounce 0.6s ease; /* BOUNCE ANIMATION */
}

.colleges-note {
    color: var(--msu-dark);
    font-size: 0.8rem;
    font-weight: 500;
    font-style: italic;
    opacity: 0.7;
    margin-top: 0.3rem;
    padding: 0;
    animation: fadeIn 1.5s ease-out; /* FADE ANIMATION */
}

/* BORDER GLOW ANIMATION */
.colleges-section:hover {
    animation: borderGlow 2s infinite; /* GLOW EFFECT ON HOVER */
}

/* KEYFRAMES ANIMATIONS */
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

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1.15);
    }
}

@keyframes bounce {
    0%, 20%, 53%, 80%, 100% {
        transform: scale(1);
    }
    40%, 43% {
        transform: scale(1.3);
    }
    70% {
        transform: scale(1.1);
    }
}

@keyframes borderGlow {
    0%, 100% {
        box-shadow: 0 0 5px var(--msu-green);
        border-color: var(--msu-green);
    }
    50% {
        box-shadow: 0 0 20px var(--msu-green);
        border-color: #2ecc71;
    }
}

/* STAGGER ANIMATION FOR LOGOS */
.college-item:nth-child(1) { animation-delay: 0.1s; }
.college-item:nth-child(2) { animation-delay: 0.2s; }
.college-item:nth-child(3) { animation-delay: 0.3s; }
.college-item:nth-child(4) { animation-delay: 0.4s; }
.college-item:nth-child(5) { animation-delay: 0.5s; }
.college-item:nth-child(6) { animation-delay: 0.6s; }
.college-item:nth-child(7) { animation-delay: 0.7s; }
.college-item:nth-child(8) { animation-delay: 0.8s; }
.college-item:nth-child(9) { animation-delay: 0.9s; }

/* Responsive Design */
@media (max-width: 1400px) {
    .colleges-horizontal-no-scroll {
        height: 140px;
    }
    
    .colleges-title {
        font-size: 2.3rem;
    }
}

@media (max-width: 1200px) {
    .colleges-horizontal-no-scroll {
        height: 130px;
    }
    
    .colleges-title {
        font-size: 2.1rem;
    }
}

@media (max-width: 992px) {
    .colleges-horizontal-no-scroll {
        height: 120px;
    }
    
    .colleges-title {
        font-size: 1.9rem;
    }
    
    .colleges-subtitle {
        font-size: 0.9rem;
    }
}

@media (max-width: 768px) {
    .colleges-horizontal-no-scroll {
        height: 110px;
    }
    
    .colleges-title {
        font-size: 1.7rem;
    }
    
    .colleges-subtitle {
        font-size: 0.85rem;
    }
}

@media (max-width: 576px) {
    .colleges-horizontal-no-scroll {
        height: 100px;
    }
    
    .colleges-title {
        font-size: 1.5rem;
    }
    
    .colleges-subtitle {
        font-size: 0.8rem;
    }
    
    .colleges-note {
        font-size: 0.7rem;
    }
}

@media (max-width: 480px) {
    .colleges-horizontal-no-scroll {
        height: 90px;
    }
    
    .colleges-title {
        font-size: 1.3rem;
    }

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

 /* WELCOME CAROUSEL STYLES - ZERO SPACING */
        .welcome-carousel {
            min-height: calc(100vh - 180px);
            position: relative;
            overflow: hidden;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .carousel-slide.active {
            opacity: 1;
        }

        .slide-1 { background-image: url('logos.jpg'); }
        .slide-2 { background-image: url('femsusco.jpeg'); }
        .slide-3 { background-image: url('images/54.jpg'); }
        .slide-4 { background-image: url('msu.jpg'); }

        .carousel-indicators {
            display: flex;
            justify-content: center;
            gap: 0.8rem;
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 3;
            margin: 0;
            padding: 0;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid white;
        }

        .indicator.active {
            background: var(--msu-gold);
            transform: scale(1.2);
        }

        .scroll-indicator {
            position: absolute;
            bottom: 50px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 2rem;
            animation: bounce 2s infinite;
            cursor: pointer;
            z-index: 3;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0) translateX(-50%);
            }
            40% {
                transform: translateY(-10px) translateX(-50%);
            }
            60% {
                transform: translateY(-5px) translateX(-50%);
            }
        }

       
        /* 3-COLUMN LAYOUT */
        .hero-three-column {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 2rem;
            margin: 3rem 0;
        }

        .column-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: 3px solid transparent;
            transition: all 0.3s ease;
            height: 100%;
        }

        .column-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-color: var(--msu-green);
        }

        .column-title {
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: var(--msu-green);
            text-align: center;
            border-bottom: 3px solid var(--msu-gold);
            padding-bottom: 0.8rem;
        }

        .clickable-item {
            display: block;
            text-decoration: none;
            color: inherit;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .clickable-item:hover {
            transform: translateX(5px);
            background: rgba(139, 0, 0, 0.03);
            padding-left: 10px;
            border-radius: 8px;
            text-decoration: none;
            color: inherit;
        }

        .item-date {
            color: var(--msu-green);
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .item-title {
            font-weight: 600;
            color: var(--msu-dark);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .item-desc {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .item-image {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-bottom: 10px;
            height: 120px;
        }

        .clickable-item:hover .item-image {
            transform: scale(1.05);
        }

        .center-column {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .main-update {
            background: linear-gradient(135deg, rgba(139, 0, 0, 0.05), rgba(255, 215, 0, 0.05));
            border-radius: 15px;
            overflow: hidden;
            border: 3px solid var(--msu-green);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }

        .main-update:hover {
            transform: translateY(-5px);
            text-decoration: none;
            color: inherit;
        }

        .update-image {
            width: 100%;
            height: 250px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .update-content {
            padding: 2rem;
        }

        .update-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--msu-green);
            margin-bottom: 1rem;
        }

        .update-date {
            color: var(--msu-light);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .update-desc {
            color: #444;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .other-updates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .small-update {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid var(--msu-green);
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .small-update:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            text-decoration: none;
            color: inherit;
        }

        .small-update-title {
            font-weight: 700;
            color: var(--msu-dark);
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }

        .small-update-date {
            color: var(--msu-green);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .view-all-btn {
            background: transparent;
            color: var(--msu-green);
            border: 1px solid var(--msu-green);
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            width: 100%;
            margin-top: 1rem;
            cursor: pointer;
            font-size: 0.9rem;
            text-align: center;
        }

        .view-all-btn:hover {
            background: var(--msu-green);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 0, 0, 0.2);
            text-decoration: none;
        }

 
/* SUSTAINABLE DEVELOPMENT GOALS SECTION - ONE ROW HORIZONTAL */
.sustainable-section {
    padding: 0; /* REMOVED PADDING */
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    /* REMOVED BORDER */
    margin: 0; /* REMOVED MARGIN */
}

.sustainable-main-banner {
    width: 100%;
    max-width: 100%;
    height: 120px;
    object-fit: contain;
    margin: 0; /* REMOVED MARGIN - NO GAP */
    display: block;
}

.sdg-horizontal-no-scroll {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0;
    width: 100%;
    flex-wrap: nowrap;
    padding: 0;
    overflow: hidden;
    margin: 0 auto;
    height: 100px;
}

.sdg-item {
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    position: relative;
    flex-shrink: 0;
    padding: 0 !important;
    margin: 0 !important;
    flex: 1;
    height: 100%;
    min-width: 0;
}

.sdg-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: all 0.3s ease;
    filter: grayscale(0.2);
    opacity: 0.9;
    padding: 8px;
}

.sdg-item:hover .sdg-img {
    transform: scale(1.2);
    filter: grayscale(0);
    opacity: 1;
    z-index: 10;
}

/* RESPONSIVE DESIGN */
@media (max-width: 1400px) {
    .sdg-horizontal-no-scroll {
        height: 90px;
    }
}

@media (max-width: 1200px) {
    .sdg-horizontal-no-scroll {
        height: 80px;
    }
}

@media (max-width: 992px) {
    .sdg-horizontal-no-scroll {
        height: 70px;
    }
    
    .sustainable-main-banner {
        height: 100px;
    }
}

@media (max-width: 768px) {
    .sdg-horizontal-no-scroll {
        height: 60px;
    }
    
    .sustainable-main-banner {
        height: 80px;
    }
}

@media (max-width: 576px) {
    .sdg-horizontal-no-scroll {
        height: 50px;
    }
    
    .sustainable-main-banner {
        height: 60px;
    }
}

@media (max-width: 480px) {
    .sdg-horizontal-no-scroll {
        height: 45px;
    }
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

        .systems-list {
            list-style: none;
            padding: 0;
            margin: 0;
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

        /* SCROLL INDICATOR */
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: var(--msu-green);
            font-size: 2rem;
            animation: bounce 2s infinite;
            opacity: 0.8;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .scroll-indicator:hover {
            opacity: 1;
            transform: translateX(-50%) scale(1.1);
            background: white;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0) translateX(-50%);
            }
            40% {
                transform: translateY(-10px) translateX(-50%);
            }
            60% {
                transform: translateY(-5px) translateX(-50%);
            }
        }

        /* SECTION MANAGEMENT */
        .section {
            display: none;
        }
        
        .section.active {
            display: block;
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
/* FULL WIDTH IMAGE SECTION - NO BORDERS */
.fullwidthsection {
    width: 100%;
    padding: 40px 0;
    background: linear-gradient(135deg, #8B0000 0%, #A52A2A 50%, #8B0000 100%);
    margin: 20px 0;
}

.fullwidthcontainer {
    display: flex;
    justify-content: space-between;
    align-items: stretch;
    gap: 0; /* WALANG GAP PARA FULL OCCUPY */
    max-width: 100%;
    margin: 0;
    padding: 0;
}

.fullwidthcard {
    flex: 1;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    padding: 0;
    background: white;
    transition: all 0.3s ease;
    text-align: center;
    overflow: hidden;
    min-height: 400px;
    /* WALANG BORDER */
    /* WALANG BOX-SHADOW */
    margin: 0;
}

.fullwidthcard:hover {
    transform: translateY(-5px);
    background: #f8f9fa;
    text-decoration: none;
}

.imagebox {
    width: 100%;
    height: 250px;
    background: linear-gradient(135deg, #8B0000, #A52A2A);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    overflow: hidden;
}

.imagebox img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: white;
}

.fullwidthcard h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #8B0000;
    margin: 25px 0 15px 0;
    padding: 0 20px;
}

.fullwidthcard p {
    font-size: 1rem;
    color: #666;
    line-height: 1.5;
    margin: 0;
    font-weight: 500;
    padding: 0 20px 25px 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .fullwidthsection {
        padding: 30px 0;
    }
    
    .fullwidthcontainer {
        flex-direction: column;
    }
    
    .fullwidthcard {
        min-height: 350px;
    }
    
    .imagebox {
        height: 200px;
    }
}

@media (max-width: 576px) {
    .fullwidthsection {
        padding: 20px 0;
    }
    
    .fullwidthcard {
        min-height: 300px;
    }
    
    .imagebox {
        height: 180px;
    }
    
    .fullwidthcard h3 {
        font-size: 1.3rem;
        margin: 20px 0 12px 0;
    }
    
    .fullwidthcard p {
        font-size: 0.9rem;
        padding: 0 15px 20px 15px;
    }
    /* SEARCH BAR STYLES */
/* ===== COMPACT SEARCH BAR STYLES ===== */
.search-container {
    max-width: 250px;
    margin-left: 20px;
}

.search-form {
    position: relative;
}

.search-input-group {
    height: 38px; /* Same height as nav links */
}

.search-field {
    height: 100%;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-right: none;
    border-radius: 20px 0 0 20px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    padding: 8px 15px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}

.search-field::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.search-field:focus {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-color: var(--msu-gold);
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.1);
    outline: none;
}

.search-btn {
    height: 100%;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-left: none;
    border-radius: 0 20px 20px 0;
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
    padding: 8px 15px;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: var(--msu-gold);
}

/* Search bar expands on hover */
.search-container:hover .search-field {
    width: 280px;
    background: rgba(255, 255, 255, 0.15);
}

/* Mobile responsive */
@media (max-width: 991.98px) {
    .search-container {
        max-width: 100%;
        margin: 10px 15px 15px 15px;
        order: 3;
    }
    
    .search-field {
        width: 100% !important;
        background: rgba(255, 255, 255, 0.15);
    }
    
    .search-btn {
        background: rgba(255, 255, 255, 0.2);
    }
}

/* Optional: Add search icon only on mobile */
.search-mobile-icon {
    display: none;
    color: white;
    font-size: 1.2rem;
    padding: 10px;
    cursor: pointer;
}

@media (max-width: 768px) {
    .search-container {
        display: none; /* Hide full search bar */
    }
    
    .search-mobile-icon {
        display: block;
        margin-left: auto;
    }
    
    /* Show search when icon clicked */
    .search-container.mobile-visible {
        display: block;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--msu-dark);
        padding: 15px;
        z-index: 1000;
        margin: 0;
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
                                        <li><a class="dropdown-item" href="senior-high.php">Senior High School</a></li>
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
                                <li><a class="dropdown-item" href="phanalam-publication.php">Phanalam Publication</a></li>
                                <li><a class="dropdown-item" href="research-colloquium.php">The Research Colloquium Publication</a></li>
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
    
    <!-- Search bar -->
  
<div class="ms-auto me-3 compact-search">
    <form action="search_results.php" method="GET">
        <div class="input-group" style="width: 220px;">
            <input type="text" name="search_query" 
                   class="form-control search-compact" 
                   placeholder="Search..."
                   aria-label="Search">
            <button class="btn btn-sm btn-search-compact" type="submit">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>
    <!-- WELCOME CAROUSEL -->
    <section class="welcome-carousel">
        <div class="carousel-slide slide-1 active"></div>
        <div class="carousel-slide slide-2"></div>
        <div class="carousel-slide slide-3"></div>
        <div class="carousel-slide slide-4"></div>

        

        

            <div class="carousel-indicators">
                <div class="indicator active" onclick="goToSlide(0)"></div>
                <div class="indicator" onclick="goToSlide(1)"></div>
                <div class="indicator" onclick="goToSlide(2)"></div>
                <div class="indicator" onclick="goToSlide(3)"></div>
            </div>
        </div>
    </section>

   <div class="hero-three-column">
                    <?php
                    require_once 'config/database.php';

                    $featured_update = $pdo->query("SELECT * FROM latest_updates WHERE is_featured = 1 LIMIT 1")->fetch();

                    if (!$featured_update) {
                        $featured_update = $pdo->query("SELECT * FROM latest_updates ORDER BY date_posted DESC LIMIT 1")->fetch();
                    }

                    $other_updates = $pdo->query("SELECT * FROM latest_updates WHERE id != " . ($featured_update['id'] ?? 0) . " LIMIT 2")->fetchAll();
                    $events = $pdo->query("SELECT * FROM upcoming_events LIMIT 3")->fetchAll();
                    $announcements = $pdo->query("SELECT * FROM announcements LIMIT 3")->fetchAll();
                    ?>
                    
                   <!-- LEFT COLUMN - UPCOMING EVENTS -->
<div class="left-column">
    <div class="column-card">
        <h3 class="column-title">Upcoming Events</h3>
        
        <?php if (!empty($events)): ?>
            <?php foreach($events as $event): ?>
                <a href="view_event.php?id=<?= $event['id'] ?>" class="clickable-item">
                    <?php if (!empty($event['image_path'])): ?>
                        <div class="item-image" style="background-image: url('images/<?= $event['image_path'] ?>');"></div>
                    <?php else: ?>
                        <div class="item-image" style="background-image: url('default-event.jpg');"></div>
                    <?php endif; ?>
                    <div class="item-date"><?= date('M j, Y', strtotime($event['event_date'])) ?></div>
                    <h4 class="item-title"><?= htmlspecialchars($event['title']) ?></h4>
                    <p class="item-desc">
                        <?php 
                        $description = $event['description'] ?? $event['location'] ?? 'No description available';
                        echo htmlspecialchars(substr($description, 0, 120));
                        if (strlen($description) > 120) echo '...';
                        ?>
                    </p>
                    <div class="item-meta">
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location'] ?? 'TBA') ?>
                        </span>
                        <?php if (!empty($event['event_time'])): ?>
                        <span class="meta-item">
                            <i class="fas fa-clock"></i> <?= date('g:i A', strtotime($event['event_time'])) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="clickable-item">
                <div class="item-image" style="background-image: url('default-event.jpg');"></div>
                <div class="item-date"><?= date('M j, Y') ?></div>
                <h4 class="item-title">No Upcoming Events</h4>
                <p class="item-desc">
                    There are no scheduled events at the moment. Please check back later for updates on upcoming activities.
                </p>
                <div class="item-meta">
                    <span class="meta-item">
                        <i class="fas fa-calendar-plus"></i> Check Back Soon
                    </span>
                </div>
            </div>
        <?php endif; ?>
        
        <a href="all_events.php" class="view-all-btn">
            <i class="fas fa-calendar-alt me-2"></i>View All Events
        </a>
    </div>
</div>
                    <!-- CENTER COLUMN - LATEST UPDATES -->
                    <div class="center-column">
                         <h3 class="column-title">Latest Updates</h3>
                        <?php if ($featured_update): ?>
                            <a href="view_update.php?id=<?= $featured_update['id'] ?>" class="main-update">
                                <?php if (!empty($featured_update['image_path'])): ?>
                                    <div class="update-image" style="background-image: url('images/<?= $featured_update['image_path'] ?>');"></div>
                                <?php else: ?>
                                    <div class="update-image" style="background-image: url('default-update.jpg');"></div>
                                <?php endif; ?>
                                <div class="update-content">
                                    <div class="update-date"><?= date('F j, Y', strtotime($featured_update['date_posted'])) ?></div>
                                    <h2 class="update-title"><?= htmlspecialchars($featured_update['title']) ?></h2>
                                    <p class="update-desc">
                                        <?php 
                                        $content = $featured_update['content'] ?? '';
                                        echo htmlspecialchars(substr($content, 0, 200));
                                        if (strlen($content) > 200) echo '...';
                                        ?>
                                    </p>
                                </div>
                            </a>
                        <?php else: ?>
                            <a href="#" class="main-update">
                                <div class="update-image" style="background-image: url('default-update.jpg');"></div>
                                <div class="update-content">
                                    <div class="update-date"><?= date('F j, Y') ?></div>
                                    <h2 class="update-title">Welcome to MSU Buug</h2>
                                    <p class="update-desc">Stay tuned for the latest updates and news from our campus. We're committed to providing quality education and building a strong community.</p>
                                </div>
                            </a>
                        <?php endif; ?>
                        
                        <div class="other-updates">
                            <?php if (!empty($other_updates)): ?>
                                <?php foreach($other_updates as $update): ?>
                                    <a href="view_update.php?id=<?= $update['id'] ?>" class="small-update">
                                        <?php if (!empty($update['image_path'])): ?>
                                            <div class="item-image" style="background-image: url('images/<?= $update['image_path'] ?>');"></div>
                                        <?php else: ?>
                                            <div class="item-image" style="background-image: url('default-update.jpg');"></div>
                                        <?php endif; ?>
                                        <div class="small-update-date"><?= date('M j, Y', strtotime($update['date_posted'])) ?></div>
                                        <h4 class="small-update-title"><?= htmlspecialchars($update['title']) ?></h4>
                                        <p>
                                            <?php 
                                            $content = $update['content'] ?? '';
                                            echo htmlspecialchars(substr($content, 0, 100));
                                            if (strlen($content) > 100) echo '...';
                                            ?>
                                        </p>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <a href="#" class="small-update">
                                    <div class="item-image" style="background-image: url('default-update.jpg');"></div>
                                    <div class="small-update-date"><?= date('M j, Y') ?></div>
                                    <h4 class="small-update-title">Campus Updates Coming Soon</h4>
                                    <p>We're working on bringing you the latest news and updates from MSU Buug.</p>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <a href="all_updates.php" class="view-all-btn">
                            <i class="fas fa-newspaper me-2"></i>View All Updates
                        </a>
                    </div>
                    
                    <!-- RIGHT COLUMN - ANNOUNCEMENTS -->
                    <div class="right-column">
                        <div class="column-card">
                            <h3 class="column-title">Announcements</h3>
                            
                            <?php if (!empty($announcements) && is_array($announcements)): ?>
                                <?php foreach($announcements as $announcement): ?>
                                    <?php if (is_array($announcement) && isset($announcement['title'])): ?>
                                        <a href="view_announcement.php?id=<?= $announcement['id'] ?? '1' ?>" class="clickable-item">
                                            <?php if (!empty($announcement['image_path'])): ?>
                                                <div class="item-image" style="background-image: url('images/<?= $announcement['image_path'] ?>');"></div>
                                            <?php else: ?>
                                                <div class="item-image" style="background-image: url('default-announcement.jpg');"></div>
                                            <?php endif; ?>
                                            <div class="item-date">
                                                <?= !empty($announcement['date_posted']) ? date('M j, Y', strtotime($announcement['date_posted'])) : 'Date not set' ?>
                                            </div>
                                            <h4 class="item-title"><?= htmlspecialchars($announcement['title'] ?? 'No title') ?></h4>
                                            <p class="item-desc">
                                                <?php 
                                                $content = $announcement['content'] ?? 'No content available';
                                                echo htmlspecialchars(substr($content, 0, 120));
                                                if (strlen($content) > 120) echo '...';
                                                ?>
                                            </p>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="clickable-item">
                                    <div class="item-image" style="background-image: url('default-announcement.jpg');"></div>
                                    <div class="item-date"><?= date('M j, Y') ?></div>
                                    <h4 class="item-title">Welcome to MSU Buug</h4>
                                    <p class="item-desc">Important announcements will be posted here. Please check back regularly for updates.</p>
                                </div>
                            <?php endif; ?>
                            
                            <a href="all_announcements.php" class="view-all-btn">
                                <i class="fas fa-bullhorn me-2"></i>View All Announcements
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="scroll-indicator" onclick="showSection('home')">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>
 <!-- BAGONG COLLEGES SECTION - ONE COLUMN HORIZONTAL, NO SCROLL -->
<section class="colleges-section">
    <div class="container-fluid px-0">
        <div class="row">
            <div class="col-12 text-center mb-3">
                <h2 class="colleges-title">Our Colleges</h2>
                <p class="colleges-subtitle">Connect with our academic departments</p>
            </div>
        </div>
        
        <!-- One Column Horizontal - All 9 colleges in a single horizontal line, no scroll -->
        <div class="colleges-horizontal-no-scroll">
            <a href="https://www.facebook.com/profile.php?id=100086391167152" target="_blank" class="college-item">
                <img src="images/cit.png" alt="College of Information Technology" class="college-img">
            </a>
            <a href="https://www.facebook.com/profile.php?id=100083083433140" target="_blank" class="college-item">
                <img src="images/agriculture.webp" alt="College of Agriculture" class="college-img">
            </a>
            <a href="https://www.facebook.com/profile.php?id=100067837181019" target="_blank" class="college-item">
                <img src="images/cas.webp" alt="College of Arts and Sciences" class="college-img">
            </a>
            <a href="https://www.facebook.com/MSUBCOED" target="_blank" class="college-item">
                <img src="images/education.png" alt="College of Education" class="college-img">
            </a>
            <a href="https://www.facebook.com/MSUBuugCPAOfficialPage" target="_blank" class="college-item">
                <img src="images/cpa.webp" alt="College of Public Affairs" class="college-img">
            </a>
            <a href="https://www.facebook.com/profile.php?id=61558061909066" target="_blank" class="college-item">
                <img src="images/hm.png" alt="College of Hospitality Management" class="college-img">
            </a>
            <a href="https://www.facebook.com/forenviMSUBuug" target="_blank" class="college-item">
                <img src="images/envi.webp" alt="College of Forestry & Environmental Studies" class="college-img">
            </a>
            <a href="https://www.facebook.com/profile.php?id=61581718049459" target="_blank" class="college-item">
                <img src="images/nursing.jpg" alt="College of Nursing" class="college-img">
            </a>
            <a href="https://www.facebook.com/cofmsubuug" target="_blank" class="college-item">
                <img src="images/fisheries.jpg" alt="College of Fisheries" class="college-img">
            </a>
        </div>
        
        <div class="row mt-3">
            <div class="col-12 text-center">
                <p class="colleges-note">Click on any college logo to visit their Facebook page</p>
            </div>
        </div>
    </div>
</section>

<!-- FULL WIDTH IMAGE SECTION -->
<section class="fullwidthsection">
    <div class="fullwidthcontainer">
        <a href="transparency.php" class="fullwidthcard">
            <div class="imagebox">
                <img src="transparencie.png" alt="Transparency">
            </div>
            <h3> Seal Transparency</h3>
           
        </a>
        
        <a href="dataprivacy.php" class="fullwidthcard">
            <div class="imagebox">
                <img src="dataprivacy.png" alt="Data Privacy">
            </div>
            <h3>Data Privacy</h3>
           
        </a>
        
        <a href="https://www.foi.gov.ph/" target='_blank'class="fullwidthcard">
            <div class="imagebox">
                <img src="electron_files/electronic.png" alt="Electronic Technology">
            </div>
            <h3>Electronic Freedom Of Information</h3>
           
        </a>
    </div>
</section>
  <!-- SUSTAINABLE DEVELOPMENT GOALS SECTION - ONE ROW HORIZONTAL -->
<section class="sustainable-section">
    <div class="container-fluid px-0">
        <!-- SDG Main Banner - No Gap -->
        <div class="row">
            <div class="col-12 text-center">
                <img src="images/sustainable_goals.png" alt="Sustainable Development Goals" class="sustainable-main-banner">
            </div>
        </div>

        <!-- SDG One Row Horizontal - All 17 SDGs in single line -->
        <div class="sdg-horizontal-no-scroll">
            <!-- SDG 1 -->
            <a href="sdg_detail.php?id=1" class="sdg-item">
                <img src="images/s1.png" alt="No Poverty" class="sdg-img">
            </a>
            
            <!-- SDG 2 -->
            <a href="sdg_detail.php?id=2" class="sdg-item">
                <img src="images/s2.png" alt="Zero Hunger" class="sdg-img">
            </a>
            
            <!-- SDG 3 -->
            <a href="sdg_detail.php?id=3" class="sdg-item">
                <img src="images/s3.png" alt="Good Health and Well-being" class="sdg-img">
            </a>
            
            <!-- SDG 4 -->
            <a href="sdg_detail.php?id=4" class="sdg-item">
                <img src="images/s4.png" alt="Quality Education" class="sdg-img">
            </a>
            
            <!-- SDG 5 -->
            <a href="sdg_detail.php?id=5" class="sdg-item">
                <img src="images/s5.png" alt="Gender Equality" class="sdg-img">
            </a>
            
            <!-- SDG 6 -->
            <a href="sdg_detail.php?id=6" class="sdg-item">
                <img src="images/s6.png" alt="Clean Water and Sanitation" class="sdg-img">
            </a>
            
            <!-- SDG 7 -->
            <a href="sdg_detail.php?id=7" class="sdg-item">
                <img src="images/s7.png" alt="Affordable and Clean Energy" class="sdg-img">
            </a>
            
            <!-- SDG 8 -->
            <a href="sdg_detail.php?id=8" class="sdg-item">
                <img src="images/s8.png" alt="Decent Work and Economic Growth" class="sdg-img">
            </a>
            
            <!-- SDG 9 -->
            <a href="sdg_detail.php?id=9" class="sdg-item">
                <img src="images/s9.png" alt="Industry, Innovation and Infrastructure" class="sdg-img">
            </a>
            
            <!-- SDG 10 -->
            <a href="sdg_detail.php?id=10" class="sdg-item">
                <img src="images/s10.png" alt="Reduced Inequality" class="sdg-img">
            </a>
            
            <!-- SDG 11 -->
            <a href="sdg_detail.php?id=11" class="sdg-item">
                <img src="images/s11.png" alt="Sustainable Cities and Communities" class="sdg-img">
            </a>
            
            <!-- SDG 12 -->
            <a href="sdg_detail.php?id=12" class="sdg-item">
                <img src="images/s12.png" alt="Responsible Consumption and Production" class="sdg-img">
            </a>
            
            <!-- SDG 13 -->
            <a href="sdg_detail.php?id=13" class="sdg-item">
                <img src="images/s13.png" alt="Climate Action" class="sdg-img">
            </a>
            
            <!-- SDG 14 -->
            <a href="sdg_detail.php?id=14" class="sdg-item">
                <img src="images/s14.png" alt="Life Below Water" class="sdg-img">
            </a>
            
            <!-- SDG 15 -->
            <a href="sdg_detail.php?id=15" class="sdg-item">
                <img src="images/s15.png" alt="Life on Land" class="sdg-img">
            </a>
            
            <!-- SDG 16 -->
            <a href="sdg_detail.php?id=16" class="sdg-item">
                <img src="images/s16.png" alt="Peace, Justice and Strong Institutions" class="sdg-img">
            </a>
            
            <!-- SDG 17 -->
            <a href="sdg_detail.php?id=17" class="sdg-item">
                <img src="images/s17.png" alt="Partnerships for the Goals" class="sdg-img">
            </a>
        </div>
    </div>
</section>
    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="footer-column-title">Contact Information</h5>
                    <div class="footer-contact-info">
                        <p><strong>MSU Buug Campus</strong></p>
                        <p>Datu Panas, Buug, Zamboanga Sibugay</p>
                        <p>Email: msubuug.campus@msubuug.edu.ph</p>
                        <p>Phone: (062) 955-123</p>
                        
                        <a href="https://www.facebook.com/msubuugcampus" class="fb-link" target="_blank">
                            <i class="fab fa-facebook fa-lg me-2"></i>
                            <span>Follow our Facebook Page</span>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="footer-column-title">MSU System Campuses</h5>
                    <ul class="systems-list">
                        <li><a href="https://www.msumain.edu.ph">MSU Main Campus - Marawi</a></li>
                        <li><a href="https://msuiit.edu.ph/">MSU-Iligan Institute of Technology</a></li>
                        <li><a href="https://msugensan.edu.ph/">MSU-General Santos</a></li>
                        <li><a href="https://msunaawan.edu.ph/">MSU-Naawan</a></li>
                        <li><a href="https://msumaguindanao.edu.ph/">MSU-Maguindanao</a></li>
                        <li><a href="https://msutcto.edu.ph/">MSU-Tawi-Tawi</a></li>
                        <li><a href="https://msusulu.edu.ph/">MSU-Sulu</a></li>
                        <li><a href="https://msubuug.edu.ph/">MSU-Buug</a></li>
                        <li><a href="https://www.msumain.edu.ph/">MSU-Lanao del Norte</a></li>
                        <li><a href="https://msumsat.edu.ph/">MSU-Maigo School of Arts and Trades</a></li>
                    </ul>
                </div>
                
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
        // Smooth hover effects for college logos
document.addEventListener('DOMContentLoaded', function() {
    const collegeItems = document.querySelectorAll('.college-item');
    
    collegeItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });
});
        // Carousel functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.indicator');
        const totalSlides = slides.length;

        function showSlide(n) {
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            currentSlide = n;
            
            slides[currentSlide].classList.add('active');
            indicators[currentSlide].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(currentSlide);
        }

        function goToSlide(n) {
            showSlide(n);
        }

        // Auto slide every 5 seconds
        setInterval(nextSlide, 5000);

        // Section management
        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            
            document.getElementById(sectionId).classList.add('active');
            
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Set active nav link
            if (event && event.target) {
                event.target.classList.add('active');
            }
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Scroll to sustainable section
        function scrollToSustainable() {
            document.querySelector('.sustainable-section').scrollIntoView({ 
                behavior: 'smooth' 
            });
        }

        // Initialize
        showSlide(0);
        
        // Show landing page by default
        document.addEventListener('DOMContentLoaded', function() {
            showSection('landing');
        });
    </script>
</body>
</html>