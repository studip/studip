<? foreach ($modulteil->lvgruppen as $lvgruppe) : ?>
    <? foreach ($lvgruppe->getAssignedCoursesBySemester($selected_semester->id, false) as $course) : ?>
        <? $course_obj = Course::find($course['seminar_id']) ?>
        <? foreach ($course_obj->cycles->findBy('metadate_id', $conflicts->pluck('base_metadate_id')) as $cycle) : ?>
            <? $dates = $cycle->dates->filter(function ($c) use ($selected_semester) {
                return ($selected_semester->beginn <= $c->date && $selected_semester->ende >= $c->date);
            }); ?>
            <li>
                <div class="mvv-ovl-base-course">!</div>
                <? $id = md5($modul->abschnitt_id . $modulteil->id . $course['seminar_id']) ?>
                <input id="<?= $id ?>" type="checkbox" checked>
                <label for="<?= $id ?>"></label>
                <?= htmlReady($course_obj->VeranstaltungsNummer) ?>
                <a href="<?= $controller->url_for('admin/overlapping/course_info', $course_obj->id) ?>" data-dialog="">
                    <?= Icon::create('info-circle', Icon::ROLE_INFO, [
                        'class' => 'text-bottom',
                        'title' => _('Veranstaltungsdetails')
                    ]) ?>
                </a>
                <?= htmlReady($course_obj->getFullname('type-name')) ?>
                <? if ($course_obj->admission_turnout) : ?>
                    <?= sprintf(_('(erw. TN %s)'), htmlReady($course_obj->admission_turnout)) ?>
                <? endif; ?>
                <?= Icon::create('date-cycle', Icon::ROLE_INFO, ['class' => 'text-bottom']) ?>
                <?= sprintf('%s (%sx)', $cycle->toString('short'), count($dates)); ?>
                <ul>
                    <?= $this->render_partial('admin/overlapping/conflicts', ['cycle' => $cycle, 'base_modul' => $modul, 'selected_semester' => $selected_semester]) ?>
                </ul>
            </li>
        <? endforeach; ?>
    <? endforeach; ?>
<? endforeach; ?>
