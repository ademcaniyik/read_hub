<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Test functions
function testDirectory($path) {
    $results = [];
    
    // Test directory existence
    if (!file_exists($path)) {
        $results[] = [
            'test' => 'Directory exists',
            'status' => 'error',
            'message' => "Directory does not exist: $path",
            'fix' => "mkdir('$path', 0755, true);"
        ];
        return $results;
    }
    
    // Test directory permissions
    if (!is_writable($path)) {
        $results[] = [
            'test' => 'Directory writable',
            'status' => 'error',
            'message' => "Directory is not writable: $path",
            'fix' => "chmod('$path', 0755);"
        ];
    }
    
    return $results;
}

function testPDFFile($category, $file) {
    $results = [];
    $filePath = CATEGORIES_PATH . '/' . $category . '/' . $file;
    
    // Test file existence
    if (!file_exists($filePath)) {
        $results[] = [
            'test' => 'File exists',
            'status' => 'error',
            'message' => "File does not exist: $filePath",
            'fix' => 'Please upload the file again'
        ];
        return $results;
    }
    
    // Test file readability
    if (!is_readable($filePath)) {
        $results[] = [
            'test' => 'File readable',
            'status' => 'error',
            'message' => "File is not readable: $filePath",
            'fix' => "chmod('$filePath', 0644);"
        ];
    }
    
    // Test file size
    $fileSize = filesize($filePath);
    if ($fileSize === 0) {
        $results[] = [
            'test' => 'File size',
            'status' => 'error',
            'message' => "File is empty: $filePath",
            'fix' => 'Please upload the file again'
        ];
    }
    
    // Test file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    if ($mimeType !== 'application/pdf') {
        $results[] = [
            'test' => 'File type',
            'status' => 'error',
            'message' => "Invalid file type: $mimeType (expected application/pdf)",
            'fix' => 'Please upload a valid PDF file'
        ];
    }
    
    // Test PDF header
    if ($fp = fopen($filePath, 'rb')) {
        $header = fread($fp, 4);
        fclose($fp);
        if ($header !== '%PDF') {
            $results[] = [
                'test' => 'PDF header',
                'status' => 'error',
                'message' => "File does not start with PDF header",
                'fix' => 'Please upload a valid PDF file'
            ];
        }
    }
    
    return $results;
}

// Run tests
$allResults = [];

// Test directories
$allResults['categories'] = testDirectory(CATEGORIES_PATH);
$allResults['uploads'] = testDirectory(UPLOADS_PATH);
$allResults['metadata'] = testDirectory(METADATA_PATH);

// Test specific PDF if requested
if (!empty($_GET['category']) && !empty($_GET['file'])) {
    $allResults['pdf'] = testPDFFile($_GET['category'], $_GET['file']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReadHub - Diagnostic Tests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>ReadHub Diagnostic Tests</h1>
        
        <?php foreach ($allResults as $category => $results): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title mb-0 text-capitalize">
                        <?php echo htmlspecialchars($category); ?> Tests
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($results)): ?>
                        <div class="alert alert-success">
                            All tests passed successfully!
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Test</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                        <th>How to Fix</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($result['test']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $result['status'] === 'error' ? 'danger' : 'success'; ?>">
                                                    <?php echo ucfirst($result['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($result['message']); ?></td>
                                            <td><code><?php echo htmlspecialchars($result['fix']); ?></code></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title mb-0">Test Specific PDF</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="category" placeholder="Category name" 
                               value="<?php echo htmlspecialchars($_GET['category'] ?? ''); ?>">
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="file" placeholder="PDF file name"
                               value="<?php echo htmlspecialchars($_GET['file'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Test PDF</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">PHP Environment</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>PHP Version</th>
                                <td><?php echo phpversion(); ?></td>
                            </tr>
                            <tr>
                                <th>Upload Max Filesize</th>
                                <td><?php echo ini_get('upload_max_filesize'); ?></td>
                            </tr>
                            <tr>
                                <th>Post Max Size</th>
                                <td><?php echo ini_get('post_max_size'); ?></td>
                            </tr>
                            <tr>
                                <th>Memory Limit</th>
                                <td><?php echo ini_get('memory_limit'); ?></td>
                            </tr>
                            <tr>
                                <th>Max Execution Time</th>
                                <td><?php echo ini_get('max_execution_time'); ?> seconds</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
