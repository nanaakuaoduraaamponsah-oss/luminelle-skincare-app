<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$page_title = 'Elle Guide - Skin Type Quiz';

// Check if user has already taken the quiz
$stmt = mysqli_prepare($conn, "SELECT skin_type, date_taken FROM skin_quiz_results WHERE user_id = ? ORDER BY date_taken DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$previous_result = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get all answers
    $q1 = $_POST['q1'] ?? '';
    $q2 = $_POST['q2'] ?? '';
    $q3 = $_POST['q3'] ?? '';
    $q4 = $_POST['q4'] ?? '';
    $q5 = $_POST['q5'] ?? '';
    $q6 = $_POST['q6'] ?? '';
    
    // Validate all questions are answered
    if (empty($q1) || empty($q2) || empty($q3) || empty($q4) || empty($q5) || empty($q6)) {
        $error = 'Please answer all questions before submitting.';
    } else {
        // Count each letter
        $answers = [$q1, $q2, $q3, $q4, $q5, $q6];
        $count = array_count_values($answers);
        
        // Determine skin type based on most frequent answer
        arsort($count);
        $most_common = key($count);
        
        // Map letter to skin type
        $skin_types = [
            'A' => 'Dry',
            'B' => 'Normal',
            'C' => 'Combination',
            'D' => 'Oily',
            'E' => 'Sensitive'
        ];
        
        $skin_type = $skin_types[$most_common];
        
        // Save to database
        $stmt = mysqli_prepare($conn, "INSERT INTO skin_quiz_results (user_id, skin_type, question_1, question_2, question_3, question_4, question_5, question_6) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isssssss", $_SESSION['user_id'], $skin_type, $q1, $q2, $q3, $q4, $q5, $q6);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: results.php");
            exit();
        } else {
            $error = 'Failed to save results. Please try again.';
        }
    }
}

include '../includes/header.php';
?>

