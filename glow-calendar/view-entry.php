<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch entry
$stmt = mysqli_prepare($conn, "SELECT * FROM journal_entries WHERE user_id = ? AND entry_date = ?");
mysqli_stmt_bind_param($stmt, "is", $_SESSION['user_id'], $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$entry = mysqli_fetch_assoc($result);

// Redirect if entry doesn't exist
if (!$entry) {
    header("Location: calendar.php");
    exit();
}

$page_title = 'View Entry';

// Status display
$status_config = [
    'complete' => ['label' => 'Complete', 'icon' => '✨', 'color' => '#FF1493'],
    'half-done' => ['label' => 'Half-Done', 'icon' => '⚡', 'color' => '#FF69B4'],
    'incomplete' => ['label' => 'Incomplete', 'icon' => '😴', 'color' => '#FFD6E8']
];

$status = $status_config[$entry['completion_status']];

include '../includes/header.php';
?>

<div class="view-container">
    <div class="view-header">
        <a href="calendar.php" class="back-link">← Back to Calendar</a>
        <h1>📖 Journal Entry</h1>
        <p class="entry-date"><?php echo date('l, F j, Y', strtotime($date)); ?></p>
        <div class="status-badge" style="background: <?php echo $status['color']; ?>">
            <?php echo $status['icon'] . ' ' . $status['label']; ?>
        </div>
    </div>
    
    <!-- Morning Routine -->
    <?php if ($entry['morning_routine'] || $entry['morning_photo']): ?>
    <div class="view-section">
        <h2>🌅 Morning Routine</h2>
        <?php if ($entry['morning_photo']): ?>
            <div class="entry-photo">
                <img src="../assets/images/uploads/<?php echo htmlspecialchars($entry['morning_photo']); ?>" alt="Morning photo">
            </div>
        <?php endif; ?>
        <?php if ($entry['morning_routine']): ?>
            <div class="routine-text">
                <?php echo nl2br(htmlspecialchars($entry['morning_routine'])); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Evening Routine -->
    <?php if ($entry['evening_routine'] || $entry['evening_photo']): ?>
    <div class="view-section">
        <h2>🌙 Evening Routine</h2>
        <?php if ($entry['evening_photo']): ?>
            <div class="entry-photo">
                <img src="../assets/images/uploads/<?php echo htmlspecialchars($entry['evening_photo']); ?>" alt="Evening photo">
            </div>
        <?php endif; ?>
        <?php if ($entry['evening_routine']): ?>
            <div class="routine-text">
                <?php echo nl2br(htmlspecialchars($entry['evening_routine'])); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Personal Notes -->
    <?php if ($entry['notes']): ?>
    <div class="view-section">
        <h2>📝 Personal Notes</h2>
        <div class="notes-text">
            <?php echo nl2br(htmlspecialchars($entry['notes'])); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Action Buttons -->
    <div class="view-actions">
        <a href="add-entry.php?date=<?php echo $date; ?>" class="btn btn-primary">Edit Entry</a>
        <a href="delete-entry.php?date=<?php echo $date; ?>" class="btn btn-secondary delete-btn" onclick="return confirm('Are you sure you want to delete this entry?')">Delete Entry</a>
        <a href="calendar.php" class="btn btn-secondary">Back to Calendar</a>
    </div>
</div>

<style>
.view-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem 0;
}

.view-header {
    text-align: center;
    margin-bottom: 2rem;
}

.view-header h1 {
    color: var(--bright-pink);
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.entry-date {
    color: var(--text-light);
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    color: white;
    font-weight: bold;
    font-size: 1.1rem;
}

.back-link {
    color: var(--bright-pink);
    text-decoration: none;
    font-weight: 500;
    display: inline-block;
    margin-bottom: 1rem;
}

.view-section {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px var(--shadow);
    margin-bottom: 2rem;
}

.view-section h2 {
    color: var(--deep-pink);
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
}

.entry-photo {
    margin-bottom: 1.5rem;
}

.entry-photo img {
    max-width: 100%;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.routine-text,
.notes-text {
    background: var(--light-pink);
    padding: 1.5rem;
    border-radius: 10px;
    color: var(--text-dark);
    line-height: 1.8;
    border-left: 4px solid var(--bright-pink);
}

.view-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.delete-btn {
    background: #FFE5E5;
    color: #D8000C;
}

.delete-btn:hover {
    background: #FFB3B3;
}

@media (max-width: 768px) {
    .view-actions {
        flex-direction: column;
    }
    
    .view-actions .btn {
        width: 100%;
    }
}
</style>

<?php include '../includes/footer.php'; ?>