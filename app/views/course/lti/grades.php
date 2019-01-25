<table class="default">
    <caption>
        <?= _('Ergebnisse') ?>
    </caption>

    <thead>
        <tr class="sortable">
            <th class="<?= $desc ? 'sortdesc' : 'sortasc' ?>">
                <a href="<?= $controller->link_for('course/lti/grades', ['desc' => !$desc]) ?>">
                    <?= _('Teilnehmende') ?>
                </a>
            </th>
            <? foreach ($lti_data_array as $lti_data): ?>
                <th style="text-align: right;">
                    <?= htmlReady($lti_data->title) ?>
                </th>
            <? endforeach ?>
        </tr>
    </thead>

    <tbody>
        <? foreach ($members as $member): ?>
            <tr>
                <td>
                    <?= htmlReady($member->nachname) ?>, <?= htmlReady($member->vorname) ?>
                </td>
                <? foreach ($lti_data_array as $lti_data): ?>
                    <td style="text-align: right;">
                        <? if ($grade = $lti_data->grades->findOneBy('user_id', $member->user_id)): ?>
                            <?= sprintf('%.0f%%', $grade->score * 100) ?>
                        <? else: ?>
                            &ndash;
                        <? endif ?>
                    </td>
                <? endforeach ?>
            </tr>
        <? endforeach ?>
    </tbody>
</table>
