<?php
session_start();
require 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$book_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    header('Location: index.php');
    exit;
}

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
    <title><?= htmlspecialchars($book['title']) ?> - Book Details</title>
</head>
<body>
    <h1><?= htmlspecialchars($book['title']) ?></h1>
    <img src="uploads/<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" width="200">
    <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
    <p><strong>Price:</strong> $<?= htmlspecialchars($book['price']) ?></p>
    <p><strong>Description:</strong> <?= htmlspecialchars($book['description']) ?></p>

    <h2>Reviews</h2>
    <?php if ($reviews): ?>
        <ul>
            <?php foreach ($reviews as $review): ?>
                <li>
                    <strong><?= htmlspecialchars($review['username']) ?>:</strong>
                    <p><?= htmlspecialchars($review['rating']) ?> Stars - <?= htmlspecialchars($review['comment']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No reviews yet.</p>
    <?php endif; ?>

    <a href="index.php">Back to Book List</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="review.php?id=<?= $book_id ?>">Write a Review</a>
    <?php endif; ?>
</body>
</html>
