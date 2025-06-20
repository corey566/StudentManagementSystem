<?php
$pageTitle = 'Statistics - Education Portal';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

// Get comprehensive statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_uploads' => $pdo->query("SELECT COUNT(*) FROM uploads")->fetchColumn(),
    'approved_uploads' => $pdo->query("SELECT COUNT(*) FROM uploads WHERE approval_status = 'approved'")->fetchColumn(),
    'pending_uploads' => $pdo->query("SELECT COUNT(*) FROM uploads WHERE approval_status = 'pending'")->fetchColumn(),
    'total_downloads' => $pdo->query("SELECT COUNT(*) FROM downloads")->fetchColumn(),
    'total_comments' => $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
];

// Department statistics
$deptStats = $pdo->query("
    SELECT department, 
           COUNT(*) as upload_count,
           AVG(file_size) as avg_file_size
    FROM uploads 
    WHERE approval_status = 'approved'
    GROUP BY department
")->fetchAll();

// Popular uploads (most downloaded)
$popularUploads = $pdo->query("
    SELECT u.title, u.department, COUNT(d.id) as download_count
    FROM uploads u
    LEFT JOIN downloads d ON u.id = d.upload_id
    WHERE u.approval_status = 'approved'
    GROUP BY u.id
    ORDER BY download_count DESC
    LIMIT 10
")->fetchAll();

// Recent activity
$recentActivity = $pdo->query("
    SELECT 'upload' as activity_type, title as description, created_at, department
    FROM uploads 
    WHERE approval_status = 'approved'
    UNION ALL
    SELECT 'download' as activity_type, 
           CONCAT('Downloaded: ', u.title) as description, 
           d.downloaded_at as created_at,
           u.department
    FROM downloads d
    JOIN uploads u ON d.upload_id = u.id
    ORDER BY created_at DESC
    LIMIT 20
")->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-chart-bar"></i> Platform Statistics</h1>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Users</h5>
                            <h2><?= number_format($stats['total_users']) ?></h2>
                        </div>
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Uploads</h5>
                            <h2><?= number_format($stats['total_uploads']) ?></h2>
                        </div>
                        <i class="fas fa-upload fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Approved</h5>
                            <h2><?= number_format($stats['approved_uploads']) ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Pending</h5>
                            <h2><?= number_format($stats['pending_uploads']) ?></h2>
                        </div>
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Downloads</h5>
                            <h2><?= number_format($stats['total_downloads']) ?></h2>
                        </div>
                        <i class="fas fa-download fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Comments</h5>
                            <h2><?= number_format($stats['total_comments']) ?></h2>
                        </div>
                        <i class="fas fa-comments fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Department Statistics -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-building"></i> Department Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Uploads</th>
                                    <th>Avg File Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deptStats as $dept): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($dept['department']) ?></strong></td>
                                    <td><?= number_format($dept['upload_count']) ?></td>
                                    <td><?= formatFileSize($dept['avg_file_size']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Uploads -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-star"></i> Most Downloaded Materials</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Downloads</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popularUploads as $upload): ?>
                                <tr>
                                    <td><?= htmlspecialchars($upload['title']) ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($upload['department']) ?></span></td>
                                    <td><?= number_format($upload['download_count']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-clock"></i> Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Description</th>
                                    <th>Department</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivity as $activity): ?>
                                <tr>
                                    <td>
                                        <?php if ($activity['activity_type'] === 'upload'): ?>
                                            <span class="badge bg-success">Upload</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Download</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($activity['description']) ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($activity['department']) ?></span></td>
                                    <td><?= date('M d, Y H:i', strtotime($activity['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>