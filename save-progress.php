<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['category']) || empty($_POST['file']) || !isset($_POST['page'])) {
            throw new Exception('Missing required parameters');
        }
        
        $category = $_POST['category'];
        $file = $_POST['file'];
        $page = (int)$_POST['page'];
        
        if ($page < 1) {
            throw new Exception('Invalid page number');
        }
        
        savePDFProgress($category . '/' . $file, $page);
        http_response_code(200);
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
