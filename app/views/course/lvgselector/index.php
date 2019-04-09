<? if (!$locked) : ?>
    <form action="<?= $controller->url_for('course/lvgselector/index/' . $course_id, $url_params) ?>" method="post">
<? endif ?>
<h1><?= _('Lehrveranstaltungsgruppen') ?></h1>
<div id="assigned" data-ajax-url="<?= $ajax_url ?>" data-forward-url="<?= $no_js_url ?>">
    <h2>
        <span class="required">
            <?= _('Bereits zugewiesen') ?>
        </span>
    </h2>
    <ul class="css-tree">
        <li class="lvgroup-tree-assigned-root keep-node" data-id="root">
            <ul id="lvgroup-tree-assigned-selected">
              <? foreach ($selection->getAreas() as $area) : ?>
            	<?= $this->render_partial('course/wizard/steps/lvgroups/lvgroup_entry', compact('area')) ?>
              <? endforeach ?>
            </ul>
        </li>
    </ul>
    <? if (!$locked) : ?> 
    <div data-dialog-button class="hidden-no-js"> 
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?> 
    </div> 
    <? endif ?>
</div>
<? if (!$locked) : ?>
    <div id="lvgroup-tree-open-nodes">
    <? foreach ($open_lvg_nodes as $opennode): ?>
            <input type="hidden" name="open_lvg_nodes[]" value="<?= $opennode; ?>">
    <? endforeach; ?>
    </div>
    <div id="studyareas" data-ajax-url="<?= $ajax_url ?>"
        data-forward-url="<?= $no_js_url ?>" data-no-search-result="<?=_('Es wurde kein Suchergebnis gefunden.') ?>">
        <h2><?= _('Lehrveranstaltungsgruppen Suche') ?></h2>
        <div>
            <input type="text" style="width: auto;" size="40" name="search" id="lvgroup-tree-search"
                   value="<?= $searchterm ?>">
            <span id="lvgroup-tree-search-start">
                <?= Icon::create('search', 'clickable')->asInput(["name" => 'start_search', "onclick" => "return MVV.CourseWizard.searchTree()", "class" => $search_result?'hidden-no-js':'']) ?>
            </span>
            <span id="lvgroup-tree-search-reset" class="hidden-js">
                <?= Icon::create('refresh', 'clickable')->asInput(["name" => 'reset_search', "onclick" => "return MVV.CourseWizard.resetSearch()", "class" => $search_result?'':' hidden-no-js']) ?>
            </span>
        </div>

        <div id="lvgsearchresults" style="display: none;">
            <h2><?= _('Suchergebnisse') ?></h2>
            <ul class="collapsable css-tree">

            </ul>
        </div>
        <h2><?= _('Alle Lehrveranstaltungsgruppen') ?></h2>
        <ul class="collapsable css-tree">
            <li class="lvgroup-tree-root tree-loaded keep-node">
                <input type="checkbox" id="root" checked="checked"/>
                <label for="root" class="undecorated">
                    <?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?>
                </label>
                <ul>
                <? $pos_id = 1; ?>
                <? foreach ((array) $tree as $node) : ?>
                    <? $children = $node->getChildren(); ?>
                    <? if (count($children) || $node->isAssignable()) : ?>
                    <?= $this->render_partial('course/wizard/steps/lvgroups/_node',
                        ['node' => $node, 'pos_id' => $pos_id++,
                            'open_nodes' => $open_lvg_nodes ?: [],
                            'search_result' => $search_result ?: [],
                            'children' => $children]) ?>
                    <? endif; ?>
                <? endforeach; ?>
                </ul>
            </li>
        </ul>
    </div>
    <? if ($open_lvg_nodes) : ?>
    <input type="hidden" name="open_nodes" value="<?= json_encode($open_lvg_nodes) ?>">
    <? endif ?>
    <? if ($searchterm) : ?>
    <input type="hidden" name="searchterm" value="<?= $searchterm ?>">
    <? endif ?>
    <script>
    //<!--
    $(function() {
        var element = $('#lvgroup-tree-search');
        element.on('keypress', function(e) {
            if (e.keyCode == 13) {
                if (element.val() != '') {
                    return MVV.CourseWizard.searchTree();
                } else {
                    return MVV.CourseWizard.resetSearch();
                }
            }
        });
    });
    //-->
    </script>
<? endif ?>
<? if(!$locked) : ?>
</form>
<? endif ?>
