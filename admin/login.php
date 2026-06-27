<?php

require_once __DIR__ . '/../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = hash('sha256', trim($_POST['password']));

    $stmt = $pdo->prepare("
        SELECT *
        FROM admins
        WHERE email = ?
          AND password = ?
    ");
    $stmt->execute([$email, $password]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];

        header('Location: dashboard.php');
        exit;
    }

    $message = 'Invalid email or password.';
}

include __DIR__ . '/../includes/header.php';

?>

<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card-soft p-4">

            <h1 class="h3 mb-3">Admin Login</h1>

            <?php if ($message): ?>
                <div class="alert alert-danger">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <div class="mb-3">
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="admin@example.com"
                        required
                    >
                </div>

                <div class="mb-3">
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        value="admin123"
                        required
                    >
                </div>

                <button class="btn btn-dark w-100">
                    Login
                </button>

            </form>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>