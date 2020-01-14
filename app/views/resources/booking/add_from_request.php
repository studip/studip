<? if ($show_form): ?>
    <form class="default" method="post"
          action="<?= URLHelper::getLink(
                  'dispatch.php/resources/booking/add_from_request/'
                  . $resource->id
                  . '/'
                  . $request->id
                  )?>"
          <?= Request::isDialog() ? 'data-dialog="size=auto; reload-on-close"' : '' ?>>
        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= _('Details zur Anfrage') ?></legend>
            <h3><?= _('Angefragte Zeiträume')?></h3>
            <ul>
                <? $appointments = explode("\n", $request->getDateString()) ?>
                <? foreach ($appointments as $appointment): ?>
                    <li><?= htmlReady($appointment) ?></li>
                <? endforeach ?>
            </ul>
            <h3><?= _('Zu buchende Ressource') ?></h3>
            <?= htmlReady($resource->getFullName()) ?>
        </fieldset>
        <fieldset>
            <legend><?= _('Buchungsoptionen') ?></legend>
            <label>
                <?= _('Rüstzeit (Minuten)') ?>
                <input type="number" min="0"
                       max="<?= htmlReady($max_preparation_time) ?>"
                       value="<?= htmlReady($preparation_time) ?>"
                       name="preparation_time">
            </label>
            <label>
                <input type="checkbox" name="notify_lecturers" value="1"
                       <?= $notify_lecturers ? 'checked="checked"' : ''?>>
                <?= _('Lehrende über die Buchung benachrichtigen') ?>
            </label>
            <label>
                <?= _('Beschreibung') ?>
                <textarea name="description"><?= htmlReady($description) ?></textarea>
            </label>
            <label>
                <?= _('Interner Kommentar zur Buchung') ?>
                <textarea name="internal_comment"><?= htmlReady($internal_comment) ?></textarea>
            </label>
        </fieldset>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Speichern'), 'save') ?>
        </div>
    </form>
<? endif ?>
