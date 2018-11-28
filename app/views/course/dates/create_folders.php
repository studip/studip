<form class="default" action="<?= $controller->link_for() ?>" method="POST">
    <?=CSRFProtection::tokenTag()?>
    <fieldset>
        <legend>
            <?= _('Auswahl der Termine') ?>
        </legend>

        <label>
            <input type="checkbox" name="all" data-proxyfor=":checkbox[name^=course_date_folders]">
            <?=_('Alle auswählen')?>
        </label>

    <? foreach ($dates as $one) : ?>
        <label>
            <input type="checkbox" name="course_date_folders[]" value="<?=htmlReady($one->id)?>">
            <?= htmlReady(CourseDateFolder::formatDate($one)) ?>
            <? if ($one->folders->count()) : ?>
                <span style="font-size:smaller">
                    - <?= sprintf('%s Ordner vorhanden', $one->folders->count()) ?>
                </span>
            <? endif;?>
        </label>
    <? endforeach; ?>
    </fieldset>
    <fieldset>
        <legend>
            <?= _('Schreibberechtigung für Studierende') ?>
        </legend>
        <label>
            <input name="course_date_folder_perm_write" type="checkbox" checked value="1">
            <?= _('Studierende dürfen Dateien in diesen Ordner hochladen') ?>
        </label>
    </fieldset>
    <div data-dialog-button>
        <?= Studip\Button::create(_('Ordner erstellen'), 'go') ?>
    </div>
</form>
