<?php
require_once __DIR__ . '/config/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = filter_var($_POST['book_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    if ($action === 'add' && $id > 0) {
        $stmt = $pdo->prepare("SELECT id,stock,title FROM books WHERE id=?"); $stmt->execute([$id]); $book = $stmt->fetch();
        if (!$book || (int)$book['stock'] < 1) { set_flash('This book is currently unavailable.','error'); }
        else {
            $qty = max(1, min((int)($_POST['quantity'] ?? 1), (int)$book['stock']));
            $_SESSION['cart'][$id] = min((int)($_SESSION['cart'][$id] ?? 0) + $qty, (int)$book['stock']);
            set_flash($book['title'] . ' added to your cart.');
        }
    } elseif ($action === 'update') {
        foreach (($_POST['quantities'] ?? []) as $bookId=>$qty) {
            $bookId = filter_var($bookId,FILTER_VALIDATE_INT); $qty = filter_var($qty,FILTER_VALIDATE_INT);
            if (!$bookId || $qty === false) continue;
            if ($qty <= 0) { unset($_SESSION['cart'][$bookId]); continue; }
            $s = $pdo->prepare("SELECT stock FROM books WHERE id=?"); $s->execute([$bookId]); $row = $s->fetch();
            if ($row && (int)$row['stock'] > 0) $_SESSION['cart'][$bookId] = min(max(1,$qty),(int)$row['stock']);
            else unset($_SESSION['cart'][$bookId]);
        }
        set_flash('Your cart has been updated.');
    } elseif ($action === 'remove') { unset($_SESSION['cart'][$id]); set_flash('Book removed from cart.'); }
    header('Location: cart.php'); exit;
}
$cart = $_SESSION['cart'] ?? []; $items=[]; $total=0; $totalQty=0; $totalSavings=0;
if ($cart) {
    $ids = array_map('intval',array_keys($cart)); $marks = implode(',',array_fill(0,count($ids),'?'));
    $s=$pdo->prepare("SELECT * FROM books WHERE id IN ($marks)"); $s->execute($ids);
    foreach ($s->fetchAll() as $b) {
        $b['quantity']=max(1,(int)$cart[$b['id']]);
        if ((int)$b['stock'] < 1) continue;
        $b['quantity']=min($b['quantity'],(int)$b['stock']);
        $b['line_total']=$b['price']*$b['quantity'];
        $b['line_saving']=(!empty($b['old_price']) && $b['old_price']>$b['price']) ? ($b['old_price']-$b['price'])*$b['quantity'] : 0;
        $total += $b['line_total']; $totalQty += $b['quantity']; $totalSavings += $b['line_saving']; $items[]=$b;
    }
}
$pageTitle='My cart — OnlineBookStore'; include __DIR__ . '/includes/header.php';
?>
<main class="container section cart-page"><div class="page-heading cart-page-heading"><span class="eyebrow">YOUR PERSONAL BOOKSHELF</span><h1>My <span class="gradient-text">cart.</span></h1><p>Your next great read is just a few steps away.</p><div class="cart-steps"><span class="cart-step active"><i>1</i> Cart</span><span class="cart-step-line"></span><span class="cart-step"><i>2</i> Delivery</span><span class="cart-step-line"></span><span class="cart-step"><i>3</i> Confirmation</span></div></div>
<div class="cart-promo"><div class="cart-promo-icon">✦</div><div><strong>Good choice. Great stories ahead.</strong><p>Review your books and quantities before moving to checkout.</p></div><a href="categories.php">Keep exploring ↗</a></div>
<?php if (!$items): ?><div class="empty-cart-card"><div class="empty-cart-art"><span>📚</span><i>✦</i></div><span class="eyebrow">A SHELF WITH ROOM FOR MORE</span><h2>Your cart is waiting for a good book.</h2><p>Explore our collections and add a title that catches your eye. Your selections will be saved here during this session.</p><a class="btn btn-primary" href="categories.php">Browse categories ↗</a><a class="text-link" href="index.php#books">View popular books →</a></div>
<?php else: ?><div class="cart-layout cart-layout-pro"><form method="post" class="cart-table cart-table-pro"><div class="cart-list-heading"><div><h2>Your selected books <span><?= count($items) ?></span></h2><p><?= $totalQty ?> item<?= $totalQty===1?'':'s' ?> in your cart</p></div><a class="text-link" href="categories.php">+ Add more books</a></div><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="update">
<?php foreach($items as $item): ?><article class="cart-row cart-row-pro"><a class="cart-thumb" href="book.php?id=<?= (int)$item['id'] ?>"><img src="<?= e($item['cover'] ?: 'assets/img/book-placeholder.svg') ?>" alt="<?= e($item['title']) ?> cover" onerror="this.onerror=null;this.src='assets/img/book-placeholder.svg'"></a><div class="cart-book"><span class="cart-item-label">BOOK / <?= str_pad((string)$item['id'],3,'0',STR_PAD_LEFT) ?></span><h3><a href="book.php?id=<?= (int)$item['id'] ?>"><?= e($item['title']) ?></a></h3><p>by <?= e($item['author']) ?></p><div class="cart-item-price"><?= money($item['price']) ?><?php if (!empty($item['old_price']) && $item['old_price']>$item['price']): ?> <del><?= money($item['old_price']) ?></del><?php endif; ?></div><button class="remove-link" type="submit" form="remove-<?= (int)$item['id'] ?>">Remove</button></div><label class="quantity-label quantity-label-pro">Quantity<input type="number" min="0" max="<?= max(1,(int)$item['stock']) ?>" name="quantities[<?= (int)$item['id'] ?>]" value="<?= (int)$item['quantity'] ?>" aria-label="Quantity for <?= e($item['title']) ?>"><small>Stock: <?= (int)$item['stock'] ?></small></label><div class="cart-line-total"><small>Item total</small><strong><?= money($item['line_total']) ?></strong><?php if ($item['line_saving']>0): ?><span>You save <?= money($item['line_saving']) ?></span><?php endif; ?></div></article><?php endforeach; ?>
<div class="cart-table-actions"><button class="btn btn-outline" type="submit">↻ Update quantities</button><span class="muted small">Set quantity to 0 to remove an item.</span></div></form>
<aside class="summary-card summary-card-pro"><div class="summary-title-row"><span class="summary-bag">🛍</span><div><h2>Order summary</h2><p>Price details</p></div></div><div><span>Books subtotal (<?= $totalQty ?> items)</span><strong><?= money($total) ?></strong></div><div><span>Delivery fee</span><strong class="free-delivery">FREE</strong></div><?php if ($totalSavings>0): ?><div><span>Book discounts</span><strong class="saving-value">−<?= money($totalSavings) ?></strong></div><?php endif; ?><div class="delivery-note"><span>✓</span><p>Free delivery included in this demo order.</p></div><hr><div class="summary-total"><span>Total payable</span><strong><?= money($total) ?></strong></div><p class="summary-tax-note">Final details will be shown at checkout.</p><a class="btn btn-primary btn-block checkout-btn" href="checkout.php">Continue to checkout <span>→</span></a><div class="secure-note"><span>⌑</span><p><strong>Checkout with confidence</strong><br><small>Demo store · No real payment is collected.</small></p></div><a class="continue-shopping" href="categories.php">← Continue shopping</a></aside></div><?php foreach($items as $item): ?><form id="remove-<?= (int)$item['id'] ?>" method="post" class="hidden"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="book_id" value="<?= (int)$item['id'] ?>"></form><?php endforeach; ?><?php endif; ?></main>
<?php include __DIR__ . '/includes/footer.php'; ?>
