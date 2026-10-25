<?php
require_once __DIR__ . '/config/config.php';
if (is_logged_in()) { header('Location: my-orders.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $name=trim($_POST['name']??''); $email=strtolower(trim($_POST['email']??'')); $phone=trim($_POST['phone']??''); $password=$_POST['password']??'';
    if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<8) $error='Enter your name, a valid email, and a password with at least 8 characters.';
    else {
        try { $s=$pdo->prepare("INSERT INTO users(name,email,phone,password_hash) VALUES(?,?,?,?)"); $s->execute([$name,$email,$phone,password_hash($password,PASSWORD_DEFAULT)]); session_regenerate_id(true); $_SESSION['user_id']=(int)$pdo->lastInsertId(); $_SESSION['user_name']=$name; set_flash('Welcome to OnlineBookStore!'); header('Location: index.php'); exit; }
        catch(PDOException $e) { $error = $e->getCode()==='23000' ? 'An account with this email already exists.' : 'Registration could not be completed. Please try again.'; }
    }
}
$pageTitle='Create account — OnlineBookStore'; include __DIR__ . '/includes/header.php';
?>
<main class="auth-wrap"><section class="auth-card"><span class="eyebrow">JOIN OUR BOOK CLUB</span><h1>Create your <span class="gradient-text">account.</span></h1><p>Save your orders and keep your next read close.</p><?php if($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?><form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Full name<input name="name" required maxlength="120" autocomplete="name"></label><label>Email address<input type="email" name="email" required autocomplete="email"></label><label>Phone (optional)<input name="phone" autocomplete="tel"></label><label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password"><small>Use at least 8 characters.</small></label><button class="btn btn-primary btn-block">Create account</button></form><p class="auth-switch">Already registered? <a href="login.php">Login</a></p></section></main>
<?php include __DIR__ . '/includes/footer.php'; ?>
