<? use Studip\Button, Studip\LinkButton; ?>
<form action="" method="post">
    <div>
        <b><?= _('Eigener Teilnahmestatus') ?>:</b>
        <? $group_status = [
            CalendarEvent::PARTSTAT_TENTATIVE => _('Abwartend'),
            CalendarEvent::PARTSTAT_ACCEPTED => _('Angenommen'),
            CalendarEvent::PARTSTAT_DECLINED => _('Abgelehnt'),
            CalendarEvent::PARTSTAT_DELEGATED => _('Angenommen (keine Teilnahme)')] ?>
        <ul>
        <? foreach ($group_status as $value => $name) : ?>
            <ul class="list-unstyled">
                <label>
                    <input type="radio" value="<?= $value ?>" name="status" <?= $value == $event->group_status ? ' checked' : '' ?>>
                    <?= $name ?>
                </label>
            </li>
        <? endforeach; ?>
        </ul>
    </div>
    <div>
        <? $author = $event->getAuthor() ?>
        <? if ($author) : ?>
            <?= sprintf(_('Eingetragen am: %s von %s'),
            strftime('%x, %X', $event->mkdate),
                htmlReady($author->getFullName('no_title'))) ?>
        <? endif; ?>
    </div>
    <? if ($event->event->mkdate < $event->event->chdate) : ?>
        <? $editor = $event->getEditor() ?>
        <? if ($editor) : ?>
        <div>
            <?= sprintf(_('Zuletzt bearbeitet am: %s von %s'),
                strftime('%x, %X', $event->chdate),
                    htmlReady($editor->getFullName('no_title'))) ?>
        </div>
        <? endif; ?>
    <? endif; ?>
    <div style="text-align: center;" data-dialog-button>
        <?= Button::create(_('Speichern'), 'store', ['title' => _('Termin speichern')]) ?>
        <? if ($event->havePermission(Event::PERMISSION_DELETABLE)) : ?>
        <?= LinkButton::create(_('Löschen'), $controller->url_for('calendar/single/delete/' . implode('/', $event->getId()))) ?>
        <? endif; ?>
        <? if (!Request::isXhr()) : ?>
        <?= LinkButton::create(_('Abbrechen'), $controller->url_for('calendar/single/' . $last_view, [$event->getStart()])) ?>
        <? endif; ?>
    </div>
</form>
