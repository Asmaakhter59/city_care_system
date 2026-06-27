<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_user.php';

$complaintId = intval($_GET['complaint_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT 
        c.*,
        d.department_name
    FROM complaints c
    LEFT JOIN complaint_assignment ca ON c.id = ca.complaint_id
    LEFT JOIN departments d ON ca.department_id = d.id
    WHERE c.id = ?
      AND c.user_id = ?
");
$stmt->execute([$complaintId, $_SESSION['user_id']]);
$complaint = $stmt->fetch(PDO::FETCH_ASSOC);

$history = [];

if ($complaint) {
    $h = $pdo->prepare("
        SELECT *
        FROM complaint_status
        WHERE complaint_id = ?
        ORDER BY update_time DESC
    ");
    $h->execute([$complaintId]);
    $history = $h->fetchAll(PDO::FETCH_ASSOC);
}

include __DIR__ . '/../includes/header.php';

?>

<div class="card-soft p-4">

    <?php if ($complaint): ?>
        <h1 class="h3 mb-3">Complaint Timeline</h1>

        <div class="row g-4">

            <div class="col-lg-5">
                <div class="card p-4 h-100">
                    <p class="mb-1">
                        <strong>ID:</strong> <?= e($complaint['complaint_code']) ?>
                    </p>

                    <p class="mb-1">
                        <strong>Category:</strong> <?= e($complaint['category']) ?>
                    </p>

                    <p class="mb-1">
                        <strong>Priority:</strong> <?= e($complaint['priority']) ?>
                    </p>

                    <p class="mb-1">
                        <strong>Department:</strong> <?= e($complaint['department_name']) ?>
                    </p>

                    <p class="mb-1">
                        <strong>Location:</strong> <?= e($complaint['location']) ?>
                        <a target="_blank" href="https://www.google.com/maps?q=<?= urlencode($complaint['location']) ?>">
                            Open Map
                        </a>
                    </p>

                    <p class="mb-3">
                        <strong>Status:</strong> <?= e($complaint['status']) ?>
                    </p>

                    <p class="mb-0">
                        <strong>Description:</strong> <?= e($complaint['description']) ?>
                    </p>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="timeline">
                    <?php foreach ($history as $item): ?>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <strong><?= e($item['status']) ?></strong>
                                    <div><?= e($item['notes']) ?></div>
                                </div>

                                <small class="text-muted">
                                    <?= e($item['update_time']) ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

    <?php else: ?>
        <div class="alert alert-danger">Complaint not found.</div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>