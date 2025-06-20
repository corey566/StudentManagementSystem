<?php
$pageTitle = 'Home - Education Portal';
require_once 'includes/header.php';
?>

<div class="hero-section bg-primary text-white py-5 mb-5 rounded">
    <div class="container text-center">
        <h1 class="display-4 mb-4">Welcome to Education Portal</h1>
        <p class="lead mb-4">Your gateway to academic resources across IT, Business Management, and Biomedical departments</p>
        <?php if (!isLoggedIn()): ?>
            <div class="mt-4">
                <a href="register.php" class="btn btn-light btn-lg me-3">Get Started</a>
                <a href="login.php" class="btn btn-outline-light btn-lg">Sign In</a>
            </div>
        <?php else: ?>
            <div class="mt-4">
                <a href="dashboard.php" class="btn btn-light btn-lg me-3">Go to Dashboard</a>
                <a href="departments.php" class="btn btn-outline-light btn-lg">Browse Departments</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-laptop-code fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Information Technology</h5>
                <p class="card-text">Access web and mobile development resources including frontend, backend, and full-stack materials.</p>
                <?php if (isLoggedIn()): ?>
                    <a href="departments.php?dept=IT" class="btn btn-primary">Explore IT</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                <h5 class="card-title">Business Management</h5>
                <p class="card-text">Find resources for Marketing Management, Business Law, and Financial Accounting modules.</p>
                <?php if (isLoggedIn()): ?>
                    <a href="departments.php?dept=Business Management" class="btn btn-success">Explore Business</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-heartbeat fa-3x text-danger mb-3"></i>
                <h5 class="card-title">Biomedical</h5>
                <p class="card-text">Access resources for Human Anatomy, Biomedical Instrumentation, and Pharmacology including lab reports.</p>
                <?php if (isLoggedIn()): ?>
                    <a href="departments.php?dept=Biomedical" class="btn btn-danger">Explore Biomedical</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (isLoggedIn()): ?>
<div class="row mt-5">
    <div class="col-12">
        <h3>Quick Actions</h3>
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-upload fa-2x text-info mb-2"></i>
                        <h6>Upload Material</h6>
                        <a href="upload.php" class="btn btn-sm btn-info">Upload</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-user fa-2x text-warning mb-2"></i>
                        <h6>My Profile</h6>
                        <a href="profile.php" class="btn btn-sm btn-warning">View</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-search fa-2x text-secondary mb-2"></i>
                        <h6>Browse Materials</h6>
                        <a href="departments.php" class="btn btn-sm btn-secondary">Browse</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-tachometer-alt fa-2x text-dark mb-2"></i>
                        <h6>Dashboard</h6>
                        <a href="dashboard.php" class="btn btn-sm btn-dark">Go</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
