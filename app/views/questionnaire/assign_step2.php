<h1><?= _('Ausgewählte Veranstaltungen') ?></h1>
<p>
    <ul>
        <? if ($selected_courses): ?>
            <? foreach ($selected_courses as $selected_course): ?>
                <li>
                    <? if ($GLOBALS['perm']->have_perm('root')): ?>
                        <a href="<?= URLHelper::getLink(
                                 'dispatch.php/course/details',
                                 [
                                     'cid' => $selected_course->id
                                 ]
                                 ) ?>" data-dialog="1">
                            <?= htmlReady($selected_course->getFullName()) ?>
                        </a>
                    <? else: ?>
                        <?= htmlReady($selected_course->getFullName()) ?>
                    <? endif ?>
                </li>
            <? endforeach ?>
        <? endif ?>
    </ul>
</p>
<h1><?= _('Auswählbare Fragebögen') ?></h1>
<table class="default sortable-table" data-sortlist="[[2, 1]]">
    <thead>
        <tr>
            <th>
                <input type="checkbox"
                       data-proxyfor="input[name='selected_questionnaire_ids[]']"
                       data-activates="#questionnaire-assign-form .step2-button">
            </th>
            <th data-sort="text">
                <?= _('Titel') ?>
            </th>
            <th data-sort="htmldata">
                <?= _('Erstellungsdatum') ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($questionnaires as $questionnaire): ?>
            <tr>
                <td>
                    <input type="checkbox" name="selected_questionnaire_ids[]"
                           value="<?= htmlReady($questionnaire->id) ?>"
                           <?= in_array($questionnaire->id, $selected_questionnaires)
                                      ? 'checked="checked"'
                                      : '' ?>>
                </td>
                <td><?= htmlReady($questionnaire->title) ?></td>
                <td data-sort-value="<?= htmlReady($questionnaire->mkdate) ?>">
                    <?= date('d.m.Y', $questionnaire->mkdate) ?>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>
<label>
    <input type="checkbox" name="delete_dates" value="1"
           <?= $delete_dates ? 'checked="checked"' : '' ?>>
    <?= _('Kopierte Fragebögen händisch starten und enden lassen') ?>
</label>
<?= \Studip\Button::create(
    _('Zuweisen'),
    'assign',
    [
        'class' => 'step2-button',
        'disabled' => 'disabled'
    ]
) ?>
<?= \Studip\Button::create(
    _('In Veranstaltungen kopieren'),
    'copy',
    [
        'class' => 'step2-button',
        'disabled' => 'disabled'
    ]
) ?>
