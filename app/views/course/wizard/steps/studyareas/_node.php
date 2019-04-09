<?php if (!$search_result || in_array($node->id, $search_result)) : ?>
<li class="sem-tree-<?= htmlReady($node->id) ?> keep-node" data-id="<?= $node->id ?>">
    <?php if ($node->isAssignable()) : ?>
    <?= Icon::create('arr_2left', 'sort')->asInput(["name" => 'assign['.$node->id.']', "onclick" => "return STUDIP.CourseWizard.assignNode('".$node->id."')", "class" => in_array($node->id,$values['studyareas']?:[])?'hidden-no-js':'', "style" => in_array($node->id,$values['studyareas']?:[])?'display:none':false]) ?>
    <?php endif ?>
    <?php if ($node->hasChildren()) : ?>
    <input type="checkbox" id="<?= htmlReady($node->id) ?>"<?= (in_array($node->id, $open_nodes) && $node->parent_id != $values['open_node']) ? ' checked="checked"' : '' ?>/>
    <label onclick="return STUDIP.CourseWizard.getTreeChildren('<?= htmlReady($node->sem_tree_id) ?>', true)"
           for="<?= htmlReady($node->id) ?>" class="undecorated">
        <a href="<?= URLHelper::getLink($no_js_url,
            ['open_node' => $node->id]) ?>">
    <?php endif ?>
        <?= htmlReady($node->name) ?>
    <?php if ($node->hasChildren()) : ?>
        </a>
    </label>
    <ul>
        <?php if ($node->hasChildren() && in_array($node->id, $open_nodes) && $node->_parent->id != $values['open_node']) : ?>
            <?php foreach ($node->getChildren() as $child) : ?>
                <?= $this->render_partial('studyareas/_node',
                    ['node' => $child, 'stepnumber' => $stepnumber,
                        'temp_id' => $temp_id, 'values' => $values,
                        'open_nodes' => $open_nodes ?: [],
                        'search_result' => $search_result ?: []]) ?>
            <?php endforeach ?>
        <?php endif ?>
    </ul>
    <?php endif ?>
</li>
<?php endif ?>
