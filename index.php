<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'OnlineBookStore — Stories worth staying up for';
$q = trim($_GET['q'] ?? '');
$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: 0;
$featuredOnly = isset($_GET['featured']);
$books = [];
$bannerRows = [];
$categories = [];
try {
    $bannerRows = $pdo->query("SELECT * FROM banners WHERE is_active=1 ORDER BY sort_order,id LIMIT 5")->fetchAll();
    $categories = $pdo->query("SELECT c.*, COUNT(b.id) AS book_count FROM categories c LEFT JOIN books b ON b.category_id=c.id GROUP BY c.id ORDER BY c.name")->fetchAll();
    $sql = "SELECT b.*, c.name AS category_name FROM books b LEFT JOIN categories c ON c.id=b.category_id WHERE 1=1";
    $params = [];
    if ($q !== '') { $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR c.name LIKE ?)"; $like = "%$q%"; array_push($params,$like,$like,$like); }
    if ($categoryId) { $sql .= " AND b.category_id=?"; $params[] = $categoryId; }
    if ($featuredOnly) { $sql .= " AND b.is_featured=1"; }
    $sql .= " ORDER BY b.is_featured DESC,b.id DESC LIMIT 24";
    $stmt = $pdo->prepare($sql); $stmt->execute($params); $books = $stmt->fetchAll();
} catch (Throwable $e) { $dbProblem = true; }
include __DIR__ . '/includes/header.php';
?>
<main>
<section class="hero">
  <div class="hero-orb orb-one"></div><div class="hero-orb orb-two"></div>
  <div class="container hero-grid">
    <div class="hero-copy reveal">
      <span class="eyebrow"><i></i> YOUR NEXT CHAPTER STARTS HERE</span>
      <h1>Find your next<br><span class="gradient-text">great escape.</span></h1>
      <p>Discover stories that stay with you. Explore hand-picked books, hidden gems, and ideas that change everything.</p>
      <div class="hero-actions"><a class="btn btn-primary" href="#books">Explore books <span>↗</span></a><a class="text-link" href="categories.php">Browse categories →</a></div>
      <div class="hero-stats"><div><strong>12<span>+</span></strong><small>Books to explore</small></div><div><strong>7</strong><small>Book categories</small></div><div><strong>4.9<span>/5</span></strong><small>Reader love</small></div></div>
    </div>
    <div class="hero-art reveal">
      <div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
      <div class="floating-note note-top"><span>✦</span><div><b>Reader's choice</b><small>Curated for you</small></div></div>
      <div class="hero-book-stack">
        <div class="hero-book book-back"><small>THE ART OF</small><b>FOCUS</b><i>MAKE SPACE FOR WHAT MATTERS</i></div>
        <div class="hero-book book-mid"><small>BUILD BETTER</small><b>ATOMIC<br>HABITS</b><i>JAMES CLEAR</i></div>
        <div class="hero-book book-front"><small>A STORY TO REMEMBER</small><b>THE<br>UNSEEN<br><em>WORLD</em></b><i>OPEN A NEW CHAPTER</i><span class="sun-shape"></span></div>
      </div>
      <div class="floating-note note-bottom"><span>✧</span><div><b>Made for book lovers</b><small>One page at a time</small></div></div>
    </div>
  </div>
</section>
<section class="promo-strip"><div class="promo-track"><?php foreach (array_merge($bannerRows,$bannerRows) as $banner): ?><span>✦ <?= e($banner['title']) ?></span><?php endforeach; ?></div></section>
<section class="section container category-section">
  <div class="section-heading reveal"><div><span class="eyebrow">FIND YOUR MOOD</span><h2>Browse by <span class="gradient-text">category.</span></h2></div><a class="text-link" href="categories.php">All categories →</a></div>
  <div class="category-chips"><?php foreach ($categories as $cat): ?><a class="category-chip" href="index.php?category=<?= (int)$cat['id'] ?>#books"><span>✦</span><?= e($cat['name']) ?><small><?= (int)$cat['book_count'] ?></small></a><?php endforeach; ?></div>
