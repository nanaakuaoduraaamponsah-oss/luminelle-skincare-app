<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$user = getUserById($conn, $_SESSION['user_id']);
$page_title = 'Profile';

// Get user's skin quiz result
$stmt = mysqli_prepare($conn, "SELECT * FROM skin_quiz_results WHERE user_id = ? ORDER BY date_taken DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quiz_result = mysqli_fetch_assoc($result);

// Get journal entry statistics
$stmt = mysqli_prepare($conn, "
    SELECT 
        COUNT(*) as total_entries,
        SUM(CASE WHEN completion_status = 'complete' THEN 1 ELSE 0 END) as complete_entries,
        SUM(CASE WHEN completion_status = 'half-done' THEN 1 ELSE 0 END) as half_entries,
        SUM(CASE WHEN completion_status = 'incomplete' THEN 1 ELSE 0 END) as incomplete_entries,
        MIN(entry_date) as first_entry
    FROM journal_entries 
    WHERE user_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats = mysqli_fetch_assoc($result);

// Get last 30 days data for chart
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$stmt = mysqli_prepare($conn, "
    SELECT entry_date, completion_status
    FROM journal_entries
    WHERE user_id = ? AND entry_date >= ?
    ORDER BY entry_date ASC
");
mysqli_stmt_bind_param($stmt, "is", $_SESSION['user_id'], $thirty_days_ago);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$chart_data = [];
$date_labels = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $date_labels[] = date('M j', strtotime($date));
    $chart_data[$date] = 0; // 0 = no entry, 1 = incomplete, 2 = half, 3 = complete
}

while ($row = mysqli_fetch_assoc($result)) {
    $value = 0;
    switch ($row['completion_status']) {
        case 'incomplete': $value = 1; break;
        case 'half-done': $value = 2; break;
        case 'complete': $value = 3; break;
    }
    $chart_data[$row['entry_date']] = $value;
}

$chart_values = array_values($chart_data);

include 'includes/header.php';
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
        </div>
        <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
    </div>
    
    <div class="profile-grid">
        <div class="profile-card">
            <h3>🔍 Skin Profile</h3>
            <?php if ($quiz_result): ?>
                <div class="profile-info">
                    <p><strong>Skin Type:</strong></p>
                    <p class="highlight"><?php echo htmlspecialchars($quiz_result['skin_type']); ?></p>
                    <p class="text-muted">Discovered on <?php echo date('M d, Y', strtotime($quiz_result['date_taken'])); ?></p>
                </div>
                <a href="elle-guide/quiz.php" class="btn btn-secondary btn-sm">Retake Quiz</a>
            <?php else: ?>
                <p class="text-muted">You haven't taken the skin quiz yet.</p>
                <a href="elle-guide/quiz.php" class="btn btn-primary">Take Quiz Now</a>
            <?php endif; ?>
        </div>
        
        <div class="profile-card">
            <h3>📊 Skincare Stats</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['total_entries']; ?></div>
                    <div class="stat-label">Total Entries</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['complete_entries']; ?></div>
                    <div class="stat-label">Completed Days</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        <?php 
                        if ($stats['first_entry']) {
                            $start = new DateTime($stats['first_entry']);
                            $now = new DateTime();
                            echo $start->diff($now)->days;
                        } else {
                            echo '0';
                        }
                        ?>
                    </div>
                    <div class="stat-label">Days Tracking</div>
                </div>
            </div>
        </div>
        
        <div class="profile-card">
            <h3>✨ Account Info</h3>
            <div class="profile-info">
                <p><strong>Member Since:</strong></p>
                <p><?php echo date('F Y', strtotime($user['created_at'] ?? 'now')); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Progress Visualization Section -->
    <?php if ($stats['total_entries'] > 0): ?>
    <div class="progress-section">
        <h2>📈 Your Progress</h2>
        
        <div class="charts-grid">
            <!-- Last 30 Days Chart -->
            <div class="chart-card">
                <h3>Last 30 Days Activity</h3>
                <canvas id="activityChart"></canvas>
                <div class="chart-legend">
                    <span><span class="legend-dot" style="background: #FFFFFF; border: 2px solid #FFD6E8;"></span> No Entry</span>
                    <span><span class="legend-dot" style="background: #FFD6E8;"></span> Incomplete</span>
                    <span><span class="legend-dot" style="background: #FF69B4;"></span> Half-Done</span>
                    <span><span class="legend-dot" style="background: #FF1493;"></span> Complete</span>
                </div>
            </div>
            
            <!-- Status Distribution Chart -->
            <div class="chart-card">
                <h3>Completion Status Distribution</h3>
                <canvas id="statusChart"></canvas>
                <div class="status-summary">
                    <div class="summary-item">
                        <strong><?php echo round(($stats['complete_entries'] / $stats['total_entries']) * 100); ?>%</strong>
                        <span>Complete Rate</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.profile-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem 0;
}

.profile-header {
    text-align: center;
    margin-bottom: 3rem;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--bright-pink), var(--fuchsia));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0 auto 1rem;
}

