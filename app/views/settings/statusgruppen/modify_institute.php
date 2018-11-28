<?
use Studip\Button, Studip\LinkButton;

// Datenfelder für Rollen in Einrichtungen ausgeben
// Default-Daten der Einrichtung
$entries = (array)DataFieldEntry::getDataFieldEntries([$user->user_id, $inst_id], 'userinstrole')
?>

<form action="<?= $controller->url_for('settings/statusgruppen/store/institute', $inst_id) ?>" method="post"
      class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="name" value="<?= htmlReady($institute['name']) ?>">

    <label>
        <?= _('Status') ?>
        <? if ($GLOBALS['perm']->have_studip_perm('admin', $inst_id) && $institute['inst_perms'] != 'admin' && !$locked): ?>
            <select name="status">
                <? foreach ($user->getInstitutePerms() as $cur_status): ?>
                    <option <? if ($cur_status == $institute['inst_perms']) echo 'selected'; ?>><?= $cur_status ?></option>
                <? endforeach; ?>
            </select>
        <? else: ?>
            <?= ucfirst($institute['inst_perms']) ?>
        <? endif; ?>
    </label>
    <label>
        <?= _('Raum') ?>
        <input type="text" name="raum" id="raum"
               value="<?= htmlReady($institute['raum']) ?>"
                <? if ($locked) echo 'disabled'; ?>>
    </label>
    <label>
        <?= _('Sprechzeit') ?>
        <input type="text" name="sprech" id="sprech"
               value="<?= htmlReady($institute['sprechzeiten']) ?>"
                <? if ($locked) echo 'disabled'; ?>>
    </label>
    <label>
        <?= _('Telefon') ?>
        <input type="tel" name="tel"
               value="<?= htmlReady($institute['telefon']) ?>"
                <? if ($locked) echo 'disabled'; ?>>
    </label>
    <label>
        <?= _('Fax') ?>
        <input type="tel" name="fax"
               value="<?= htmlReady($institute['fax']) ?>"
                <? if ($locked) echo 'disabled'; ?>>
    </label>

    <? foreach ($entries as $id => $entry): ?>
        <? if (!$entry->isEditable() || $locked): ?>
        <label>
            <?= $entry->getName() ?>
            <?= $entry->getDisplayValue() ?>
        </label>
        <? else: ?>
            <?= $entry->getHTML('datafields') ?>
        <? endif; ?>
    <? endforeach; ?>

    <label>

        <? if ($institute['externdefault']) : ?>
            <?= Icon::create('accept', 'inactive')->asImg(['class' => 'text-top']); ?>
            <input type="hidden" name="default_institute" value="1">
        <? else : ?>
            <input type="checkbox" id="default_institute" name="default_institute" value="1"
                    <? if ($institute['externdefault']) echo 'checked'; ?>>
        <? endif; ?>
        <?= _('Standard-Adresse') ?>
        <?= tooltipIcon(_('Angaben, die im Adressbuch und auf den externen '
              . 'Seiten als Standard benutzt werden.')) ?>
    </label>
    <label>
        <input type="checkbox" name="invisible" id="invisible" value="1"
                <? if ($institute['visible'] != 1) echo 'checked'; ?>>
        <?= _('Einrichtung nicht auf der Profilseite'); ?>
        <?= tooltipIcon(_('Die Angaben zu dieser Einrichtung werden nicht '
              . 'auf Ihrer Profilseite und in Adressbüchern ausgegeben.')) ?>
    </label>
    <footer>
        <?= Button::createAccept(_('Änderungen speichern'), 'speichern') ?>
    </footer>
</form>
