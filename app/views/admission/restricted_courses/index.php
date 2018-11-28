<?= $this->render_partial('admission/restricted_courses/_institute_choose.php')?>
<br>
<? if (count($courses)) : ?>
    <table class="default nohover restricted_courses">
        <thead>
            <tr class="sortable">
                <th class="sortasc"><?= _("Anmeldeset")?></th>
                <th><?= _("Name")?></th>
                <th><?= _("max. Teilnehmende")?></th>
                <th><?= _("Teilnehmer aktuell")?></th>
                <th><?= _("Anmeldungen")?></th>
                <th><?= _("Warteliste")?></th>
                <th><?= _("Platzverteilung")?></th>
                <th><?= _("Startzeitpunkt")?></th>
                <th><?= _("Endzeitpunkt")?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($courses as $course) : ?>
            <tr>
                <td><a href="<?= URLHelper::getLink('dispatch.php/admission/courseset/configure/' . $course['set_id'])?>"><?= htmlReady($course['cs_name'])?></td>
                <td><a href="<?= URLHelper::getLink('dispatch.php/course/members/index', array('cid' => $course['seminar_id']))?>"><?= htmlReady(($course['course_number'] ? $course['course_number'] .'|' : '') . $course['course_name'])?></a></td>
                <td><?= htmlReady($course['admission_turnout'])?></td>
                <td><?= htmlReady($course['count_teilnehmer'] + $course['count_prelim'])?>
                <? if ($course['admission_prelim'] && $course['count_prelim']) : ?>
                <? $text = _("vorläufige Teilnahme: ") . $course['count_prelim']; ?>
                    <?= tooltipIcon($text) ?>
                <? endif ?>
                <td><?= htmlReady(isset($course['count_claiming']) ? $course['count_claiming'] : '-')?></td>
                <td><?= htmlReady(isset($course['count_waiting']) ? $course['count_waiting'] : '-')?></td>
                <td style="white-space:nowrap" data-timestamp="<?=(int)$course['distribution_time']?>"><?= htmlReady($course['distribution_time'] ? strftime('%x %R', $course['distribution_time']) : '-')?></td>
                <td style="white-space:nowrap" data-timestamp="<?=(int)$course['start_time']?>"><?= htmlReady($course['start_time'] ? strftime('%x %R', $course['start_time']) : '-')?></td>
                <td style="white-space:nowrap" data-timestamp="<?=(int)$course['end_time']?>"><?= htmlReady($course['end_time'] ? strftime('%x %R', $course['end_time']) : '-')?></td>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
    <script>
        jQuery(function () {
            jQuery(".restricted_courses").tablesorter({
                textExtraction: function (node) { return jQuery(node).data('timestamp') !== undefined ? jQuery(node).data('timestamp')+'' : jQuery(node).text()+''; },
                cssAsc: 'sortasc',
                cssDesc: 'sortdesc'
            });
        });
    </script>
<? endif ?>
