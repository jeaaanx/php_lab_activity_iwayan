<?php
require_once 'dbcon.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php?msg=invalid");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, name, email FROM users WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: index.php?msg=notfound");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $email === '') {
        $error = 'Please fill in all fields.';
    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($name) > 255 ||
        strlen($email) > 255
    ) {
        $error = 'Please enter valid information.';
    } else {
        $stmt = $conn->prepare(
            "UPDATE users SET name = ?, email = ? WHERE id = ?"
        );
        $stmt->bind_param("ssi", $name, $email, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?msg=updated");
        exit;
    }

    $user['name'] = $name;
    $user['email'] = $email;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit User</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f8f9fa;
        }

        .edit-container {
            max-width: 650px;
            margin: 50px auto;
            padding: 25px;
        }
    </style>
</head>
<body>

<div class="container edit-container">
    <h2 class="mb-4">Edit User</h2>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit.php?id=<?= (int)$id ?>">

        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input
                type="text"
                name="name"
                id="name"
                class="form-control"
                maxlength="255"
                value="<?= e($user['name']) ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input
                type="email"
                name="email"
                id="email"
                class="form-control"
                maxlength="255"
                value="<?= e($user['email']) ?>"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Update User
        </button>

        <a href="index.php" class="btn btn-secondary">
            Cancel
        </a>
    </form>
</div>

</body>
</html>
<?php $conn->close(); ?>
