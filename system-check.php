<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

function checkPermissions($path) {
    echo "<h3>Checking permissions for: " . htmlspecialchars($path) . "</h3>";
    echo "<ul>";
    
    // Check if path exists
    if (file_exists($path)) {
        echo "<li class='text-success'>✓ Path exists</li>";
    } else {
        echo "<li class='text-danger'>✗ Path does not exist</li>";
        echo "</ul>";
        return;
    }
    
    // Check if it's readable
    if (is_readable($path)) {
        echo "<li class='text-success'>✓ Path is readable</li>";
    } else {
        echo "<li class='text-danger'>✗ Path is not readable</li>";
    }
    
    // Check if it's writable
    if (is_writable($path)) {
        echo "<li class='text-success'>✓ Path is writable</li>";
    } else {
        echo "<li class='text-danger'>✗ Path is not writable</li>";
    }
      // Get permissions in a cross-platform way
    $perms = fileperms($path);
    
    if ($perms !== false) {
        // Format permissions string
        $info = '';
        
        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ? 'x' : '-');
        
        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ? 'x' : '-');
        
        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ? 'x' : '-');
        
        echo "<li>Permissions: " . htmlspecialchars($info) . "</li>";
    }
    
    // Get file owner information in a cross-platform way
    $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($path)) : ['name' => 'N/A'];
    $group = function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($path)) : ['name' => 'N/A'];
    
    echo "<li>Owner: " . htmlspecialchars($owner['name']) . "</li>";
    echo "<li>Group: " . htmlspecialchars($group['name']) . "</li>";
    
    echo "</ul>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReadHub - System Check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>ReadHub System Check</h1>
        
        <?php
        if (!empty($_GET['file']) && !empty($_GET['category'])) {
            $category = $_GET['category'];
            $file = $_GET['file'];
            $filePath = CATEGORIES_PATH . '/' . $category . '/' . $file;
            
            echo "<h2>Checking specific PDF file</h2>";
            checkPermissions($filePath);
            
            // Try to read the first few bytes of the file
            echo "<h3>File Details:</h3>";
            echo "<ul>";
            if (file_exists($filePath)) {
                echo "<li>File size: " . filesize($filePath) . " bytes</li>";
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $filePath);
                finfo_close($finfo);
                echo "<li>MIME type: " . htmlspecialchars($mimeType) . "</li>";
                
                // Try to read first few bytes
                if ($fp = fopen($filePath, 'rb')) {
                    $content = fread($fp, 4);
                    fclose($fp);
                    echo "<li>First 4 bytes: " . bin2hex($content) . " (should start with %PDF for valid PDF files)</li>";
                } else {
                    echo "<li class='text-danger'>Could not open file for reading</li>";
                }
            }
            echo "</ul>";
        }
        ?>
        
        <h2>Directory Permissions</h2>
        <?php
        checkPermissions(CATEGORIES_PATH);
        ?>
        
        <h2>PHP Information</h2>
        <ul>
            <?php
            echo "<li>PHP version: " . phpversion() . "</li>";
            echo "<li>max_file_uploads: " . ini_get('max_file_uploads') . "</li>";
            echo "<li>upload_max_filesize: " . ini_get('upload_max_filesize') . "</li>";
            echo "<li>post_max_size: " . ini_get('post_max_size') . "</li>";
            echo "<li>memory_limit: " . ini_get('memory_limit') . "</li>";
            ?>
        </ul>
    </div>
</body>
</html>
