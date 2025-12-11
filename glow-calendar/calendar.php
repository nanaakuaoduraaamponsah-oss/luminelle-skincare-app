<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$page_title = 'Glow Calendar';

// Get current month and year, or from URL parameters
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Ensure valid month
if ($current_month < 1) {
    $current_month = 12;
    $current_year--;
} elseif ($current_month > 12) {
    $current_month = 1;
    $current_year++;
}

// Calculate previous and next month
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $current_month + 1;
$next_year = $current_year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Get all entries for current month
$start_date = "$current_year-$current_month-01";
$end_date = date('Y-m-t', strtotime($start_date));

$stmt = mysqli_prepare($conn, "SELECT entry_date, completion_status FROM journal_entries WHERE user_id = ? AND entry_date BETWEEN ? AND ?");
mysqli_stmt_bind_param($stmt, "iss", $_SESSION['user_id'], $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$entries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $entries[$row['entry_date']] = $row['completion_status'];
}

// Calculate current streak
$stmt = mysqli_prepare($conn, "SELECT entry_date, completion_status FROM journal_entries WHERE user_id = ? ORDER BY entry_date DESC");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$current_streak = 0;
$check_date = new DateTime();
$all_entries = mysqli_fetch_all($result, MYSQLI_ASSOC);

foreach ($all_entries as $entry) {
    $entry_date = new DateTime($entry['entry_date']);
    if ($entry_date->format('Y-m-d') == $check_date->format('Y-m-d')) {
        $current_streak++;
        $check_date->modify('-1 day');
    } else {
        break;
    }
}

// Calculate longest streak
$longest_streak = 0;
$temp_streak = 0;
$prev_date = null;

foreach (array_reverse($all_entries) as $entry) {
    $entry_date = new DateTime($entry['entry_date']);
    
    if ($prev_date === null) {
        $temp_streak = 1;
    } else {
        $diff = $prev_date->diff($entry_date)->days;
        if ($diff == 1) {
            $temp_streak++;
        } else {
            $longest_streak = max($longest_streak, $temp_streak);
            $temp_streak = 1;
        }
    }
    
    $prev_date = $entry_date;
}
$longest_streak = max($longest_streak, $temp_streak);

// Check if today is logged
$today = date('Y-m-d');
$today_logged = isset($entries[$today]);

include '../includes/header.php';
?>

<div class="calendar-container">
    <!-- Header with Stats -->
    <div class="calendar-header">
        <h1>📅 Glow Calendar</h1>
        <p>Track your skincare journey day by day</p>
        
        <?php if (!$today_logged): ?>
            <div class="alert alert-info">
                <strong>✨ Don't forget!</strong> You haven't logged today's routine yet. 
                <a href="add-entry.php?date=<?php echo $today; ?>" style="color: var(--bright-pink); font-weight: bold;">Add it now</a>
            </div>
        <?php endif; ?>
        
        <div class="streak-display">
            <div class="streak-card">
                <div class="streak-icon">🔥</div>
                <div class="streak-info">
                    <div class="streak-number"><?php echo $current_streak; ?></div>
                    <div class="streak-label">Current Streak</div>
                </div>
            </div>
            <div class="streak-card">
                <div class="streak-icon">🏆</div>
                <div class="streak-info">
                    <div class="streak-number"><?php echo $longest_streak; ?></div>
                    <div class="streak-label">Longest Streak</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Calendar Navigation -->
    <div class="calendar-nav">
        <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="nav-btn">← Previous</a>
        <h2><?php echo date('F Y', strtotime("$current_year-$current_month-01")); ?></h2>
        <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="nav-btn">Next →</a>
    </div>
    
    <!-- Legend -->
    <div class="calendar-legend">
        <div class="legend-item">
            <span class="legend-color" style="background: #FFFFFF; border: 2px solid #FFD6E8;"></span>
            <span>No Entry</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #FFD6E8;"></span>
            <span>Incomplete</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #FF69B4;"></span>
            <span>Half-Done</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #FF1493;"></span>
            <span>Complete</span>
        </div>
    </div>
    
    <!-- Calendar Grid -->
    <div class="calendar-grid">
        <!-- Day Headers -->
        <div class="calendar-day-header">Sun</div>
        <div class="calendar-day-header">Mon</div>
        <div class="calendar-day-header">Tue</div>
        <div class="calendar-day-header">Wed</div>
        <div class="calendar-day-header">Thu</div>
        <div class="calendar-day-header">Fri</div>
        <div class="calendar-day-header">Sat</div>
        
        <?php
        // Get first day of month and number of days
        $first_day = date('w', strtotime($start_date));
        $num_days = date('t', strtotime($start_date));
        $today_date = date('Y-m-d');
        
        // Empty cells before first day
        for ($i = 0; $i < $first_day; $i++) {
            echo '<div class="calendar-cell empty"></div>';
        }
        
        // Days of the month
        for ($day = 1; $day <= $num_days; $day++) {
            $date = sprintf("%04d-%02d-%02d", $current_year, $current_month, $day);
            $is_future = strtotime($date) > strtotime($today_date);
            $is_today = $date == $today_date;
            
            // Determine color based on status
            $status_class = 'no-entry';
            if (isset($entries[$date])) {
                switch ($entries[$date]) {
                    case 'complete':
                        $status_class = 'status-complete';
                        break;
                    case 'half-done':
                        $status_class = 'status-half';
                        break;
                    case 'incomplete':
                        $status_class = 'status-incomplete';
                        break;
                }
            }
            
            echo '<div class="calendar-cell ' . $status_class . ($is_today ? ' today' : '') . ($is_future ? ' future' : '') . '">';
            echo '<div class="day-number">' . $day . '</div>';
            
            if (!$is_future) {
                if (isset($entries[$date])) {
                    echo '<a href="view-entry.php?date=' . $date . '" class="day-btn view-btn">View</a>';
                } else {
                    echo '<a href="add-entry.php?date=' . $date . '" class="day-btn add-btn">Add</a>';
                }
            }
            
            echo '</div>';
        }
        ?>
    </div>
