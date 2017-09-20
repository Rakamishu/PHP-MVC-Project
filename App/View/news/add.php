
<?php if(isset($_SESSION['flash_message'])): ?>
    <?= $_SESSION['flash_message'] ?>
<?php endif; ?>

<script>tinymce.init({ selector:'textarea' });</script>

<form action="http://localhost/MVC/public/admin/news/add" method="post">
    title:<input type="text" name="title" /> <br />
    
    <textarea name="content"></textarea><br />
    <input type="hidden" name="csrf" value="<?= $data['csrf'] ?>" />
    <input type="submit" name="add" value="add news">
</form>