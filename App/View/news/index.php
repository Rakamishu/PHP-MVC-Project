<?php foreach($data['news'] as $key => $news): ?>
    <a href="<?= SITE_ADDR.'/public/news/read/'.$news->id; ?>"><?= $news->title; ?></a> <br />
<?php endforeach; ?>

    
<ul class="pagination">
    <?= $data['pagination'] ?>
</ul>
