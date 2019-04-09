<? if (Request::isXhr()) : ?>
    <? foreach (PageLayout::getMessages() as $messagebox) : ?>
        <?= $messagebox ?>
    <? endforeach ?>
<? endif; ?>
<form id="calendar-manage-access" data-dialog="" method="post" action="<?= $controller->url_for('calendar/single/store_permissions/' . $calendar->getRangeId()) ?>">
    <? CSRFProtection::tokenTag() ?>
    <? $perms = [1 => _('Keine'), 2 => _('Lesen'), 4 => _('Schreiben')] ?>
    <table class="default">
        <caption>
            <?= htmlReady($title) ?>
            <span class="actions" style="font-size: 0.8em;">
                <label>
                    <?= _('Auswahl') ?>:
                    <select name="group_filter" size="1" class="submit-upon-select">
                        <option value="list"<?= $group_filter_selected == 'list' ? ' selected' : '' ?>><?= _('Alle Personen anzeigen') ?></option>
                        <? foreach ($filter_groups as $filter_group) : ?>
                        <option value="<?= $filter_group->getId() ?>"<?= $group_filter_selected == $filter_group->getId() ? ' selected' : '' ?>><?= htmlReady($filter_group->name) ?></option>
                        <? endforeach; ?>
                    </select>
                </label>
                <?= Icon::create('accept', 'clickable')
                      ->asInput([
                        'id' => "calendar-group-submit",
                        'name' => "calendar_group_submit",
                        'class' => "text-top"]) ?>
                <span style="padding-left: 1em;">
                    <?= $mps->render() ?>
                </span>
                <script>
                    STUDIP.MultiPersonSearch.init();
                </script>
            </span>
        </caption>
        <thead>
            <tr>
                <th>
                    <?= _('Name') ?>
                </th>
                <th>
                    <?= _('Berechtigung') ?>
                </th>
                <th>
                    <?= _('Eigene Berechtigung') ?>
                </th>
                <th class="actions">
                    <?= _('Aktionen') ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($users as $header => $usergroup): ?>
                <tr id="letter_<?= $header ?>" class="calendar-user-head">
                    <th colspan="4">
                        <?= $header ?>
                    </th>
                </tr>
                <? foreach ($usergroup as $user): ?>
                    <tr id="contact_<?= $user->user_id ?>">
                        <td>
                            <?= ObjectdisplayHelper::avatarlink($user->user) ?>
                        </td>
                        <td style="white-space: nowrap;">
                            <label>
                                <input type="radio" name="perm[<?= $user->user_id ?>]" value="<?= Calendar::PERMISSION_FORBIDDEN ?>"
                                       <?= $user->permission < Calendar::PERMISSION_READABLE ? ' checked' : '' ?>>
                                <?= _('Keine') ?>
                            </label>
                            <label>
                                <input type="radio" name="perm[<?= $user->user_id ?>]" value="<?= Calendar::PERMISSION_READABLE ?>"
                                    <?= $user->permission == Calendar::PERMISSION_READABLE ? ' checked' : '' ?>>
                                <?= _('Lesen') ?>
                            </label>
                            <label>
                                <input type="radio" name="perm[<?= $user->user_id ?>]" value="<?= Calendar::PERMISSION_WRITABLE ?>"
                                    <?= $user->permission == Calendar::PERMISSION_WRITABLE ? ' checked' : '' ?>>
                                <?= _('Schreiben') ?>
                            </label>
                        </td>
                        <td>
                            <?= $perms[$own_perms[$user->user_id]] ?>
                        </td>
                        <td class="actions">
                            <a title="<?= _('Benutzer entfernen') ?>" onClick="STUDIP.CalendarDialog.removeUser(this);" href="<?= $controller->url_for('calendar/single/remove_user/' . $calendar->getRangeId() . $filter, ['user_id' => $user->user_id]) ?>">
                                <?= Icon::create('person+remove', 'clickable')->asImg() ?>
                            </a>
                        </td>
                    </tr>
                <? endforeach; ?>
            <? endforeach; ?>
        </tbody>
    </table>
    <div style="text-align: center;" data-dialog-button>
        <?= Studip\Button::create(_('Speichern'), 'store') ?>
        <? if (!Request::isXhr()) : ?>
        <?= Studip\LinkButton::create(_('Abbrechen'), $controller->url_for('calendar/single/' . $last_view)) ?>
        <? endif; ?>
    </div>
</form>