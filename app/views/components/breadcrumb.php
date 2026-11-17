<?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <?php foreach($breadcrumbs as $name => $link): ?>
            <?php if($link === '#'): ?>
                <li class="breadcrumb-item active" aria-current="page"><?= $name ?></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?= $link ?>"><?= $name ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
<?php endif; ?>
