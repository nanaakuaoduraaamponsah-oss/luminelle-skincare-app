<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$page_title = 'Lumin Routine - Skin Concerns';

// Get all skin concerns from database
$result = mysqli_query($conn, "SELECT id, concern_name, icon, description FROM skin_concerns ORDER BY id ASC");
$concerns = mysqli_fetch_all($result, MYSQLI_ASSOC);

include '../includes/header.php';
?>

<div class="concerns-container">
    <div class="concerns-header">
        <h1>💆‍♀️ Lumin Routine</h1>
        <p>Explore common skin concerns and discover ingredient recommendations</p>
    </div>
    
    <div class="concerns-grid">
        <?php foreach ($concerns as $concern): ?>
            <a href="concern-details.php?id=<?php echo $concern['id']; ?>" class="concern-card">
                <div class="concern-icon"><?php echo $concern['icon']; ?></div>
                <h3><?php echo htmlspecialchars($concern['concern_name']); ?></h3>
                <p><?php echo htmlspecialchars(substr($concern['description'], 0, 120)) . '...'; ?></p>
                <span class="learn-more">Learn More →</span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<style>
.concerns-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 0;
}

.concerns-header {
    text-align: center;
    margin-bottom: 3rem;
}

.concerns-header h1 {
    color: var(--bright-pink);
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.concerns-header p {
    color: var(--text-light);
    font-size: 1.1rem;
}

.concerns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

.concern-card {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px var(--shadow);
    text-decoration: none;
    color: var(--text-dark);
    transition: all 0.3s;
    display: flex;
    flex-direction: column;
    border: 2px solid transparent;
}

.concern-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px var(--shadow);
    border-color: var(--bright-pink);
}

.concern-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.concern-card h3 {
    color: var(--deep-pink);
    font-size: 1.3rem;
    margin-bottom: 0.75rem;
}

.concern-card p {
    color: var(--text-light);
    line-height: 1.6;
    flex-grow: 1;
    margin-bottom: 1rem;
}

.learn-more {
    color: var(--bright-pink);
    font-weight: 600;
    align-self: flex-start;
}

.concern-card:hover .learn-more {
    transform: translateX(5px);
    display: inline-block;
    transition: transform 0.3s;
}

@media (max-width: 768px) {
    .concerns-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>