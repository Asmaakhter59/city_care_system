<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_user.php';
require_once __DIR__ . '/../includes/helpers.php';

$categories = [
    'Road & Infrastructure',
    'Drainage & Sanitation',
    'Street Lights',
    'Waste Management',
    'Water Supply',
    'Public Parks',
    'Encroachment'
];

$priorities = ['Low', 'Medium', 'High'];

$message = '';
$type    = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category    = trim($_POST['category']);
    $priority    = trim($_POST['priority']);
    $description = trim($_POST['description']);
    $location    = trim($_POST['location']);

    $code = 'CC-' . date('Ymd') . '-' . rand(1000, 9999);
    $imageName = '';

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . '_' . preg_replace(
            '/[^A-Za-z0-9._-]/',
            '_',
            $_FILES['image']['name']
        );

        @move_uploaded_file(
            $_FILES['image']['tmp_name'],
            __DIR__ . '/../uploads/complaints/' . $imageName
        );
    }

    $pdo->prepare("
        INSERT INTO complaints (
            user_id,
            complaint_code,
            category,
            priority,
            description,
            location,
            image,
            status,
            submit_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
    ")->execute([
        $_SESSION['user_id'],
        $code,
        $category,
        $priority,
        $description,
        $location,
        $imageName
    ]);

    $complaintId = $pdo->lastInsertId();

    $pdo->prepare("
        INSERT INTO complaint_status (
            complaint_id,
            status,
            notes,
            update_time
        ) VALUES (?, ?, ?, NOW())
    ")->execute([
        $complaintId,
        'Pending',
        'Complaint submitted successfully'
    ]);

    create_notification(
        $pdo,
        $complaintId,
        $_SESSION['user_id'],
        'Complaint Submitted',
        'Your complaint ' . $code . ' has been submitted successfully.'
    );

    $message = 'Complaint submitted successfully. Your complaint ID is ' . $code;
}

include __DIR__ . '/../includes/header.php';

?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-soft p-4">

            <h1 class="h3 mb-3">Submit Complaint</h1>

            <?php if ($message): ?>
                <div class="alert alert-<?= $type ?>">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">

                <div class="mb-3">
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c) ?>"><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <select name="priority" class="form-select" required>
                        <option value="">Select Priority</option>
                        <?php foreach ($priorities as $p): ?>
                            <option value="<?= e($p) ?>"><?= e($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        placeholder="Problem Description"
                        required
                    ></textarea>
                </div>

                <div class="mb-3">
                    <input
                        name="location"
                        class="form-control"
                        placeholder="Location Details"
                        required
                    >
                </div>

                <div class="mb-3">
                    <input
                        type="file"
                        name="image"
                        class="form-control"
                    >
                </div>

                <button class="btn btn-dark">Submit Complaint</button>

            </form>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>