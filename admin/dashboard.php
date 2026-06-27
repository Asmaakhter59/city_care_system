<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_admin.php';

$stats = [
    'total'    => $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn(),
    'active'   => $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('Pending','Under Investigation')")->fetchColumn(),
    'resolved' => $pdo->query("SELECT COUNT(*) FROM complaints WHERE status='Resolved'")->fetchColumn(),
    'users'    => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn()
];

$monthly = $pdo->query("
    SELECT 
        DATE_FORMAT(submit_date, '%Y-%m') AS m,
        COUNT(*) AS total
    FROM complaints
    GROUP BY DATE_FORMAT(submit_date, '%Y-%m')
    ORDER BY m ASC
")->fetchAll(PDO::FETCH_ASSOC);

$category = $pdo->query("
    SELECT category, COUNT(*) AS total
    FROM complaints
    GROUP BY category
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$dept = $pdo->query("
    SELECT d.department_name, COUNT(ca.id) AS total
    FROM departments d
    LEFT JOIN complaint_assignment ca ON d.id = ca.department_id
    GROUP BY d.id
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$latest = $pdo->query("
    SELECT c.*, d.department_name
    FROM complaints c
    LEFT JOIN complaint_assignment ca ON c.id = ca.complaint_id
    LEFT JOIN departments d ON ca.department_id = d.id
    ORDER BY c.id DESC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Admin Dashboard</h1>
        <p class="text-muted mb-0">
            Welcome, <?= e($_SESSION['admin_name']) ?>
        </p>
    </div>

    <a href="logout.php" class="btn btn-outline-danger">Logout</a>
</div>

<div class="row g-3 mb-4">

    <div class="col-md-3">
        <div class="card-soft p-4 text-center">
            <div class="metric-number"><?= e($stats['total']) ?></div>
            <div class="fw-bold text-secondary">Total Complaints</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card-soft p-4 text-center">
            <div class="metric-number"><?= e($stats['active']) ?></div>
            <div class="fw-bold text-secondary">Active Complaints</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card-soft p-4 text-center">
            <div class="metric-number"><?= e($stats['resolved']) ?></div>
            <div class="fw-bold text-secondary">Resolved</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card-soft p-4 text-center">
            <div class="metric-number"><?= e($stats['users']) ?></div>
            <div class="fw-bold text-secondary">Users</div>
        </div>
    </div>

</div>

<div class="card-soft p-4 mb-4">
    <div class="d-flex flex-wrap gap-2">
        <a href="complaints.php" class="btn btn-dark">Manage Complaints</a>
        <a href="departments.php" class="btn btn-dark">Departments</a>
        <a href="users.php" class="btn btn-dark">Users</a>
        <a href="reports.php" class="btn btn-dark">Reports</a>
    </div>
</div>

<div class="row g-4 mb-4">

    <div class="col-lg-6">
        <div class="card-soft p-4">
            <h2 class="h5 mb-3">Monthly Complaints</h2>
            <div class="chart-box">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-soft p-4">
            <h2 class="h5 mb-3">Category Breakdown</h2>
            <div class="chart-box">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

</div>

<div class="row g-4 mb-4">

    <div class="col-lg-6">
        <div class="card-soft p-4">
            <h2 class="h5 mb-3">Department Workload</h2>
            <div class="chart-box">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-soft p-4">
            <h2 class="h5 mb-3">Latest Complaint Updates</h2>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Department</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($latest as $row): ?>
                        <tr>
                            <td><?= e($row['complaint_code']) ?></td>
                            <td><?= e($row['category']) ?></td>
                            <td><?= e($row['priority']) ?></td>
                            <td><?= e($row['status']) ?></td>
                            <td><?= e($row['department_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const monthlyLabels = <?= json_encode(array_column($monthly, 'm')) ?>;
const monthlyData   = <?= json_encode(array_map('intval', array_column($monthly, 'total'))) ?>;

const categoryLabels = <?= json_encode(array_column($category, 'category')) ?>;
const categoryData   = <?= json_encode(array_map('intval', array_column($category, 'total'))) ?>;

const deptLabels = <?= json_encode(array_column($dept, 'department_name')) ?>;
const deptData   = <?= json_encode(array_map('intval', array_column($dept, 'total'))) ?>;

new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Complaints',
            data: monthlyData,
            borderWidth: 3,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: categoryLabels,
        datasets: [{
            data: categoryData
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

new Chart(document.getElementById('departmentChart'), {
    type: 'bar',
    data: {
        labels: deptLabels,
        datasets: [{
            label: 'Assigned Cases',
            data: deptData,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>