<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

$date = isset($_GET['date']) ? $_GET['date'] : '';

if (empty($date)) {
    header("Location: calendar.php");
    exit();
}

// Get entry to find photos
$stmt = mysqli_prepare($conn, "SELECT morning_photo, evening_photo FROM journal_entries WHERE user_id = ? AND entry_date = ?");
mysqli_stmt_bind_param($stmt, "is", $_SESSION['user_id'], $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$entry = mysqli_fetch_assoc($result);

if ($entry) {
    // Delete photos from filesystem
    if ($entry['morning_photo']) {
        $file_path = __DIR__ . '/../assets/images/uploads/' . $entry['morning_photo'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    if ($entry['evening_photo']) {
        $file_path = __DIR__ . '/../assets/images/uploads/' . $entry['evening_photo'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete entry from database
    $stmt = mysqli_prepare($conn, "DELETE FROM journal_entries WHERE user_id = ? AND entry_date = ?");
    mysqli_stmt_bind_param($stmt, "is", $_SESSION['user_id'], $date);
    mysqli_stmt_execute($stmt);
    
    setFlashMessage('Entry deleted successfully', 'success');
} else {
    setFlashMessage('Entry not found', 'error');
}

header("Location: calendar.php");
exit();
?>