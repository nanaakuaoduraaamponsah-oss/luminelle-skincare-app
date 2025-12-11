<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$user = getUserById($conn, $_SESSION['user_id']);
$page_title = 'Dashboard';

// Get user's skin type if they've taken the quiz
$stmt = mysqli_prepare($conn, "SELECT skin_type FROM skin_quiz_results WHERE user_id = ? ORDER BY date_taken DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$skin_data = mysqli_fetch_assoc($result);

// Get recent journal entries count
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as entry_count FROM journal_entries WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$journal_data = mysqli_fetch_assoc($result);

include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="welcome-section">
        <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>! ✨</h1>
        <p>Your skincare journey continues here</p>
    </div>
    
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-icon">🔍</div>
            <h3>Elle Guide</h3>
            <p>Discover your skin type with our personalized quiz</p>
            <?php if ($skin_data): ?>
                <div class="card-status">Your skin type: <strong><?php echo htmlspecialchars($skin_data['skin_type']); ?></strong></div>
            <?php else: ?>
                <div class="card-status">Not completed yet</div>
            <?php endif; ?>
            <a href="elle-guide/quiz.php" class="btn btn-secondary">
                <?php echo $skin_data ? 'Retake Quiz' : 'Take Quiz'; ?>
            </a>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">💆‍♀️</div>
            <h3>Lumin Routine</h3>
            <p>Explore skin concerns and get ingredient recommendations</p>
            <a href="lumin-routine/concerns.php" class="btn btn-secondary">View Concerns</a>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">📅</div>
            <h3>Glow Calendar</h3>
            <p>Track your daily routines and watch your progress</p>
            <div class="card-status">
                <?php echo $journal_data['entry_count']; ?> 
                <?php echo $journal_data['entry_count'] == 1 ? 'entry' : 'entries'; ?> logged
            </div>
            <a href="glow-calendar/calendar.php" class="btn btn-secondary">Open Calendar</a>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">👤</div>
            <h3>My Profile</h3>
            <p>View your account details and skincare profile</p>
            <a href="profile.php" class="btn btn-secondary">View Profile</a>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon">💬</div>
            <h3>Skincare Assistant</h3>
            <p>Get instant answers to skincare and app usage questions</p>
            <a href="chatbot.php" class="btn btn-secondary">Get Help</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>