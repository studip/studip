<?= $this->render_partial("course/studygroup/_feedback") ?>

<? if ($anzahl >= 1): ?>
    <table class="default studygroup-browse">
        <caption>
            <?= sprintf(ngettext('%u Studiengruppe', '%u Studiengruppen',$anzahl), $anzahl)?>
        </caption>
        <thead>
        <tr class="sortable" title="<?= _("Klicken, um die Sortierung zu ändern") ?>">
            <th class="nosort hidden-small-down"></th>
            <th <?= ($sort_type == 'name') ? 'class="sort' . $sort_order . '"' : '' ?>>
                <a href="<?= $controller->url_for('studygroup/browse/1/' . ($sort == 'name_asc' ? 'name_desc' : 'name_asc')) ?>"><?= _("Name") ?></a>
            </th>
            <th <?= ($sort_type == 'founded') ? 'class="sort' . $sort_order . '"' : '' ?>>
                <a href="<?= $controller->url_for('studygroup/browse/1/' . ($sort == 'founded_asc' ? 'founded_desc' : 'founded_asc')) ?>"><?= _("gegründet") ?></a>
            </th>
            <th <?= ($sort_type == 'member') ? 'class="sort' . $sort_order . '"' : '' ?>>
                <a href="<?= $controller->url_for('studygroup/browse/1/' . ($sort == 'member_asc' ? 'member_desc' : 'member_asc')) ?>"><?= _("Mitglieder") ?></a>
            </th>
            <th <?= ($sort_type == 'founder') ? 'class="sort' . $sort_order . '"' : '' ?>>
                <a href="<?= $controller->url_for('studygroup/browse/1/' . ($sort == 'founder_asc' ? 'founder_desc' : 'founder_asc')) ?>"><?= _("GründerIn") ?></a>
            </th>
            <th <?= ($sort_type == 'ismember') ? 'class="sort' . $sort_order . '"' : '' ?>>
                <a href="<?= $controller->url_for('studygroup/browse/1/' . ($sort == 'ismember_asc' ? 'ismember_desc' : 'ismember_asc')) ?>"><?= _("Mitglied") ?></a>
            </th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($groups as $group): ?>
            <? $is_member = $user->course_memberships->findBy('seminar_id', $group['Seminar_id'])->count(); ?>
            <tr>
                <td class="hidden-small-down">
                    <?= StudygroupAvatar::getAvatar($group['Seminar_id'])->getImageTag(Avatar::SMALL, ['title' => htmlready($group['Name'])]) ?>
                </td>
                <td class="studygroup-title">
                    <? if ($is_member): ?>
                    <a href="<?= URLHelper::getlink("seminar_main.php?auswahl=" . $group['Seminar_id']) ?>">
                        <? else: ?>
                        <a href="<?= URLHelper::getlink("dispatch.php/course/studygroup/details/" . $group['Seminar_id']) ?>">
                            <? endif; ?>
                            <?= htmlready($group['Name']) ?>
                            <?= $group['visible'] ? '' : "[" . _('versteckt') . "]" ?>
                            <? if ($group['admission_prelim'] == 1) { ?>
                                <?= Icon::create('lock-locked', 'inactive', ['title' => _('Mitgliedschaft muss beantragt werden')]) ?>
                            <? } ?>
                        </a>
                </td>
                <td><?= strftime('%x', $group['mkdate']) ?>
                </td>
                <td align="center">
                    <?= StudygroupModel::countMembers($group['Seminar_id']) ?>
                </td>
                <td style="white-space:nowrap;">
                    <? $founders = StudygroupModel::getFounder($group['Seminar_id']);
                    foreach ($founders as $founder) : ?>
                        <?= Avatar::getAvatar($founder['user_id'])
                            ->getImageTag(
                                    Avatar::SMALL,
                                    ['title' => $founder['fullname'], 'class' => 'hidden-small-down']
                            ) ?>
                        <a href="<?= URLHelper::getlink('dispatch.php/profile?username=' . $founder['uname']) ?>"><?= htmlready($founder['fullname']) ?></a>
                        <br>
                    <? endforeach; ?>
                </td>
                <td align="center">
                    <? if ($is_member) : ?>
                        <?= Icon::create('person', 'inactive', ['title' => _('Sie sind Mitglied in dieser Gruppe')])->asImg() ?>
                    <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
        <? if ($anzahl > $entries_per_page) : ?>
            <tfoot>
            <tr>
                <td colspan="6" class="actions">
                    <?= $GLOBALS['template_factory']->render('shared/pagechooser', [
                        'perPage'      => $entries_per_page,
                        'num_postings' => $anzahl,
                        'page'         => $page,
                        'pagelink'     => 'dispatch.php/studygroup/browse/%s/' . $sort,
                    ]) ?>
                </td>
            </tr>
            </tfoot>
        <? endif; ?>
    </table>
<? endif; ?>

<?= \Studip\LinkButton::createAdd(
    _('Neue Studiengruppe anlegen'),
    URLHelper::getURL('dispatch.php/course/wizard', ['studygroup' => 1]),
    ['class' => 'hidden-medium-up']
) ?>
