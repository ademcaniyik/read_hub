<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (empty($_GET['file']) || empty($_GET['category'])) {
    die('File and category parameters are required');
}

$category = $_GET['category'];
$file = $_GET['file'];
$filePath = CATEGORIES_PATH . '/' . $category . '/' . $file;

if (!file_exists($filePath)) {
    die('File not found: ' . htmlspecialchars($filePath));
}

// Get file mime type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

if ($mimeType !== 'application/pdf') {
    die('Invalid file type: ' . htmlspecialchars($mimeType));
}

// Set proper headers
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="' . basename($file) . '"');
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=3600');

// Output file
readfile($filePath);
