<?php
require_once __DIR__.'/config/database.php';
$complaint = null; $history = [];
if (!empty($_GET['complaint_code'])) {
    $stmt = $pdo->prepare("SELECT c.*, u.name AS citizen_name, d.department_name
                           FROM complaints c
                           LEFT JOIN users u ON c.user_id=u.id
                           LEFT JOIN complaint_assignment ca ON c.id=ca.complaint_id
                           LEFT JOIN departments d ON ca.department_id=d.id
                           WHERE c.complaint_code=?");
    $stmt->execute([trim($_GET['complaint_code'])]);
    $complaint = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($complaint) {
        $h = $pdo->prepare("SELECT * FROM complaint_status WHERE complaint_id=? ORDER BY update_time DESC");
        $h->execute([$complaint['id']]);
        $history = $h->fetchAll(PDO::FETCH_ASSOC);
    }
}
include __DIR__.'/includes/header.php';
?>
<div class="card-soft p-4">
  <h1 class="h3 mb-3">Track Complaint</h1>
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-6"><input name="complaint_code" class="form-control" placeholder="Enter Complaint ID" value="<?= e($_GET['complaint_code'] ?? '') ?>"></div>
    <div class="col-md-3"><button class="btn btn-dark">Search</button></div>
  </form>

  <?php if($complaint): ?>
  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card p-4 h-100">
        <h5 class="mb-3">Complaint Details</h5>
        <p class="mb-1"><strong>ID:</strong> <?= e($complaint['complaint_code']) ?></p>
        <p class="mb-1"><strong>Citizen:</strong> <?= e($complaint['citizen_name']) ?></p>
        <p class="mb-1"><strong>Category:</strong> <?= e($complaint['category']) ?></p>
        <p class="mb-1"><strong>Priority:</strong> <?= e($complaint['priority']) ?></p>
        <p class="mb-1"><strong>Department:</strong> <?= e($complaint['department_name']) ?></p>
        <p class="mb-1"><strong>Location:</strong> <?= e($complaint['location']) ?> <a target="_blank" href="https://www.google.com/maps?q=<?= urlencode($complaint['location']) ?>">Open Map</a></p>
        <p class="mb-1"><strong>Status:</strong> <?= e($complaint['status']) ?></p>
        <p class="mb-0"><strong>Description:</strong> <?= e($complaint['description']) ?></p>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card p-4 h-100">
        <h5 class="mb-3">Progress Timeline</h5>
        <div class="timeline">
          <?php foreach($history as $item): ?>
            <div class="timeline-item">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                  <strong><?= e($item['status']) ?></strong>
                  <div><?= e($item['notes']) ?></div>
                </div>
                <small class="text-muted"><?= e($item['update_time']) ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php elseif(isset($_GET['complaint_code'])): ?>
    <div class="alert alert-danger">No complaint found with this ID.</div>
  <?php endif; ?>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>