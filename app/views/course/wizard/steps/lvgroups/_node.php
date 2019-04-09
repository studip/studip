<? if (!$search_result || in_array($node->id, $search_result)) : ?>
<? $id = $node->id . '-' . $pos_id; ?>
<li class="lvgroup-tree-<?= htmlReady($id) ?> keep-node" data-id="<?= $id ?>" data-type="<?= get_class($node) ?>">
    <? if ($node->isAssignable()) : ?>
        <?= Icon::create('arr_2left', 'sort')->asInput(["name" => 'assign[' . $node->id . ']', "onclick" => "return MVV.CourseWizard.assignNode('" . $node->id . "')", "style" => in_array($id, $selection->getLvGruppenIDs()) ? 'display: none;' : '']) ?>
        <?= htmlReady($node->getDisplayname()) ?>
    <? else : ?>
    <input type="checkbox" id="<?= htmlReady($id) ?>"<?= (in_array($id, $open_nodes)) ? ' checked="checked"' : '' ?>/>
    <label onclick="return MVV.CourseWizard.getTreeChildren('<?= htmlReady($id) ?>', true, '<?= htmlReady(get_class($node)) ?>')"
           for="<?= htmlReady($id) ?>" class="undecorated">
        <a href="<?= URLHelper::getLink($no_js_url,
            ['open_node' => $id, 'open_nodes' => json_encode($open_nodes)]) ?>">
        <?= htmlReady($node->getDisplayname()) ?>
        </a>
    </label>
    <input type="hidden" name="open_nodes[]" value="<?= $id; ?>">
    <ul>
        <? if (in_array($id, $open_nodes)) : ?>
            <? $i = 1; ?>
            <? foreach ($children as $child) : ?>
                <? $children = $child->getChildren(); ?>
                <? if (count($children) || $child->isAssignable()) : ?>
                <?= $this->render_partial('course/wizard/steps/lvgroups/_node',
                    ['node' => $child, 'stepnumber' => $stepnumber,
                        'pos_id' => $pos_id . '_' . $i++, 'open_nodes' => $open_nodes ?: [],
                        'search_result' => $search_result ?: [],
                        'children' => $children]) ?>
                <? endif; ?>
            <? endforeach ?>
        <? endif; ?>
    </ul>
    <? endif; ?>
</li>
<? endif; ?>
