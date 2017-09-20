
<?php if(isset($_SESSION['flash_message'])): ?>
    <?= $_SESSION['flash_message'] ?>
<?php endif; ?>

RECOVERY 
<form id="login" method="post" action="http://localhost/MVC/public/user/recovery" class="well">
    <div class="form-group">
        <label>Your E-mail</label>
        <input type="email" name="email" class="form-control" placeholder="E-mail">
        <input type="hidden" name="csrf" value="<?= $data['csrf'] ?>" />
    </div>
    <button type="submit" class="btn btn-default btn-block" name="recover">Request a new password</button>
</form>
