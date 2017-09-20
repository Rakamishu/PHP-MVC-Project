
<?php if(isset($_SESSION['flash_message'])): ?>
    <?= $_SESSION['flash_message'] ?>
<?php endif; ?>

<form id="login" method="post" action="http://localhost/MVC/public/user/settings/email" class="well">
    <div class="form-group">
        <label>New email</label>
        <input type="text" name="email" class="form-control" placeholder="">
    </div>
    <div class="form-group">
        <label>Repeat new email</label>
        <input type="text" name="email_repeat" class="form-control" placeholder="">
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Password">
        <input type="hidden" name="csrf" value="<?= $data['csrf'] ?>" />
    </div>
    <button type="submit" class="btn btn-default btn-block" name="update">Update Email</button>
</form>
