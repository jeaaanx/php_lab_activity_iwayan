
<?php
require_once 'dbcon.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$msg = $_GET['msg'] ?? '';
$alert = '';
$alertType = 'success';

$messages = [
    'added' => 'User added successfully!',
    'updated' => 'User updated successfully!',
    'deleted' => 'User deleted successfully!',
    'invalid' => 'Invalid user ID.',
    'notfound' => 'User not found.'
];

if (isset($messages[$msg])) {
    $alert = $messages[$msg];

    if (in_array($msg, ['deleted', 'invalid', 'notfound'], true)) {
        $alertType = 'danger';
    } else {
        $alertType = 'success';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $email === '') {
        $alert = 'Please fill in all fields.';
        $alertType = 'danger';
    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($name) > 255 ||
        strlen($email) > 255
    ) {
        $alert = 'Please enter valid information.';
        $alertType = 'danger';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO users (name, email) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?msg=added");
        exit;
    }
}

$result = $conn->query(
    "SELECT * FROM users ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>User Management System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            color: #333;
        }

        .page-title {
            text-align: center;
            margin: 20px 0 35px;
            font-size: 28px;
            font-weight: 400;
        }

        .section-title {
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 400;
        }

        .form-control {
            font-size: 14px;
            border-radius: 4px;
            padding: 10px;
        }

        .btn-submit {
            width: 100%;
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 9px;
            border-radius: 4px;
        }

        .btn-submit:hover {
            background-color: #0b5ed7;
            color: white;
        }

        .table {
            font-size: 13px;
            vertical-align: middle;
        }

        .table th {
            font-weight: 600;
            white-space: nowrap;
        }

        .table td {
            overflow-wrap: anywhere;
        }

        .alert {
            font-size: 13px;
            padding: 10px;
        }

        @media (max-width: 767px) {
            .page-title {
                font-size: 24px;
                margin: 20px 0 25px;
            }

            .section-title {
                margin-top: 10px;
            }

            .table {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid px-4 px-md-5 py-3">

    <h1 class="page-title">User Management System</h1>

    <div class="row g-4">

        <!-- LEFT SIDE: ADD NEW USER -->
        <div class="col-12 col-md-4 col-lg-4">

            <h2 class="section-title">Add New User</h2>

            <?php if ($alert !== ''): ?>
                <div class="alert alert-<?= e($alertType) ?>
                            alert-dismissible fade show"
                     role="alert">
                    <?= e($alert) ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Close">
                    </button>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST">

                <div class="mb-3">
                    <label for="name" class="form-label">
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control"
                        placeholder="Enter Full Name"
                        maxlength="255"
                        value="<?= e($_POST['name'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        placeholder="Enter Email Address"
                        maxlength="255"
                        value="<?= e($_POST['email'] ?? '') ?>"
                        required
                    >
                </div>

                <button type="submit" class="btn-submit">
                    Submit
                </button>

            </form>
        </div>

        <!-- RIGHT SIDE: REGISTERED USERS -->
        <div class="col-12 col-md-8 col-lg-8">

            <h2 class="section-title">Registered Users</h2>

            <div class="table-responsive">
                <table class="table table-hover">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int)$user['id'] ?></td>

                                <td><?= e($user['name']) ?></td>

                                <td><?= e($user['email']) ?></td>

                                <td><?= e($user['created_at']) ?></td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a
                                            href="edit.php?id=<?= (int)$user['id'] ?>"
                                            class="btn btn-warning btn-sm"
                                        >
                                            Edit
                                        </a>

                                        <a
                                            href="delete.php?id=<?= (int)$user['id'] ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this user?');"
                                        >
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5"
                                class="text-center text-muted py-3">
                                No data available
                            </td>
                        </tr>

                    <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$result->free();
$conn->close();
?>
