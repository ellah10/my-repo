
<?php
session_start();
require '../includes/db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); 
    exit;
}

if (isset($_GET['id'])) {
    $book_id = intval($_GET['id']); 

    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);

    if ($stmt->execute()) {
        header('Location: manage_book.php?success=Book deleted successfully');
    } else {
        header('Location: manage_book.php?error=Failed to delete book');
    }
    
    exit;
} else {
    header('Location: manage_book.php?error=No book ID provided');
    exit;
}
?>
