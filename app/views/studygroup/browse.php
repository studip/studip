<?= $this->render_partial("course/studygroup/_feedback") ?>

<?php
$headers = [
    'name'     => _('Name'),
    'founded'  => _('gegründet'),
    'member'   => _('Mitglieder'),
    'founder'  => _('GründerIn'),
    'ismember' => _('Mitglied'),
];
?>

<? if ($anzahl > 0): ?>
    <table class="default studygroup-browse">
        <caption>
            <?= sprintf(ngettext('%u Studiengruppe', '%u Studiengruppen', $anzahl), $anzahl)?>
        </caption>
        <colgroup>
            <col style="width: 32px">
            <col>
            <col style="width: 10%">
            <col style="width: 10%">
            <col style="width: 20%">
            <col style="width: 10%">
        </colgroup>
        <thead>
            <tr class="sortable" title="<?= _('Klicken, um die Sortierung zu ändern') ?>">
                <th class="nosort hidden-small-down"></th>
            <? foreach ($headers as $key => $label): ?>
                <th <? if ($sort_type === $key) echo 'class="sort' . $sort_order . '"'; ?>>
                    <a href="<?= $controller->link_for("studygroup/browse/1/{$key}_" . ($sort_order === 'asc' ? 'desc' : 'asc'), compact('q', 'closed')) ?>">
                        <?= htmlReady($label) ?>
                    </a>
                </th>
            <? endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <? foreach ($groups as $group): ?>
            <? $is_member = $user->course_memberships->findBy('seminar_id', $group['Seminar_id'])->count(); ?>
            <tr>
                <td class="hidden-small-down">
                    <?= StudygroupAvatar::getAvatar($group['Seminar_id'])->getImageTag(Avatar::SMALL, ['title' => $group['Name']]) ?>
                </td>
                <td class="studygroup-title">
                    <? if ($is_member): ?>
                    <a href="<?= URLHelper::getlink("seminar_main.php?auswahl=" . $group['Seminar_id']) ?>">
                        <? else: ?>
                        <a href="<?= URLHelper::getlink("dispatch.php/course/studygroup/details/" . $group['Seminar_id'], ['cid' => null]) ?>">
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
                        <?= Avatar::getAvatar($founder['user_id'])->getImageTag(Avatar::SMALL, [
                            'class' => 'hidden-small-down',
                        ]) ?>
                        <a href="<?= URLHelper::getlink('dispatch.php/profile', ['username' => $founder['uname']]) ?>">
                            <?= htmlready($founder['fullname']) ?>
                        </a>
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
                        'pagelink'     => "dispatch.php/studygroup/browse/%s/{$sort}",
                        'pageparams'   => compact('q', 'closed'),
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
