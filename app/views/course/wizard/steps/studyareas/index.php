<legend>
    <?= _('Studienbereiche') ?>
</legend>
<div id="assigned">
    <h2>
        <span class="required">
            <?= _('Bereits zugewiesen') ?>
        </span>
    </h2>
    <ul class="css-tree">
        <li class="sem-tree-assigned-root keep-node" data-id="root"<?=
                $assigned ? ' class="hidden-no-js hidden-js"' : '' ?>>
            <?= htmlReady($GLOBALS['UNI_NAME_CLEAN']) ?>
            <ul>
            <?php foreach ($assigned as $element) : ?>
            <?= $element->name ?>
            <?= $this->render_partial('studyareas/_assigned_node',
                    ['element' => $element, 'studyareas' => $values['studyareas']]) ?>
            <?php endforeach ?>
            </ul>
        </li>
    </ul>
    <? if (!$stepnumber && !$values['locked']) : ?>
        <div data-dialog-button class="hidden-no-js">
            <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        </div>
    <? endif ?>
</div>
<? if (!$values['locked']) : ?>
    <div id="studyareas" data-ajax-url="<?= $ajax_url ?>"
        data-forward-url="<?= $no_js_url ?>" data-no-search-result="<?=_('Es wurde kein Suchergebnis gefunden.') ?>">
        <h2><?= _('Alle Studienbereiche') ?></h2>
        <div>
            <input style="width:auto" type="text" size="40" name="search" id="sem-tree-search"
                   value="<?= $values['searchterm'] ?>"/>
            <span id="sem-tree-search-start">
                <?= Icon::create('search', 'clickable')->asInput(["name" => 'start_search', "onclick" => "return STUDIP.CourseWizard.searchTree()", "class" => $search_result?'hidden-no-js':'']) ?>
            </span>
            <span id="sem-tree-search-reset" class="hidden-js">
                <?= Icon::create('refresh', 'clickable')->asInput(["name" => 'reset_search', "onclick" => "return STUDIP.CourseWizard.resetSearch()", "class" => $search_result?'':' hidden-no-js']) ?>
            </span>
        </div>
        <div id="sem-tree-assign-all" class="hidden-js hidden-no-js">
            <a href="" onclick="return STUDIP.CourseWizard.assignAllNodes()">
                <?= Icon::create('arr_2left', 'sort')->asImg() ?>
                <?= _('Alle Suchergebnisse zuweisen') ?>
            </a>
        </div>
        <ul class="collapsable css-tree">
            <li class="sem-tree-root tree-loaded keep-node">
                <input type="checkbox" id="root" checked="checked">
                <label for="root" class="undecorated">
                    <?= htmlReady($GLOBALS['UNI_NAME_CLEAN']) ?>
                </label>
                <ul>
                <?php foreach ($tree as $node) : ?>
                <?= $this->render_partial('studyareas/_node',
                        ['node' => $node, 'stepnumber' => $stepnumber,
                            'temp_id' => $temp_id, 'values' => $values,
                            'open_nodes' => $open_nodes ?: [],
                            'search_result' => $search_result ?: []]) ?>
                <?php endforeach ?>
                </ul>
            </li>
        </ul>
    </div>
    <?php if ($values['open_node']) : ?>
    <input type="hidden" name="open_node" value="<?= $values['open_node'] ?>"/>
    <?php endif ?>
    <?php if ($values['searchterm']) : ?>
    <input type="hidden" name="searchterm" value="<?= $values['searchterm'] ?>"/>
    <?php endif ?>
    <script>
    //<!--
    $(function() {
        var element = $('#sem-tree-search');
        element.on('keypress', function(e) {
            if (e.keyCode == 13) {
                if (element.val() != '') {
                    return STUDIP.CourseWizard.searchTree();
                } else {
                    return STUDIP.CourseWizard.resetSearch();
                }
            }
        });
    });
    //-->
    </script>
<? endif ?>
