<?php
// dynamic_sections.php - WITH DYNAMIC TITLE FROM DATABASE
require_once 'config/database.php';

// Kunin ang data mula sa database
$featured_update = $pdo->query("SELECT * FROM latest_updates WHERE is_featured = 1 LIMIT 1")->fetch();

// Kung walang featured, kunin ang pinakabago
if (!$featured_update) {
    $featured_update = $pdo->query("SELECT * FROM latest_updates ORDER BY date_posted DESC LIMIT 1")->fetch();
}

$other_updates = $pdo->query("SELECT * FROM latest_updates WHERE id != " . ($featured_update['id'] ?? 0) . " LIMIT 2")->fetchAll();
$events = $pdo->query("SELECT * FROM upcoming_events LIMIT 3")->fetchAll();
$announcements = $pdo->query("SELECT * FROM announcements LIMIT 3")->fetchAll();
?>

<style>
.hero-three-column { 
    display: grid; 
    grid-template-columns: 1.5fr 2fr 1.5fr; /* MAS MALAPAD NA SIDE COLUMNS */
    gap: 1rem; /* MAS MALIIT NA GAP */
    width: 100%; 
    margin: 0; 
    padding: 0;
}

.simple-section { 
    padding: 2rem 0; 
    background: #f8f9fa; 
    width: 100%;
    margin: 0;
}

