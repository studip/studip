<div id="globalsearch-searchbar">
    <input class="hidden-small-down" type="text" name="globalsearchterm" id="globalsearch-input"
           placeholder="<?= _('Was suchen Sie?') ?>">
    <?= Icon::create('decline', Icon::ROLE_INACTIVE)->asImg([
        'id' => 'globalsearch-clear',
        'class' => 'hidden-small-down'
    ]) ?>
    <?= Icon::create('search', Icon::ROLE_INFO_ALT)->asInput(16, [
        'id' => 'globalsearch-icon'
    ]) ?>
    <div id="globalsearch-list">
        <a href="#" id="globalsearch-togglehints" data-toggle-text="<?= _('Tipps ausblenden') ?>">
            <?= _('Tipps einblenden') ?>
        </a>
        <?= $GLOBALS['template_factory']->render('globalsearch/_hints') ?>
        <div id="globalsearch-searching">
            <?= _('Suche...') ?>
        </div>
        <div id="globalsearch-results" data-more-results="<?= _('alle anzeigen') ?>"
             data-no-result="<?= _('Keine Ergebnisse gefunden.') ?>"
             data-current-semester="<?= htmlReady(GlobalSearchModule::getCurrentSemester()) ?>"
             data-results-per-type="<?= Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE ?>"
        ></div>
    </div>
</div>
