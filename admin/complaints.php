<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../includes/helpers.php';

$departments = $pdo->query("
    SELECT *
    FROM departments
    ORDER BY department_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $complaint_id  = intval($_POST['complaint_id']);
    $department_id = intval($_POST['department_id']);
    $status        = trim($_POST['status']);
    $priority      = trim($_POST['priority']);
    $notes         = trim($_POST['notes']);

    $userStmt = $pdo->prepare("
        SELECT user_id, complaint_code
        FROM complaints
        WHERE id = ?
    ");
    $userStmt->execute([$complaint_id]);
    $meta = $userStmt->fetch(PDO::FETCH_ASSOC);

    $exists = $pdo->prepare("
        SELECT id
        FROM complaint_assignment
        WHERE complaint_id = ?
    ");
    $exists->execute([$complaint_id]);

    if ($exists->fetch()) {
        $pdo->prepare("
            UPDATE complaint_assignment
            SET department_id = ?, assigned_date = NOW()
            WHERE complaint_id = ?
        ")->execute([$department_id, $complaint_id]);
    } else {
        $pdo->prepare("
            INSERT INTO complaint_assignment (complaint_id, department_id, assigned_date)
            VALUES (?, ?, NOW())
        ")->execute([$complaint_id, $department_id]);
    }

    $pdo->prepare("
        UPDATE complaints
        SET status = ?, priority = ?
        WHERE id = ?
    ")->execute([$status, $priority, $complaint_id]);

    $pdo->prepare("
        INSERT INTO complaint_status (complaint_id, status, notes, update_time)
        VALUES (?, ?, ?, NOW())
    ")->execute([$complaint_id, $status, $notes]);

    if ($meta) {
        create_notification(
            $pdo,
            $complaint_id,
            $meta['user_id'],
            'Complaint Update',
            'Your complaint ' . $meta['complaint_code'] . ' is now marked as ' . $status . '.'
        );
    }

    header('Location: complaints.php');
    exit;
}

$filterStatus   = $_GET['status'] ?? '';
$filterPriority = $_GET['priority'] ?? '';

$sql = "
    SELECT 
        c.*,
        u.name AS citizen_name,
        d.department_name,
        ca.department_id
    FROM complaints c
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN complaint_assignment ca ON c.id = ca.complaint_id
    LEFT JOIN departments d ON ca.department_id = d.id
    WHERE 1=1
";

$params = [];

if ($filterStatus !== '') {
    $sql .= " AND c.status = ?";
    $params[] = $filterStatus;
}

if ($filterPriority !== '') {
    $sql .= " AND c.priority = ?";
    $params[] = $filterPriority;
}

$sql .= " ORDER BY c.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';

?>

<div class="card-soft p-4">

    <h1 class="h3 mb-3">Complaint Management</h1>

    <form method="get" class="row g-3 mb-4">

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <?php foreach (['Pending','Under Investigation','Resolved','Rejected'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>>
                        <?= $s ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <select name="priority" class="form-select">
                <option value="">All Priority</option>
                <?php foreach (['Low','Medium','High'] as $p): ?>
                    <option value="<?= $p ?>" <?= $filterPriority === $p ? 'selected' : '' ?>>
                        <?= $p ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <button class="btn btn-dark">Filter</button>
            <a href="complaints.php" class="btn btn-secondary">Reset</a>
        </div>

    </form>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th>ID</th>
                <th>Citizen</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Location</th>
                <th>Status</th>
                <th>Department</th>
                <th>Map</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['complaint_code']) ?></td>
                    <td><?= e($row['citizen_name']) ?></td>
                    <td><?= e($row['category']) ?></td>
                    <td><?= e($row['priority']) ?></td>
                    <td><?= e($row['location']) ?></td>
                    <td><?= e($row['status']) ?></td>
                    <td><?= e($row['department_name']) ?></td>

                    <td>
                        <a target="_blank" class="btn btn-sm btn-outline-dark"
                           href="https://www.google.com/maps?q=<?= urlencode($row['location']) ?>">
                            Map
                        </a>
                    </td>

                    <td>
                        <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#m<?= $row['id'] ?>">
                            Manage
                        </button>
                    </td>
                </tr>

                <!-- Modal -->
                <div class="modal fade" id="m<?= $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Manage Complaint <?= e($row['complaint_code']) ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <form method="post">

                                <div class="modal-body">

                                    <input type="hidden" name="complaint_id" value="<?= $row['id'] ?>">

                                    <p>
                                        <strong>Description:</strong>
                                        <?= e($row['description']) ?>
                                    </p>

                                    <div class="mb-3">
                                        <label class="form-label">Assign Department</label>
                                        <select name="department_id" class="form-select" required>
                                            <?php foreach ($departments as $d): ?>
                                                <option value="<?= $d['id'] ?>"
                                                    <?= $row['department_id'] == $d['id'] ? 'selected' : '' ?>>
                                                    <?= e($d['department_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="row">

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select" required>
                                                <?php foreach (['Pending','Under Investigation','Resolved','Rejected'] as $s): ?>
                                                    <option value="<?= $s ?>"
                                                        <?= $row['status'] === $s ? 'selected' : '' ?>>
                                                        <?= $s ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Priority</label>
                                            <select name="priority" class="form-select" required>
                                                <?php foreach (['Low','Medium','High'] as $p): ?>
                                                    <option value="<?= $p ?>"
                                                        <?= $row['priority'] === $p ? 'selected' : '' ?>>
                                                        <?= $p ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Investigation Notes</label>
                                        <textarea name="notes" class="form-control" rows="4" required></textarea>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-dark">Save Changes</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>