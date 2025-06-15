<?php
/**
 * Custom error handler function
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ];
    
    // Log error to file
    error_log(json_encode($error) . "\n", 3, BASE_PATH . '/.copilot/error.log');
    
    // Display user-friendly message in production
    if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
        return true;
    }
    
    return false;
}

/**
 * Create a new category
 */
function createCategory($categoryName) {
    $categoryPath = CATEGORIES_PATH . '/' . sanitizeFileName($categoryName);
    
    if (!is_dir($categoryPath)) {
        if (!mkdir($categoryPath, 0755, true)) {
            throw new Exception("Failed to create category directory: $categoryName");
        }
    }
    
    // Update categories metadata
    $metadataFile = METADATA_PATH . '/categories.json';
    $categories = [];
    
    if (file_exists($metadataFile)) {
        $categories = json_decode(file_get_contents($metadataFile), true) ?? [];
    }
    
    if (!in_array($categoryName, $categories)) {
        $categories[] = $categoryName;
        file_put_contents($metadataFile, json_encode($categories, JSON_PRETTY_PRINT));
    }
    
    return true;
}

/**
 * Get all categories
 */
function getCategories() {
    $metadataFile = METADATA_PATH . '/categories.json';
    
    if (file_exists($metadataFile)) {
        return json_decode(file_get_contents($metadataFile), true) ?? [];
    }
    
    return [];
}

/**
 * Save PDF progress
 */
function savePDFProgress($pdfFile, $pageNumber) {
    $metadataFile = METADATA_PATH . '/pdf_progress.json';
    $progress = [];
    
    if (file_exists($metadataFile)) {
        $progress = json_decode(file_get_contents($metadataFile), true) ?? [];
    }
    
    $progress[$pdfFile] = [
        'page' => $pageNumber,
        'lastAccessed' => date('Y-m-d H:i:s')
    ];
    
    return file_put_contents($metadataFile, json_encode($progress, JSON_PRETTY_PRINT));
}

/**
 * Get PDF progress
 */
function getPDFProgress($pdfFile) {
    $metadataFile = METADATA_PATH . '/pdf_progress.json';
    
    if (file_exists($metadataFile)) {
        $progress = json_decode(file_get_contents($metadataFile), true) ?? [];
        return $progress[$pdfFile] ?? ['page' => 1, 'lastAccessed' => null];
    }
    
    return ['page' => 1, 'lastAccessed' => null];
}

/**
 * Sanitize file name
 */
function sanitizeFileName($fileName) {
    // Remove any character that isn't a letter, number, dash or underscore
    $fileName = preg_replace('/[^a-zA-Z0-9\-_]/', '', $fileName);
    return strtolower($fileName);
}

/**
 * Validate PDF file
 */
function validatePDFFile($file) {
    if (!isset($file['type']) || !in_array($file['type'], ALLOWED_TYPES)) {
        throw new Exception('Invalid file type. Only PDF files are allowed.');
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File size exceeds limit of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
    }
    
    return true;
}

/**
 * Delete a PDF file
 */
function deletePDF($category, $fileName) {
    $filePath = CATEGORIES_PATH . '/' . $category . '/' . $fileName;
    
    if (!file_exists($filePath)) {
        throw new Exception('PDF file not found');
    }
    
    // Delete the file
    if (!unlink($filePath)) {
        throw new Exception('Failed to delete PDF file');
    }
    
    // Remove progress data
    $metadataFile = METADATA_PATH . '/pdf_progress.json';
    if (file_exists($metadataFile)) {
        $progress = json_decode(file_get_contents($metadataFile), true) ?? [];
        unset($progress[$category . '/' . $fileName]);
        file_put_contents($metadataFile, json_encode($progress, JSON_PRETTY_PRINT));
    }
    
    return true;
}

/**
 * Delete a category
 */
function deleteCategory($categoryName) {
    $categoryPath = CATEGORIES_PATH . '/' . sanitizeFileName($categoryName);
    
    if (!is_dir($categoryPath)) {
        throw new Exception('Category not found');
    }
    
    // Get all files in the category
    $files = glob($categoryPath . '/*.pdf');
    
    // Delete each PDF file
    foreach ($files as $file) {
        $fileName = basename($file);
        try {
            deletePDF($categoryName, $fileName);
        } catch (Exception $e) {
            // Log error but continue with other files
            error_log("Error deleting PDF $fileName: " . $e->getMessage());
        }
    }
    
    // Delete the category directory
    if (!rmdir($categoryPath)) {
        throw new Exception('Failed to delete category directory');
    }
    
    // Update categories metadata
    $metadataFile = METADATA_PATH . '/categories.json';
    if (file_exists($metadataFile)) {
        $categories = json_decode(file_get_contents($metadataFile), true) ?? [];
        $categories = array_diff($categories, [$categoryName]);
        file_put_contents($metadataFile, json_encode(array_values($categories), JSON_PRETTY_PRINT));
    }
    
    return true;
}
