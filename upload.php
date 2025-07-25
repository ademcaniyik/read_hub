<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['category'])) {
            throw new Exception('Please select a category');
        }
        
        if (empty($_FILES['pdf_file'])) {
            throw new Exception('Please select a PDF file');
        }
        
        $category = $_POST['category'];
        $file = $_FILES['pdf_file'];
        
        // Validate file
        validatePDFFile($file);
          // Generate unique filename with timestamp
        $timestamp = date('Y-m-d-His');
        $fileName = sanitizeFileName(pathinfo($file['name'], PATHINFO_FILENAME)) . '-' . $timestamp . '.pdf';
        
        // Ensure category directory exists and is writable
        $categoryPath = CATEGORIES_PATH . '/' . $category;
        if (!is_dir($categoryPath)) {
            if (!@mkdir($categoryPath, 0755, true)) {
                throw new Exception('Failed to create category directory. Please check permissions.');
            }
        }
        
        // Check if directory is writable
        if (!is_writable($categoryPath)) {
            throw new Exception('Category directory is not writable. Please check permissions.');
        }
        
        $targetPath = $categoryPath . '/' . $fileName;
        
        // Check if file already exists
        if (file_exists($targetPath)) {
            throw new Exception('A file with this name already exists in the selected category');
        }
        
        // Move file to category directory
        if (!@move_uploaded_file($file['tmp_name'], $targetPath)) {
            $uploadError = error_get_last();
            throw new Exception('Failed to upload file: ' . ($uploadError['message'] ?? 'Unknown error'));
        }
        
        // Initialize progress tracking
        savePDFProgress($category . '/' . $fileName, 1);
        
        $success = 'PDF uploaded successfully';
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
    <title>Upload PDF - ReadHub</title>
    
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
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Upload PDF
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <?php if (empty($categories)): ?>
                            <div class="alert alert-warning">
                                Please <a href="categories.php">create a category</a> before uploading PDFs.
                            </div>
                        <?php else: ?>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Select Category</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Choose category...</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category); ?>">
                                                <?php echo htmlspecialchars($category); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="pdf_file" class="form-label">Select PDF File</label>
                                    <input type="file" class="form-control" id="pdf_file" name="pdf_file" 
                                           accept="application/pdf" required>
                                    <div class="form-text">Maximum file size: <?php echo MAX_FILE_SIZE / 1024 / 1024; ?>MB</div>
                                </div>
                                <button type="submit" class="btn btn-primary">Upload PDF</button>
                            </form>
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
