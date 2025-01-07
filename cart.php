<?php
session_start();
require 'includes/db.php'; // Include the database connection

// Initialize the cart session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle updating cart quantities
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $book_id => $quantity) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$book_id]); // Remove book from cart if quantity is 0 or less
        } else {
            $_SESSION['cart'][$book_id] = $quantity; // Update quantity
        }
    }
    header('Location: cart.php'); // Redirect to the cart page
    exit;
}

// Fetch cart items details
$cart_items = [];
if (!empty($_SESSION['cart'])) {
    $book_ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = $conn->prepare("SELECT * FROM books WHERE id IN ($book_ids)");
    $stmt->execute();
    $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Your Cart</title>
</head>
<body>
    <h1 class="title">Your Shopping Cart</h1>
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty. <a href="index.php">Continue Shopping</a></p>
    <?php else: ?>
        <form method="POST" action="cart.php">
            <table border="1">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
                <?php $total = 0; ?>
                <?php foreach ($cart_items as $book): ?>
                    <?php $quantity = $_SESSION['cart'][$book['id']]; ?>
                    <?php $subtotal = $book['price'] * $quantity; ?>
                    <?php $total += $subtotal; ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td>$<?= number_format($book['price'], 2) ?></td>
                        <td>
                            <input type="number" name="quantities[<?= $book['id'] ?>]" value="<?= $quantity ?>" min="0">
                        </td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <p class="total"><strong>Total: $<?= number_format($total, 2) ?></strong></p>
            <button type="submit" class="btn" name="update_cart">Update Cart</button>
        </form>
        <a href="checkout.php" class="return-btn">Proceed to Checkout</a>
        <a href="index.php" class="return-btn">Continue Shopping</a>
    <?php endif; ?>


    <script src="/js/cart.js"></script>
</body>
</html>
