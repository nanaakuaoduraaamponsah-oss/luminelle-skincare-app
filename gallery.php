<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Progress Gallery';

// Get filter from URL
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // all, morning, evening

// Build query based on filter
$query = "SELECT entry_date, morning_photo, evening_photo FROM journal_entries WHERE user_id = ? AND (morning_photo IS NOT NULL OR evening_photo IS NOT NULL) ORDER BY entry_date DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$photos = [];
while ($row = mysqli_fetch_assoc($result)) {
    if ($filter == 'all' || $filter == 'morning') {
        if ($row['morning_photo']) {
            $photos[] = [
                'date' => $row['entry_date'],
                'photo' => $row['morning_photo'],
                'type' => 'Morning',
                'icon' => '🌅'
            ];
        }
    }
    
    if ($filter == 'all' || $filter == 'evening') {
        if ($row['evening_photo']) {
            $photos[] = [
                'date' => $row['entry_date'],
                'photo' => $row['evening_photo'],
                'type' => 'Evening',
                'icon' => '🌙'
            ];
        }
    }
}

include 'includes/header.php';
?>

<div class="gallery-container">
    <div class="gallery-header">
        <h1>📸 Progress Gallery</h1>
        <p>Track your skincare journey through photos</p>
        <div class="gallery-stats">
            <div class="stat-badge">
                <strong><?php echo count($photos); ?></strong> Photos
            </div>
        </div>
    </div>
    
    <!-- Filter Buttons -->
    <div class="gallery-filters">
        <a href="?filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">
            ✨ All Photos
        </a>
        <a href="?filter=morning" class="filter-btn <?php echo $filter == 'morning' ? 'active' : ''; ?>">
            🌅 Morning Only
        </a>
        <a href="?filter=evening" class="filter-btn <?php echo $filter == 'evening' ? 'active' : ''; ?>">
            🌙 Evening Only
        </a>
    </div>
    
    <?php if (empty($photos)): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-icon">📷</div>
            <h2>No Photos Yet</h2>
            <p>Start uploading photos in your Glow Calendar entries to track your skincare progress!</p>
            <a href="glow-calendar/calendar.php" class="btn btn-primary">Go to Calendar</a>
        </div>
    <?php else: ?>
        <!-- Photo Grid -->
        <div class="photo-grid">
            <?php foreach ($photos as $index => $photo): ?>
                <div class="photo-card" data-index="<?php echo $index; ?>">
                    <div class="photo-wrapper">
                        <img src="assets/images/uploads/<?php echo htmlspecialchars($photo['photo']); ?>" 
                            alt="<?php echo $photo['type']; ?> photo from <?php echo date('M d, Y', strtotime($photo['date'])); ?>"
                            loading="lazy">
                        <div class="photo-overlay">
                            <button class="zoom-btn" onclick="openLightbox(<?php echo $index; ?>)">
                                🔍 View
                            </button>
                        </div>
                    </div>
                    <div class="photo-info">
                        <div class="photo-date">
                            <?php echo $photo['icon']; ?> 
                            <?php echo date('M d, Y', strtotime($photo['date'])); ?>
                        </div>
                        <div class="photo-type"><?php echo $photo['type']; ?> Routine</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <img id="lightbox-img" src="" alt="Full size photo">
    <div class="lightbox-info">
        <span id="lightbox-date"></span>
        <span id="lightbox-type"></span>
    </div>
    <button class="lightbox-nav prev" onclick="navigateLightbox(-1)">❮</button>
    <button class="lightbox-nav next" onclick="navigateLightbox(1)">❯</button>
</div>

<style>
.gallery-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 0;
}

.gallery-header {
    text-align: center;
    margin-bottom: 2rem;
}

.gallery-header h1 {
    color: var(--bright-pink);
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.gallery-header p {
    color: var(--text-light);
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.gallery-stats {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1rem;
}

.stat-badge {
    background: var(--baby-pink);
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    color: var(--deep-pink);
    font-size: 1rem;
}

.gallery-filters {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 0.75rem 1.5rem;
    background: var(--white);
    color: var(--text-dark);
    text-decoration: none;
    border-radius: 10px;
    border: 2px solid var(--border-color);
    transition: all 0.3s;
    font-weight: 500;
}

.filter-btn:hover {
    border-color: var(--fuchsia);
    background: var(--light-pink);
}

.filter-btn.active {
    background: var(--bright-pink);
    color: white;
    border-color: var(--bright-pink);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--white);
    border-radius: 20px;
    box-shadow: 0 5px 20px var(--shadow);
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h2 {
    color: var(--deep-pink);
    margin-bottom: 1rem;
}

.empty-state p {
    color: var(--text-light);
    margin-bottom: 2rem;
    font-size: 1.1rem;
}

.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.photo-card {
    background: var(--white);
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px var(--shadow);
    transition: transform 0.3s;
}

.photo-card:hover {
    transform: translateY(-5px);
}

.photo-wrapper {
    position: relative;
    padding-top: 100%;
    overflow: hidden;
    background: var(--light-pink);
}

.photo-wrapper img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 20, 147, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.photo-card:hover .photo-overlay {
    opacity: 1;
}

.zoom-btn {
    padding: 0.75rem 1.5rem;
    background: white;
    color: var(--bright-pink);
    border: none;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
    font-size: 1rem;
}

.photo-info {
    padding: 1rem;
}

.photo-date {
    color: var(--text-dark);
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.photo-type {
    color: var(--text-light);
    font-size: 0.9rem;
}

/* Lightbox */
.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.lightbox.active {
    display: flex;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 40px;
    color: white;
    font-size: 3rem;
    cursor: pointer;
    z-index: 1001;
}

.lightbox img {
    max-width: 90%;
    max-height: 80vh;
    border-radius: 10px;
}

.lightbox-info {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.9);
    padding: 1rem 2rem;
    border-radius: 10px;
    display: flex;
    gap: 2rem;
    color: var(--text-dark);
    font-weight: 600;
}

.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    padding: 1rem 1.5rem;
    font-size: 2rem;
    cursor: pointer;
    border-radius: 10px;
    transition: background 0.3s;
}

.lightbox-nav:hover {
    background: rgba(255, 255, 255, 0.4);
}

.lightbox-nav.prev {
    left: 20px;
}

.lightbox-nav.next {
    right: 20px;
}

@media (max-width: 768px) {
    .photo-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .lightbox-nav {
        padding: 0.5rem 1rem;
        font-size: 1.5rem;
    }
}
</style>

<script>
const photos = <?php echo json_encode($photos); ?>;
let currentIndex = 0;

function openLightbox(index) {
    currentIndex = index;
    const photo = photos[index];
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    const date = document.getElementById('lightbox-date');
    const type = document.getElementById('lightbox-type');
    
    img.src = 'assets/images/uploads/' + photo.photo;
    date.textContent = photo.icon + ' ' + formatDate(photo.date);
    type.textContent = photo.type + ' Routine';
    
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    lightbox.classList.remove('active');
    document.body.style.overflow = 'auto';
}

function navigateLightbox(direction) {
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = photos.length - 1;
    if (currentIndex >= photos.length) currentIndex = 0;
    openLightbox(currentIndex);
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Close lightbox on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') navigateLightbox(-1);
    if (e.key === 'ArrowRight') navigateLightbox(1);
});
</script>

<?php include 'includes/footer.php'; ?>