<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transparency Seal - Mindanao State University</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f8f9fa;
        }

        /* TOP BAR */
        .topbar {
            background: #8B0000;
            color: white;
            padding: 15px 0;
        }

        .topbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .university-name {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .top-nav {
            display: flex;
            gap: 30px;
        }

        .top-nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }

        .top-nav a:hover {
            color: #FFD700;
        }

        /* MAIN CONTENT */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* TRANSPARENCY IMAGE */
        .transparency-image {
            text-align: center;
            margin: 20px 0;
        }

        .transparency-image img {
            max-width: 300px;
            height: auto;
        }

        /* SCHOOLS NAVIGATION */
        .schools-nav {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .nav-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .nav-row a {
            color: #8B0000;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #8B0000;
            border-radius: 3px;
            font-size: 0.9rem;
        }

        .nav-row a:hover {
            background: #8B0000;
            color: white;
        }

        /* CONTENT SECTIONS */
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 5px;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .section-title {
            color: #8B0000;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #8B0000;
        }

        .content-list {
            list-style: none;
        }

        .content-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .content-list a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 5px 0;
        }

        .content-list a:hover {
            color: #8B0000;
            background: #f8f9fa;
            padding-left: 10px;
        }

        .year-section {
            margin: 15px 0;
            padding-left: 20px;
        }

        .year-title {
            color: #8B0000;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .topbar-container {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            .top-nav {
                gap: 15px;
            }

            .nav-row {
                gap: 10px;
            }

            .nav-row a {
                font-size: 0.8rem;
                padding: 4px 8px;
            }

            .container {
                padding: 10px;
            }

            .content-section {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .university-name {
                font-size: 1.2rem;
            }

            .top-nav {
                flex-direction: column;
                gap: 5px;
            }

            .transparency-image img {
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- TOP BAR -->
    <div class="topbar">
        <div class="topbar-container">
            <div class="university-name">Mindanao State University</div>
            <div class="top-nav">
                <a href="#msu-system">MSU System</a>
                <a href="#transparency">Transparency</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- TRANSPARENCY IMAGE -->
        <div class="transparency-image">
            <img src="transparencie.png" alt="Transparency Seal">
        </div>

        <!-- SCHOOLS NAVIGATION -->
        <div class="schools-nav">
            <div class="nav-row">
                <a href="#msu-marawi">MSU-Marawi</a>
                <a href="#msu-iit">MSU-IIT</a>
                <a href="#msu-gensan">MSU-Gensan</a>
                <a href="#msu-naawan">MSU-Naawan</a>
                <a href="#msu-maguindanao">MSU-Maguindanao</a>
                <a href="#msu-tawi">MSU-Tawi-Tawi</a>
                <a href="#msu-sulu">MSU-Sulu</a>
                <a href="#msu-buug">MSU-Buug</a>
                <a href="#msu-lanao">MSU-Lanao del Norte</a>
                <a href="#msu-maigo">MSU-Maigo</a>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="content-section">
            <h2 class="section-title">Agency Information</h2>
            <ul class="content-list">
                <li><a href="#mandates">The agency's mandates and functions, names of its officials with their positions and designations, and contact information</a></li>
                <li><a href="#msu-marawi-mandate">MSU-Marawi Mandate and Functions</a></li>
                <li><a href="#msu-charter">MSU Charter</a></li>
                <li><a href="#mission-vision">Mission, Vision and Objectives</a></li>
                <li><a href="#mandate-reform">Mandate and Reform Agenda</a></li>
            </ul>
        </div>

        <div class="content-section">
            <h2 class="section-title">Directory of Key Officials</h2>
            <ul class="content-list">
                <li><a href="#board-regents">Board of Regents</a></li>
                <li><a href="#msu-officials">MSU System Officials</a></li>
                <li><a href="#chancellors">Chancellors of MSU Campuses and Heads of Integrated MSU Campuses (CSI)</a></li>
                <li><a href="#academic-units">Academic Units of Main Campus</a></li>
                <li><a href="#semi-academic">Semi-Academic Units of Main Campus</a></li>
                <li><a href="#administrative">Administrative Units of Main Campus</a></li>
                <li><a href="#research">Research and Extension Services of Main Campus</a></li>
                <li><a href="#contact">Contact Directory</a></li>
            </ul>
        </div>

        <div class="content-section">
            <h2 class="section-title">Annual Financial Reports</h2>
            
            <div class="year-section">
                <div class="year-title">2016</div>
                <ul class="content-list">
                    <li><a href="#2016-far1">FAR No. 1: SAAOBDB (Statement of Appropriations, Allotments, Obligations, Disbursements and Balances)</a></li>
                    <li><a href="#2016-summary">Summary Report of Disbursements</a></li>
                    <li><a href="#2016-bar1">BAR No. 1: Quarterly Physical Report of Operations/Physical Plan</a></li>
                    <li><a href="#2016-far5">FAR No. 5: Quarterly Report on Revenue and Other Receipts</a></li>
                    <li><a href="#2016-financial">Financial Plan (Detailed Statement of Current Year's Obligations, Disbursements and Unpaid Obligations)</a></li>
                </ul>
            </div>

            <div class="year-section">
                <div class="year-title">2015</div>
                <ul class="content-list">
                    <li><a href="#2015-far1">FAR No. 1: SAAOBDB (Statement of Appropriations, Allotments, Obligations, Disbursements and Balances)</a></li>
                    <li><a href="#2015-summary">Summary Report of Disbursements</a></li>
                    <li><a href="#2015-bar1">BAR No. 1: Quarterly Physical Report of Operations/Physical Plan</a></li>
                    <li><a href="#2015-far5">FAR No. 5: Quarterly Report on Revenue and Other Receipts</a></li>
                    <li><a href="#2015-financial">Financial Plan (Detailed Statement of Current Year's Obligations, Disbursements and Unpaid Obligations)</a></li>
                </ul>
            </div>

            <div class="year-section">
                <div class="year-title">2014</div>
                <ul class="content-list">
                    <li><a href="#2014-far1">FAR No. 1 - Statement of Appropriations, Allotments,Obligations,Disbursements and Balances</a></li>
                    <li><a href="#2014-far1a">FAR No. 1A - Summary of Prior Year's Obligations and Unpaid Prior Year's Obligations</a></li>
                    <li><a href="#2014-far1b">FAR No. 1B - List of ABM/SAROs/Sub-Allotment Release Orders</a></li>
                    <li><a href="#2014-far2">FAR No. 2 - Statement of Approved Budget, Utilization, Disbursements and Balances</a></li>
                    <li><a href="#2014-far2a">FAR No. 2A - Summary of Approved Budget, Utilizations, Disbursements and Balances by Object of Expenditures</a></li>
                    <li><a href="#2014-far3">FAR No. 3 - Aging of Due and Demandable Obligations</a></li>
                    <li><a href="#2014-far4">FAR No. 4 - Monthly Report of Disbursements</a></li>
                    <li><a href="#2014-far5">FAR No. 5 - Quarterly Report of Revenue and other Receipts</a></li>
                </ul>
            </div>

            <div class="year-section">
                <div class="year-title">2013</div>
                <ul class="content-list">
                    <li><a href="#2013-annexa">Annex A Statement of Appropriations, Allotments, Obligations, Disbursements and Balances</a></li>
                    <li><a href="#2013-annexa1">Annex A-1 List of Agency Budget Matrix Special Allotment Release Orders, Sub-Allotment Release Orders</a></li>
                    <li><a href="#2013-annexb">Annex B Detailed Statement of Current Year's Obligations Disbursement and Unpaid Obligations</a></li>
                    <li><a href="#2013-annexc">Annex C Summary of Prior Year's Obligations, Disbursements and Unpaid Prior Year Obligations</a></li>
                    <li><a href="#2013-annexd">Annex D Summary Report of Disbursements</a></li>
                </ul>
            </div>
        </div>

        <div class="content-section">
            <h2 class="section-title">Other Documents</h2>
            <ul class="content-list">
                <li><a href="#dbm-budget">DBM Approved Budget and Targets</a></li>
                <li><a href="#budget-2016">Budget 2016</a></li>
                <li><a href="#mfo-2016">MFO Targets 2016</a></li>
                <li><a href="#projects">Projects, Programs and Activities, Beneficiaries, and Status of Implementation</a></li>
                <li><a href="#procurement">Annual Procurement Plans 2016</a></li>
                <li><a href="#ranking">System of Ranking Delivery Units</a></li>
                <li><a href="#operations">Agency Operations Manual</a></li>
                <li><a href="#faculty">Manual on Faculty Recruitment</a></li>
            </ul>
        </div>
    </div>
</body>
</html>