<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['category_name'])) {
            throw new Exception('Category name is required');
        }
        
        $categoryName = $_POST['category_name'];
        createCategory($categoryName);
        $success = "Category '$categoryName' created successfully";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle category deletion
if (isset($_POST['delete_category'])) {
    try {
        $categoryName = $_POST['delete_category'];
        deleteCategory($categoryName);
        $success = "Category '$categoryName' deleted successfully";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - ReadHub</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ReadHub">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/svg+xml" href="assets/img/readhub-logo.svg">
    <link rel="apple-touch-icon" href="assets/img/readhub-icon-192.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Add New Category
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="category_name" name="category_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Category</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Existing Categories
                    </div>
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                            <p class="text-muted">No categories found.</p>
                        <?php else: ?>
                            <div class="list-group">                                <?php foreach ($categories as $category): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <a href="category.php?name=<?php echo urlencode($category); ?>" 
                                           class="text-decoration-none flex-grow-1">
                                            <?php echo htmlspecialchars($category); ?>
                                            <span class="badge bg-primary rounded-pill ms-2">
                                                <?php 
                                                $files = glob(CATEGORIES_PATH . '/' . $category . '/*.pdf');
                                                echo count($files);
                                                ?>
                                            </span>
                                        </a>
                                        <form method="POST" class="d-inline ms-2" onsubmit="return confirm('Are you sure you want to delete this category? All PDFs in this category will be permanently deleted.');">
                                            <input type="hidden" name="delete_category" value="<?php echo htmlspecialchars($category); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
