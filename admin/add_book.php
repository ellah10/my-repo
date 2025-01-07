<?php
session_start();
require '../includes/db.php'; 


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars(trim($_POST['title']));
    $author = htmlspecialchars(trim($_POST['author']));
    $price = htmlspecialchars(trim($_POST['price']));
    $description = htmlspecialchars(trim($_POST['description']));
    $category_id = htmlspecialchars(trim($_POST['category_id']));

    if ($_FILES['cover_image']['name']) {
        $target_dir = "../uploads/images/";
        $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
        move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file);
        $cover_image = htmlspecialchars(basename($_FILES["cover_image"]["name"]));
    } else {
        $cover_image = null; 
    }

    $stmt = $conn->prepare("INSERT INTO books (title, author, price, description, cover_image, category_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssi", $title, $author, $price, $description, $cover_image, $category_id);

    if ($stmt->execute()) {
        header('Location: manage_book.php'); 
        exit;
    } else {
        $error_message = "Error adding book: " . $stmt->error;
    }
}

$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>Add Book</title>
</head>
<body>
    
    <h1 class="title">Add New Book</h1>
    <?php if (isset($error_message)): ?>
        <p style="color:red;"><?= $error_message ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form container">
        <div class="form-group">
            <label for="title" class="label-control">Title:</label><br>
            <input type="text" class="form-control" name="title" id="title" required><br>
        </div>
        <div class="form-group">
            <label for="author" class="label-control">Author:</label><br>
            <input type="text" class="form-control" name="author" id="author" required><br>
        </div>
        <div class="form-group">
            <label for="price" class="label-control">Price:</label><br>
            <input type="number" class="form-control" step="0.01" name="price" id="price" required><br>
        </div>
        <div class="form-group">
            <label for="description" class="label-control">Description:</label><br>
            <textarea name="description" class="text" id="description" required></textarea><br>
        </div>
        <div class="form-group">
            <label for="category_id" class="label-control">Category:</label><br>
            <select name="category_id" class="label-control" id="category_id" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select><br>
        </div>
        <div class="form-group">
            <label for="cover_image" class="label-control">Cover Image:</label><br>
            <input type="file"  name="cover_image" id="cover_image" required><br>
            <button class="btn" type="submit">Add Book</button>
            <a href="manage_book.php" class="back-btn">Back to Manage Books</a>
        </div>
        
    </form>
</body>
</html>
