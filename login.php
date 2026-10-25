<?php
require_once __DIR__ . '/config/config.php';
if (is_logged_in()) { header('Location: my-orders.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf(); $email=strtolower(trim($_POST['email']??'')); $password=$_POST['password']??'';
    $s=$pdo->prepare("SELECT id,name,password_hash FROM users WHERE email=?"); $s->execute([$email]); $user=$s->fetch();
    if ($user && password_verify($password,$user['password_hash'])) { session_regenerate_id(true); $_SESSION['user_id']=(int)$user['id']; $_SESSION['user_name']=$user['name']; $next=$_GET['next']??''; header('Location: '.($next==='checkout.php'?'checkout.php':'index.php')); exit; }
    $error='Email or password is incorrect.';
}
$pageTitle='Login — OnlineBookStore'; include __DIR__ . '/includes/header.php';
?>
<main class="auth-wrap"><section class="auth-card"><span class="eyebrow">WELCOME BACK</span><h1>Login to your <span class="gradient-text">account.</span></h1><p>Your next favourite read is just around the corner.</p><?php if($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?><form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Email address<input type="email" name="email" required autocomplete="email"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="btn btn-primary btn-block">Login</button></form><p class="auth-switch">New here? <a href="register.php">Create an account</a></p></section></main>
<?php include __DIR__ . '/includes/footer.php'; ?>
