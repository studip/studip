<?= $this->render_partial('admission/restricted_courses/_institute_choose.php')?>
<br>
<? if (count($courses)) : ?>
    <table class="default nohover sortable-table">
        <thead>
            <tr>
                <th data-sort="text"><?= _('Anmeldeset') ?></th>
                <th data-sort="text"><?= _('Name') ?></th>
                <th data-sort="digit"><?= _('max. Teilnehmende') ?></th>
                <th data-sort="digit"><?= _('Teilnehmer aktuell') ?></th>
                <th data-sort="htmldata"><?= _('Anmeldungen') ?></th>
                <th data-sort="htmldata"><?= _('Warteliste') ?></th>
                <th data-sort="htmldata"><?= _('Platzverteilung') ?></th>
                <th data-sort="htmldata"><?= _('Startzeitpunkt') ?></th>
                <th data-sort="htmldata"><?= _('Endzeitpunkt') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($courses as $course) : ?>
            <tr>
                <td><a href="<?= URLHelper::getLink('dispatch.php/admission/courseset/configure/' . $course['set_id'])?>"><?= htmlReady($course['cs_name'])?></td>
                <td><a href="<?= URLHelper::getLink('dispatch.php/course/members/index', ['cid' => $course['seminar_id']])?>"><?= htmlReady(($course['course_number'] ? $course['course_number'] .'|' : '') . $course['course_name'])?></a></td>
                <td><?= htmlReady($course['admission_turnout'])?></td>
                <td>
                    <?= htmlReady($course['count_teilnehmer'] + $course['count_prelim'])?>
                <? if ($course['admission_prelim'] && $course['count_prelim']) : ?>
                    <?= tooltipIcon(_('vorläufige Teilnahme: ') . $course['count_prelim']) ?>
                <? endif ?>
                </td>
                <td data-value="<?= $course['count_claiming'] ?: 0 ?>">
                    <?= htmlReady(isset($course['count_claiming']) ? $course['count_claiming'] : '-') ?>
                </td>
                <td data-sort-value="<?= $course['count_waiting'] ?: 0 ?>">
                    <?= htmlReady(isset($course['count_waiting']) ? $course['count_waiting'] : '-') ?>
                </td>
                <td style="white-space:nowrap" data-sort-value="<?= (int) $course['distribution_time']?>">
                    <?= htmlReady($course['distribution_time'] ? strftime('%x %R', $course['distribution_time']) : '-') ?>
                </td>
                <td style="white-space:nowrap" data-sort-value="<?= (int) $course['start_time']?>">
                    <?= htmlReady($course['start_time'] ? strftime('%x %R', $course['start_time']) : '-') ?>
                </td>
                <td style="white-space:nowrap" data-sort-value="<?= (int) $course['end_time']?>">
                    <?= htmlReady($course['end_time'] ? strftime('%x %R', $course['end_time']) : '-') ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif ?>
