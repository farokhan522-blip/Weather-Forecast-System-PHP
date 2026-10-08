<?php
require 'connection.php';
session_start();

$conn = connect();
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $query = "SELECT * FROM user WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION["user_id"] = $row["user_id"];
        $_SESSION["name"] = $row["name"];
        $_SESSION["role"] = $row["role"];

        header("Location: home.php");
        exit();
    } else {
        $msg = "❌ Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Weather Forecast</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('logsign.webp') no-repeat center center fixed;
            background-size: cover;
            backdrop-filter: ;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.92);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 0 25px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 400px;
        }

        .login-card h2 {
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

        .signup-text {
            text-align: center;
            margin-top: 15px;
            font-size: 15px;
        }

        .signup-text a {
            text-decoration: none;
            color: #0077be;
            font-weight: bold;
        }

        .signup-text a:hover {
            text-decoration: underline;
        }

        .alert {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>🌦️ Weather App Login</h2>
        <?php if ($msg): ?>
            <div class="alert alert-danger"><?= $msg ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">📧 Email</label>
                <input type="email" name="email" class="form-control" id="email" required placeholder="Enter your email">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">🔑 Password</label>
                <input type="password" name="password" class="form-control" id="password" required placeholder="Enter your password">
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <div class="signup-text">
            Don’t have an account? <a href="signup.php">Sign up</a>
        </div>
    </div>
</body>
</html>