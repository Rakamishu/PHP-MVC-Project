
<?php if(isset($_SESSION['flash_message'])): ?>
    <?= $_SESSION['flash_message'] ?>
<?php endif; ?>

<form id="login" method="post" action="http://localhost/MVC/public/user/login" class="well">
    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control" placeholder="">
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Password">
        <input type="hidden" name="csrf" value="<?= $data['csrf'] ?>" />
        <div class="help-block text-right"><a href="http://localhost/MVC/public/user/recovery">Forgot the password?</a></div>
    </div>
    <div class="checkbox">
        <label><input type="checkbox" name="remember_me" value="1" checked /> Remember Me</label>
    </div>
    <button type="submit" class="btn btn-default btn-block" name="login">Login</button>
</form>

<a href="http://localhost/MVC/public/user/login/facebook">Login with Facebook</a>
<?php if(isset($_SESSION['id'])): ?>
    <?= $_SESSION['fb-login-id']; ?>
    name:<?= $_SESSION['fb-login-name']; ?>
<?php endif; ?>

<?php var_dump($_SESSION); ?>