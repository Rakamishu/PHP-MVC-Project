
<?php if(isset($_SESSION['flash_message'])): ?>
    <?= $_SESSION['flash_message'] ?>
<?php endif; ?>

<script>tinymce.init({ selector:'textarea' });</script>

<form action="http://localhost/MVC/public/admin/news/edit/<?= $data['news']->id ?>" method="post">
    title:<input type="text" name="title" value="<?= $data['news']->title ?>" /> <br />
    
    <textarea name="content" rows="20" cols="50"><?= $data['news']->content ?></textarea><br />
    <input type="hidden" name="csrf" value="<?= $data['csrf'] ?>" />
    <input type="submit" name="edit" value="edit news">
</form>