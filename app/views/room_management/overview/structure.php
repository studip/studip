<? foreach ($locations as $index => $location) : ?>
    <?= $this->render_partial(
        'resources/_common/_resource_tree_item.php',
        [
            'resource' => $location,
            'open' => (count($locations) === 1 || $index === 0)
        ]
    ) ?>
<? endforeach ?>
