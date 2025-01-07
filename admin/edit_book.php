<?php
session_start();
require '../includes/db.php'; 

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_book.php'); 
    exit;
}

$book_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    header('Location: manage_book.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars(trim($_POST['title']));
    $author = htmlspecialchars(trim($_POST['author']));
    $price = htmlspecialchars(trim($_POST['price']));
    $description = htmlspecialchars(trim($_POST['description']));
    
    $cover_image = $book['cover_image']; 
    if ($_FILES['cover_image']['name']) {
        $target_dir = "../uploads/"; 
        $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
        
        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
            $cover_image = htmlspecialchars(basename($_FILES["cover_image"]["name"]));
        } else {
            echo "Erreur lors du téléchargement du fichier.";
        }
    }
    $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, price = ?, description = ?, cover_image = ? WHERE id = ?");
    $stmt->bind_param("ssdsii", $title, $author, $price, $description, $cover_image, $book_id);
    
    if ($stmt->execute()) {
        header('Location: manage_book.php'); 
        exit;
    } else {
        $error_message = "error on book update : " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>Edit Book</title>
</head>
<body>
    <h1 class="title">Edit Book</h1>
    <?php if (isset($error_message)): ?>
        <p style="color:red;"><?= $error_message ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="form">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" name="title" class="form-control" id="title" value="<?= htmlspecialchars($book['title']) ?>" required>
        </div>
        <div class="form-group">
            <label for="author">Author:</label>
            <input type="text" name="author" class="form-control" id="author" value="<?= htmlspecialchars($book['author']) ?>" required>
        </div>
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" class="form-control" step="0.01" name="price" id="price" value="<?= htmlspecialchars($book['price']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description" id="description" class="form-control" required><?= htmlspecialchars($book['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="cover_image">Cover Image:</label>
            <input type="file" name="cover_image" id="cover_image">
            <p>Current Image: <img src="../uploads/?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" width="100"></p>
            <button type="submit" class="btn">Update Book</button>
        </div>
    </form>

</body>
</html>
