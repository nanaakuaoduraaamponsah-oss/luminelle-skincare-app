<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$page_title = 'Your Skin Type Results';

// Get user's latest quiz result
$stmt = mysqli_prepare($conn, "SELECT * FROM skin_quiz_results WHERE user_id = ? ORDER BY date_taken DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quiz_result = mysqli_fetch_assoc($result);

// If no result found, redirect to quiz
if (!$quiz_result) {
    header("Location: quiz.php");
    exit();
}

$skin_type = $quiz_result['skin_type'];

// Define skin type information
$skin_info = [
    'Oily' => [
        'icon' => '💧',
        'description' => 'Your skin produces excess sebum, which can lead to a shiny appearance, enlarged pores, and a higher tendency for breakouts. Oily skin often looks glossy and feels greasy, especially in the T-zone area.',
        'tips' => [
            'Use a gentle, foaming cleanser twice daily to remove excess oil without stripping your skin. Look for ingredients like salicylic acid or tea tree oil.',
            'Choose oil-free, non-comedogenic moisturizers and always use a lightweight, gel-based sunscreen to avoid clogging pores.'
        ]
    ],
    'Dry' => [
        'icon' => '🏜️',
        'description' => 'Your skin lacks moisture and natural oils, which can result in a tight, rough feeling, visible flakiness, and fine lines. Dry skin may also appear dull and feel uncomfortable, especially after cleansing.',
        'tips' => [
            'Use a creamy, hydrating cleanser that won\'t strip your skin\'s natural oils. Avoid harsh soaps and hot water which can worsen dryness.',
            'Apply a rich, emollient moisturizer with ingredients like hyaluronic acid, ceramides, or glycerin immediately after cleansing while skin is still damp to lock in hydration.'
        ]
    ],
    'Normal' => [
        'icon' => '✨',
        'description' => 'Lucky you! Your skin is well-balanced with an even tone, smooth texture, and minimal sensitivity. Normal skin has a healthy glow, barely visible pores, and experiences few breakouts or dry patches.',
        'tips' => [
            'Maintain your skin\'s balance with a gentle cleanser and a lightweight moisturizer. Focus on prevention and protection to keep your skin healthy.',
            'Don\'t skip sunscreen! Even though your skin is balanced now, daily SPF protection is essential to prevent premature aging and maintain your healthy complexion.'
        ]
    ],
    'Sensitive' => [
        'icon' => '🌸',
        'description' => 'Your skin is easily irritated and reactive to products, weather changes, or environmental factors. You may experience redness, itching, burning sensations, or develop rashes when using certain skincare products.',
        'tips' => [
            'Choose fragrance-free, hypoallergenic products designed for sensitive skin. Patch test new products on a small area before applying to your entire face.',
            'Keep your routine simple with minimal products. Look for soothing ingredients like aloe vera, chamomile, or centella asiatica, and avoid harsh exfoliants or alcohol-based products.'
        ]
    ],
    'Combination' => [
        'icon' => '🎭',
        'description' => 'Your skin shows characteristics of multiple skin types, typically with an oily T-zone (forehead, nose, and chin) while your cheeks remain normal to dry. This means you need to address different needs in different areas of your face.',
        'tips' => [
            'Use a gentle cleanser that balances without over-drying. Consider multi-masking—applying different masks to different zones (clay mask on oily areas, hydrating mask on dry areas).',
            'Apply lighter, oil-free products to your T-zone and richer moisturizers to dry areas. Use blotting papers throughout the day to control shine in oily zones without disrupting your entire routine.'
        ]
    ]
];

$current_info = $skin_info[$skin_type];

include '../includes/header.php';
?>

<div class="results-container">
    <div class="results-header">
        <div class="results-icon"><?php echo $current_info['icon']; ?></div>
        <h1>Your Skin Type: <span class="skin-type-name"><?php echo htmlspecialchars($skin_type); ?></span></h1>
        <p class="results-date">Discovered on <?php echo date('F d, Y', strtotime($quiz_result['date_taken'])); ?></p>
    </div>
    
    <div class="results-content">
        <div class="results-card">
            <h2>Understanding Your <?php echo htmlspecialchars($skin_type); ?> Skin</h2>
            <p class="skin-description"><?php echo $current_info['description']; ?></p>
        </div>
        
        <div class="results-card">
            <h2>💡 Skincare Tips for You</h2>
            <div class="tips-list">
                <?php foreach ($current_info['tips'] as $index => $tip): ?>
                    <div class="tip-item">
                        <div class="tip-number"><?php echo $index + 1; ?></div>
                        <p><?php echo $tip; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="results-actions">
            <a href="quiz.php" class="btn btn-secondary">Retake Quiz</a>
            <a href="../lumin-routine/concerns.php" class="btn btn-primary">Explore Skin Concerns</a>
            <a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>

<style>
.results-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 0;
}

.results-header {
    text-align: center;
    margin-bottom: 3rem;
}

.results-icon {
    font-size: 5rem;
    margin-bottom: 1rem;
    animation: fadeInScale 0.6s ease-out;
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.results-header h1 {
    color: var(--text-dark);
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.skin-type-name {
    color: var(--bright-pink);
    font-size: 2.5rem;
    display: block;
    margin-top: 0.5rem;
}

.results-date {
    color: var(--text-light);
    font-size: 0.95rem;
}

.results-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.results-card {
    background: var(--white);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 5px 20px var(--shadow);
}

.results-card h2 {
    color: var(--deep-pink);
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
}

.skin-description {
    color: var(--text-dark);
    font-size: 1.1rem;
    line-height: 1.8;
}

.tips-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.tip-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    padding: 1.5rem;
    background: var(--light-pink);
    border-radius: 15px;
    border-left: 4px solid var(--bright-pink);
}

.tip-number {
    background: var(--bright-pink);
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.tip-item p {
    color: var(--text-dark);
    line-height: 1.7;
    margin: 0;
}

.results-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.results-actions .btn {
    padding: 0.75rem 2rem;
}

@media (max-width: 768px) {
    .results-card {
        padding: 1.5rem;
    }
    
    .skin-type-name {
        font-size: 2rem;
    }
    
    .results-actions {
        flex-direction: column;
    }
    
    .results-actions .btn {
        width: 100%;
    }
}
</style>

<?php include '../includes/footer.php'; ?>