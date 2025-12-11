<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

// Get concern ID from URL
$concern_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch concern details
$stmt = mysqli_prepare($conn, "SELECT * FROM skin_concerns WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $concern_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$concern = mysqli_fetch_assoc($result);

// Redirect if concern not found
if (!$concern) {
    header("Location: concerns.php");
    exit();
}

$page_title = $concern['concern_name'];

// Parse pipe-separated lists into arrays
$foods_to_eat = explode('|', $concern['foods_to_eat']);
$foods_to_avoid = explode('|', $concern['foods_to_avoid']);
$ingredients = explode('|', $concern['recommended_ingredients']);
$suggestions = explode('|', $concern['suggestions']);

include '../includes/header.php';
?>

<div class="concern-detail-container">
    <!-- Header Section -->
    <div class="detail-header">
        <a href="concerns.php" class="back-link">← Back to All Concerns</a>
        <div class="concern-title">
            <span class="title-icon"><?php echo $concern['icon']; ?></span>
            <h1><?php echo htmlspecialchars($concern['concern_name']); ?></h1>
        </div>
    </div>
    
    <!-- Description Section -->
    <div class="detail-card">
        <h2>📖 Understanding This Concern</h2>
        <p class="concern-description"><?php echo htmlspecialchars($concern['description']); ?></p>
    </div>
    
    <!-- Two Column Layout -->
    <div class="detail-grid">
        <!-- Foods to Eat -->
        <div class="detail-card">
            <h2>🥗 Foods to Eat</h2>
            <ul class="info-list">
                <?php foreach ($foods_to_eat as $food): ?>
                    <li><?php echo htmlspecialchars($food); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <!-- Foods to Avoid -->
        <div class="detail-card">
            <h2>🚫 Foods to Avoid</h2>
            <ul class="info-list">
                <?php foreach ($foods_to_avoid as $food): ?>
                    <li><?php echo htmlspecialchars($food); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <!-- Recommended Ingredients -->
    <div class="detail-card">
        <h2>💊 Recommended Ingredients</h2>
        <ul class="ingredients-list">
            <?php foreach ($ingredients as $ingredient): ?>
                <li><?php echo htmlspecialchars($ingredient); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <!-- Myths Section -->
    <div class="detail-card myths-card">
        <h2>❌ Myths to Debunk</h2>
        <div class="myths-container">
            <div class="myth-item">
                <div class="myth-label">MYTH</div>
                <p class="myth-title"><?php echo htmlspecialchars($concern['myth_1_title']); ?></p>
                <div class="truth-label">TRUTH</div>
                <p class="myth-truth"><?php echo htmlspecialchars($concern['myth_1_truth']); ?></p>
            </div>
            
            <div class="myth-item">
                <div class="myth-label">MYTH</div>
                <p class="myth-title"><?php echo htmlspecialchars($concern['myth_2_title']); ?></p>
                <div class="truth-label">TRUTH</div>
                <p class="myth-truth"><?php echo htmlspecialchars($concern['myth_2_truth']); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Suggestions -->
    <div class="detail-card">
        <h2>💡 Helpful Suggestions</h2>
        <ul class="suggestions-list">
            <?php foreach ($suggestions as $suggestion): ?>
                <li><?php echo htmlspecialchars($suggestion); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <!-- Action Buttons -->
    <div class="detail-actions">
        <a href="concerns.php" class="btn btn-secondary">View Other Concerns</a>
        <a href="../dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
</div>

<style>
.concern-detail-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 0;
}

.detail-header {
    margin-bottom: 2rem;
}

.back-link {
    color: var(--bright-pink);
    text-decoration: none;
    font-weight: 500;
    display: inline-block;
    margin-bottom: 1rem;
    transition: transform 0.3s;
}

.back-link:hover {
    transform: translateX(-5px);
}

.concern-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.title-icon {
    font-size: 3.5rem;
}

.concern-title h1 {
    color: var(--bright-pink);
    font-size: 2.5rem;
}

.detail-card {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px var(--shadow);
    margin-bottom: 2rem;
}

.detail-card h2 {
    color: var(--deep-pink);
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
}

.concern-description {
    color: var(--text-dark);
    font-size: 1.1rem;
    line-height: 1.8;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.info-list {
    list-style: none;
    padding: 0;
}

.info-list li {
    padding: 0.75rem 1rem;
    margin-bottom: 0.75rem;
    background: var(--light-pink);
    border-radius: 10px;
    border-left: 4px solid var(--bright-pink);
    color: var(--text-dark);
}

.ingredients-list {
    list-style: none;
    padding: 0;
    display: grid;
    gap: 1rem;
}

.ingredients-list li {
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, var(--light-pink) 0%, var(--baby-pink) 100%);
    border-radius: 10px;
    color: var(--text-dark);
    font-weight: 500;
}

.myths-card {
    background: linear-gradient(135deg, #FFF0F5 0%, #FFE5F0 100%);
}

.myths-container {
    display: grid;
    gap: 2rem;
}

.myth-item {
    background: var(--white);
    padding: 1.5rem;
    border-radius: 15px;
}

.myth-label {
    display: inline-block;
    background: #FF6B9D;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 5px;
    font-size: 0.8rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.myth-title {
    color: var(--text-dark);
    font-style: italic;
    margin-bottom: 1rem;
    font-size: 1.05rem;
}

.truth-label {
    display: inline-block;
    background: #4CAF50;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 5px;
    font-size: 0.8rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.myth-truth {
    color: var(--text-dark);
    line-height: 1.7;
}

.suggestions-list {
    list-style: none;
    padding: 0;
    counter-reset: suggestion-counter;
}

.suggestions-list li {
    padding: 1rem 1rem 1rem 3.5rem;
    margin-bottom: 1rem;
    background: var(--light-pink);
    border-radius: 10px;
    position: relative;
    color: var(--text-dark);
    line-height: 1.7;
}

.suggestions-list li::before {
    counter-increment: suggestion-counter;
    content: counter(suggestion-counter);
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: var(--bright-pink);
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.detail-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .concern-title h1 {
        font-size: 1.8rem;
    }
    
    .title-icon {
        font-size: 2.5rem;
    }
    
    .detail-actions {
        flex-direction: column;
    }
    
    .detail-actions .btn {
        width: 100%;
    }
}
</style>

<?php include '../includes/footer.php'; ?>