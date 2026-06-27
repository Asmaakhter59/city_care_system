<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_admin.php';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM departments WHERE id=?")
        ->execute([intval($_GET['delete'])]);

    header('Location: departments.php');
    exit;
}

$edit = null;

if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM departments WHERE id=?");
    $s->execute([intval($_GET['edit'])]);
    $edit = $s->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_name = trim($_POST['department_name']);
    $officer_name    = trim($_POST['officer_name']);

    if (!empty($_POST['id'])) {
        $pdo->prepare("
            UPDATE departments
            SET department_name = ?, officer_name = ?
            WHERE id = ?
        ")->execute([
            $department_name,
            $officer_name,
            intval($_POST['id'])
        ]);
    } else {
        $pdo->prepare("
            INSERT INTO departments (department_name, officer_name)
            VALUES (?, ?)
        ")->execute([
            $department_name,
            $officer_name
        ]);
    }

    header('Location: departments.php');
    exit;
}

$rows = $pdo->query("
    SELECT *
    FROM departments
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';

?>

<div class="row g-4">

    <div class="col-lg-4">
        <div class="card-soft p-4">

            <h2 class="h5 mb-3">
                <?= $edit ? 'Edit Department' : 'Add Department' ?>
            </h2>

            <form method="post">

                <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">

                <div class="mb-3">
                    <input
                        name="department_name"
                        class="form-control"
                        placeholder="Department Name"
                        value="<?= e($edit['department_name'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <input
                        name="officer_name"
                        class="form-control"
                        placeholder="Officer Name"
                        value="<?= e($edit['officer_name'] ?? '') ?>"
                        required
                    >
                </div>

                <button class="btn btn-dark">
                    <?= $edit ? 'Update' : 'Save' ?>
                </button>

            </form>

        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-soft p-4">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department</th>
                        <th>Officer</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= e($row['department_name']) ?></td>
                            <td><?= e($row['officer_name']) ?></td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                                    Edit
                                </a>

                                <a href="?delete=<?= $row['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete department?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>