<?php
session_start();
require 'includes/db.php';


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$search_query = '';
$category_filter = '';
$price_min = '';
$price_max = '';

$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['search'])) {
    $search_query = htmlspecialchars(trim($_GET['search']));
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_filter = htmlspecialchars(trim($_GET['category']));
}


if (isset($_GET['price_min']) && is_numeric($_GET['price_min'])) {
    $price_min = floatval($_GET['price_min']);
}
if (isset($_GET['price_max']) && is_numeric($_GET['price_max'])) {
    $price_max = floatval($_GET['price_max']);
}


$sql = "SELECT * FROM books WHERE 1=1"; 

if ($search_query) {
    $sql .= " AND (title LIKE ? OR author LIKE ?)";
}
if ($category_filter) {
    $sql .= " AND category_id = ?";
}
if ($price_min) {
    $sql .= " AND price >= ?";
}
if ($price_max) {
    $sql .= " AND price <= ?";
}


$stmt = $conn->prepare($sql);
$params = [];


if ($search_query) {
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}


if ($category_filter) {
    $params[] = $category_filter;
}


if ($price_min) {
    $params[] = $price_min;
}
if ($price_max) {
    $params[] = $price_max;
}

if ($params) {
    $types = str_repeat("s", count($params)); 
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_to_cart'])) {
    $book_id = $_POST['book_id'];
    $quantity = $_POST['quantity'] ?? 1;

    if (isset($_SESSION['cart'][$book_id])) {
        $_SESSION['cart'][$book_id] += $quantity; 
    } else {
        $_SESSION['cart'][$book_id] = $quantity; 
    }

    header('Location: index.php'); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_SESSION['user_id']; 
    $rating = $_POST['rating'];
    $comment = htmlspecialchars(trim($_POST['comment']));

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, book_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $book_id, $rating, $comment);

    if ($stmt->execute()) {
        header("Location: index.php"); 
        exit;
    } else {
        echo "Error adding review.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Booklish</title>
</head>
<body>
    <h1>Welcome to online bookstore</h1>
    <h1 class="title">Available Books</h1>

    <form method="get" action="index.php" class="container">
        <input type="text" name="search" class="search-bar" placeholder="Search..." value="<?= htmlspecialchars($search_query) ?>">
        
        <select name="category" class="select-control">
            <option value="0">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= $category['id'] == $category_filter ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="price_min" placeholder="Min Price" class="form-control" value="<?= htmlspecialchars($price_min) ?>">
        <input type="number" name="price_max" placeholder="Max Price" class="form-control" value="<?= htmlspecialchars($price_max) ?>">
        
        <button type="submit" class="btn">Filter</button>
    </form>

    <div class="book-list container">
        <a href="admin/manage_book.php">Manage Books</a>
        <a href="cart.php">View Cart (<?= count($_SESSION['cart']) ?>)</a>
        
        <?php foreach ($books as $book): ?>
            <div class="book">
                <div class="book_info">
                    <h2 class="subtitle"><?= htmlspecialchars($book['title']) ?></h2>
                    <p>Authore: <?= htmlspecialchars($book['author']) ?></p>
                    <p>Price: $<?= htmlspecialchars($book['price']) ?></p>
                    <p><?= htmlspecialchars($book['description']) ?></p>
                    <img src="uploads/<?= htmlspecialchars($book['cover_image']) ?>" class="book-image" alt="<?= htmlspecialchars($book['title']) ?>" width="200">
                </div>
                <div class="cart">
                    <form method="POST" action="index.php" class="add_to_cart">
                        <label for="quantity">Quantity:</label>
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                        <input type="number" name="quantity" value="1" min="1" id="quantity">
                        <button type="submit" class="btn cart-btn" name="add_to_cart">Add to Cart</button>
                    </form>

                    <h3>Reviews</h3>
                    <?php
                    $review_stmt = $conn->prepare("SELECT r.rating, r.comment, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.book_id = ?");
                    $review_stmt->bind_param("i", $book['id']);
                    $review_stmt->execute();
                    $reviews = $review_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    ?>
                    <?php if ($reviews): ?>
                        <ul>
                            <?php foreach ($reviews as $review): ?>
                                <li>
                                    <strong><?= htmlspecialchars($review['username']) ?>:</strong>
                                    <?= htmlspecialchars($review['rating']) ?> Stars - <?= htmlspecialchars($review['comment']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No reviews yet.</p>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <h3>Add a Review</h3>
                        <form method="POST" action="index.php">
                            <input type="hidden" class="review-input" name="book_id" value="<?= $book['id'] ?>"><br>
                            <label for="rating">Rating:</label><br>
                            <select name="rating" required><br>
                                <option value="1">1 Star</option>
                                <option value="2">2 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="5">5 Stars</option>
                            </select><br>
                            <textarea name="comment" placeholder="Write your review..." required></textarea><br>
                            <button type="submit" name="submit_review" class="btn">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <p>Please <a href="login.php">log in</a> To Add a Review.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
