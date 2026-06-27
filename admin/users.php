<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_admin.php';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM users WHERE id=?")
        ->execute([intval($_GET['delete'])]);

    header('Location: users.php');
    exit;
}

$rows = $pdo->query("
    SELECT *
    FROM users
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';

?>

<div class="card-soft p-4">

    <h1 class="h3 mb-3">User Management</h1>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= e($row['name']) ?></td>
                    <td><?= e($row['email']) ?></td>
                    <td><?= e($row['phone']) ?></td>
                    <td>
                        <a href="?delete=<?= $row['id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete user?')">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>