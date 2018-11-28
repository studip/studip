<?php
SkipLinks::addIndex(_('Suchformular'), 'search_sem_qs', 100);
?>

<?= $search_obj->getFormStart(URLHelper::getLink(), ['class' => 'default']) ?>

<fieldset>
    <legend>
        <?= $GLOBALS['SEM_CLASS'][$_SESSION['sem_portal']["bereich"]]["description"]
                ?: _('Suche nach Veranstaltungen') ?>
    </legend>

    <label class="col-3">
        <?= _('Suchbegriff') ?>
        <?= $quicksearch->render() ?>
    </label>

    <label class="col-3">
        <?= _('Suchen in') ?>
        <?= $search_obj->getSearchField('qs_choose', [ 'id' => 'search_sem_qs_choose' ]) ?>
    </label>

    <? if ($sem_browse_data['level'] === 'vv'): ?>
        <label class="col-3">
            <?= _('in') ?>
            <?= $search_obj->getSearchField('scope_choose', [ 'id' => 'search_sem_scope_choose' ] ,$sem_tree->start_item_id) ?>
            <input type="hidden" name="level" value="vv">
        </label>
    <? endif; ?>

    <? if ($sem_browse_data['level'] === 'ev'): ?>
        <label class="col-3">
            <?=  _('in') ?>:
            <?= $search_obj->getSearchField('range_choose', [ 'id' => 'search_sem_range_choose', ], $range_tree->start_item_id) ?>
            <input type="hidden" name="level" value="ev">
        </label>
    <? endif; ?>

    <input type="hidden" name="search_sem_sem" value="<?= htmlReady($_SESSION['sem_browse_data']['default_sem']) ?>">
</fieldset>

<footer>
    <span class="button-group">
        <?= $search_obj->getSearchButton(
            [
                'style' => 'vertical-align:middle',
                'class' => 'quicksearchbutton',
            ],
            true
        ) ?>
        <?
        $option = Request::get('option');
        $nav = SemBrowse::getSearchOptionNavigation(
                Config::get()->COURSE_SEARCH_NAVIGATION_OPTIONS[$option]['target'], $option);
        $url = $nav ? $nav->getUrl() :   URLHelper::getUrl('',
                [
                    'level' => $_SESSION['sem_browse_data']['level'],
                    'cmd'   => 'qs'
                ]);
        ?>
        <?= Studip\LinkButton::create(
            _('Zurücksetzen'),
            URLHelper::getURL($url, ['reset_all' => 1]),
            ['title' => _('Zurücksetzen')]
        ) ?>
    </span>
</footer>

<?= $search_obj->getFormEnd() ?>
