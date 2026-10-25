<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Book categories — OnlineBookStore';
$categories = $pdo->query("SELECT c.*,COUNT(b.id) AS book_count FROM categories c LEFT JOIN books b ON b.category_id=c.id GROUP BY c.id ORDER BY c.name")->fetchAll();
$categoryBooks = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("SELECT id,title,author,price,cover FROM books WHERE category_id=? ORDER BY is_featured DESC,id DESC LIMIT 3");
    $stmt->execute([(int)$cat['id']]);
    $categoryBooks[(int)$cat['id']] = $stmt->fetchAll();
}
include __DIR__ . '/includes/header.php';
?>
<main>
<section class="categories-hero"><div class="container categories-hero-inner"><div><span class="eyebrow">FOLLOW YOUR CURIOSITY</span><h1>Find your next <span class="gradient-text">favourite shelf.</span></h1><p>Browse collections by mood, interest and the kind of story you want to get lost in.</p><div class="category-hero-actions"><a class="btn btn-primary" href="index.php#books">Explore all books ↗</a><span class="category-count"><strong><?= count($categories) ?></strong> curated categories</span></div></div><div class="category-hero-visual" aria-hidden="true"><div class="category-orbit"></div><div class="category-hero-book cat-book-one">FICTION<br><b>IMAGINE</b></div><div class="category-hero-book cat-book-two">GROW<br><b>DAILY</b></div><div class="category-hero-book cat-book-three">IDEAS<br><b>UNLOCKED</b></div></div></div></section>
<section class="container section category-directory"><div class="section-heading"><div><span class="eyebrow">THE WHOLE BOOKSHELF</span><h2>Browse every <span class="gradient-text">category.</span></h2><p>Pick a collection to see all available titles.</p></div></div><div class="category-grid category-grid-pro">
<?php foreach ($categories as $i=>$cat): $preview=$categoryBooks[(int)$cat['id']] ?? []; ?>
<a class="category-tile category-tile-pro reveal" style="--delay:<?= ($i%4)*60 ?>ms" href="index.php?category=<?= (int)$cat['id'] ?>#books"><div class="category-tile-top"><span class="tile-icon">✦</span><span class="category-count-pill"><?= (int)$cat['book_count'] ?> titles</span></div><h2><?= e($cat['name']) ?></h2><p><?= e($cat['description'] ?: 'Explore a collection of books selected for curious readers.') ?></p><?php if ($preview): ?><div class="category-preview-books"><?php foreach ($preview as $book): ?><img src="<?= e($book['cover'] ?: 'assets/img/book-placeholder.svg') ?>" alt="<?= e($book['title']) ?> cover" loading="lazy" onerror="this.onerror=null;this.src='assets/img/book-placeholder.svg'"><?php endforeach; ?><span>+<?= max(0,(int)$cat['book_count']-count($preview)) ?> more</span></div><?php endif; ?><span class="tile-bottom">Explore this shelf <b>↗</b></span></a>
<?php endforeach; ?></div></section>
<section class="category-mini-banner container reveal">
  <div class="category-mini-copy"><span class="eyebrow">YOUR NEXT CHAPTER STARTS HERE</span><h2>Good books. <span class="gradient-text">Fresh perspectives.</span></h2><p>Browse a shelf, follow your curiosity, and discover a story worth keeping.</p><a class="btn btn-primary" href="index.php#books">Explore the bookshelf ↗</a></div>
  <div class="category-mini-art" aria-hidden="true"><span class="mini-spark">✦</span><div class="mini-book mini-book-one">STAY<br><b>CURIOUS</b></div><div class="mini-book mini-book-two">READ<br><b>MORE</b></div><div class="mini-orbit"></div></div>
</section>
<section class="category-bottom-cta"><div class="container category-cta-inner"><div><span class="eyebrow">NOT SURE WHERE TO START?</span><h2>Your next great read is <span class="gradient-text">waiting.</span></h2><p>Search by book title, author or category to find the right fit.</p></div><form class="search-form" action="index.php#books" method="get"><input name="q" placeholder="Search books and authors..." aria-label="Search books and authors"><button type="submit" aria-label="Search">⌕</button></form></div></section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
