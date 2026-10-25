<?php
$siteTitle = $pageTitle ?? 'OnlineBookStore — Read beyond the ordinary';
$flash = get_flash();
$categoriesNav = [];
try { $categoriesNav = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll(); } catch (Throwable $e) {}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#101525">
  <meta name="description" content="Discover your next favourite book at OnlineBookStore.">
  <title><?= e($siteTitle) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/logo.svg">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <script defer src="<?= BASE_URL ?>/assets/js/main.js"></script>
</head>
<body>
<div class="announcement"><div class="announcement-track"><span>✦ Stories worth staying up for</span><span>✦ Great reads, great prices</span><span>✦ Your next chapter starts here</span><span>✦ Stories worth staying up for</span><span>✦ Great reads, great prices</span></div></div>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="<?= BASE_URL ?>/index.php"><img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="" width="42" height="42"><span>Online<span>BookStore</span><small>YOUR NEXT CHAPTER</small></span></a>
    <button class="mobile-menu-toggle" type="button" aria-label="Open menu" aria-expanded="false">☰</button>
    <nav class="main-nav">
      <a href="<?= BASE_URL ?>/index.php">Home</a><a href="<?= BASE_URL ?>/categories.php">Categories</a><a href="<?= BASE_URL ?>/index.php#books">Books</a><a href="<?= BASE_URL ?>/track-order.php">Track order</a>
    </nav>
    <form class="nav-search" action="<?= BASE_URL ?>/index.php#books" method="get"><input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search books..." aria-label="Search books"><button aria-label="Search" type="submit">⌕</button></form>
    <div class="nav-actions">
      <a class="icon-link" href="<?= BASE_URL ?>/cart.php" aria-label="Shopping cart">🛒 <span class="cart-count"><?= cart_count() ?></span></a>
      <?php if (is_logged_in()): ?><a class="nav-account" href="<?= BASE_URL ?>/my-orders.php">My orders</a><a class="nav-login" href="<?= BASE_URL ?>/logout.php">Logout</a>
      <?php else: ?><a class="nav-login" href="<?= BASE_URL ?>/login.php">Login</a><?php endif; ?>
    </div>
  </div>
</header>
<?php if ($flash): ?><div class="container"><div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div></div><?php endif; ?>
