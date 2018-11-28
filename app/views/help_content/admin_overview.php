<? use Studip\Button; ?>
<? if ($count = count($help_contents)) : ?>
    <form action="<?= $controller->url_for('help_content/store_settings') ?>" method="post">
        <input type="hidden" name="help_content_searchterm" value="<?= $help_content_searchterm ?>">
        <?= CSRFProtection::tokenTag() ?>
        <table class="default">
            <caption>
                <?= sprintf(ngettext('%u Hilfe-Text', '%u Hilfe-Texte', $count), $count) ?>
            </caption>
            <colgroup>
                <col width="20">
                <col width="20%">
                <col width="10%">
                <col>
                <col width="80">
            </colgroup>
            <thead>
                <tr>
                    <th><?= _("Aktiv") ?></th>
                    <th><?= _("Seite") ?></th>
                    <th><?= _("Sprache") ?></th>
                    <th><?= _("Inhalt") ?></th>
                    <th><?= _("Aktion") ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($help_contents as $help_content_id => $help_content) : ?>
                    <tr>
                        <td><input type="checkbox" name="help_content_status_<?= $help_content_id ?>"
                                   id="help_content_status_<?= $help_content_id ?>" value="1"
                                   class="studip-checkbox help_on"
                                <?= tooltip(_("Status der Hilfe (aktiv oder inaktiv)"), false) ?><?= ($help_content->visible) ? ' checked' : '' ?>>
                            <label for="help_content_status_<?= $help_content_id ?>">
                                <? _('Status der Hilfe (aktiv oder inaktiv)') ?>
                            </label>
                        </td>
                        <td><?= htmlReady($help_content->route) ?></td>
                        <td><?= htmlReady($help_content->language) ?></td>
                        <td><?= formatReady($help_content->content) ?></td>
                        <td>
                            <a href="<?= URLHelper::getURL('dispatch.php/help_content/edit/' . $help_content_id) ?>" <?= tooltip(_('Hilfe-Text bearbeiten')) ?>
                               data-dialog="size=auto;reload-on-close">
                                <?= Icon::create('edit', 'clickable')->asImg() ?></a>
                            <a href="<?= URLHelper::getURL('dispatch.php/help_content/delete/' . $help_content_id) ?>" <?= tooltip(_('Hilfe-Text löschen')) ?>
                               data-dialog="size=auto;reload-on-close">
                                <?= Icon::create('trash', 'clickable')->asImg() ?></a>
                        </td>
                    </tr>
                <? endforeach ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6">
                        <?= Button::createAccept(_('Speichern'), 'save_help_content_settings') ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
<? else : ?>
    <?= _('Keine Hilfe-Texte vorhanden.') ?>
<? endif ?>