.profile-header h1 {
    color: var(--bright-pink);
    margin-bottom: 0.5rem;
}

.profile-email {
    color: var(--text-light);
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.profile-card {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px var(--shadow);
}

.profile-card h3 {
    color: var(--deep-pink);
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
}

.profile-info {
    margin-bottom: 1.5rem;
}

.profile-info p {
    margin-bottom: 0.5rem;
}

.highlight {
    color: var(--bright-pink);
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0.5rem 0;
}

.text-muted {
    color: var(--text-light);
    font-size: 0.9rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    text-align: center;
}

.stat-item {
    padding: 1rem;
    background: var(--light-pink);
    border-radius: 10px;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: var(--bright-pink);
}

.stat-label {
    font-size: 0.85rem;
    color: var(--text-light);
    margin-top: 0.25rem;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

/* Progress Section */
.progress-section {
    margin-top: 3rem;
}

.progress-section h2 {
    color: var(--bright-pink);
    text-align: center;
    margin-bottom: 2rem;
    font-size: 2rem;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

.chart-card {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px var(--shadow);
}

.chart-card h3 {
    color: var(--deep-pink);
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
}

.chart-card canvas {
    max-height: 300px;
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
    font-size: 0.9rem;
}

.legend-dot {
    display: inline-block;
    width: 15px;
    height: 15px;
    border-radius: 3px;
    margin-right: 0.5rem;
}

.status-summary {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--baby-pink);
}

.summary-item strong {
    display: block;
    font-size: 2rem;
    color: var(--bright-pink);
    margin-bottom: 0.25rem;
}

.summary-item span {
    color: var(--text-light);
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-grid {
        grid-template-columns: 1fr;
    }
    
    .charts-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Activity Chart (Last 30 Days)
const activityCtx = document.getElementById('activityChart');
if (activityCtx) {
    const activityData = <?php echo json_encode($chart_values); ?>;
    const labels = <?php echo json_encode($date_labels); ?>;
    
    new Chart(activityCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Daily Activity',
                data: activityData,
                backgroundColor: activityData.map(value => {
                    if (value === 0) return '#FFFFFF';
                    if (value === 1) return '#FFD6E8';
                    if (value === 2) return '#FF69B4';
                    return '#FF1493';
                }),
                borderColor: activityData.map(value => value === 0 ? '#FFD6E8' : 'transparent'),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y;
                            if (value === 0) return 'No Entry';
                            if (value === 1) return 'Incomplete';
                            if (value === 2) return 'Half-Done';
                            return 'Complete';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 3,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            if (value === 0) return 'None';
                            if (value === 1) return 'Incomplete';
                            if (value === 2) return 'Half';
                            if (value === 3) return 'Complete';
                            return '';
                        }
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

// Status Distribution Pie Chart
const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Complete', 'Half-Done', 'Incomplete'],
            datasets: [{
                data: [
                    <?php echo $stats['complete_entries']; ?>,
                    <?php echo $stats['half_entries']; ?>,
                    <?php echo $stats['incomplete_entries']; ?>
                ],
                backgroundColor: ['#FF1493', '#FF69B4', '#FFD6E8'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>