</section>
<section class="store-banner container reveal">
  <div class="store-banner-copy"><span class="eyebrow">THE BOOKISH WEEKEND</span><h2>Make room for your <span class="gradient-text">next favourite.</span></h2><p>Explore hand-picked reads across fiction, personal growth, business and more.</p><a class="btn btn-primary" href="categories.php">Explore all shelves <span>↗</span></a></div>
  <div class="store-banner-art" aria-hidden="true"><span class="banner-spark">✦</span><div class="banner-book banner-book-a"><small>READ MORE</small><b>NEW<br>WORLDS</b></div><div class="banner-book banner-book-b"><small>YOUR NEXT</small><b>CHAPTER</b></div><div class="banner-circle"></div></div>
</section>
<?php $shelfCount = 0; $shelfTotal = count(array_filter($categories, fn($c) => (int)$c['book_count'] > 0)); ?>
<?php foreach ($categories as $cat): ?>
<?php
  $railStmt = $pdo->prepare("SELECT id,title,author,price,old_price,cover,stock FROM books WHERE category_id=? ORDER BY is_featured DESC,id DESC LIMIT 12");
  $railStmt->execute([(int)$cat['id']]); $railBooks = $railStmt->fetchAll();
?>
<?php if ($railBooks): $shelfCount++; ?>
<section class="category-shelf container" aria-label="<?= e($cat['name']) ?> books">
  <div class="shelf-heading"><div><span class="eyebrow">CURATED SHELF</span><h2><?= e($cat['name']) ?> <span class="gradient-text">reads.</span></h2><p><?= e($cat['description'] ?: 'A few good reads picked from this shelf.') ?></p></div><div class="shelf-controls"><button type="button" class="shelf-arrow" data-scroll="left" aria-label="Scroll <?= e($cat['name']) ?> books left">←</button><button type="button" class="shelf-arrow" data-scroll="right" aria-label="Scroll <?= e($cat['name']) ?> books right">→</button><a class="text-link" href="index.php?category=<?= (int)$cat['id'] ?>#books">View all →</a></div></div>
  <div class="book-rail">
  <?php foreach ($railBooks as $book): ?>
    <article class="book-card rail-book-card"><a class="book-cover-wrap" href="book.php?id=<?= (int)$book['id'] ?>"><img src="<?= e($book['cover'] ?: 'assets/img/book-placeholder.svg') ?>" alt="<?= e($book['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='assets/img/book-placeholder.svg'"><span class="cover-overlay">View book ↗</span></a><div class="book-info"><div><h3><a href="book.php?id=<?= (int)$book['id'] ?>"><?= e($book['title']) ?></a></h3><p>by <?= e($book['author']) ?></p></div><strong><?= money($book['price']) ?></strong></div><div class="book-card-bottom"><span class="rating">★★★★★ <small>Reader pick</small></span><form action="cart.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>"><button class="mini-link" type="submit" <?= (int)$book['stock'] < 1 ? 'disabled' : '' ?>><?= (int)$book['stock'] < 1 ? 'Out of stock' : '+ Add to cart' ?></button></form></div></article>
  <?php endforeach; ?>
  </div>
</section>
<?php if ($shelfCount % 2 === 0 && $shelfCount < $shelfTotal):
  $midBanners = [
    ['eyebrow'=>'A NEW CHAPTER AWAITS','title'=>'Small moments. <span class="gradient-text">Big stories.</span>','copy'=>'Find a book that takes you somewhere new, one page at a time.','button'=>'Explore the collection','href'=>'categories.php','art'=>'banner-art-books'],
    ['eyebrow'=>'GIVE YOUR CURIOSITY ROOM','title'=>'Ideas worth <span class="gradient-text">getting lost in.</span>','copy'=>'From fresh perspectives to unforgettable adventures, your next read is here.','button'=>'Find your next read','href'=>'categories.php','art'=>'banner-art-orbit']
  ];
  $midBanner = $midBanners[(int)(($shelfCount / 2 - 1) % count($midBanners))];
