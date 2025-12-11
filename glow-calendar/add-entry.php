<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$today = date('Y-m-d');

// Prevent future dates
if (strtotime($date) > strtotime($today)) {
    header("Location: calendar.php");
    exit();
}

// Check if editing existing entry
$stmt = mysqli_prepare($conn, "SELECT * FROM journal_entries WHERE user_id = ? AND entry_date = ?");
mysqli_stmt_bind_param($stmt, "is", $_SESSION['user_id'], $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$existing_entry = mysqli_fetch_assoc($result);

$is_edit = ($existing_entry !== null);
$page_title = $is_edit ? 'Edit Entry' : 'Add Entry';

// Get previous day's entry for copy feature
$prev_date = date('Y-m-d', strtotime($date . ' -1 day'));
$stmt = mysqli_prepare($conn, "SELECT morning_routine, evening_routine FROM journal_entries WHERE user_id = ? AND entry_date = ?");
mysqli_stmt_bind_param($stmt, "is", $_SESSION['user_id'], $prev_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$prev_entry = mysqli_fetch_assoc($result);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $morning_routine = $_POST['morning_routine'] ?? '';
    $evening_routine = $_POST['evening_routine'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $completion_status = $_POST['completion_status'] ?? 'incomplete';
    
    // Handle photo uploads
    $morning_photo = $existing_entry['morning_photo'] ?? '';
    $evening_photo = $existing_entry['evening_photo'] ?? '';
    
    // Morning photo upload
    if (isset($_FILES['morning_photo']) && $_FILES['morning_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['morning_photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['morning_photo']['size'] <= MAX_FILE_SIZE) {
            $new_filename = $_SESSION['user_id'] . '_' . str_replace('-', '', $date) . '_morning.' . $ext;
            $upload_path = __DIR__ . '/../assets/images/uploads/' . $new_filename;
            
            // Create directory if it doesn't exist
            $upload_dir = __DIR__ . '/../assets/images/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['morning_photo']['tmp_name'], $upload_path)) {
                // Delete old photo if exists
                if ($morning_photo && file_exists(__DIR__ . '/../assets/images/uploads/' . $morning_photo)) {
                    unlink(__DIR__ . '/../assets/images/uploads/' . $morning_photo);
                }
                $morning_photo = $new_filename;
            } else {
                $error = 'Failed to upload morning photo. Please check folder permissions.';
            }
        } else {
            $error = 'Invalid morning photo format or size too large (max 5MB). Allowed: JPG, PNG';
        }
    }
    
    // Evening photo upload
    if (isset($_FILES['evening_photo']) && $_FILES['evening_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['evening_photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['evening_photo']['size'] <= MAX_FILE_SIZE) {
            $new_filename = $_SESSION['user_id'] . '_' . str_replace('-', '', $date) . '_evening.' . $ext;
            $upload_path = __DIR__ . '/../assets/images/uploads/' . $new_filename;
            
            // Create directory if it doesn't exist
            $upload_dir = __DIR__ . '/../assets/images/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['evening_photo']['tmp_name'], $upload_path)) {
                // Delete old photo if exists
                if ($evening_photo && file_exists(__DIR__ . '/../assets/images/uploads/' . $evening_photo)) {
                    unlink(__DIR__ . '/../assets/images/uploads/' . $evening_photo);
                }
                $evening_photo = $new_filename;
            } else {
                $error = 'Failed to upload evening photo. Please check folder permissions.';
            }
        } else {
            $error = 'Invalid evening photo format or size too large (max 5MB). Allowed: JPG, PNG';
        }
    }
    
    if (empty($error)) {
        if ($is_edit) {
            // Update existing entry
            $stmt = mysqli_prepare($conn, "UPDATE journal_entries SET morning_routine = ?, evening_routine = ?, morning_photo = ?, evening_photo = ?, notes = ?, completion_status = ? WHERE user_id = ? AND entry_date = ?");
            mysqli_stmt_bind_param($stmt, "ssssssis", $morning_routine, $evening_routine, $morning_photo, $evening_photo, $notes, $completion_status, $_SESSION['user_id'], $date);
        } else {
            // Insert new entry
            $stmt = mysqli_prepare($conn, "INSERT INTO journal_entries (user_id, entry_date, morning_routine, evening_routine, morning_photo, evening_photo, notes, completion_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isssssss", $_SESSION['user_id'], $date, $morning_routine, $evening_routine, $morning_photo, $evening_photo, $notes, $completion_status);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: view-entry.php?date=" . $date);
            exit();
        } else {
            $error = 'Failed to save entry. Please try again.';
        }
    }
}

include '../includes/header.php';
?>

