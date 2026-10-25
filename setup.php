<?php
require_once __DIR__ . '/config/config.php';
$message = '';
try {
    $check = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $check->execute(['admin']);
    if (!$check->fetch()) {
        $hash = password_hash('BookStore@123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username,password_hash) VALUES (?,?)");
        $stmt->execute(['admin',$hash]);
    }
    $message = 'Admin account is ready. Username: admin | Password: BookStore@123';
} catch (Throwable $e) {
    $message = 'Setup could not finish. First import database.sql in phpMyAdmin, then refresh this page.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OnlineBookStore Setup</title><link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css"></head>
<body><main class="setup-wrap"><div class="panel"><img class="setup-logo" src="<?= BASE_URL ?>/assets/img/logo.svg" alt="OnlineBookStore"><h1>Local setup check</h1><p><?= e($message) ?></p><p>After setup, open the homepage. For safety, delete or rename <code>setup.php</code> when you finish local setup.</p><a class="btn btn-primary" href="index.php">Open OnlineBookStore</a></div></main></body></html>
