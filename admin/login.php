<?php
require_once dirname(__DIR__).'/config/config.php';
if(is_admin()){header('Location: dashboard.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$username=trim($_POST['username']??'');$password=$_POST['password']??'';$s=$pdo->prepare("SELECT * FROM admins WHERE username=?");$s->execute([$username]);$admin=$s->fetch();if($admin&&password_verify($password,$admin['password_hash'])){session_regenerate_id(true);$_SESSION['admin_id']=(int)$admin['id'];$_SESSION['admin_name']=$admin['username'];header('Location: dashboard.php');exit;}$error='Invalid admin username or password.';}
$pageTitle='Admin login — OnlineBookStore';include dirname(__DIR__).'/includes/header.php';
?>
<main class="auth-wrap"><section class="auth-card"><span class="eyebrow">STORE MANAGEMENT</span><h1>Admin <span class="gradient-text">login.</span></h1><p>Manage books, inventory and customer orders.</p><?php if($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?><form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Username<input name="username" required autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="btn btn-primary btn-block">Login to dashboard</button></form><p class="muted small">First time? Import database.sql and visit <a href="../setup.php">setup.php</a>.</p></section></main>
<?php include dirname(__DIR__).'/includes/footer.php'; ?>
