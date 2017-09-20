logo 
<?php if(isset($_SESSION['userid'])): ?>
Welcome, <a href="<?= SITE_ADDR.'/public/user/profile/'.$_SESSION['userid'] ?>"><?= $_SESSION['username'] ?></a> | 
<a href="<?= SITE_ADDR.'/public/user/settings/email' ?>">Edit Email</a> | 
<a href="<?= SITE_ADDR.'/public/user/settings/password' ?>">Edit Password</a> | 

<?php endif; ?>

<a href="<?= SITE_ADDR.'/public/news' ?>">News</a> -|-
<a href="<?= SITE_ADDR.'/public/user/signup' ?>">Sign up</a> -|- 
<a href="<?= SITE_ADDR.'/public/user/login' ?>">Login</a> -|- 
<a href="<?= SITE_ADDR.'/public/admin' ?>">Admin panel</a> -|- 
<br /><br />

<?php if(isset($_SESSION['userid'])): ?>
    Hello, <?= $_SESSION['username'] ?>
    <a href="<?= SITE_ADDR.'/public/user/logout' ?>">Logout</a>
<?php endif; ?>

<hr />