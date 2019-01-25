<table class="default">
    <caption>
        <?= _('Ergebnisse') ?>
    </caption>

    <thead>
        <tr>
            <th>
                <?= _('Abschnitt') ?>
            </th>
            <th style="text-align: right;">
                <?= _('Bewertung') ?>
            </th>
        </tr>
    </thead>

    <tbody>
        <? foreach ($lti_data_array as $lti_data): ?>
            <tr>
                <td>
                    <?= htmlReady($lti_data->title) ?>
                </td>
                <td style="text-align: right;">
                    <? if ($grade = LtiGrade::find([$lti_data->id, $GLOBALS['user']->id])): ?>
                        <?= sprintf('%.0f%%', $grade->score * 100) ?>
                    <? else: ?>
                        &ndash;
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>
