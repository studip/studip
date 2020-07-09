<ul class="resource-tree">
    <? foreach ($resources as $resource): ?>
        <? $selected = $resource->id == $selected_resource; ?>
        <? $resource = $resource->getDerivedClassInstance() ?>
        <? $link = '';
        if ($parameter_name) {
            $link = URLHelper::getLink(
                $_SERVER['REQUEST_URI'],
                [
                    $parameter_name => $resource->id
                ]
            );
        } else {
            //$parameter_name is not set. Redirect to the resource's
            //details page:
            $link = $resource->getActionLink('show');
        }
        ?>
        <li <?= ((!$resource_path && $resource->level > $max_open_depth) || $hide) ? 'style="display: none;"' : ''; ?>>
            <? if (count($resource->children)): ?>
                <? if ($resource_path && !in_array($resource->id, $resource_path)): ?>
                    <a href="<?= $link ?>">
                <? endif; ?>
                <?= Icon::create('arr_1right', Icon::ROLE_CLICKABLE, [
                'class'   => (
                in_array($resource->id, $resource_path) || (!$resource_path && $resource->level < $max_open_depth)
                    ? 'rotated'
                    : ''
                ),
                'style'   =>
                    (in_array($resource->id, $resource_path) || (!$resource_path && $resource->level < $max_open_depth)
                        ? 'transform: rotate(90deg)'
                        : ''
                    ),
                'onClick' =>
                    (!$resource_path || in_array($resource->id, $resource_path)
                        ? 'STUDIP.Resources.toggleTreeNode($(this).parent());'
                        : ''
                    )]) ?>
                
                <? if ($resource_path && !in_array($resource->id, $resource_path)): ?>
                    </a>
                <? endif; ?>
            
            <? endif ?>
            <a href="<?= $link ?>" <?= !$resource_path ? 'data-dialog' : ''; ?>
               <?= $selected
                   ? 'class="selected-resource"'
                   : '' ?>>
                <?= $resource->getIcon($selected ? Icon::ROLE_INFO_ALT : Icon::ROLE_CLICKABLE)->asImg(
                    [
                        'class' => 'text-bottom'
                    ]
                ) ?>
                <?= htmlReady($resource->name) ?>
            </a>
            <? if ($resource->children): ?>
                <? if (!$resource_path || in_array($resource->id, $resource_path)): ?>
                    <?= $this->render_partial(
                        'sidebar/resource-tree-widget',
                        [
                            'resources'         => $resource->children->orderBy('sort_position DESC, name'),
                            'selected_resource' => $selected_resource,
                            'resource_path'     => $resource_path,
                            'hide'              => ($resource_path && !in_array($resource->id, $resource_path))
                        ]
                    ) ?>
                <? endif ?>
            <? endif ?>
            </a>
        </li>
    <? endforeach ?>
</ul>
