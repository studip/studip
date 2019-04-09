<?
$start_pages = [
    '' => _('keine'),
     1 => _('Meine Veranstaltungen'),
     3 => _('Mein Stundenplan'),
     5 => _('Mein Terminkalender'),
     4 => _('Mein Adressbuch'),
     6 => _('Mein globaler Blubberstream'),
];
?>

<form method="post" action="<?= $controller->url_for('settings/general/store') ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <fieldset>
        <legend>
            <?= _('Allgemeine Einstellungen') ?>
        </legend>

        <label>
            <?= _('Sprache') ?>
            <select name="forced_language" class="size-s">
                <? foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $key => $language): ?>
                    <option value="<?= $key ?>"
                        <? if ($user_language == $key) echo 'selected'; ?>>
                        <?= $language['name'] ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <? if (!$GLOBALS['perm']->have_perm('root')): ?>
        <label>
            <?= _('Persönliche Startseite') ?>
            <?= tooltipHtmlIcon(_('Sie können hier einstellen, welche Seite standardmäßig nach dem Einloggen '
                .'angezeigt wird. Wenn Sie zum Beispiel regelmäßig die Seite &raquo;Meine '
                .'Veranstaltungen&laquo; nach dem Login aufrufen, so können Sie dies hier '
                .'direkt einstellen.')) ?>
            <select name="personal_startpage">
            <? foreach ($start_pages as $index => $label): ?>
                <option value="<?= $index ?>" <? if ($config->PERSONAL_STARTPAGE == $index) echo 'selected'; ?>>
                    <?= htmlReady($label) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
        <? endif ?>

        <label>
            <input type="checkbox" name="skiplinks_enable"
                   value="1"
                <? if ($config->SKIPLINKS_ENABLE) echo 'checked'; ?>>
            <?= _('Skiplinks einblenden') ?>
            <?= tooltipIcon(_('Mit dieser Einstellung wird nach dem ersten Drücken der Tab-Taste eine '
                .'Liste mit Skiplinks eingeblendet, mit deren Hilfe Sie mit der Tastatur '
                .'schneller zu den Hauptinhaltsbereichen der Seite navigieren können. '
                .'Zusätzlich wird der aktive Bereich einer Seite hervorgehoben.')) ?>
        </label>

        <label>
            <input type="checkbox" name="accesskey_enable"
                   aria-describedby="accesskey_enable_description" value="1"
                <? if ($config->ACCESSKEY_ENABLE) echo 'checked'; ?>>
            <?= _('Tastenkombinationen für Hauptfunktionen') ?>
            <?= tooltipHtmlIcon(_('Mit dieser Einstellung können Sie für die meisten in der Kopfzeile '
                .'erreichbaren Hauptfunktionen eine Bedienung über Tastenkombinationen '
                .'aktivieren. <br>Die Tastenkombination wird im Tooltip des jeweiligen '
                .'Icons angezeigt.')." "._('Diese kann für jeden Browser und jedes Betriebssystem unterschiedlich '
                    .'sein (siehe <a href="http://en.wikipedia.org/wiki/Accesskey" '
                    .'target="_blank" rel="noopener noreferrer">Wikipedia</a>).')) ?>
        </label>

        <label>
            <input type="checkbox"
                   name="showsem_enable"
                   value="1"
                <? if ($config->SHOWSEM_ENABLE) echo 'checked'; ?>>
            <?= _('Semesteranzeige auf &raquo;Meine Veranstaltungen&laquo;');?>
            <?= tooltipIcon(_('Mit dieser Einstellung können Sie auf der Seite &raquo;Meine '
                .'Veranstaltungen&laquo; die Einblendung des Start- und Endsemesters '
                .'hinter jeder Veranstaltung aktivieren.')) ?>
        </label>

        <? if (get_config('TOURS_ENABLE')) : ?>
        <label>
            <input type="checkbox" name="tour_autostart_disable"
                   aria-describedby="tour_autostart_disable_description" value="1"
                <? if ($config->TOUR_AUTOSTART_DISABLE) echo 'checked'; ?>>
            <?= _('Autostart von Touren verhindern');?>
            <?= tooltipIcon(_('Mit dieser Einstellung können Sie verhindern, dass Touren zu einzelnen '
                .'Stud.IP-Seiten automatisch starten, wenn Sie die Seite aufrufen. Die Touren '
                .'können weiterhin über die Hilfe gestartet werden.')) ?>
        </label>
        <? endif ?>
    </fieldset>

    <fieldset>
        <legend>
            <?= _('Benachrichtigungen') ?>
        </legend>

        <label>
            <input type="checkbox" name="personal_notifications_activated"
                   aria-describedby="personal_notifications_activated_description" value="1"
                <? if (PersonalNotifications::isActivated($user->user_id)) echo 'checked'; ?>>
            <?= _('Benachrichtigungen über Javascript') ?>
            <?= tooltipIcon(_('Hiermit wird in der Kopfzeile dargestellt, wenn es Benachrichtigungen für '
                .'Sie gibt. Die Benachrichtigungen werden auch angezeigt, wenn Sie nicht die '
                .'Seite neuladen.')) ?>
        </label>

        <label>
            <input type="checkbox" name="personal_notifications_audio_activated"
                   aria-describedby="personal_notifications_audio_activated_description" value="1"
                <? if (PersonalNotifications::isAudioActivated($user->user_id)) echo 'checked'; ?>>
            <?= _('Audio-Feedback zu Benachrichtigungen') ?>
            <?= tooltipIcon(_('Wenn eine neue Benachrichtigung für Sie reinkommt, ' .
                'werden Sie mittels eines kleinen Plopps darüber in Kenntnis gesetzt ' .
                '- auch wenn Sie gerade einen anderen Browsertab anschauen. Der Plopp ist ' .
                'nur zu hören, wenn Sie die Benachrichtigungen über Javascript aktiviert haben.')) ?>
        </label>
    </fieldset>

    <footer>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </footer>
</form>
