<?php

require_once __DIR__ . '/../config/database.php';

$message = '';
$type    = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = trim($_POST['password']);

    $check = $pdo->prepare("
        SELECT id 
        FROM users 
        WHERE email = ?
    ");
    $check->execute([$email]);

    if ($check->fetch()) {
        $message = 'Email already exists.';
        $type    = 'danger';
    } else {

        $pdo->prepare("
            INSERT INTO users (name, email, phone, password, role)
            VALUES (?, ?, ?, ?, 'citizen')
        ")->execute([
            $name,
            $email,
            $phone,
            hash('sha256', $password)
        ]);

        $message = 'Registration successful. Please login now.';
    }
}

include __DIR__ . '/../includes/header.php';

?>

<div class="row justify-content-center">
    <div class="col-lg-6">

        <div class="card-soft p-4">

            <h1 class="h3 mb-3">Citizen Registration</h1>

            <?php if ($message): ?>
                <div class="alert alert-<?= $type ?>">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <div class="mb-3">
                    <input
                        name="name"
                        class="form-control"
                        placeholder="Full Name"
                        required
                    >
                </div>

                <div class="mb-3">
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Email"
                        required
                    >
                </div>

                <div class="mb-3">
                    <input
                        name="phone"
                        class="form-control"
                        placeholder="Phone"
                        required
                    >
                </div>

                <div class="mb-3">
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Password"
                        required
                    >
                </div>

                <button class="btn btn-dark w-100">
                    Register
                </button>

            </form>

        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>