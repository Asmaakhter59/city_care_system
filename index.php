<?php

require_once __DIR__ . '/config/database.php';

$stats = [
    'complaints'  => $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn(),
    'active'      => $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('Pending','Under Investigation')")->fetchColumn(),
    'resolved'    => $pdo->query("SELECT COUNT(*) FROM complaints WHERE status='Resolved'")->fetchColumn(),
    'departments' => $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn()
];

$latest = $pdo->query("
    SELECT 
        c.*,
        u.name AS citizen_name
    FROM complaints c
    LEFT JOIN users u ON c.user_id = u.id
    ORDER BY c.id DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';

?>

<section class="hero p-4 p-lg-5 mb-4">
    <div class="row align-items-center g-4">

        <div class="col-lg-6">
            <span class="badge bg-light text-dark px-3 py-2 mb-3">
                City Care Complaint Management
            </span>

            <h1 class="display-5 fw-bold mb-3">
                Report city problems faster and follow each update from one smart platform.
            </h1>

            <p class="lead opacity-75 mb-4">
                Citizens can submit complaints with location and image, track progress by complaint ID,
                and monitor actions taken by city departments.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?= $base_url ?>/user/register.php" class="btn btn-light btn-lg">
                    Create Account
                </a>

                <a href="<?= $base_url ?>/track.php" class="btn btn-warning btn-lg">
                    Track Complaint
                </a>
            </div>
        </div>

        <div class="col-lg-6">
            <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">

                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1200&auto=format&fit=crop" class="d-block w-100" alt="">
                    </div>

                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1494526585095-c41746248156?q=80&w=1200&auto=format&fit=crop" class="d-block w-100" alt="">
                    </div>

                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1473448912268-2022ce9509d8?q=80&w=1200&auto=format&fit=crop" class="d-block w-100" alt="">
                    </div>
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>

                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>

            </div>
        </div>

    </div>
</section>

<section class="mb-4">
    <div class="row g-3">

        <div class="col-md-3">
            <div class="card-soft p-4 metric-card text-center">
                <div class="metric-number"><?= e($stats['complaints']) ?></div>
                <div class="fw-bold text-secondary">Total Complaints</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-soft p-4 metric-card text-center">
                <div class="metric-number"><?= e($stats['active']) ?></div>
                <div class="fw-bold text-secondary">Active Issues</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-soft p-4 metric-card text-center">
                <div class="metric-number"><?= e($stats['resolved']) ?></div>
                <div class="fw-bold text-secondary">Resolved Cases</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-soft p-4 metric-card text-center">
                <div class="metric-number"><?= e($stats['departments']) ?></div>
                <div class="fw-bold text-secondary">Departments</div>
            </div>
        </div>

    </div>
</section>

<section class="mb-4">
    <div class="row g-4">

        <div class="col-md-3">
            <div class="card-soft p-4 h-100 feature-card">
                <div class="icon-pill mb-3">📝</div>
                <h5>Submit Complaint</h5>
                <p class="text-muted mb-0">
                    Citizens can submit issues with category, description, image, location and priority.
                </p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-soft p-4 h-100 feature-card">
                <div class="icon-pill mb-3">🔎</div>
                <h5>Track Status</h5>
                <p class="text-muted mb-0">
                    Search by complaint ID and view full status timeline from submission to resolution.
                </p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-soft p-4 h-100 feature-card">
                <div class="icon-pill mb-3">🏢</div>
                <h5>Department Control</h5>
                <p class="text-muted mb-0">
                    Admin assigns cases to departments and updates progress with investigation notes.
                </p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-soft p-4 h-100 feature-card">
                <div class="icon-pill mb-3">📊</div>
                <h5>Reports & Charts</h5>
                <p class="text-muted mb-0">
                    Monthly charts, category summaries, department workload and print-ready reports.
                </p>
            </div>
        </div>

    </div>
</section>

<section class="card-soft p-4 mb-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1">Latest Complaints</h2>
            <p class="text-muted mb-0">Recently submitted city issues.</p>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Citizen</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Location</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($latest as $row): ?>
                <tr>
                    <td><?= e($row['complaint_code']) ?></td>
                    <td><?= e($row['citizen_name']) ?></td>
                    <td><?= e($row['category']) ?></td>
                    <td><?= e($row['priority']) ?></td>
                    <td><?= e($row['location']) ?></td>
                    <td><?= e($row['status']) ?></td>
                    <td><?= e($row['submit_date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</section>

<?php include __DIR__ . '/includes/footer.php'; ?>