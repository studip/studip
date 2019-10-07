<article class="studip">
    <header><h1><?= _('Gefundene Veranstaltungen') ?></h1></header>
    <section>
        <? if ($found_courses): ?>
            <table class="default">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" data-proxyfor=".FoundCourseListItem"
                                   id="AskALotPlugin_course_select_proxy"
                                   data-proxyfor="input[name='course_id_list[]']"
                                   data-activates="#questionnaire_assign_step1_button">
                        </th>
                        <th><?= dgettext('AskALotPlugin', 'Veranstaltung') ?></th>
                        <th><?= dgettext('AskALotPlugin', 'Lehrende') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($found_courses as $found_course): ?>
                        <? $teachers = CourseMember::findByCourseAndStatus(
                            $found_course->id,
                            'dozent'
                        );
                        $teacher_arr = [];
                        foreach ($teachers as $teacher) {
                            $teacher_arr[] = $teacher->getUserFullName();
                        } ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="course_id_list[]"
                                       class="FoundCourseListItem"
                                       value="<?= htmlReady($found_course->id) ?>">
                            </td>
                            <td>
                                <a href="<?= $controller->link_for(
                                         'course/details',
                                         ['cid' => $found_course->id]
                                         ) ?>" data-dialog="1">
                                    <?= htmlReady($found_course->getFullName()) ?>
                                </a>
                            </td>
                            <td><?= htmlReady(implode(', ', $teacher_arr)) ?></td>
                        </tr>
                    <? endforeach ?>
                </tbody>
            </table>
            <?= \Studip\Button::create(
                _('Auswählen'),
                'select_courses',
                [
                    'id' => 'questionnaire_assign_step1_button',
                    'disabled' => 'disabled'
                ]
            ) ?>
        <? else: ?>
            <?= MessageBox::info(
                _('Es wurden keine zur Suche passenden Veranstaltungen gefunden!')
            ) ?>
        <? endif ?>
        <?= \Studip\LinkButton::create(
            _('Neue Suche'),
            $controller->link_for('questionnaire/assign')
        ) ?>
    </section>
</article>