/* MAS MALALAKI NA SIDE COLUMNS */
.left-column, .right-column {
    background: #fff;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-height: 600px; /* MAS MATAGAS NA HEIGHT */
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.center-column {
    background: #fff;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.events-header, .featured-header, .announcements-header {
    background: #8B0000; 
    color: white; 
    padding: 1.2rem; /* MAS MALAKING PADDING */
    border-radius: 8px; 
    text-align: center; 
    margin-bottom: 1.5rem;
    font-weight: bold;
    font-size: 1.3rem; /* MAS MALAKING FONT */
}

.event-card, .featured-top, .announcement-card, .bottom-card {
    background: white; 
    border-radius: 8px; 
    padding: 1.5rem; 
    margin-bottom: 1.5rem; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 6px solid #FFD700;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

/* MAS MALALAKI NA IMAGES FOR SIDE COLUMNS */
.event-image-container {
    width: 100%; 
    height: 180px; /* MAS MATAAS */
    background: #f0f0f0; 
    margin-bottom: 1rem; 
    border-radius: 8px; 
    overflow: hidden;
}

.featured-image-container {
    width: 100%; 
    height: 350px;
    background: #f0f0f0; 
    margin-bottom: 1rem; 
    border-radius: 8px; 
    overflow: hidden;
}

.bottom-image-container {
    width: 100%; 
    height: 180px; 
    background: #f0f0f0; 
    margin-bottom: 1rem; 
    border-radius: 8px; 
    overflow: hidden;
}

.featured-image-container img, 
.bottom-image-container img,
.event-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.featured-top h2 { 
    color: #8B0000; 
    margin: 1rem 0 0.5rem 0; 
    font-size: 2rem; 
    font-weight: bold;
    line-height: 1.2;
    text-align: center;
}

/* MAS MALALAKI NA FONT FOR SIDE CARDS */
.event-card h4, .announcement-card h4 {
    color: #333; 
    margin: 0.5rem 0; 
    font-size: 1.3rem; /* MAS MALAKI */
    font-weight: 600;
    line-height: 1.3;
}

.bottom-card h4 {
    color: #333; 
    margin: 0.5rem 0; 
    font-size: 1.1rem;
    font-weight: 600;
}

.featured-date, .bottom-date, .event-date, .announcement-date {
    color: #666; 
    font-size: 1rem; /* MAS MALAKI */
    margin-bottom: 0.5rem;
    font-weight: 500;
    text-align: center;
}

.featured-read-more, .bottom-read-more, .event-button, .announcement-link {
    background: #8B0000; 
    color: white; 
    padding: 0.8rem 1.8rem; /* MAS MALAKI */
    text-decoration: none; 
    border-radius: 4px; 
    display: inline-block;
    margin-top: 0.5rem; 
    transition: background 0.3s ease;
    font-weight: 600;
    font-size: 1rem; /* MAS MALAKI */
}
.featured-read-more:hover, .bottom-read-more:hover, .event-button:hover, .announcement-link:hover {
    background: #a00;
}

.empty-state { 
    text-align: center; 
    padding: 2rem; 
    color: #666; 
    background: white; 
    border-radius: 8px; 
    border: 1px dashed #ccc;
    font-size: 1.1rem; /* MAS MALAKI */
}

.bottom-updates-grid { 
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 0.5rem;
    margin-top: 0.5rem; 
}

@media (max-width: 768px) { 
    .bottom-updates-grid { 
        grid-template-columns: 1fr; 
        gap: 0.5rem;
    } 
}

/* INALIS NA ANG .event-location CSS KASI TANGGAL NA ANG LOCATION */

.featured-content {
    text-align: center;
    padding: 0 1rem;
}

.see-more-button {
    background: #8B0000; 
    color: white; 
    padding: 1rem 2.5rem; 
    text-decoration: none; 
    border-radius: 6px; 
    display: inline-block;
    font-weight: 600;
    font-size: 1.1rem;
    border: 2px solid #FFD700;
    transition: all 0.3s ease;
}

.see-more-button:hover {
    background: #a00;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 0, 0, 0.3);
}

.featured-top {
    margin-bottom: 0.5rem;
}

/* MAS DRAMATIC NA HOVER EFFECTS */
.event-card:hover, .featured-top:hover, .announcement-card:hover, .bottom-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    border-color: #FFC107;
}

.left-column:hover, .center-column:hover, .right-column:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

/* MAS MALALAKI NA ANNOUNCEMENT CARDS */
.announcement-card {
    min-height: 120px; /* MAS MATAGAS NA HEIGHT */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.announcement-card p {
    font-size: 1rem; /* MAS MALAKI */
    line-height: 1.4;
    margin: 0.5rem 0;
    flex-grow: 1;
}

/* RESPONSIVE FOR MOBILE */
@media (max-width: 768px) {
    .hero-three-column {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .left-column, .center-column, .right-column {
        min-height: auto;
    }
    
    .events-header, .featured-header, .announcements-header {
        font-size: 1.1rem;
        padding: 1rem;
    }
}
</style>

<div class="simple-section">
    <div class="hero-three-column">
        
       <!-- LEFT - EVENTS -->
<div class="left-column">
    <div class="events-header"><h3>Upcoming Events</h3></div>
    <?php if (!empty($events)): ?>
        <?php foreach($events as $event): ?>
        <div class="event-card">
            <?php if (!empty($event['image_path'])): ?>
            <div class="event-image-container">
                <img src="images/<?= $event['image_path'] ?>" alt="<?= htmlspecialchars($event['title']) ?>">
            </div>
            <?php endif; ?>
            <div class="event-date">📅 <?= date('M j, Y', strtotime($event['event_date'])) ?></div>
            <h4><?= htmlspecialchars($event['title']) ?></h4>
            <!-- INALIS NA ANG LOCATION LINE -->
            <a href="view_event.php?id=<?= $event['id'] ?>" class="event-button">View Details</a>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">No upcoming events at the moment</div>
    <?php endif; ?>
</div>
        
       <!-- CENTER - LATEST UPDATES -->
<div class="center-column">
    <div class="featured-header"><h3>Latest Updates</h3></div>
    
    <div class="featured-top">
        <?php if ($featured_update): ?>
            <div class="featured-image-container">
                <?php if (!empty($featured_update['image_path'])): ?>
                    <img src="images/<?= $featured_update['image_path'] ?>" alt="Featured Update">
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999; background: #f8f9fa;">
                        <div style="text-align: center;">
                            <div style="font-size: 4rem;">🏫</div>
                            <div style="font-size: 1.2rem; margin-top: 1rem;">MSU Buug Campus</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="featured-content">
                <div class="featured-date">📅 <?= date('F j, Y', strtotime($featured_update['date_posted'])) ?></div>
                <h2><?= htmlspecialchars($featured_update['title']) ?></h2>
                <a href="view_update.php?id=<?= $featured_update['id'] ?>" class="featured-read-more">Read Full Story →</a>
            </div>
        <?php else: ?>
            <div class="featured-image-container">
                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999; background: #f8f9fa;">
                    <div style="text-align: center;">
                        <div style="font-size: 4rem;">🎯</div>
                        <div style="font-size: 1.2rem; margin-top: 1rem;">Featured Image</div>
                    </div>
                </div>
            </div>
            <div class="featured-content">
                <div class="featured-date">📅 Latest Update</div>
                <h2>No Featured Updates</h2>
                <a href="#" class="featured-read-more">Read Full Story →</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="bottom-updates-grid">
        <?php if (!empty($other_updates)): ?>
            <?php foreach($other_updates as $update): ?>
            <div class="bottom-card">
                <div class="bottom-image-container">
                    <?php if (!empty($update['image_path'])): ?>
                        <img src="images/<?= $update['image_path'] ?>" alt="Update Image">
                    <?php else: ?>
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999; background: #f8f9fa;">
                            <div style="text-align: center;">
                                <div style="font-size: 2rem;">📰</div>
                                <div>Campus News</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="bottom-date">📅 <?= date('M j, Y', strtotime($update['date_posted'])) ?></div>
                <h4><?= htmlspecialchars($update['title']) ?></h4>
                <a href="view_update.php?id=<?= $update['id'] ?>" class="bottom-read-more">Read More →</a>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bottom-card empty-state">
                <h4>Academic Updates</h4>
                <p>Latest academic news and announcements</p>
                <a href="#" class="bottom-read-more">Read More →</a>
            </div>
            <div class="bottom-card empty-state">
                <h4>Campus Development</h4>
                <p>Infrastructure and facility updates</p>
                <a href="#" class="bottom-read-more">Read More →</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- SEE MORE UPDATES BUTTON -->
    <div class="see-more-container" style="text-align: center; margin-top: 0.5rem;">
        <a href="all_updates.php" class="see-more-button">See More Updates →</a>
    </div>
</div>
        
               <!-- RIGHT - ANNOUNCEMENTS -->
        <div class="right-column">
            <div class="announcements-header"><h3>Announcements</h3></div>
            <?php 
            // I-debug muna natin kung may data
            // echo "<!-- Debug: announcements count = " . count($announcements) . " -->";
            ?>
            
            <?php if (!empty($announcements) && is_array($announcements)): ?>
                <?php foreach($announcements as $announcement): ?>
                    <?php if (is_array($announcement) && isset($announcement['title'])): ?>
                    <div class="announcement-card">
                        <div class="announcement-date">
                            📅 <?= !empty($announcement['date_posted']) ? date('M j, Y', strtotime($announcement['date_posted'])) : 'Date not set' ?>
                        </div>
                        <h4><?= htmlspecialchars($announcement['title'] ?? 'No title') ?></h4>
                        <p>
                            <?php 
                            $content = $announcement['content'] ?? 'No content available';
                            echo htmlspecialchars(substr($content, 0, 120));
                            if (strlen($content) > 120) echo '...';
                            ?>
                        </p>
                        <a href="view_announcement.php?id=<?= $announcement['id'] ?? '1' ?>" class="announcement-link">Read More →</a>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h4>No Announcements Available</h4>
                    <p>There are no announcements at this time. Please check back later.</p>
                    <!-- Sample announcement for testing -->
                    <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                        <strong>Sample Announcement:</strong><br>
                        Second SSC General Assembly 2025 - October 27, 2025
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>