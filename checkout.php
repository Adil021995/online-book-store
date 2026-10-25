<?php
require_once __DIR__ . '/config/config.php';
require_login();
$cart=$_SESSION['cart']??[]; if(!$cart){ set_flash('Your cart is empty.','error'); header('Location: cart.php'); exit; }
$ids=array_map('intval',array_keys($cart)); $marks=implode(',',array_fill(0,count($ids),'?')); $s=$pdo->prepare("SELECT * FROM books WHERE id IN ($marks)"); $s->execute($ids); $items=[];$total=0;
foreach($s->fetchAll() as $b){$b['quantity']=min(max(1,(int)$cart[$b['id']]),(int)$b['stock']); if($b['quantity']<1)continue;$b['line_total']=$b['price']*$b['quantity'];$total+=$b['line_total'];$items[]=$b;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $name=trim($_POST['full_name']??'');$phone=trim($_POST['phone']??'');$line=trim($_POST['address_line']??'');$city=trim($_POST['city']??'');$state=trim($_POST['state']??'');$postal=trim($_POST['postal_code']??'');$method=$_POST['payment_method']??'cod';
 if(!$items)$error='Your cart has no available books.';
 elseif(!$name||!$phone||!$line||!$city||!$state||!$postal)$error='Please fill all delivery address fields.';
 elseif(!in_array($method,['cod','demo'],true))$error='Select a valid payment method.';
 else {
  try {
   $pdo->beginTransaction();
   foreach($items as $it){$lock=$pdo->prepare("SELECT stock FROM books WHERE id=? FOR UPDATE");$lock->execute([$it['id']]);$stock=$lock->fetchColumn();if($stock===false||(int)$stock<$it['quantity'])throw new RuntimeException('Not enough stock for '.$it['title'].'. Please update your cart.');}
   $a=$pdo->prepare("INSERT INTO addresses(user_id,full_name,phone,address_line,city,state,postal_code) VALUES(?,?,?,?,?,?,?)");$a->execute([$_SESSION['user_id'],$name,$phone,$line,$city,$state,$postal]);$addressId=(int)$pdo->lastInsertId();
   $number='OBS'.date('ymd').strtoupper(bin2hex(random_bytes(3)));
   $payStatus=$method==='cod'?'pending':'demo_success';$orderStatus='Placed';
   $o=$pdo->prepare("INSERT INTO orders(order_number,user_id,address_id,total,payment_method,payment_status,status) VALUES(?,?,?,?,?,?,?)");$o->execute([$number,$_SESSION['user_id'],$addressId,$total,$method,$payStatus,$orderStatus]);$orderId=(int)$pdo->lastInsertId();
   $oi=$pdo->prepare("INSERT INTO order_items(order_id,book_id,book_title,price,quantity) VALUES(?,?,?,?,?)");$stockUpdate=$pdo->prepare("UPDATE books SET stock=stock-? WHERE id=?");
   foreach($items as $it){$oi->execute([$orderId,$it['id'],$it['title'],$it['price'],$it['quantity']]);$stockUpdate->execute([$it['quantity'],$it['id']]);}
   $p=$pdo->prepare("INSERT INTO payments(order_id,method,amount,status,transaction_reference) VALUES(?,?,?,?,?)");$p->execute([$orderId,$method,$total,$payStatus,$method==='demo'?'DEMO-'.strtoupper(bin2hex(random_bytes(4))):null]);
   $tr=$pdo->prepare("INSERT INTO order_tracking(order_id,status,note) VALUES(?,?,?)");$tr->execute([$orderId,'Placed','Order received by OnlineBookStore.']);
   $pdo->commit();unset($_SESSION['cart']);set_flash('Order placed successfully! Order number: '.$number);header('Location: my-orders.php');exit;
  }catch(Throwable $ex){if($pdo->inTransaction())$pdo->rollBack();$error=$ex instanceof RuntimeException?$ex->getMessage():'Order could not be placed. Please try again.';}
 }
}
$pageTitle='Checkout — OnlineBookStore';include __DIR__.'/includes/header.php';
?>
<main class="container section"><div class="page-heading"><span class="eyebrow">ALMOST YOURS</span><h1>Secure <span class="gradient-text">checkout.</span></h1><p>Demo checkout for your local project. No real money is charged.</p></div><?php if($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?><div class="checkout-layout"><form method="post" class="panel form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><h2>Delivery details</h2><label>Full name<input name="full_name" required value="<?= e($_POST['full_name']??($_SESSION['user_name']??'')) ?>"></label><label>Phone number<input name="phone" required value="<?= e($_POST['phone']??'') ?>"></label><label>Address<input name="address_line" required value="<?= e($_POST['address_line']??'') ?>"></label><div class="form-row"><label>City<input name="city" required value="<?= e($_POST['city']??'') ?>"></label><label>State<input name="state" required value="<?= e($_POST['state']??'') ?>"></label></div><label>PIN code<input name="postal_code" required value="<?= e($_POST['postal_code']??'') ?>"></label><h2>Payment method</h2><label class="radio-option"><input type="radio" name="payment_method" value="cod" checked> Cash on Delivery <small>Pay when your order arrives</small></label><label class="radio-option"><input type="radio" name="payment_method" value="demo"> Demo online payment <small>Marks payment as simulated success; no real payment gateway.</small></label><button class="btn btn-primary btn-block">Place order →</button></form><aside class="summary-card"><h2>Your order</h2><?php foreach($items as $it): ?><div class="summary-item"><span><?= e($it['title']) ?> × <?= (int)$it['quantity'] ?></span><strong><?= money($it['line_total']) ?></strong></div><?php endforeach; ?><hr><div><span>Delivery</span><strong>Free</strong></div><div class="summary-total"><span>Total</span><strong><?= money($total) ?></strong></div></aside></div></main>
<?php include __DIR__.'/includes/footer.php'; ?>
