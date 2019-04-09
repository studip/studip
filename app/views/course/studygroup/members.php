<?= $this->render_partial("course/studygroup/_feedback", compact('anzahl', 'page', 'sem_id')) ?>

<? if (count($moderators) > 0): ?>
    <?= $this->render_partial('course/studygroup/_members_' . $view . '.php', [
        'title'          => $sem_class['title_dozent_plural'] ?: _('Gruppenadministrator/-innen'),
        'sem_id'         => $sem_id,
        'members'        => $moderators,
        'moderator_list' => true
    ]) ?>
<? endif ?>

<? if (count($tutors) > 0): ?>
    <?= $this->render_partial('course/studygroup/_members_' . $view . '.php', [
        'title'   => $sem_class['title_tutor_plural'] ?: _('Moderator/-innen'),
        'sem_id'  => $sem_id,
        'members' => $tutors
    ]) ?>
<? endif ?>

<? if (count($autors) > 0): ?>
    <?= $this->render_partial('course/studygroup/_members_' . $view . '.php', [
        'title'   => $sem_class['title_autor_plural'] ?: _('Mitglieder'),
        'sem_id'  => $sem_id,
        'members' => $autors
    ]) ?>
<? endif ?>


<? if ($rechte): ?>
    <? if (count($accepted) > 0): ?>
        <table class="default sortable-table">
            <caption><?= _('Offene Mitgliedsanträge') ?></caption>
            <colgroup>
                <col width="40">
                <col>
                <col width="80">
            </colgroup>
            <thead>
                <tr>
                    <th data-sort="false"></th>
                    <th data-sort="text"><?= _('Name') ?></th>
                    <th data-sort="false" class="actions">
                        <?= _('Aktionen') ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($accepted as $p) : ?>
                    <tr>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $p->username) ?>">
                                <?= Avatar::getAvatar($p['user_id'])->getImageTag(Avatar::SMALL) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $p->username) ?>">
                                <?= htmlReady($p->user->getFullname('no_title_rev')) ?>
                            </a>
                        </td>
                        <td class="actions">
                            <a href="<?= $controller->url_for('course/studygroup/edit_members/accept?user=' . $p->username, ['cid' => $sem_id]) ?>">
                                <?= Icon::create('accept', 'clickable', ['title' => _('Eintragen')])->asImg() ?>
                            </a>

                            <a href="<?= $controller->url_for('course/studygroup/edit_members/deny?user=' . $p->username, ['cid' => $sem_id]) ?>" data-confirm="<?= _('Wollen Sie die Mitgliedschaft wirklich ablehnen?') ?>">
                                <?= Icon::create('trash', 'clickable', ['title' => _('Mitgliedschaft ablehnen')])->asImg() ?>
                            </a>
                        </td>
                    </tr>
                <? endforeach ?>
            </tbody>
        </table>
    <? endif; ?>

    <? if (count($invitedMembers) > 0) : ?>
        <table class="default sortable-table">
            <caption><?= _('Verschickte Einladungen') ?></caption>
            <colgroup>
                <col width="40">
                <col>
                <col width="80">
            </colgroup>
            <thead>
                <tr>
                    <th data-sort="false"></th>
                    <th data-sort="text"><?= _('Name') ?></th>
                    <th data-sort="false" class="actions">
                        <?= _('Aktionen') ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($invitedMembers as $p) : ?>
                    <tr>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $p['username']) ?>">
                                <?= Avatar::getAvatar($p['user_id'])->getImageTag(Avatar::SMALL) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $p['username']) ?>">
                                <?= htmlReady($p['fullname']) ?>
                            </a>
                        </td>
                        <td class="actions">
                            <a href="<?= $controller->url_for('course/studygroup/edit_members/cancelInvitation?user=' . $p['username'], ['cid' => $sem_id]) ?>" data-confirm="<?= _('Wollen Sie die Einladung wirklich löschen?') ?>">
                                <?= Icon::create('trash', 'clickable', ['title' => _('Einladung löschen')])->asImg() ?>
                            </a>
                        </td>
                    </tr>
                <? endforeach ?>
            </tbody>
        </table>
    <? endif; ?>
<? endif; ?>
