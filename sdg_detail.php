<?php
// sdg_detail.php - ENHANCED VERSION
$sdg_number = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Comprehensive SDG Data
$sdg_data = [
    1 => [
        'title' => 'No Poverty',
        'description' => 'End poverty in all its forms everywhere',
        'full_description' => 'Poverty is more than the lack of income and resources to ensure a sustainable livelihood. Its manifestations include hunger and malnutrition, limited access to education and other basic services, social discrimination and exclusion, and the lack of participation in decision-making.',
        'targets' => [
            'Eradicate extreme poverty for all people everywhere',
            'Reduce at least by half the proportion of men, women and children living in poverty',
            'Implement social protection systems and measures for all',
            'Ensure equal rights to economic resources and basic services'
        ],
        'initiatives' => [
            'Scholarship programs for underprivileged students',
            'Community outreach and livelihood training',
            'Research on poverty alleviation in local communities'
        ]
    ],
    2 => [
        'title' => 'Zero Hunger',
        'description' => 'End hunger, achieve food security and improved nutrition and promote sustainable agriculture',
        'full_description' => 'This goal aims to end hunger and all forms of malnutrition by 2030. It also aims to double agricultural productivity and incomes of small-scale food producers, in particular women, indigenous peoples, family farmers, pastoralists and fishers.',
        'targets' => [
            'End hunger and ensure access to safe, nutritious and sufficient food',
            'End all forms of malnutrition',
            'Double agricultural productivity and incomes of small-scale food producers',
            'Ensure sustainable food production systems'
        ],
        'initiatives' => [
            'Agricultural research and development programs',
            'Campus vegetable garden and farming projects',
            'Nutrition education and feeding programs'
        ]
    ],
    3 => [
        'title' => 'Good Health and Well-being',
        'description' => 'Ensure healthy lives and promote well-being for all at all ages',
        'full_description' => 'This goal addresses all major health priorities, including reproductive, maternal and child health; communicable, non-communicable and environmental diseases; universal health coverage; and access for all to safe, effective, quality and affordable medicines and vaccines.',
        'targets' => [
            'Reduce maternal mortality and end preventable deaths of newborns and children',
            'End epidemics of AIDS, tuberculosis, malaria and other communicable diseases',
            'Reduce premature mortality from non-communicable diseases',
            'Strengthen prevention and treatment of substance abuse'
        ],
        'initiatives' => [
            'Campus health and wellness programs',
            'Mental health awareness campaigns',
            'Community medical missions'
        ]
    ],
    // Continue similar structure for other SDGs...
    17 => [
        'title' => 'Partnerships for the Goals',
        'description' => 'Strengthen the means of implementation and revitalize the global partnership for sustainable development',
        'full_description' => 'This goal seeks to strengthen global partnerships to support and achieve the ambitious targets of the 2030 Agenda, bringing together national governments, the international community, civil society, the private sector and other actors.',
        'targets' => [
            'Strengthen domestic resource mobilization',
            'Developed countries to implement official development assistance commitments',
            'Mobilize additional financial resources for developing countries',
            'Enhance global macroeconomic stability'
        ],
        'initiatives' => [
            'International academic partnerships and exchanges',
            'Collaborative research projects with other universities',
            'Industry-academe linkage programs'
        ]
    ]
];

