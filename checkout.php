<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$total = 0;
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Your cart is empty!";
    exit;
}
foreach ($_SESSION['cart'] as $id => $quantity) {
    $book_stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $book_stmt->bind_param("i", $id);
    $book_stmt->execute();
    $book = $book_stmt->get_result()->fetch_assoc();
    $total += $book['price'] * $quantity;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    echo "Thank you, $name! Your order has been placed successfully.";
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>checkout</title>
</head>
<body>
    
</body>
</html>

<h1 class="title">Checkout</h1>
<form method="POST" class="form">
    <div class="form-group">
        <label class="label-control">Name:</label>
        <input class="form-control" type="text" name="name" required>
    </div>
    <div class="form-group">
        <label class="label-control">Address:</label>
        <textarea class="form-control" name="address" required></textarea>
    </div>
    <div class="form-group">
        <label class="label-control">Contact:</label>
        <input class="form-control check" type="text" name="contact" required>
        <button type="submit" class="btn">Place Order ($<?= number_format($total, 2) ?>)</button>
        <a href="index.php" class="return-btn">return to book list</a>
    </div>
</form>
