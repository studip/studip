<?
# Lifter010: TODO - Quicksearches still lack a label

use Studip\Button, Studip\LinkButton;

?>
<form action="<?= $controller->url_for('admin/user/migrate') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Benutzermigration') ?>
        </legend>

        <label>
            <?= _('Quellaccount') ?>

            <? if ($user !== null): ?>
                <?= QuickSearch::get('old_id', new StandardSearch('user_id'))
                               ->defaultValue($user->id, $user->getFullname() . ' (' . $user->username . ')')
                               ->render() ?>
            <? else: ?>
                <?= QuickSearch::get('old_id', new StandardSearch('user_id'))->render() ?>
            <? endif; ?>
        </label>

        <label>
            <?= _('Zielaccount') ?>

            <?= QuickSearch::get('new_id', new StandardSearch('user_id'))->render() ?>
        </label>


        <label>
            <input type="checkbox" name="convert_ident" id="convert_ident" checked>

            <?= _('Identitätsrelevante Daten migrieren') ?>
            <?= tooltipIcon(_('(Es werden zusätzlich folgende Daten migriert: '
                 .'Veranstaltungen, Studiengänge, persönliche '
                 .'Profildaten inkl. Nutzerbild, Institute, '
                 .'generische Datenfelder und Buddies.)')) ?>
        </label>

        <label>
            <input type="checkbox" name="delete_old" id="delete_old" value="1">
            <?= _('Den alten Benutzer löschen') ?>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Umwandeln'),
                           'umwandeln',
                           ['title' => _('Den ersten Benutzer in den zweiten Benutzer migrieren')]) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/user/index')) ?>
    </footer>
</form>
