<?php

require 'db.php';

$errors = [];
$success = '';

// Check if $conn is set and is a valid mysqli object
if (!isset($conn) || !$conn instanceof mysqli) {
    die("Database connection error. Please check db.php.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if ($user_name === '') $errors[] = "User name is required.";
    if ($email === '')     $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";

    if (!$errors) {
        // Check if user already exists
        $stmt = $conn->prepare("SELECT * FROM login WHERE user_name = ? OR email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $user_name, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $errors[] = "User name or email already registered.";
            } else {
                // Insert new user
                $stmt_insert = $conn->prepare("INSERT INTO login (user_name, email) VALUES (?, ?)");
                if ($stmt_insert) {
                    $stmt_insert->bind_param("ss", $user_name, $email);
                    if ($stmt_insert->execute()) {
                        $success = "Registration successful! You can now login.";
                        $user_name = $email = '';
                    } else {
                        $errors[] = "Registration failed: " . $conn->error;
                    }
                    $stmt_insert->close();
                } else {
                    $errors[] = "Database insert error: " . $conn->error;
                }
            }
            $stmt->close();
        } else {
            $errors[] = "Database query error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form</title>
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
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>  
    <div class="container">
        <h2 class="mb-4">Register Form</h2>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="user_name">User Name</label>
                <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user_name ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


</html></body>    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
</body>
</html>