<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_user.php';

$userId = $_SESSION['user_id'];

$stats = [
    'total'    => $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id=?"),
    'pending'  => $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id=? AND status='Pending'"),
    'resolved' => $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id=? AND status='Resolved'")
];

foreach ($stats as $k => $stmt) {
    $stmt->execute([$userId]);
    $stats[$k] = $stmt->fetchColumn();
}

$rows = $pdo->prepare("SELECT * FROM complaints WHERE user_id=? ORDER BY id DESC");
$rows->execute([$userId]);
$complaints = $rows->fetchAll(PDO::FETCH_ASSOC);

$noti = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 8");
$noti->execute([$userId]);
$notifications = $noti->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Citizen Dashboard</h1>
        <p class="text-muted mb-0">
            Welcome, <?= e($_SESSION['user_name']) ?>
        </p>
    </div>

    <a href="logout.php" class="btn btn-outline-danger">Logout</a>
</div>

<div class="row g-3 mb-4">

    <div class="col-md-4">
        <div class="card-soft p-4 text-center">
            <div class="metric-number"><?= e($stats['total']) ?></div>
            <div class="fw-bold text-secondary">Total Complaints</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-soft p-4 text-center">
            <div class="metric-number"><?= e($stats['pending']) ?></div>
            <div class="fw-bold text-secondary">Pending Cases</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-soft p-4 text-center">
            <div class="metric-number"><?= e($stats['resolved']) ?></div>
            <div class="fw-bold text-secondary">Resolved Cases</div>
        </div>
    </div>

</div>

<div class="row g-4">

    <div class="col-lg-8">
        <div class="card-soft p-4 mb-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">My Complaints</h2>
                <a href="submit_complaint.php" class="btn btn-dark">Submit Complaint</a>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($complaints as $row): ?>
                        <tr>
                            <td><?= e($row['complaint_code']) ?></td>
                            <td><?= e($row['category']) ?></td>
                            <td><?= e($row['priority']) ?></td>
                            <td><?= e($row['location']) ?></td>
                            <td><?= e($row['status']) ?></td>
                            <td><?= e($row['submit_date']) ?></td>
                            <td>
                                <a href="history.php?complaint_id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-soft p-4">
            <h2 class="h5 mb-3">Notifications</h2>

            <?php foreach ($notifications as $n): ?>
                <div class="border rounded-4 p-3 mb-3 hover-lift">
                    <div class="fw-bold"><?= e($n['title']) ?></div>
                    <div><?= e($n['message']) ?></div>
                    <small class="text-muted"><?= e($n['created_at']) ?></small>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>