?>
<section class="inter-shelf-banner container reveal <?= e($midBanner['art']) ?>">
  <div class="inter-banner-copy"><span class="eyebrow"><?= e($midBanner['eyebrow']) ?></span><h2><?= $midBanner['title'] ?></h2><p><?= e($midBanner['copy']) ?></p><a class="btn btn-primary" href="<?= e($midBanner['href']) ?>"><?= e($midBanner['button']) ?> <span>↗</span></a></div>
  <div class="inter-banner-art" aria-hidden="true"><span class="inter-spark spark-one">✦</span><span class="inter-spark spark-two">✧</span><div class="inter-book inter-book-one"><small>TURN THE PAGE</small><b>NEW<br>WORLDS</b></div><div class="inter-book inter-book-two"><small>JUST ONE MORE</small><b>CHAPTER</b></div><div class="inter-banner-orbit"></div></div>
</section>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>

<section class="section books-section" id="books"><div class="container">
  <div class="section-heading reveal"><div><span class="eyebrow">THE BOOKSHELF</span><h2><?php if ($q !== ''): ?>Search <span class="gradient-text">results.</span><?php elseif ($featuredOnly): ?>Reader <span class="gradient-text">favourites.</span><?php else: ?>Stories for every <span class="gradient-text">side of you.</span><?php endif; ?></h2><p>From page-turners to perspective-shifters, find something worth your time.</p></div>
    <form class="search-form" action="index.php#books" method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search title, author, genre..." aria-label="Search books"><button type="submit" aria-label="Search">⌕</button></form>
  </div>
  <?php if (!empty($dbProblem)): ?><div class="empty-state"><h3>Database needs setup</h3><p>Import database.sql in phpMyAdmin, then refresh this page.</p></div>
  <?php elseif ($books): ?>
  <div class="book-grid">
    <?php foreach ($books as $i => $book): ?>
      <article class="book-card reveal" style="--delay: <?= ($i % 4) * 70 ?>ms">
        <a class="book-cover-wrap" href="book.php?id=<?= (int)$book['id'] ?>"><img src="<?= e($book['cover'] ?: 'assets/img/book-placeholder.svg') ?>" alt="<?= e($book['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='assets/img/book-placeholder.svg'"><span class="book-category"><?= e($book['category_name'] ?? 'Featured') ?></span><span class="cover-overlay">View book ↗</span></a>
        <div class="book-info"><div><h3><a href="book.php?id=<?= (int)$book['id'] ?>"><?= e($book['title']) ?></a></h3><p>by <?= e($book['author']) ?></p></div><strong><?= money($book['price']) ?></strong></div>
        <?php if (!empty($book['old_price']) && $book['old_price'] > $book['price']): ?><div class="old-price"><?= money($book['old_price']) ?> <span>Special price</span></div><?php endif; ?>
        <div class="book-card-bottom"><span class="rating">★★★★★ <small>4.9</small></span><form action="cart.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>"><button class="mini-link" type="submit" <?= (int)$book['stock'] < 1 ? 'disabled' : '' ?>><?= (int)$book['stock'] < 1 ? 'Out of stock' : '+ Add to cart' ?></button></form></div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php else: ?><div class="empty-state"><span>⌕</span><h3>No books found just yet.</h3><p>Try a different title, author, or category.</p><a class="btn btn-primary" href="index.php#books">View all books →</a></div><?php endif; ?>
</div></section>
<section class="feature-band"><div class="container feature-inner reveal"><div class="feature-icon">✳</div><div><span class="eyebrow">A LITTLE BOOKISH MAGIC</span><h2>Less scrolling.<br><span class="gradient-text">More soul-searching.</span></h2><p>We believe the right book finds you at the right time. Browse thoughtfully, follow your curiosity, and make room for a new favourite.</p><a class="btn btn-light" href="categories.php">Find your next read ↗</a></div><blockquote><span>“</span><p>A reader lives a thousand lives before he dies.</p><small>— George R. R. Martin</small></blockquote></div></section>
<section class="newsletter"><div class="container newsletter-inner reveal"><div><span class="eyebrow">THE SUNDAY BOOK CLUB</span><h2>Good reads, <span class="gradient-text">good ideas.</span></h2><p>Explore fresh picks and reading inspiration. Just bookish things.</p></div><form class="newsletter-form" onsubmit="event.preventDefault();this.querySelector('.newsletter-feedback').textContent='Thanks for your interest! Newsletter demo only.';"><div><input type="email" required placeholder="Your email address" aria-label="Your email address"><button type="submit">Join the club ↗</button></div><small class="newsletter-feedback">Newsletter preview — no email is sent in this demo.</small></form></div></section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
