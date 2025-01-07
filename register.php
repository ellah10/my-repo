<?php
session_start();
require 'includes/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));
    $email = htmlspecialchars(trim($_POST['email']));

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "Username or email already exists.";
    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $email);

        if ($stmt->execute()) {
            $message = "Registration successful! You can now log in.";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Register</title>
</head>

<body>
    <h1 class="title">User Registration</h1>
    <form method="POST" class="form">
        <div class="form-group">
            <label for="username"> username</label>
            <input type="text" name="username" class="form-control" required placeholder="Username">
        </div>
        <div class="form-group">
            <label for="password" class="label-control">password</label>
            <input type="password" class="form-control" name="password" required placeholder="Password">
        </div>
        <div class="form-group">
            <label for="email" class="label-control">email</label>
            <input type="email" class="form-control" name="email" required placeholder="Email">
            <?php if ($message): ?>
                <p class="message"><?php echo $message; ?></p>
            <?php endif; ?>
            <button type="submit" class="btn register">Register</button>
            <a href="login.php">log in here</a>
        </div>
    </form>
</body>

</html>