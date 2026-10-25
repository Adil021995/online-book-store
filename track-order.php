<?php
require_once __DIR__ . '/config/config.php';

$order = null;
$events = [];
$items = [];
$error = '';
$number = trim($_GET['order'] ?? '');

if ($number !== '') {
    try {
        if (is_logged_in()) {
            $stmt = $pdo->prepare('SELECT o.*, u.name AS customer_name, u.email AS customer_email,
                    a.full_name AS recipient_name, a.phone AS recipient_phone,
                    a.address_line, a.city, a.state, a.postal_code
                FROM orders o
                LEFT JOIN users u ON u.id = o.user_id
                LEFT JOIN addresses a ON a.id = o.address_id
                WHERE o.order_number = ? AND o.user_id = ? LIMIT 1');
            $stmt->execute([$number, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare('SELECT o.*, u.name AS customer_name, u.email AS customer_email,
                    a.full_name AS recipient_name, a.phone AS recipient_phone,
                    a.address_line, a.city, a.state, a.postal_code
                FROM orders o
                LEFT JOIN users u ON u.id = o.user_id
                LEFT JOIN addresses a ON a.id = o.address_id
                WHERE o.order_number = ? LIMIT 1');
            $stmt->execute([$number]);
        }
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $itemStmt = $pdo->prepare('SELECT oi.*, b.cover AS book_cover, b.author AS book_author
                FROM order_items oi
                LEFT JOIN books b ON b.id = oi.book_id
                WHERE oi.order_id = ? ORDER BY oi.id ASC');
            $itemStmt->execute([$order['id']]);
            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

            $trackingStmt = $pdo->prepare('SELECT status, note, created_at FROM order_tracking WHERE order_id = ? ORDER BY created_at ASC, id ASC');
            $trackingStmt->execute([$order['id']]);
            $events = $trackingStmt->fetchAll(PDO::FETCH_ASSOC);

            // If the admin has not added tracking events yet, still show the current order status.
            if (!$events) {
                $events[] = [
                    'status' => $order['status'] ?: 'Placed',
                    'note' => 'Your order has been received. We will update this timeline as it moves through delivery.',
                    'created_at' => $order['created_at']
                ];
            }
        } else {
            $error = 'We could not find that order. Please check the order number and try again.';
        }
    } catch (Throwable $exception) {
        $error = 'We could not load tracking details right now. Please check your database setup and try again.';
    }
}

$status = strtolower((string)($order['status'] ?? 'placed'));
$statusSteps = ['Placed', 'Processing', 'Shipped', 'Out for delivery', 'Delivered'];
$statusMap = [
    'placed' => 0, 'pending' => 0, 'confirmed' => 0,
    'processing' => 1, 'packed' => 1,
    'shipped' => 2, 'in transit' => 2, 'in_transit' => 2,
    'out for delivery' => 3, 'out_for_delivery' => 3,
    'delivered' => 4,
    'cancelled' => -1, 'canceled' => -1, 'refunded' => -1
];
$currentStep = $statusMap[$status] ?? 0;
$isCancelled = in_array($status, ['cancelled', 'canceled', 'refunded'], true);
$pageTitle = 'Track order — OnlineBookStore';
include __DIR__ . '/includes/header.php';
?>

<main class="container section tracking-page">
    <div class="page-heading tracking-heading">
        <span class="eyebrow">ORDER UPDATES · EVERY STEP OF THE WAY</span>
        <h1>Track your <span class="gradient-text">order.</span></h1>
        <p>See what you ordered, how many copies you purchased, and the latest delivery updates.</p>
    </div>

    <form class="track-search panel" method="get" action="track-order.php">
        <label for="order-number">Order number
            <input id="order-number" name="order" required value="<?= e($number) ?>" placeholder="Example: OBS261024ABC123" autocomplete="off">
        </label>
        <button class="btn btn-primary" type="submit">Track order <span aria-hidden="true">→</span></button>
    </form>

    <?php if ($error): ?>
        <div class="flash error tracking-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($order): ?>
        <section class="tracking-overview">
            <div class="tracking-overview-main">
                <div class="tracking-label"><span class="tracking-label-icon">✓</span><span>ORDER FOUND</span></div>
                <p class="tracking-muted">ORDER NUMBER</p>
                <h2><?= e($order['order_number']) ?></h2>
                <p class="tracking-date">Placed on <?= e(date('d M Y, h:i A', strtotime($order['created_at']))) ?></p>
            </div>
            <div class="tracking-total-box">
                <span>Order total</span>
                <strong><?= money($order['total']) ?></strong>
                <span class="status-pill <?= $isCancelled ? 'status-cancelled' : '' ?>"><?= e(ucwords(str_replace('_', ' ', $order['status'] ?: 'Placed'))) ?></span>
            </div>
        </section>

        <?php if (!$isCancelled): ?>
            <section class="tracking-progress panel" aria-label="Delivery progress">
                <div class="tracking-section-heading">
                    <div><span class="eyebrow">DELIVERY JOURNEY</span><h2>Order progress</h2></div>
                    <span class="tracking-live-dot"><i></i> Latest status</span>
                </div>
                <div class="progress-steps">
                    <?php foreach ($statusSteps as $index => $step): ?>
                        <div class="progress-step <?= $index <= $currentStep ? 'is-complete' : '' ?> <?= $index === $currentStep ? 'is-current' : '' ?>">
                            <span class="progress-step-icon"><?= $index < $currentStep ? '✓' : ($index + 1) ?></span>
                            <span class="progress-step-label"><?= e($step) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="tracking-progress-note">Current update: <strong><?= e(ucwords(str_replace('_', ' ', $order['status'] ?: 'Placed'))) ?></strong>. The timeline below shows updates recorded for your order.</p>
            </section>
        <?php else: ?>
            <div class="flash error">This order is marked as <?= e($order['status']) ?>. Please contact the store if you need help.</div>
        <?php endif; ?>

        <div class="tracking-details-grid">
            <section class="tracking-items panel">
                <div class="tracking-section-heading">
                    <div><span class="eyebrow">IN THIS ORDER</span><h2>Your books <span class="tracking-count"><?= count($items) ?></span></h2></div>
                </div>
                <?php if ($items): ?>
                    <div class="tracking-book-list">
                        <?php foreach ($items as $item): ?>
                            <?php
                                $cover = trim((string)($item['book_cover'] ?? ''));
                                if ($cover === '') $cover = 'assets/img/book-placeholder.svg';
                                $quantity = max(0, (int)$item['quantity']);
                                $lineTotal = (float)$item['price'] * $quantity;
                            ?>
                            <article class="tracking-book-item">
                                <div class="tracking-book-cover"><img src="<?= e($cover) ?>" alt="Cover of <?= e($item['book_title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='assets/img/book-placeholder.svg';"></div>
                                <div class="tracking-book-info">
                                    <h3><?= e($item['book_title']) ?></h3>
                                    <?php if (!empty($item['book_author'])): ?><p class="tracking-book-author">by <?= e($item['book_author']) ?></p><?php endif; ?>
                                    <div class="tracking-book-meta"><span>Qty: <strong><?= $quantity ?></strong></span><span>Unit price: <strong><?= money($item['price']) ?></strong></span></div>
                                </div>
                                <strong class="tracking-line-total"><?= money($lineTotal) ?></strong>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="tracking-items-total"><span>Items subtotal</span><strong><?= money(array_sum(array_map(static fn($item) => (float)$item['price'] * (int)$item['quantity'], $items))) ?></strong></div>
                <?php else: ?>
                    <div class="tracking-empty-items"><span>▤</span><p>Book item details are not available for this order.</p></div>
                <?php endif; ?>
            </section>

            <aside class="tracking-side-column">
                <section class="panel tracking-history">
                    <div class="tracking-section-heading"><div><span class="eyebrow">LATEST UPDATES</span><h2>Tracking history</h2></div></div>
                    <div class="timeline">
                        <?php foreach (array_reverse($events) as $index => $event): ?>
                            <div class="timeline-item <?= $index === 0 ? 'timeline-item-latest' : '' ?>">
                                <span class="timeline-dot"></span>
                                <div><strong><?= e(ucwords(str_replace('_', ' ', $event['status']))) ?></strong>
                                    <?php if (!empty($event['note'])): ?><p><?= e($event['note']) ?></p><?php endif; ?>
                                    <small><?= e(date('d M Y, h:i A', strtotime($event['created_at']))) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="panel tracking-payment">
                    <span class="eyebrow">PAYMENT DETAILS</span><h2>Payment summary</h2>
                    <div class="tracking-info-row"><span>Method</span><strong><?= e(strtoupper(str_replace('_', ' ', $order['payment_method'] ?: 'COD'))) ?></strong></div>
                    <div class="tracking-info-row"><span>Payment status</span><strong class="payment-status-text"><?= e(ucwords(str_replace('_', ' ', $order['payment_status'] ?: 'Pending'))) ?></strong></div>
                    <div class="tracking-info-row tracking-grand-total"><span>Total paid / due</span><strong><?= money($order['total']) ?></strong></div>
                    <p class="tracking-demo-note">Payment information is shown as recorded by the store. Demo payment options do not process real payments.</p>
                </section>

                <?php if (!empty($order['address_line']) || !empty($order['city'])): ?>
                    <section class="panel tracking-address">
                        <span class="eyebrow">DELIVERY DESTINATION</span><h2>Shipping address</h2>
                        <?php if (!empty($order['recipient_name'])): ?><strong><?= e($order['recipient_name']) ?></strong><?php endif; ?>
                        <?php if (!empty($order['recipient_phone'])): ?><p><?= e($order['recipient_phone']) ?></p><?php endif; ?>
                        <?php if (!empty($order['address_line'])): ?><p><?= nl2br(e($order['address_line'])) ?></p><?php endif; ?>
                        <p><?= e(implode(', ', array_filter([$order['city'] ?? '', $order['state'] ?? '', $order['postal_code'] ?? '']))) ?></p>
                    </section>
                <?php endif; ?>
            </aside>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
