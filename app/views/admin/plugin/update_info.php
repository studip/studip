<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<?= sprintf(ngettext('Es ist ein Update für ein Plugin verfügbar', 'Es sind Updates für %d Plugins verfügbar', $num_updates), $num_updates) ?>

<form action="<?= $controller->url_for('admin/plugin/install_updates') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <div style="margin: 1ex;">
        <? foreach ($plugins as $plugin): ?>
            <? $pluginid = $plugin['id'] ?>
            <? if (isset($update_info[$pluginid]['update']) && !$plugin['depends']): ?>
                <div>
                    <label>
                        <input type="checkbox" name="update[]" value="<?= $pluginid ?>" checked>
                        <?= htmlReady(sprintf(_('%s: Version %s installieren'), $plugin['name'], $update_info[$pluginid]['update']['version'])) ?>
                    </label>
                </div>
            <? endif ?>
        <? endforeach ?>
    </div>
    
    <?= Button::createAccept(_('Starten'), 'doUpdate', ['title' => _('Updates installieren')])?>
    </form>
