<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_admin.php';

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$where  = " WHERE 1=1 ";
$params = [];

if ($from !== '') {
    $where .= " AND DATE(submit_date) >= ? ";
    $params[] = $from;
}

if ($to !== '') {
    $where .= " AND DATE(submit_date) <= ? ";
    $params[] = $to;
}

$q1 = $pdo->prepare("
    SELECT 
        DATE_FORMAT(submit_date, '%Y-%m') AS month_label,
        COUNT(*) AS total
    FROM complaints
    $where
    GROUP BY DATE_FORMAT(submit_date, '%Y-%m')
    ORDER BY month_label DESC
");
$q1->execute($params);
$monthly = $q1->fetchAll(PDO::FETCH_ASSOC);

$q2 = $pdo->prepare("
    SELECT 
        category,
        COUNT(*) AS total
    FROM complaints
    $where
    GROUP BY category
    ORDER BY total DESC
");
$q2->execute($params);
$category = $q2->fetchAll(PDO::FETCH_ASSOC);

$q3 = $pdo->prepare("
    SELECT 
        priority,
        COUNT(*) AS total
    FROM complaints
    $where
    GROUP BY priority
    ORDER BY FIELD(priority, 'High', 'Medium', 'Low')
");
$q3->execute($params);
$priority = $q3->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';

?>

<div class="card-soft p-4 mb-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Reports & Analytics</h1>
        <button class="btn btn-dark" onclick="window.print()">Print</button>
    </div>

    <form method="get" class="row g-3">

        <div class="col-md-3">
            <input
                type="date"
                name="from"
                value="<?= e($from) ?>"
                class="form-control"
            >
        </div>

        <div class="col-md-3">
            <input
                type="date"
                name="to"
                value="<?= e($to) ?>"
                class="form-control"
            >
        </div>

        <div class="col-md-3">
            <button class="btn btn-primary">Filter</button>
            <a href="reports.php" class="btn btn-secondary">Reset</a>
        </div>

    </form>

</div>

<div class="row g-4">

    <div class="col-lg-4">
        <div class="card-soft p-4">
            <h2 class="h5 mb-3">Monthly Statistics</h2>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($monthly as $row): ?>
                        <tr>
                            <td><?= e($row['month_label']) ?></td>
                            <td><?= e($row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-soft p-4">
            <h2 class="h5 mb-3">Category Report</h2>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($category as $row): ?>
                        <tr>
                            <td><?= e($row['category']) ?></td>
                            <td><?= e($row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-soft p-4">
            <h2 class="h5 mb-3">Priority Report</h2>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($priority as $row): ?>
                        <tr>
                            <td><?= e($row['priority']) ?></td>
                            <td><?= e($row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>