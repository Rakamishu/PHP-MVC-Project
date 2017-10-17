    
<?php if(isset($_SESSION['flash_message'])): ?>
    <?= $_SESSION['flash_message'] ?>
<?php endif; ?>

<form id="login" method="post" action="http://localhost/MVC/public/user/signup/facebook" class="well">
    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control" placeholder="Username">
    </div>
    <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="Email Address">
    </div>
    <input type="hidden" name="csrf" value="<?= $data['csrf'] ?>" />
    <button type="submit" class="btn btn-default btn-block" name="signup">Register</button>
</form>
    