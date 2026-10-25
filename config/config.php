<?php
// OnlineBookStore configuration. XAMPP defaults: username=root, password empty.
if (session_status() === PHP_SESSION_NONE) { session_start(); }
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307'); // Set to 3306 if your MySQL uses the default port.
define('DB_NAME', 'online_book_store');
define('BASE_URL', '/OnlineBookStore'); // Update if the folder name or deployment path changes.
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Start MySQL in XAMPP, import database.sql in phpMyAdmin, and check config/config.php.');
}
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function money($amount) { return '₹' . number_format((float)$amount, 2); }
function is_logged_in() { return !empty($_SESSION['user_id']); }
function is_admin() { return !empty($_SESSION['admin_id']); }
function require_login() {
    if (!is_logged_in()) { header('Location: login.php?next=checkout.php'); exit; }
}
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf_token'];
}
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
            http_response_code(403); exit('Invalid request token. Please go back and try again.');
        }
    }
}
function set_flash($message, $type = 'success') { $_SESSION['flash'] = ['message' => $message, 'type' => $type]; }
function get_flash() {
    if (empty($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash']; unset($_SESSION['flash']); return $flash;
}
function cart_count() {
    $cart = $_SESSION['cart'] ?? [];
    return array_sum(array_map('intval', $cart));
}
