<?php

require_once __DIR__ . '/../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']);
    $password = hash('sha256', trim($_POST['password']));

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE email = ?
          AND password = ?
    ");
    $stmt->execute([$email, $password]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];

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

            <h1 class="h3 mb-3">Citizen Login</h1>

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
                        required
                    >
                </div>

                <div class="mb-3">
                    <input
                        type="password"
                        name="password"
                        class="form-control"
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