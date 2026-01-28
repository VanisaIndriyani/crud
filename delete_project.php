<?php
require_once 'includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Delete project (stages will be deleted automatically due to ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        header("Location: dashboard.php?msg=deleted");
    } catch (Exception $e) {
        // If there's an error (e.g., constraint violation if ON DELETE CASCADE isn't working as expected),
        // we should handle it. But for now, we'll just redirect with an error.
        header("Location: dashboard.php?error=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: dashboard.php");
}
?>