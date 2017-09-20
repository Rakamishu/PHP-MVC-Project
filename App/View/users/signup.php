    
<?php if(isset($_SESSION['flash_message'])): ?>
    <?= $_SESSION['flash_message'] ?>
<?php endif; ?>

<form id="login" method="post" action="http://localhost/MVC/public/user/signup" class="well">
    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control" placeholder="Username">
    </div>
    <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="Email Address">
    </div>
    <div class="form-group">
        <label>Repeat Email Address</label>
        <input type="email" name="email_repeat" class="form-control" placeholder="Repeat Email Address">
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Password">
    </div>
    <div class="form-group">
        <label>Repeat Password</label>
        <input type="password" name="password_repeat" class="form-control" placeholder="Repeat Password">
    </div>
    <input type="hidden" name="csrf" value="<?= $data['csrf'] ?>" />
    <button type="submit" class="btn btn-default btn-block" name="signup">Register</button>
    <div class="g-recaptcha" data-sitekey="6LdngyEUAAAAAJAuJ0hXEaH9-_H8KU9wbZDYMCuy"></div>
</form>
    