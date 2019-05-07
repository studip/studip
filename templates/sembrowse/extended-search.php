<?php
SkipLinks::addIndex(_('Suchformular'), 'search_sem_xts', 100);
// add skip link for simple search here
SkipLinks::addLink(
    _('Schnellsuche'),
    URLHelper::getURL('dispatch.php/search/courses', ['cmd' => 'qs', 'level' => 'f']),
    120
);
?>

<?= $search_obj->getFormStart(URLHelper::getLink('?send=yes'), ['class' => 'default']) ?>

<fieldset>
    <legend>
        <?= _('Erweiterte Suche') ?>
    </legend>

    <label class="col-3">
        <?= _('Titel') ?>
        <?= $search_obj->getSearchField('title') ?>
    </label>

    <label class="col-3">
        <?= _('Untertitel') ?>
        <?= $search_obj->getSearchField('sub_title') ?>
    </label>

    <label class="col-3">
        <?= _('Nummer') ?>
        <?= $search_obj->getSearchField('number') ?>
    </label>

    <label class="col-3">
        <?= _('Kommentar') ?>
        <?= $search_obj->getSearchField('comment') ?>
    </label>

    <label class="col-3">
        <?= _('Lehrende') ?>
        <?= $search_obj->getSearchField('lecturer') ?>
    </label>

    <? if ($show_class): ?>
    <label class="col-3">
        <?= _('Bereich') ?>
        <?= $search_obj->getSearchField('scope') ?>
    </label>
    <? endif; ?>

    <label class="col-2">
        <?= _('Typ') ?>
        <?= $search_obj->getSearchField('type') ?>
    </label>

    <label class="col-2">
        <?= _('Verknüpfung') ?>
        <?= $search_obj->getSearchField('combination') ?>
    </label>

    <label>
        <div class="text-center">
            <?= $search_obj->getSearchButton([
                'class' => 'search'
                ]) ?>
            <?= Studip\LinkButton::createCancel(
                _('Zurücksetzen'),
                URLHelper::getURL('?cmd=xts&reset_all=1&level='.$sem_browse_data['level']),
                ['title' => _('Zurücksetzen')]
            ) ?>
        </div>
    </label>
</fieldset>
<?= $search_obj->getFormEnd() ?>
