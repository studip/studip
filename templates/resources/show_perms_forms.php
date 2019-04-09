<?
# Lifter010: TEST
use Studip\Button;

?>
<h1><?=_('Rechte bearbeiten')?></h1>

    <form method="post" action="<?= URLHelper::getLink('?change_object_perms=' . $resObject->getId()) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <table class="default">
            <colgroup>
                <col width="20">
                <col width="70%">
                <col>
            </colgroup>
            <caption>
                <?= _('Verantwortlich') ?>
                <span class="actions">
                     <? if ($owner_perms) : ?>
                         <? showSearchForm('search_owner', $search_string_search_owner, FALSE,TRUE); ?>
                     <? endif; ?>
                </span>
            </caption>
            <thead>
            <tr>
                <th colspan="2"><?= _('Name') ?></th>
                <th><?= _('Funktion') ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?= Avatar::getAvatar($resObject->getOwnerId())->getImageTag(Avatar::SMALL,
                        ['style' => 'margin-right: 5px', 'title' => htmlReady($resObject->getOwnerName(true))]); ?>
                </td>
                <td>
                    <a href="<?= $resObject->getOwnerLink() ?>"><?= htmlReady($resObject->getOwnerName(true)) ?></a>
                </td>
                <td>
                    <?= _('Raumverantwortung') ?>
                </td>
            </tr>
            </tbody>
        </table>


        <table class="default">
            <colgroup>
                <col width="20">
                <col width="40%">
                <col width="20%">
                <col width="30%">
                <col>
            </colgroup>
            <caption>
                <?= _('Berechtigungen') ?>
                <span class="actions">
                    <? showSearchForm('search_perm_user', $search_string_search_perm_user, false, false, false, true) ?>
                </span>
            </caption>
            <thead>
            <tr>
                <th colspan="2"><?= _('Name') ?></th>
                <th colspan="2"><?= _('Berechtigung') ?></th>
                <th><?= _('Aktion') ?></th>
            </tr>
            </thead>

            <tbody>

            <? if (count($selectPerms) > 0): ?>
                <? $i = 0;
                foreach ($selectPerms as $user_id => $perm): ?>
                    <tr>
                        <td>
                            <?= Avatar::getAvatar($user_id)->getImageTag(Avatar::SMALL,
                                ['style' => 'margin-right: 5px', 'title' => htmlReady($resObject->getOwnerName(true, $user_id))]); ?>
                        </td>
                        <td>
                            <input type="hidden" name="change_user_id[]" value="<?= $user_id ?>">
                            <a href="<?= $resObject->getOwnerLink($user_id) ?>">
                                <?= htmlReady($resObject->getOwnerName(true, $user_id)) ?>
                            </a>
                        </td>
                        <td>
                            <? if ($perm == 'admin'): ?>
                                <?= _('Admin') ?>
                                <?= tooltipIcon(_('Nutzer ist Admin und kann sämtliche Belegungen und Eigenschaften ändern und Rechte vergeben.')) ?>
                            <? elseif ($perm == 'tutor'): ?>
                                <?= _('Tutor') ?>
                                <?= tooltipIcon(_('Nutzer ist Tutor und kann sämtliche Belegungen ändern.')) ?>
                            <? elseif ($perm == 'autor'): ?>
                                <?= _('Autor') ?>
                                <?= tooltipIcon(_('Nutzer ist Autor und kann nur eigene Belegungen ändern.')) ?>
                            <? endif; ?>
                        </td>
                        <td class="action">
                            <!-- admin-perms -->
                            <? if (($resObject->getOwnerType($user_id) == 'user') && $owner_perms): ?>
                                <label>
                                    <input type="radio" name="change_user_perms[<?= $i ?>]" value="admin"
                                        <? if ($perm == 'admin') echo 'checked'; ?>>
                                    admin
                                </label>
                            <? else: ?>
                                <label style="color: #888;">
                                    <input type="radio" disabled <? if ($perm == 'admin') echo 'checked'; ?>>
                                    admin
                                </label>
                            <? endif; ?>

                            <!-- tutor-perms -->
                            <? if (($resObject->getOwnerType($user_id) == 'user') && $admin_perms && (($perm == 'tutor') || $owner_perms)): ?>
                                <label>
                                    <input type="radio" name="change_user_perms[<?= $i ?>]" value="tutor"
                                        <? if ($perm == 'tutor') echo 'checked'; ?>>
                                    tutor
                                </label>
                            <? else: ?>
                                <label style="color: #888;">
                                    <input type="radio" disabled <? if ($perm == 'tutor') echo 'checked'; ?>>
                                    tutor
                                </label>
                            <? endif; ?>

                            <!-- autor-perms -->
                            <? if ($admin_perms && (($perm == 'autor') || $owner_perms)): ?>
                                <label>
                                    <input type="radio" name="change_user_perms[<?= $i ?>]" value="autor"
                                        <? if ($perm == 'autor') echo 'checked'; ?>>
                                    autor
                                </label>
                            <? else: ?>
                                <label style="color: #888;">
                                    <input type="radio" disabled <? if ($perm == 'autor') echo 'checked'; ?>>
                                    autor
                                </label>
                            <? endif; ?>
                        </td>
                        <td class="action">
                            <!-- Trash  -->
                            <? if ($owner_perms || ($admin_perms && $perm == 'autor')) : ?>
                                <a href="<?= URLHelper::getLink('?change_object_perms=' . $resObject->getId() . '&delete_user_perms=' . $user_id) ?>">
                                    <?= Icon::create('trash', 'clickable', ['title' => _('Berechtigung löschen')])->asImg() ?>
                                </a>
                            <? else : ?>
                                <?= Icon::create('trash+decline', 'inactive', ['title' => _('Sie dürfen diese Berechtigung leider nicht löschen')])->asImg(16) ?>
                            <? endif; ?>
                        </td>
                    </tr>
                    <? $i += 1; endforeach; ?>
            <? else : ?>
                <tr>
                    <td colspan="5">
                        <?= _('Es sind keine weiteren Berechtigungen eingetragen') ?>
                    </td>
                </tr>
            <? endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="5">
                    <label>
                        <input type="checkbox" name="change_lockable" <? if ($resObject->isLockable()) echo 'checked'; ?>>
                        <?= _('Blockierung') ?>
                        <?= tooltipIcon(_('Diesen Raum bei globaler Blockierung gegen eine Bearbeitung durch lokale Administratoren und andere Personen sperren')) ?>
                    </label>
                    <br>
                    <strong><?= _('Aktueller Zustand') ?></strong>:
                    <? if ($resObject->isLockable()): ?>
                        <?= _('Raum <span style="text-decoration: underline">kann</span> blockiert werden') ?>
                    <? else: ?>
                        <?= _('Raum kann <span style="text-decoration: underline">nicht</span> blockiert werden') ?>
                    <? endif; ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4" style="text-align: center">
                    <?= Button::create(_('Übernehmen'), ['title' => _('Zuweisen')]) ?>
                </td>
            </tr>
            </tfoot>
        </table>
    </form>

<?
$sidebar = Sidebar::Get();
$sidebar->setTitle(htmlReady($resObject->getName()));
$action = new ActionsWidget();
$action->addLink(_('Ressourcensuche'), URLHelper::getURL('resources.php?view=search&quick_view_mode=' . $view_mode));

$sidebar->addWidget($action);
?>
