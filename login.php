<?php
require 'db.php';

function get_safe_value($conn, $str) {
    return mysqli_real_escape_string($conn, trim($str));
}

$msg = '';
$user_name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $user_name = get_safe_value($conn, $_POST['user_name'] ?? '');
    $email = get_safe_value($conn, $_POST['email'] ?? '');

    $sql = "SELECT * FROM login WHERE user_name='$user_name' AND email='$email'";
    $res = mysqli_query($conn, $sql);
    $count = mysqli_num_rows($res);

    if ($count > 0) {
        // Redirect to index.php on successful login
        header("Location: index.php");
        exit();
    } else {
        $msg = "<div class='alert alert-danger'>Please enter correct login details</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>  
    <div class="container">
        <h2 class="mb-4">Login Form</h2>
        <?php echo $msg; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="user_name">User Name</label>
                <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user_name); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Login</button>
            <a href="register.php" class="btn btn-secondary">Register Now</a>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
</body>
</html>