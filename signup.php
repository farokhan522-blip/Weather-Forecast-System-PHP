<?php
require 'connection.php';

$conn = connect();
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    $check = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "<div class='alert alert-warning text-center'>⚠️ Email already registered.</div>";
    } else {
        $query = "INSERT INTO user (name, email, password, role) VALUES ('$name', '$email', '$password', 'user')";
        if (mysqli_query($conn, $query)) {
            $msg = "<div class='alert alert-success text-center'>✅ Registration successful! Redirecting to <a href='login.php' class='alert-link'>Login</a> page...</div>";
            header("refresh:3;url=login.php");
        } else {
            $msg = "<div class='alert alert-danger text-center'>❌ Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - Weather App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('logsign.webp') no-repeat center center fixed;
            background-size: cover;
            backdrop-filter: blur(6px);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signup-card {
            background: rgba(255, 255, 255, 0.92);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 0 25px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 450px;
        }

        .signup-card h2 {
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            color: #0077be;
        }

        .form-control {
            border-radius: 12px;
            font-size: 16px;
        }

        .btn-primary {
            width: 100%;
            border-radius: 12px;
            font-size: 18px;
            padding: 10px;
        }

        .alert a {
            font-weight: bold;
            color: #0077be;
        }

        .login-text {
            text-align: center;
            margin-top: 15px;
            font-size: 15px;
        }

        .login-text a {
            text-decoration: none;
            color: #0077be;
            font-weight: bold;
        }

        .login-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-card">
        <h2>📝 User Signup</h2>
        <?= $msg ?>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">👤 Name</label>
                <input type="text" name="name" class="form-control" id="name" required placeholder="Your name">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">📧 Email</label>
                <input type="email" name="email" class="form-control" id="email" required placeholder="you@example.com">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">🔐 Password</label>
                <input type="password" name="password" class="form-control" id="password" required placeholder="Create a password">
            </div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>

        <div class="login-text">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>
</body>
</html>