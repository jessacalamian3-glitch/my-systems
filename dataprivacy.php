<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Privacy Act - Mindanao State University Buug</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f8f9fa;
            line-height: 1.6;
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

        /* PRIVACY IMAGE */
        .privacy-image {
            text-align: center;
            margin: 20px 0;
        }

        .privacy-image img {
            max-width: 300px;
            height: auto;
        }

        /* CONTENT SECTIONS */
        .content-section {
            background: white;
            padding: 30px;
            border-radius: 5px;
            margin: 25px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .section-title {
            color: #8B0000;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #8B0000;
            font-size: 1.8rem;
        }

        .subsection-title {
            color: #8B0000;
            margin: 25px 0 15px 0;
            font-size: 1.3rem;
        }

        .content-text {
            margin-bottom: 15px;
            text-align: justify;
        }

        .highlight-box {
            background: #f8f9fa;
            border-left: 4px solid #8B0000;
            padding: 15px 20px;
            margin: 20px 0;
        }

        .data-list {
            list-style: none;
            margin: 15px 0;
        }

        .data-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            padding-left: 20px;
            position: relative;
        }

        .data-list li:before {
            content: "•";
            color: #8B0000;
            font-weight: bold;
            position: absolute;
            left: 0;
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

            .container {
                padding: 10px;
            }

            .content-section {
                padding: 20px;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .subsection-title {
                font-size: 1.2rem;
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

            .privacy-image img {
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- TOP BAR -->
    <div class="topbar">
        <div class="topbar-container">
            <div class="university-name">Mindanao State University - Buug</div>
            <div class="top-nav">
                <a href="#privacy-policy">Privacy Policy</a>
                <a href="#data-protection">Data Protection</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- PRIVACY IMAGE -->
        <div class="privacy-image">
            <img src="dataprivacy.png" alt="Data Privacy Act">
        </div>

        <!-- PRIVACY POLICY SECTION -->
        <div class="content-section">
            <h1 class="section-title">Data Privacy Act</h1>
            
            <div class="content-text">
                <h2 class="subsection-title">Privacy Policy</h2>
                <p>The Mindanao State University – Buug (MSU – Buug) is committed to uphold and protect your personal data privacy. We are at the forefront of not only implementing but also complying with the Republic Act 10173, also known as the "Data Privacy Act of 2012".</p>
            </div>

            <div class="highlight-box">
                <p>We will provide individuals a Personal Information Collection Statement in an appropriate format whenever we obtain personal data from them (i.e., in the manual form or web page that collects personal data).</p>
            </div>
        </div>

        <!-- STATEMENT OF POLICY -->
        <div class="content-section">
            <h2 class="subsection-title">Statement of Policy</h2>
            <div class="content-text">
                <p>The Mindanao State University – Buug (MSU – Buug) is committed to uphold and protect your personal data privacy. We are at the forefront of not only implementing but also complying with the Republic Act 10173, also known as the "Data Privacy Act of 2012".</p>
            </div>
        </div>

        <!-- PRIVACY NOTICE -->
        <div class="content-section">
            <h2 class="subsection-title">Privacy Notice of MSU – Buug for Processing Inquiries and/or Requests</h2>
            
            <div class="content-text">
                <h3 class="subsection-title">Personal Data Collected</h3>
                <p>We collect the following personal information from you when you manually or electronically submit to us your inquiries and/or requests:</p>
                
                <ul class="data-list">
                    <li>Name</li>
                    <li>Contact information</li>
                    <li>Email address</li>
                    <li>Contact Number</li>
                    <li>College/Office</li>
                </ul>
            </div>

            <div class="content-text">
                <p>The University uses WordPress Statistics, a third-party service, to analyze the web traffic data for us. This service uses cookies. Rest assured that the data generated is not shared with any other party. The following web traffic data are analyzed:</p>
                
                <ul class="data-list">
                    <li>Your IP addresses</li>
                    <li>The search terms you used</li>
                    <li>The pages and internal links accessed on our webpage</li>
                    <li>The date and time you visited the webpage</li>
                    <li>Geolocation</li>
                    <li>Your operating system</li>
                    <li>Web browser type</li>
                </ul>
            </div>

            <div class="content-text">
                <h3 class="subsection-title">Use</h3>
                <p>The collected personal information is utilized solely for quality assurance, documentation, and processing purposes within the University and is not shared with any external parties. They assist the University to properly address the inquiries, concerns and requests, forward them to appropriate internal academic, semi-academic and administrative offices for action and feedback, and provide the students and faculty members with relevant updates and advisories in a legitimate format and in an orderly and timely approach.</p>
            </div>

            <div class="content-text">
                <h3 class="subsection-title">Protection Measures</h3>
                <p>Only authorized personnel of the University have access to these personal information, the exchange of which will be facilitated through email and/or hard copy. The collected personal information will be stored in a database for five (5) years (after inquiries and/or requests are acted upon) after which physical records shall be disposed through shredding, while digital files shall be anonymized to ensure maximum data security and protection.</p>
            </div>

            <div class="content-text">
                <h3 class="subsection-title">Access and Correction</h3>
                <p>You have the right to ask for a copy of any personal information we hold about you, as well as to ask for it to be altered if you think it is incorrect.</p>
            </div>
        </div>

        <!-- CONTACT INFORMATION -->
        <div class="content-section">
            <h2 class="subsection-title">Contact Information</h2>
            <div class="content-text">
                <p>For any concerns regarding data privacy, please contact our Data Protection Officer:</p>
                <div class="highlight-box">
                    <p><strong>Data Protection Officer</strong><br>
                    Mindanao State University - Buug<br>
                    Datu Panas, Buug, Zamboanga Sibugay<br>
                    Email: dpo@msubuug.edu.ph<br>
                    Phone: (062) 000-0000</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>