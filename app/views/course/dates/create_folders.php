<form class="default" action="<?= $controller->link_for() ?>" method="POST"
      data-dialog="size=auto">
    <?=CSRFProtection::tokenTag()?>
    <? foreach ($previously_selected_dates as $date_id): ?>
        <input type="hidden" name="previously_selected_dates[]"
               value="<?= htmlReady($date_id) ?>">
    <? endforeach ?>
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
            <input type="checkbox" name="course_date_folders[]" value="<?=htmlReady($one->id)?>"
                   <?= in_array($one->id, $course_date_folders) ? 'checked="checked"' : '' ?>>
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
        <? if ($show_confirmation_button): ?>
            <?= Studip\Button::create(_('Trotzdem erstellen'), 'force_go') ?>
        <? else: ?>
            <?= Studip\Button::create(_('Ordner erstellen'), 'go') ?>
        <? endif ?>
    </div>
</form>