<div class="quiz-container">
    <div class="quiz-header">
        <h1>✨ Discover Your Skin Type</h1>
        <p>Answer these 6 questions to find out your skin type and get personalized recommendations</p>
        
        <?php if ($previous_result): ?>
            <div class="alert alert-info">
                <strong>Previous Result:</strong> <?php echo htmlspecialchars($previous_result['skin_type']); ?> 
                (taken on <?php echo date('M d, Y', strtotime($previous_result['date_taken'])); ?>)
                <br><small>Retake the quiz below to update your results</small>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>
    
    <form method="POST" action="" class="quiz-form" id="quizForm">
        
        <!-- Question 1 -->
        <div class="quiz-question">
            <h3><span class="question-number">1.</span> How does your skin feel after cleansing?</h3>
            <div class="quiz-options">
                <label class="quiz-option">
                    <input type="radio" name="q1" value="A" required>
                    <span>Tight, rough, and uncomfortable</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q1" value="B">
                    <span>Smooth and comfortable</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q1" value="C">
                    <span>Oily in the T-zone (forehead, nose, chin) but normal on cheeks</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q1" value="D">
                    <span>Greasy and shiny all over</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q1" value="E">
                    <span>Itchy or irritated</span>
                </label>
            </div>
        </div>
        
        <!-- Question 2 -->
        <div class="quiz-question">
            <h3><span class="question-number">2.</span> How does your skin look by midday?</h3>
            <div class="quiz-options">
                <label class="quiz-option">
                    <input type="radio" name="q2" value="A" required>
                    <span>Flaky with visible dry patches</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q2" value="B">
                    <span>Fresh and the same as morning</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q2" value="C">
                    <span>Shiny only on my forehead, nose, and chin</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q2" value="D">
                    <span>Very shiny and oily everywhere</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q2" value="E">
                    <span>Red or blotchy in some areas</span>
                </label>
            </div>
        </div>
        
        <!-- Question 3 -->
        <div class="quiz-question">
            <h3><span class="question-number">3.</span> How often do you experience breakouts?</h3>
            <div class="quiz-options">
                <label class="quiz-option">
                    <input type="radio" name="q3" value="A" required>
                    <span>Rarely, but my skin feels tight</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q3" value="B">
                    <span>Occasionally, but nothing major</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q3" value="C">
                    <span>Frequently in my T-zone area</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q3" value="D">
                    <span>Very frequently all over my face</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q3" value="E">
                    <span>When I try new products or in certain conditions</span>
                </label>
            </div>
        </div>
        
        <!-- Question 4 -->
        <div class="quiz-question">
            <h3><span class="question-number">4.</span> How do your pores look?</h3>
            <div class="quiz-options">
                <label class="quiz-option">
                    <input type="radio" name="q4" value="A" required>
                    <span>Very small and barely visible</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q4" value="B">
                    <span>Small to medium and not noticeable</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q4" value="C">
                    <span>Larger in my T-zone, smaller on cheeks</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q4" value="D">
                    <span>Large and visible everywhere</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q4" value="E">
                    <span>Normal size but my skin gets red easily</span>
                </label>
            </div>
        </div>
        
        <!-- Question 5 -->
        <div class="quiz-question">
            <h3><span class="question-number">5.</span> How does your skin react to new products?</h3>
            <div class="quiz-options">
                <label class="quiz-option">
                    <input type="radio" name="q5" value="A" required>
                    <span>Gets even drier or flakes</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q5" value="B">
                    <span>Usually fine, no issues</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q5" value="C">
                    <span>Sometimes breaks out in oily areas</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q5" value="D">
                    <span>Gets more oily or breaks out</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q5" value="E">
                    <span>Gets red, itchy, or irritated easily</span>
                </label>
            </div>
        </div>
        
        <!-- Question 6 -->
        <div class="quiz-question">
            <h3><span class="question-number">6.</span> What is your main skin concern?</h3>
            <div class="quiz-options">
                <label class="quiz-option">
                    <input type="radio" name="q6" value="A" required>
                    <span>Dryness and flaking</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q6" value="B">
                    <span>Maintaining my current healthy skin</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q6" value="C">
                    <span>Oil control in some areas, dryness in others</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q6" value="D">
                    <span>Excess oil and shine</span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="q6" value="E">
                    <span>Redness, irritation, and sensitivity</span>
                </label>
            </div>
        </div>
        
        <div class="quiz-submit">
            <button type="submit" class="btn btn-primary btn-large">Get My Results ✨</button>
        </div>
    </form>
</div>

<style>
.quiz-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem 0;
}

.quiz-header {
    text-align: center;
    margin-bottom: 3rem;
}

.quiz-header h1 {
    color: var(--bright-pink);
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.quiz-header p {
    color: var(--text-light);
    font-size: 1.1rem;
}

.quiz-form {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px var(--shadow);
}

.quiz-question {
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid var(--baby-pink);
}

.quiz-question:last-of-type {
    border-bottom: none;
}

.quiz-question h3 {
    color: var(--deep-pink);
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
}

.question-number {
    display: inline-block;
    background: var(--bright-pink);
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    text-align: center;
    line-height: 35px;
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

.quiz-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.quiz-option {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    background: var(--light-pink);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid transparent;
}

.quiz-option:hover {
    background: var(--baby-pink);
    transform: translateX(5px);
}

.quiz-option input[type="radio"] {
    width: 20px;
    height: 20px;
    margin-right: 1rem;
    cursor: pointer;
    accent-color: var(--bright-pink);
}

.quiz-option input[type="radio"]:checked + span {
    color: var(--bright-pink);
    font-weight: 600;
}

.quiz-option:has(input[type="radio"]:checked) {
    background: var(--baby-pink);
    border-color: var(--bright-pink);
}

.quiz-submit {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
}

.btn-large {
    padding: 1rem 3rem;
    font-size: 1.2rem;
}

@media (max-width: 768px) {
    .quiz-form {
        padding: 1.5rem;
    }
    
    .quiz-question h3 {
        font-size: 1.1rem;
    }
    
    .quiz-option {
        padding: 0.75rem 1rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>