// Get current SDG data or default data
$current_sdg = isset($sdg_data[$sdg_number]) ? $sdg_data[$sdg_number] : [
    'title' => 'Sustainable Development Goal ' . $sdg_number,
    'description' => 'Description for SDG ' . $sdg_number,
    'full_description' => 'Detailed information about this Sustainable Development Goal will be added soon.',
    'targets' => ['Target 1', 'Target 2', 'Target 3'],
    'initiatives' => ['Initiative 1', 'Initiative 2']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDG <?php echo $sdg_number; ?> - <?php echo $current_sdg['title']; ?> | MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --msu-green: #8B0000;
            --msu-gold: #FFD700;
            --msu-light: #A52A2A;
            --msu-dark: #600000;
            --msu-cream: #FFF8E1;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding-top: 70px;
        }
        
        .sdg-hero {
            background: linear-gradient(135deg, var(--msu-green) 0%, var(--msu-dark) 100%);
            color: white;
            padding: 80px 0 50px 0;
            position: relative;
            overflow: hidden;
        }
        
        .sdg-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><circle cx="50" cy="50" r="2" fill="white"/></svg>') repeat;
            background-size: 50px 50px;
        }
        
        .sdg-icon-container {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            border: 5px solid var(--msu-gold);
            transition: transform 0.3s ease;
        }
        
        .sdg-icon-container:hover {
            transform: scale(1.05) rotate(2deg);
        }
        
        .sdg-content-section {
            padding: 60px 0;
        }
        
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-left: 5px solid var(--msu-green);
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .section-title {
            color: var(--msu-green);
            font-weight: 800;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--msu-gold);
            border-radius: 2px;
        }
        
        .target-item, .initiative-item {
            background: var(--msu-cream);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--msu-green);
            transition: all 0.3s ease;
        }
        
        .target-item:hover, .initiative-item:hover {
            background: white;
            transform: translateX(10px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .target-number, .initiative-number {
            background: var(--msu-green);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .sdg-sidebar {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
            border: 3px solid var(--msu-cream);
        }
        
        .sidebar-title {
            color: var(--msu-green);
            font-weight: 800;
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--msu-gold);
        }
        
        .sdg-nav-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            background: #f8f9fa;
        }
        
        .sdg-nav-item:hover {
            background: var(--msu-green);
            color: white;
            transform: translateX(8px);
            text-decoration: none;
            border-color: var(--msu-gold);
        }
        
        .sdg-nav-item.active {
            background: var(--msu-green);
            color: white;
            border-color: var(--msu-gold);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }
        
        .sdg-nav-icon {
            width: 50px;
            height: 50px;
            margin-right: 15px;
            border-radius: 10px;
            object-fit: contain;
            background: white;
            padding: 5px;
            border: 2px solid #dee2e6;
        }
        
        .sdg-nav-item.active .sdg-nav-icon {
            border-color: var(--msu-gold);
            background: var(--msu-cream);
        }
        
        .back-btn {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-dark));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            color: var(--msu-gold);
            box-shadow: 0 8px 25px rgba(139, 0, 0, 0.4);
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(139, 0, 0, 0.2);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 5px;
            color: var(--msu-gold);
        }
        
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .progress-bar {
            background: var(--msu-gold);
        }
        
        @media (max-width: 768px) {
            .sdg-hero {
                padding: 60px 0 30px 0;
                text-align: center;
            }
            
            .content-card {
                padding: 25px;
            }
            
            .sdg-icon-container {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: linear-gradient(135deg, var(--msu-green), var(--msu-dark));">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-university me-2"></i>
                MSU Buug - SDG <?php echo $sdg_number; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="btn btn-outline-light">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="sdg-hero">
        <div class="container">
            <a href="index.php" class="back-btn mb-4">
                <i class="fas fa-arrow-left me-2"></i>Back to All SDGs
            </a>
            
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-4 text-center mb-4 mb-md-0">
                    <div class="sdg-icon-container">
                        <img src="images/s<?php echo $sdg_number; ?>.png" 
                             alt="<?php echo $current_sdg['title']; ?>" 
                             class="img-fluid" style="max-height: 180px;">
                    </div>
                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="text-center text-md-start">
                        <span class="badge bg-warning text-dark fs-6 mb-3">Sustainable Development Goal <?php echo $sdg_number; ?></span>
                        <h1 class="display-4 fw-bold mb-3"><?php echo $current_sdg['title']; ?></h1>
                        <p class="lead mb-4 opacity-90"><?php echo $current_sdg['description']; ?></p>
                        <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo count($current_sdg['targets']); ?></div>
                                <div class="stats-label">Key Targets</div>
                            </div>
                            <div class="stats-card">
                                <div class="stats-number"><?php echo count($current_sdg['initiatives']); ?></div>
                                <div class="stats-label">MSU Initiatives</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="sdg-content-section">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8 mb-4">
                    <!-- About Section -->
                    <div class="content-card">
                        <h2 class="section-title">
                            <i class="fas fa-info-circle me-3"></i>About This Goal
                        </h2>
                        <p class="fs-5 text-muted mb-4"><?php echo $current_sdg['full_description']; ?></p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5 class="fw-bold text-success mb-3">
                                    <i class="fas fa-bullseye me-2"></i>Global Impact
                                </h5>
                                <p>This goal addresses critical global challenges and contributes to building a more sustainable and equitable world for future generations.</p>
                            </div>
                            <div class="col-md-6">
                                <h5 class="fw-bold text-success mb-3">
                                    <i class="fas fa-handshake me-2"></i>Collaboration
                                </h5>
                                <p>Achieving this goal requires partnerships between governments, private sector, civil society, and academic institutions like MSU Buug.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Key Targets Section -->
                    <div class="content-card">
                        <h2 class="section-title">
                            <i class="fas fa-tasks me-3"></i>Key Targets
                        </h2>
                        <p class="text-muted mb-4">The following are the specific targets set by the United Nations for this Sustainable Development Goal:</p>
                        
                        <?php foreach ($current_sdg['targets'] as $index => $target): ?>
                            <div class="target-item">
                                <div class="d-flex align-items-start">
                                    <span class="target-number"><?php echo $index + 1; ?></span>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-2"><?php echo $target; ?></h5>
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo rand(60, 90); ?>%"></div>
                                        </div>
                                        <small class="text-muted">Global progress towards this target</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- MSU Buug Initiatives Section -->
                    <div class="content-card">
                        <h2 class="section-title">
                            <i class="fas fa-seedling me-3"></i>MSU Buug Initiatives
                        </h2>
                        <p class="text-muted mb-4">Mindanao State University - Buug Campus is actively contributing to this goal through various programs and initiatives:</p>
                        
                        <?php foreach ($current_sdg['initiatives'] as $index => $initiative): ?>
                            <div class="initiative-item">
                                <div class="d-flex align-items-start">
                                    <span class="initiative-number"><?php echo $index + 1; ?></span>
                                    <div>
                                        <h5 class="fw-bold mb-2 text-success"><?php echo $initiative; ?></h5>
                                        <p class="mb-0 text-muted">This initiative demonstrates MSU Buug's commitment to sustainable development and community engagement.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-4 p-4 bg-light rounded">
                            <h5 class="fw-bold text-success">
                                <i class="fas fa-lightbulb me-2"></i>Get Involved
                            </h5>
                            <p class="mb-0">Students, faculty, and staff can participate in these initiatives through research projects, community service, and academic programs.</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sdg-sidebar">
                        <h3 class="sidebar-title">
                            <i class="fas fa-compass me-2"></i>All SDGs
                        </h3>
                        
                        <?php for ($i = 1; $i <= 17; $i++): ?>
                            <?php 
                            $sdg_title = isset($sdg_data[$i]['title']) ? $sdg_data[$i]['title'] : "SDG $i";
                            $activeClass = $i == $sdg_number ? 'active' : '';
                            ?>
                            <a href="sdg_detail.php?id=<?php echo $i; ?>" class="sdg-nav-item <?php echo $activeClass; ?>">
                                <img src="images/s<?php echo $i; ?>.png" 
                                     alt="<?php echo $sdg_title; ?>" 
                                     class="sdg-nav-icon">
                                <div>
                                    <strong>SDG <?php echo $i; ?></strong><br>
                                    <small><?php echo $sdg_title; ?></small>
                                </div>
                            </a>
                        <?php endfor; ?>
                        
                        <div class="mt-4 p-3 bg-warning rounded text-center">
                            <h6 class="fw-bold mb-2">Learn More About SDGs</h6>
                            <a href="https://sdgs.un.org/goals" target="_blank" class="btn btn-sm btn-success">
                                <i class="fas fa-external-link-alt me-1"></i>UN SDGs Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h5 class="fw-bold mb-3">Mindanao State University - Buug Campus</h5>
                    <p class="mb-4">Committed to excellence, sustainability, and community development through education and research.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <p class="mb-1">Datu Panas, Buug, Zamboanga Sibugay</p>
                    <p class="mb-1">Email: info@msubuug.edu.ph</p>
                    <p class="mb-0">Phone: (062) 000-0000</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 Mindanao State University - Buug Campus. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>