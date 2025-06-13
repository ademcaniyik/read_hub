<?php
$pdfs = [];
$categories = getCategories();

foreach ($categories as $category) {
    $categoryPath = CATEGORIES_PATH . '/' . $category;
    if (is_dir($categoryPath)) {
        $files = glob($categoryPath . '/*.pdf');
        foreach ($files as $file) {
            $fileName = basename($file);
            $progress = getPDFProgress($category . '/' . $fileName);
            $pdfs[] = [
                'category' => $category,
                'name' => $fileName,
                'lastAccessed' => $progress['lastAccessed'],
                'currentPage' => $progress['page']
            ];
        }
    }
}

// Sort PDFs by last accessed date
usort($pdfs, function($a, $b) {
    if (!$a['lastAccessed']) return 1;
    if (!$b['lastAccessed']) return -1;
    return strtotime($b['lastAccessed']) - strtotime($a['lastAccessed']);
});

if (empty($pdfs)): ?>
    <p class="text-muted">No PDFs found. <a href="upload.php">Upload your first PDF</a>.</p>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($pdfs as $pdf): ?>
            <a href="viewer.php?category=<?php echo urlencode($pdf['category']); ?>&file=<?php echo urlencode($pdf['name']); ?>" 
               class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1"><?php echo htmlspecialchars($pdf['name']); ?></h5>
                    <small class="text-muted">
                        Category: <?php echo htmlspecialchars($pdf['category']); ?>
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
