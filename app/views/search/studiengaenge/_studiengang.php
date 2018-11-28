<dt class="<?= $cycle_class ?>" id="<?= $category->getId() ?>">
    <a href="<?= $controller->url_for('search/studiengang/info/' . $category->getId() . '#' . $modul->getId()) ?>">
        <?= htmlReady($modul->getDisplayName() . ' (' . $modul->responsible_institute->institute->getDisplayName() . ')'); ?>
    </a>
</dt>
<dd class="odd">
    <? if ($details_id == $modul->getId()) : ?>
    <div>
        <? if ($semester_select) : ?> 
        <div style="width: 50%; float:left;">
            <form name="semesterSelect" action="<?= $controller->url_for('search/module/overview/' . $modul->getId() . '#' . $modul->getId()) ?>" method="GET">
                <?= _('Semesterauswahl') ?>:
                <select name="sem_select" class="submit-upon-select">
                <? foreach ($semester_select as $sem) : ?>
                    <option value="<?= $sem->getId() ?>"<?= $sem->getId() == $selected_semester ? ' selected' : '' ?>>
                        <?= htmlReady($sem->name) ?>
                    </option>
                <? endforeach; ?>
                </select>
                <?= Icon::create('accept', 'clickable')->asInput(); ?>
            </form>
        </div>
        <div style="text-align:right;">
            <a data-dialog href="<?= $controller->url_for('search/module/description/' . $modul->getId()) ?>">
            <?= _('Vollständige Modulbeschreibung') ?>
            </a>
        </div>
        <? else : ?>
        <div style="text-align:right;">
            <a href="<?= $controller->url_for('search/module/overview/' . $modul->getId()) ?>">
            <?= _('Modulübersicht') ?>
            </a>
        </div>
        <? endif; ?>
        <?= $modul_content ?>
    </div>
    <? endif; ?>
</dd>
