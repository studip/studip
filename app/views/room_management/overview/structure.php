<? foreach ($locations as $location) : ?>
    <?= $this->render_partial(
        'resources/_common/_resource_tree_item.php',
        ['resource' => $location]
    ) ?>
<? endforeach ?>
