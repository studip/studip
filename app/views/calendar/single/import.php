<?
use Studip\Button, Studip\LinkButton;
SkipLinks::addIndex(_('Termine importieren'), 'main_content', 100);
?>
<form action="<?= $controller->url_for('calendar/single/import/'
        . $calendar->getRangeId(), ['atime' => $atime, 'last_view' => $last_view]) ?>" method="post" enctype="multipart/form-data" class="default">

    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= sprintf(_('Termine importieren')) ?>
        </legend>

        <label for="event-type">
            <input type="checkbox" name="import_privat" value="1" checked>
            <?= _('Öffentliche Termine als "privat" importieren') ?>
        </label>

        <label class="file-upload">
            <?= _('Datei zum Importieren wählen') ?>
            <input type="file" name="importfile">
        </label>

        <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('Termine importieren'), 'import', ['title' => _('Termine importieren')]) ?>
        <? if (!Request::isXhr()) : ?>
        <?= LinkButton::create(_('Abbrechen'), $controller->url_for('calendar/single/' . $last_view)) ?>
        <? endif; ?>
    </footer>
</form>
