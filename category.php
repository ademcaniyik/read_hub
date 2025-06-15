<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (empty($_GET['name'])) {
    header('Location: categories.php');
    exit;
}

$categoryName = $_GET['name'];
$categoryPath = CATEGORIES_PATH . '/' . $categoryName;

if (!is_dir($categoryPath)) {
    header('Location: categories.php');
    exit;
}

$pdfs = [];
$files = glob($categoryPath . '/*.pdf');
foreach ($files as $file) {
    $fileName = basename($file);
    $progress = getPDFProgress($categoryName . '/' . $fileName);
    $pdfs[] = [
        'name' => $fileName,
        'lastAccessed' => $progress['lastAccessed'],
        'currentPage' => $progress['page'],
        'size' => filesize($file)
    ];
}

// Sort PDFs by last accessed date
usort($pdfs, function($a, $b) {
    if (!$a['lastAccessed']) return 1;
    if (!$b['lastAccessed']) return -1;
    return strtotime($b['lastAccessed']) - strtotime($a['lastAccessed']);
});

// Handle PDF deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pdf'])) {
    try {
        $fileName = $_POST['delete_pdf'];
        deletePDF($categoryName, $fileName);
        $success = "PDF '$fileName' deleted successfully";
        // Refresh the file list
        $files = glob($categoryPath . '/*.pdf');
        foreach ($files as $file) {
            $fileName = basename($file);
            $progress = getPDFProgress($categoryName . '/' . $fileName);
            $pdfs[] = [
                'name' => $fileName,
                'lastAccessed' => $progress['lastAccessed'],
                'currentPage' => $progress['page'],
                'size' => filesize($file)
            ];
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($categoryName); ?> - ReadHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include 'includes/category-list.php'; ?>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo htmlspecialchars($categoryName); ?></h5>
                        <a href="upload.php?category=<?php echo urlencode($categoryName); ?>" 
                           class="btn btn-primary btn-sm">Upload PDF</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pdfs)): ?>
                            <p class="text-muted">No PDFs in this category. 
                                <a href="upload.php?category=<?php echo urlencode($categoryName); ?>">Upload one now</a>.
                            </p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($pdfs as $pdf): ?>
                                    <a href="viewer.php?category=<?php echo urlencode($categoryName); ?>&file=<?php echo urlencode($pdf['name']); ?>" 
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($pdf['name']); ?></h5>
                                            <small class="text-muted">
                                                <?php echo number_format($pdf['size'] / 1024 / 1024, 2); ?> MB
                                            </small>
                                        </div>
                                        <?php if ($pdf['lastAccessed']): ?>
                                            <p class="mb-1">
                                                Last read: page <?php echo $pdf['currentPage']; ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo date('F j, Y g:i A', strtotime($pdf['lastAccessed'])); ?>
                                                </small>
                                            </p>
                                        <?php else: ?>
                                            <p class="mb-1"><small class="text-muted">Not read yet</small></p>
                                        <?php endif; ?>
                                    </a>
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
