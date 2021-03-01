<?= $this->render_partial('course/studygroup/_feedback', compact('anzahl', 'page', 'sem_id')) ?>

<? if (count($moderators) > 0): ?>
    <?= $this->render_partial("course/studygroup/_members_{$view}.php", [
        'title'          => $sem_class['title_dozent_plural'] ?: _('Gruppenadministrator/-innen'),
        'sem_id'         => $sem_id,
        'members'        => $moderators,
        'moderator_list' => true,
        'type'           => 'moderator',
    ]) ?>
<? endif ?>

<? if (count($tutors) > 0): ?>
    <?= $this->render_partial("course/studygroup/_members_{$view}.php", [
        'title'   => $sem_class['title_tutor_plural'] ?: _('Moderator/-innen'),
        'sem_id'  => $sem_id,
        'members' => $tutors,
        'type'    => 'tutor',
    ]) ?>
<? endif ?>

<? if (count($autors) > 0): ?>
    <?= $this->render_partial("course/studygroup/_members_{$view}.php", [
        'title'   => $sem_class['title_autor_plural'] ?: _('Mitglieder'),
        'sem_id'  => $sem_id,
        'members' => $autors,
        'type'    => 'autor',
    ]) ?>
<? endif ?>


<? if ($rechte): ?>
    <? if (count($accepted) > 0): ?>
        <form action="<?= $controller->edit_members('bulk') ?>" method="post">
            <table class="default sortable-table" id="studygroup-members">
                <caption><?= _('Offene Mitgliedsanträge') ?></caption>
                <colgroup>
                    <col style="width: 24px">
                    <col style="width: 40px">
                    <col>
                    <col style="width: 80px">
                </colgroup>
                <thead>
                    <tr>
                        <th data-sort="false">
                            <input type="checkbox"
                                   data-proxyfor="#studygroup-members tbody :checkbox"
                                   data-activates="#studygroup-members tfoot .button">
                        </th>
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
                            <input type="checkbox" name="members[]" value="<?= htmlReady($p['username']) ?>">
                        </td>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $p->username]) ?>">
                                <?= Avatar::getAvatar($p['user_id'])->getImageTag(Avatar::SMALL) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $p->username]) ?>">
                                <?= htmlReady($p->user->getFullname('no_title_rev')) ?>
                            </a>
                        </td>
                        <td class="actions">
                            <a href="<?= $controller->edit_members('accept', ['user' => $p->username]) ?>">
                                <?= Icon::create('accept')->asImg(['title' => _('Eintragen')]) ?>
                            </a>

                            <a href="<?= $controller->edit_members('deny', ['user' => $p->username]) ?>" data-confirm="<?= _('Wollen Sie die Mitgliedschaft wirklich ablehnen?') ?>">
                                <?= Icon::create('trash')->asImg(['title' => _('Mitgliedschaft ablehnen')]) ?>
                            </a>
                        </td>
                    </tr>
                <? endforeach ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">
                            <?= Studip\Button::create(_('Personen eintragen'), 'accept', [
                                'data-confirm' => _('Wollen Sie die markierten Personen wirklich eintragen?'),
                            ]) ?>
                            <?= Studip\Button::create(_('Mitgliedschaften ablehnen'), 'deny', [
                                'data-confirm' => _('Wollen Sie die Mitgliedschaften wirklich ablehnen?'),
                            ]) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    <? endif; ?>

    <? if (count($invitedMembers) > 0) : ?>
        <form action="<?= $controller->edit_members('bulk') ?>" method="post">
            <table class="default sortable-table" id="studygroup-awaiting">
                <caption><?= _('Verschickte Einladungen') ?></caption>
                <colgroup>
                    <col style="width: 24px">
                    <col style="width: 40px">
                    <col>
                    <col style="width: 80px">
                </colgroup>
                <thead>
                    <tr>
                        <th data-sort="false">
                            <input type="checkbox"
                                   data-proxyfor="#studygroup-awaiting tbody :checkbox"
                                   data-activates="#studygroup-awaiting tfoot .button">
                        </th>
                        <th data-sort="false"></th>
                        <th data-sort="text"><?= _('Name') ?></th>
                        <th data-sort="false" class="actions">
                            <?= _('Aktionen') ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <? foreach ($invitedMembers as $p): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="members[]" value="<?= htmlReady($p['username']) ?>">
                        </td>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile' , ['username' => $p['username']]) ?>">
                                <?= Avatar::getAvatar($p['user_id'])->getImageTag(Avatar::SMALL) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile' , ['username' => $p['username']]) ?>">
                                <?= htmlReady($p['fullname']) ?>
                            </a>
                        </td>
                        <td class="actions">
                            <a href="<?= $controller->edit_members('cancelInvitation', ['user' => $p['username']]) ?>" data-confirm="<?= _('Wollen Sie die Einladung wirklich löschen?') ?>">
                                <?= Icon::create('trash')->asImg(['title' => _('Einladung löschen')]) ?>
                            </a>
                        </td>
                    </tr>
                <? endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">
                            <?= Studip\Button::create(_('Einladungen löschen'), 'cancel-invitations', [
                                'data-confirm' => _('Wollen Sie die markierten Einladungen wirklich löschen?'),
                            ]) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    <? endif; ?>
<? endif; ?>
