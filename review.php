<?php
session_start();
require 'includes/db.php'; // Include the database connection

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if the book ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php'); // Redirect if no valid ID is provided
    exit;
}

$book_id = $_GET['id'];

// Handle review submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Insert the review into the database
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, book_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $_SESSION['user_id'], $book_id, $rating, $comment);

    if ($stmt->execute()) {
        // Redirect to the book details page after successful submission
        header("Location: book.php?id=$book_id");
        exit;
    } else {
        // Handle errors (optional)
        $error_message = "There was an error submitting your review.";
    }
}

// Fetch reviews for the book
$review_stmt = $conn->prepare("SELECT r.rating, r.comment, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.book_id = ?");
$review_stmt->bind_param("i", $book_id);
$review_stmt->execute();
$reviews = $review_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write a Review</title>
</head>
<body>
    <h1>Write a Review for Book ID: <?= htmlspecialchars($book_id) ?></h1>
    
    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>
    
    <form method="POST" action="review.php?id=<?= $book_id ?>">
        <label>Rating:</label>
        <select name="rating" required>
            <option value="">Select a rating</option>
            <option value="1">1 Star</option>
            <option value="2">2 Stars</option>
            <option value="3">3 Stars</option>
            <option value="4">4 Stars</option>
            <option value="5">5 Stars</option>
        </select>
        
        <label>Comment:</label>
        <textarea name="comment" required></textarea>
        
        <button type="submit">Submit Review</button>
    </form>

    <h2>User Reviews</h2>
    <ul>
        <?php foreach ($reviews as $review): ?>
            <li>
                <strong><?= htmlspecialchars($review['username']) ?>:</strong>
                <?= htmlspecialchars($review['rating']) ?> Stars - <?= htmlspecialchars($review['comment']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <a href="book.php?id=<?= $book_id ?>">Back to Book Details</a>
</body>
</html>
