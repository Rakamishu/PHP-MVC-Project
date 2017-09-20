
<?php if(isset($_SESSION['flash_message'])): ?>
    <?= $_SESSION['flash_message'] ?>
<?php endif; ?>

<form id="login" method="post" action="http://localhost/MVC/public/user/settings/password" class="well">
    <div class="form-group">
        <label>Old password</label>
        <input type="text" name="password" class="form-control" placeholder="">
    </div>
    <div class="form-group">
        <label>New password</label>
        <input type="text" name="newpassword_repeat" class="form-control" placeholder="">
    </div>
    <div class="form-group">
        <label>Repeat New password</label>
        <input type="password" name="newpassword_repeat" class="form-control">
        <input type="hidden" name="csrf" value="<?= $data['csrf'] ?>" />
    </div>
    <button type="submit" class="btn btn-default btn-block" name="update">Update Password</button>
</form>
