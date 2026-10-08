<?php
require 'connection.php';
session_start();

$conn = connect();

// Access Control
if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "super_admin" && $_SESSION["role"] !== "admin")) {
    header("Location: login.php");
    exit();
}

$msg = "";

// Delete
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $allowed = ($_SESSION['role'] === 'super_admin') ? "role='admin'" : "role='user'";
    mysqli_query($conn, "DELETE FROM user WHERE user_id = $delete_id AND $allowed");
    header("Location: manageusers.php?deleted=1");
    exit();
}

// Edit
if (isset($_POST['update_user'])) {
    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $email = $_POST['edit_email'];
    mysqli_query($conn, "UPDATE user SET name='$name', email='$email' WHERE user_id=$id");
    header("Location: manageusers.php?updated=1");
    exit();
}

// Add sub-admin
if (isset($_POST['add_sub_admin']) && $_SESSION['role'] === 'super_admin') {
    $name = $_POST['subadmin_name'];
    $email = $_POST['subadmin_email'];
    $password = $_POST['subadmin_password'];

    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user WHERE role='admin'"))['total'];
    if ($count < 3) {
        mysqli_query($conn, "INSERT INTO user (name, email, password, role) VALUES ('$name', '$email', '$password', 'admin')");
        header("Location: manageusers.php?added=1");
        exit();
    } else {
        $msg = "❌ Cannot add more than 3 sub-admins.";
    }
}

// Fetch users
$role_filter = ($_SESSION['role'] === 'super_admin') ? "" : "WHERE u.role = 'user'";
$users = mysqli_query($conn, "
    SELECT 
        u.user_id, u.name, u.email, u.role,
        f.description, f.last_checked,
        l.city, l.country
    FROM user u
    LEFT JOIN forecast f ON u.user_id = f.user_id
    LEFT JOIN location l ON f.location_id = l.location_id
    $role_filter
    ORDER BY u.user_id DESC
");

$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM user WHERE user_id = $edit_id");
    $edit_user = mysqli_fetch_assoc($result);
}

if (isset($_GET['updated'])) $msg = "✅ User updated successfully.";
if (isset($_GET['added'])) $msg = "✅ Sub-admin added.";
if (isset($_GET['deleted'])) $msg = "🗑️ User deleted.";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background: url('use.jpg') no-repeat center center fixed;
        background-size: cover;
        backdrop-filter: ;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .table thead {
        background-color: #e9f5f1;
    }

    .table td, .table th {
        vertical-align: middle;
        padding: 1rem;
    }

    .table tbody tr:hover {
        background-color: #f5fcf9;
    }

    .role-badge {
        text-transform: capitalize;
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 0.8rem;
        color: white;
    }

    .role-user {
        background-color: #6c757d;
    }

    .role-admin {
        background-color: #17a2b8;
    }

    .role-super_admin {
        background-color: #28a745;
    }

    .btn-outline-secondary {
        color: white;
        border-color: white;
    }

    .btn-outline-secondary:hover {
        background-color: white;
        color: #2f776b;
        border-color: white;
    }

    .card-header.bg-primary {
        background-color: #2f776b !important;
    }

    .btn-primary {
        background-color: #2f776b !important;
        border-color: #2f776b !important;
    }

    .btn-primary:hover {
        background-color: #265f56 !important;
        border-color: #265f56 !important;
    }

    .btn-success {
        background-color: #198754 !important;
        border-color: #198754 !important;
    }

    .btn-success:hover {
        background-color: #157347 !important;
        border-color: #157347 !important;
    }

    .card {
        border-radius: 12px;
        overflow: hidden;
    }

    .btn-warning {
        padding: 4px 10px;
    }

    .btn-danger {
        padding: 4px 10px;
    }

    .text-primary {
        color: #2f776b !important;
    }

    .table td.actions-col {
        white-space: nowrap;
        width: 150px; /* Enough space for 2 buttons */
    }

    .table td.actions-col a {
        display: inline-block;
        margin-right: 5px;
        margin-bottom: 0 !important;
    }


</style>
    <script>
        function toggleAddForm() {
            const form = document.getElementById("addSubAdminForm");
            form.style.display = form.style.display === "none" ? "block" : "none";
        }
    </script>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold">👥 Manage Users</h3>
        <a href="home.php" class="btn btn-outline-secondary">⏪ Back to Home</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-info shadow-sm"> <?= $msg ?> </div>
    <?php endif; ?>

    <script>
        if (window.location.search.includes("updated=1") ||
            window.location.search.includes("added=1") ||
            window.location.search.includes("deleted=1")) {
            const url = new URL(window.location);
            url.search = ""; // Remove query params
            window.history.replaceState({}, document.title, url);
        }
    </script>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-semibold">User & Forecast Overview</div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>City</th>
                        <th>Country</th>
                        <th>Forecast</th>
                        <th>Last Checked</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge role-badge <?= $u['role'] === 'super_admin' ? 'role-super_admin' : ($u['role'] === 'admin' ? 'role-admin' : 'role-user') ?>">
                                    <?= $u['role'] ?>
                                </span>
                            </td>
                            <td><?= $u['city'] ?? '—' ?></td>
                            <td><?= $u['country'] ?? '—' ?></td>
                            <td><?= $u['description'] ?? '—' ?></td>
                            <td><?= $u['last_checked'] ?? '—' ?></td>
                            <td class="text-center actions-col">
                                <a href="?edit=<?= $u['user_id'] ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                                <a href="?delete=<?= $u['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($edit_user): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning fw-bold">✏️ Edit User</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="edit_id" value="<?= $edit_user['user_id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Name:</label>
                        <input type="text" name="edit_name" class="form-control" value="<?= $edit_user['name'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email:</label>
                        <input type="email" name="edit_email" class="form-control" value="<?= $edit_user['email'] ?>" required>
                    </div>
                    <button name="update_user" class="btn btn-success">Update User</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'super_admin'): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
                <span>➕ Add Sub-Admin</span>
                <button class="btn btn-light btn-sm" onclick="toggleAddForm()">Toggle Form</button>
            </div>
            <div class="card-body" id="addSubAdminForm" style="display: none;">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Name:</label>
                        <input type="text" name="subadmin_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email:</label>
                        <input type="email" name="subadmin_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password:</label>
                        <input type="password" name="subadmin_password" class="form-control" required>
                    </div>
                    <button name="add_sub_admin" class="btn btn-primary">Add Sub-Admin</button>
                    <p class="text-muted mt-2">You can only add up to 3 sub-admins.</p>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Auto-hide alert after 15 seconds (15000 milliseconds)
    setTimeout(function () {
        const alert = document.querySelector(".alert");
        if (alert) {
            alert.style.transition = "opacity 1s ease";
            alert.style.opacity = 0;
            setTimeout(() => alert.remove(), 1000); // Fully remove after fade out
        }
    }, 15000);
</script>


</body>
</html>