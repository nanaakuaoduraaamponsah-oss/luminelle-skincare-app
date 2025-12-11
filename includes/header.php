<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - LuminElle' : 'LuminElle - Your Skincare Companion'; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="<?php echo SITE_URL; ?>/dashboard.php">✨ LuminElle</a>
            </div>
            <ul class="nav-menu">
                <li><a href="<?php echo SITE_URL; ?>/dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo SITE_URL; ?>/elle-guide/quiz.php">Elle Guide</a></li>
                <li><a href="<?php echo SITE_URL; ?>/lumin-routine/concerns.php">Lumin Routine</a></li>
                <li><a href="<?php echo SITE_URL; ?>/glow-calendar/calendar.php">Glow Calendar</a></li>
                <li><a href="<?php echo SITE_URL; ?>/gallery.php">Gallery</a></li>
                <li><a href="<?php echo SITE_URL; ?>/chatbot.php">Help</a></li>
                <li><a href="<?php echo SITE_URL; ?>/profile.php">Profile</a></li>
                <li><a href="<?php echo SITE_URL; ?>/logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>
    
    <main class="main-content">
        <?php displayFlashMessage(); ?>