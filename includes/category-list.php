<?php
$categories = getCategories();
if (empty($categories)): ?>
    <p class="text-muted">No categories found.</p>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($categories as $category): ?>
            <a href="category.php?name=<?php echo urlencode($category); ?>" 
               class="list-group-item list-group-item-action">
                <?php echo htmlspecialchars($category); ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
