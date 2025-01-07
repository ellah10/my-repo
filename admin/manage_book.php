<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$books = $conn->query("SELECT * FROM books")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>Manage Books</title>
</head>

<body>
    <h1 class="title">Manage Books</h1>
    <table>
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Price</th>
            <th>description</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($books as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td><?= htmlspecialchars($book['price']) ?></td>
                <td><?= htmlspecialchars($book['description']) ?></td>
                <td>
                    <a class="add-btn" href="edit_book.php?id=<?= $book['id'] ?>" >Edit</a>
                    <a class="del-btn" href="delete_book.php?id=<?= $book['id'] ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="add_book.php" class="return-btn" >Add New Book</a>
    <a href="../logout.php" class="return-btn">Logout</a>
    <a href="../index.php" class="return-btn">Back to Book list</a>
</body>

</html>