<div class="entry-container">
    <div class="entry-header">
        <a href="calendar.php" class="back-link">← Back to Calendar</a>
        <h1><?php echo $is_edit ? 'Edit' : 'Add'; ?> Entry</h1>
        <p class="entry-date"><?php echo date('l, F j, Y', strtotime($date)); ?></p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($prev_entry && !$is_edit): ?>
        <div class="copy-previous">
            <button type="button" id="copyPrevBtn" class="btn btn-secondary">
                📋 Copy Yesterday's Routine
            </button>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data" class="entry-form">
        
        <!-- Morning Routine -->
        <div class="form-section">
            <h2>🌅 Morning Routine</h2>
            <div class="form-group">
                <label for="morning_routine">Describe your morning skincare routine</label>
                <textarea id="morning_routine" name="morning_routine" rows="4" placeholder="e.g., Cleanser, toner, serum, moisturizer, sunscreen..."><?php echo $existing_entry['morning_routine'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="morning_photo">Morning Photo (Optional)</label>
                <?php if ($existing_entry && $existing_entry['morning_photo']): ?>
                    <div class="current-photo">
                        <img src="../assets/images/uploads/<?php echo htmlspecialchars($existing_entry['morning_photo']); ?>" alt="Morning photo">
                        <a href="delete-photo.php?date=<?php echo $date; ?>&type=morning" class="delete-photo-btn" onclick="return confirm('Delete this photo?')">Delete Photo</a>
                    </div>
                <?php endif; ?>
                <input type="file" id="morning_photo" name="morning_photo" accept="image/jpeg,image/png">
                <small>Max size: 5MB. Formats: JPG, PNG</small>
            </div>
        </div>
        
        <!-- Evening Routine -->
        <div class="form-section">
            <h2>🌙 Evening Routine</h2>
            <div class="form-group">
                <label for="evening_routine">Describe your evening skincare routine</label>
                <textarea id="evening_routine" name="evening_routine" rows="4" placeholder="e.g., Makeup remover, cleanser, treatment, night cream..."><?php echo $existing_entry['evening_routine'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="evening_photo">Evening Photo (Optional)</label>
                <?php if ($existing_entry && $existing_entry['evening_photo']): ?>
                    <div class="current-photo">
                        <img src="../assets/images/uploads/<?php echo htmlspecialchars($existing_entry['evening_photo']); ?>" alt="Evening photo">
                        <a href="delete-photo.php?date=<?php echo $date; ?>&type=evening" class="delete-photo-btn" onclick="return confirm('Delete this photo?')">Delete Photo</a>
                    </div>
                <?php endif; ?>
                <input type="file" id="evening_photo" name="evening_photo" accept="image/jpeg,image/png">
                <small>Max size: 5MB. Formats: JPG, PNG</small>
            </div>
        </div>
        
        <!-- Notes -->
        <div class="form-section">
            <h2>📝 Personal Notes</h2>
            <div class="form-group">
                <label for="notes">How did your skin feel today? Any observations?</label>
                <textarea id="notes" name="notes" rows="3" placeholder="e.g., Skin felt hydrated, noticed improvement in texture..."><?php echo $existing_entry['notes'] ?? ''; ?></textarea>
            </div>
        </div>
        
        <!-- Completion Status -->
        <div class="form-section">
            <h2>✅ Completion Status</h2>
            <div class="form-group">
                <label for="completion_status">How would you rate today's routine completion?</label>
                <select id="completion_status" name="completion_status" required>
                    <option value="complete" <?php echo (isset($existing_entry) && $existing_entry['completion_status'] == 'complete') ? 'selected' : ''; ?>>✨ Complete - Did my full routine</option>
                    <option value="half-done" <?php echo (isset($existing_entry) && $existing_entry['completion_status'] == 'half-done') ? 'selected' : ''; ?>>⚡ Half-Done - Did some steps</option>
                    <option value="incomplete" <?php echo (isset($existing_entry) && $existing_entry['completion_status'] == 'incomplete') ? 'selected' : ''; ?>>😴 Incomplete - Skipped routine</option>
                </select>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-large">
                <?php echo $is_edit ? 'Update Entry' : 'Save Entry'; ?>
            </button>
            <a href="calendar.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php if ($prev_entry && !$is_edit): ?>
<script>
document.getElementById('copyPrevBtn').addEventListener('click', function() {
    document.getElementById('morning_routine').value = <?php echo json_encode($prev_entry['morning_routine']); ?>;
    document.getElementById('evening_routine').value = <?php echo json_encode($prev_entry['evening_routine']); ?>;
    this.style.display = 'none';
    alert('✅ Previous routine copied! You can now edit and save.');
});
</script>
<?php endif; ?>

<style>
.entry-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem 0;
}

.entry-header {
    text-align: center;
    margin-bottom: 2rem;
}

.entry-header h1 {
    color: var(--bright-pink);
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.entry-date {
    color: var(--text-light);
    font-size: 1.1rem;
}

.back-link {
    color: var(--bright-pink);
    text-decoration: none;
    font-weight: 500;
    display: inline-block;
    margin-bottom: 1rem;
}

.copy-previous {
    text-align: center;
    margin-bottom: 2rem;
}

.entry-form {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px var(--shadow);
}

.form-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid var(--baby-pink);
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h2 {
    color: var(--deep-pink);
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
}

.current-photo {
    margin-bottom: 1rem;
    position: relative;
}

.current-photo img {
    max-width: 200px;
    border-radius: 10px;
    display: block;
    margin-bottom: 0.5rem;
}

.delete-photo-btn {
    color: #D8000C;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
}

.delete-photo-btn:hover {
    text-decoration: underline;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn-large {
    padding: 1rem 3rem;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<?php include '../includes/footer.php'; ?>