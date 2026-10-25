<?php
require_once __DIR__ . '/config/config.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare("SELECT b.*,c.name AS category_name FROM books b LEFT JOIN categories c ON c.id=b.category_id WHERE b.id=?");
$stmt->execute([$id ?: 0]); $book = $stmt->fetch();
if (!$book) { http_response_code(404); }
$pageTitle = $book ? $book['title'] . ' — OnlineBookStore' : 'Book not found — OnlineBookStore';
include __DIR__ . '/includes/header.php';
?>
<main class="container section">
<?php if (!$book): ?><div class="empty-state"><h1>Book not found</h1><a class="btn btn-primary" href="index.php">Back to books</a></div>
<?php else: ?>
<div class="product-detail reveal"><div class="detail-cover"><img src="<?= e($book['cover'] ?: 'assets/img/book-placeholder.svg') ?>" alt="<?= e($book['title']) ?>" onerror="this.onerror=null;this.src='assets/img/book-placeholder.svg'"></div><div class="detail-copy"><span class="eyebrow"><?= e($book['category_name'] ?? 'BOOK') ?></span><h1><?= e($book['title']) ?></h1><p class="byline">Written by <strong><?= e($book['author']) ?></strong></p><div class="rating">★★★★★ <small>4.9 reader rating</small></div><p class="detail-description"><?= nl2br(e($book['description'] ?? 'A great addition to your bookshelf.')) ?></p><div class="detail-price"><?= money($book['price']) ?> <?php if ($book['old_price']): ?><del><?= money($book['old_price']) ?></del><?php endif; ?></div><p class="stock-line"><?= (int)$book['stock'] > 0 ? '✓ In stock (' . (int)$book['stock'] . ' available)' : 'Currently out of stock' ?></p><form action="cart.php" method="post" class="detail-actions"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>"><label>Qty <input type="number" name="quantity" min="1" max="<?= max(1,(int)$book['stock']) ?>" value="1"></label><button class="btn btn-primary" type="submit" <?= (int)$book['stock'] < 1 ? 'disabled' : '' ?>>Add to cart 🛒</button><a class="btn btn-outline" href="cart.php">View cart</a></form><div class="detail-perks"><span>✓ Secure checkout demo</span><span>✓ Order tracking</span><span>✓ Reader-first service</span></div></div></div>
<?php endif; ?></main>
<?php include __DIR__ . '/includes/footer.php'; ?>
