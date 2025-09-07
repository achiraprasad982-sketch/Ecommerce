<?php
session_start();

$msg = '';
$username = '';
$password = '';

// Simple hardcoded admin credentials (for demo; use database for real projects)
$admin_user = 'admin';
$admin_pass = 'admin123';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $admin_user && $password === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_panal.php");
        exit();
    } else {
        $msg = "<div class='alert alert-danger'>Invalid admin username or password.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Prasad Electronic</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; }
        .container { max-width: 400px; margin-top: 80px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center">Admin Login</h2>
        <?php echo $msg; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Admin Username</label>
                <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($username); ?>">
            </div>
            <div class="form-group">
                <label for="password">Admin Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
        </form>
        <a href="index.php" class="btn btn-link mt-3">Back to Home</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>