</div>

<style>
.calendar-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem 0;
}

.calendar-header {
    text-align: center;
    margin-bottom: 2rem;
}

.calendar-header h1 {
    color: var(--bright-pink);
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.calendar-header p {
    color: var(--text-light);
    margin-bottom: 1.5rem;
}

.streak-display {
    display: flex;
    gap: 2rem;
    justify-content: center;
    margin-top: 1.5rem;
}

.streak-card {
    background: var(--white);
    border-radius: 15px;
    padding: 1.5rem 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 5px 20px var(--shadow);
}

.streak-icon {
    font-size: 2.5rem;
}

.streak-number {
    font-size: 2rem;
    font-weight: bold;
    color: var(--bright-pink);
}

.streak-label {
    color: var(--text-light);
    font-size: 0.9rem;
}

.calendar-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--white);
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 1.5rem;
    box-shadow: 0 5px 20px var(--shadow);
}

.calendar-nav h2 {
    color: var(--deep-pink);
    font-size: 1.5rem;
}

.nav-btn {
    padding: 0.5rem 1.5rem;
    background: var(--baby-pink);
    color: var(--deep-pink);
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s;
}

.nav-btn:hover {
    background: var(--fuchsia);
    color: white;
}

.calendar-legend {
    display: flex;
    gap: 2rem;
    justify-content: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-color {
    width: 30px;
    height: 30px;
    border-radius: 5px;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
    background: var(--white);
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px var(--shadow);
}

.calendar-day-header {
    text-align: center;
    font-weight: bold;
    color: var(--deep-pink);
    padding: 0.75rem;
}

.calendar-cell {
    aspect-ratio: 1;
    border-radius: 10px;
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s;
}

.calendar-cell.empty {
    background: transparent;
}

.calendar-cell.no-entry {
    background: #FFFFFF;
    border: 2px solid #FFD6E8;
}

.calendar-cell.status-incomplete {
    background: #FFD6E8;
}

.calendar-cell.status-half {
    background: #FF69B4;
}

.calendar-cell.status-complete {
    background: #FF1493;
}

.calendar-cell.status-complete .day-number,
.calendar-cell.status-half .day-number {
    color: white;
}

.calendar-cell.today {
    border: 3px solid var(--deep-pink);
    box-shadow: 0 0 10px var(--shadow);
}

.calendar-cell.future {
    opacity: 0.4;
    cursor: not-allowed;
}

.day-number {
    font-weight: bold;
    font-size: 1.1rem;
    color: var(--text-dark);
}

.day-btn {
    padding: 0.25rem 0.75rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.75rem;
    font-weight: 600;
    transition: all 0.3s;
}

.add-btn {
    background: var(--bright-pink);
    color: white;
}

.add-btn:hover {
    background: var(--deep-pink);
}

.view-btn {
    background: rgba(255, 255, 255, 0.3);
    color: var(--text-dark);
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.calendar-cell.status-complete .view-btn,
.calendar-cell.status-half .view-btn {
    color: white;
    border-color: white;
}

.view-btn:hover {
    background: rgba(255, 255, 255, 0.5);
}

@media (max-width: 768px) {
    .streak-display {
        flex-direction: column;
        gap: 1rem;
    }
    
    .calendar-grid {
        gap: 0.25rem;
        padding: 1rem;
    }
    
    .day-btn {
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>