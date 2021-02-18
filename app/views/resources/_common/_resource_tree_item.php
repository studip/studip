<?php
$children = $resource->children;
$has_children = count($children) > 0;
?>
<article class="studip <?= $has_children ? 'toggle' : ''?> <?= $open ? 'open' : ''?>">
    <header>
        <h1>
            <a href="#"><?= htmlReady($resource->getFullName()) ?></a>
        </h1>
        <a href="<?= $resource->getActionLink('show') ?>" data-dialog>
            <?= Icon::create('info-circle') ?>
        </a>
    </header>
    <? if ($has_children) : ?>
        <section>
            <? foreach ($children as $child) : ?>
                <?= $this->render_partial(
                    'resources/_common/_resource_tree_item',
                    [
                        'resource' => $child,
                        'open' => false
                    ]
                ) ?>
            <? endforeach ?>
        </section>
    <? else : ?>
        <section>
            <?= htmlReady($resource->description) ?>
        </section>
    <? endif ?>
</article>
