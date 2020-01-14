<ul class="resource-tree">
    <? foreach($resources as $resource): ?>
        <? $selected = $resource->id == $selected_resource; ?>
        <? $resource = $resource->getDerivedClassInstance() ?>
        <? $resource_class = get_class($resource); ?>
        <? $search_object = strtolower($resource_class) . '_' . $resource->id; ?>

        <li <?=  ((!$resource_path && $resource->level > $max_open_depth) || $hide) ? 'style="display: none;"' : ''; ?> >
            <? if (count($resource->children)): ?>

                <?= Icon::create('arr_1right', 'clickable')->asImg(
                    '16px',
                    [
                        'class' => implode(
                            ' ',
                            [
                                'resource-tree-node',
                                (
                                    in_array($resource->id, $resource_path) || (!$resource_path && $resource->level < $max_open_depth)
                                    ? 'rotated'
                                    : ''
                                )
                            ]
                        ),
                        'style' => implode(
                            '; ',
                            [
                                (
                                    in_array($resource->id, $resource_path) || (!$resource_path && $resource->level < $max_open_depth)
                                    ? 'transform: rotate(90deg)'
                                    : ''
                                ),
                                'cursor: pointer;'
                            ]
                        ),
                        'onClick' => 'STUDIP.Resources.toggleTreeNode($(this).parent());'
                    ]
                ) ?>

            <? endif ?>
            <span id="<?= $search_object; ?>" style="cursor: pointer;">
                <?= $resource->getIcon('clickable')->asImg(
                    '16px',
                    [
                        'class' => 'text-bottom'
                    ]
                ) ?>
                <?= htmlReady($resource->name) ?>
            </span>
            <script type="text/javascript">
                jQuery(function ($) {
                    $('#<?= $search_object; ?>').on('click', function(event) {
                        $('input[name="special__building_location"]').val('<?= $search_object; ?>');
                        $('button[name="room_search"]').trigger('click');
                    });
                });
            </script>
            <? if ($resource->children): ?>
                <?= $this->render_partial(
                    'sidebar/room-search-tree-widget',
                    [
                        'resources' => $resource->children->orderBy('sort_position DESC, name'),
                        'selected_resource' => $selected_resource,
                        'resource_path' => $resource_path,
                        'hide' => ($resource_path && !in_array($resource->id, $resource_path))
                    ]
                ) ?>
                <? endif ?>
            </a>
        </li>
    <? endforeach ?>
</ul>
