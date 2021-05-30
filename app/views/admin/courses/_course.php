<?php
/*
 * Show course only if it has no parent course or the parent course is not
 * part of the current view. Otherwise the current course will be listed
 * as subcourse under its parent.
 */
if (!$values['parent_course'] || !in_array($values['parent_course'], array_keys($courses))) : ?>
    <?php
    $course = Course::find($semid);
    $children = [];
    if ($GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$values['status']]['class']]['is_group']) {
        $children = Course::findbyParent_Course($semid);
    }
    ?>
    <tr id="course-<?= $semid ?>"<?= $parent ? ' class="subcourses subcourse-' . $parent . '"' : '' ?> data-course-id="<?= $semid ?>">
        <td>
        <? if (Config::get()->ADMIN_COURSES_SHOW_COMPLETE): ?>
            <? if ($GLOBALS['perm']->have_studip_perm('tutor', $semid)) : ?>
                <a href="<?= $controller->url_for('admin/courses/toggle_complete/' . $semid) ?>"
                   class="course-completion"
                   data-course-completion="<?= $values['completion'] ?>"
                   title="<?= _('Bearbeitungsstatus ändern') ?>">
                    <?= _('Bearbeitungsstatus ändern') ?>
                </a>
            <? else : ?>
                <?= $course->getCompletionIcon()->asImg(['title' => _('Bearbeitungsstatus kann nicht von Ihnen geändert werden.')]) ?>
            <? endif ?>
        <? else: ?>
            <?= CourseAvatar::getAvatar($semid)->getImageTag(Avatar::SMALL, ['title' => trim($values['Name'])]) ?>
        <? endif; ?>
        </td>
        <? if (in_array('number', $view_filter)) : ?>
            <td>
                <? if ($GLOBALS['perm']->have_studip_perm('autor', $semid)) : ?>
                <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $semid]) ?>">
                    <? endif ?>
                    <?= htmlReady($values["VeranstaltungsNummer"]) ?>
                    <? if ($GLOBALS['perm']->have_studip_perm('autor', $semid)) : ?>
                </a>
            <? endif ?>
            </td>
        <? endif ?>
        <? if (in_array('name', $view_filter)) : ?>
            <td>
                <? if ($GLOBALS['perm']->have_studip_perm("autor", $semid)) : ?>
                <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $semid]) ?>">
                    <? endif ?>
                    <?= htmlReady(trim($values['Name'])) ?>
                    <? if ($GLOBALS['perm']->have_studip_perm("autor", $semid)) : ?>
                </a>
            <? endif ?>
                <a data-dialog="buttons=false" href="<?= $controller->url_for(sprintf('course/details/index/%s', $semid)) ?>">
                    <? $params = tooltip2(_("Veranstaltungsdetails anzeigen")); ?>
                    <? $params['style'] = 'cursor: pointer'; ?>
                    <?= Icon::create('info-circle', 'inactive')->asImg($params) ?>
                </a>
                <? if ($values["visible"] == 0) : ?>
                    <?= _("(versteckt)") ?>
                <? endif ?>
                <?php if (count($children) > 0) : ?>
                    <br>
                    <a href="" class="toggle-subcourses" data-get-subcourses-url="<?= $controller->url_for('admin/courses/get_subcourses', $semid) ?>">
                        <?= Icon::create('add', 'clickable')->asImg(12) ?>
                        <?= Icon::create('remove', 'clickable', ['class' => 'hidden-js'])->asImg(12) ?>
                        <?= sprintf(
                            ngettext('%u Unterveranstaltung', '%u Unterveranstaltungen',
                                count($children)),
                            count($children)) ?>
                    </a>
                <?php endif ?>
            </td>
        <? endif ?>
        <? if (in_array('type', $view_filter)) : ?>
            <td>
                <?= htmlReady($GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$values["status"]]["class"]]['name']) ?>:
                <strong><?= htmlReady($GLOBALS['SEM_TYPE'][$values["status"]]["name"]) ?></strong>
            </td>
        <? endif ?>
        <? if (in_array('room_time', $view_filter)) : ?>
            <td class="raumzeit">
                <?= Seminar::GetInstance($semid)->getDatesHTML([
                    'semester_id' => $semester ? $semester->id : null,
                    'show_room'   => true,
                ]) ?: _('nicht angegeben') ?>
            </td>
        <? endif ?>
        <? if (in_array('semester', $view_filter)) : ?>
            <td>
                <?= htmlReady($course->semester_text) ?>
            </td>
        <? endif?>
        <? if (in_array('requests', $view_filter)) : ?>
            <td style="text-align: center;">
                <a title="<?=_('Raumanfragen')?>" href="<?= URLHelper::getLink('dispatch.php/course/room_requests', ['cid' => $semid])?>">
                    <?= $values['requests'] ?>
                </a>
            </td>
        <? endif ?>
        <? if (in_array('teachers', $view_filter)) : ?>
            <td>
                <?= $this->render_partial_collection('my_courses/_dozent', $values['dozenten']) ?>

            </td>
        <? endif ?>
        <? if (in_array('members', $view_filter)) : ?>
            <td style="text-align: center;">
                <a title="<?=_('Teilnehmende')?>" href="<?= URLHelper::getLink('dispatch.php/course/members', ['cid' => $semid])?>">
                    <?= $values["teilnehmer"] ?>
                </a>
            </td>
        <? endif ?>
        <? if (in_array('waiting', $view_filter)) : ?>
            <td style="text-align: center;">
                <a title="<?=_('Teilnehmende auf der Warteliste')?>" href="<?= URLHelper::getLink('dispatch.php/course/members', ['cid' => $semid])?>">
                    <?= $values["waiting"] ?>
                </a>
            </td>
        <? endif ?>
        <? if (in_array('preliminary', $view_filter)) : ?>
            <td style="text-align: center;">
                <a title="<?=_('Vorläufige Anmeldungen') ?>" href="<?= URLHelper::getLink('dispatch.php/course/members', ['cid' => $semid])?>">
                    <?= $values['prelim'] ?>
                </a>
            </td>
        <? endif ?>
        <? if (in_array('contents', $view_filter)) : ?>
            <td style="text-align: left; white-space: nowrap;">
                <? if (!empty($values['navigation'])) : ?>
                    <? foreach (MyRealmModel::array_rtrim($values['navigation']) as $key => $nav)  : ?>
                        <? if ($nav instanceof Navigation && $nav->isVisible(true)) : ?>
                            <a href="<?=
                            UrlHelper::getLink('seminar_main.php',
                                ['auswahl'     => $semid,
                                    'redirect_to' => strtr($nav->getURL(), '?', '&')]) ?>" <?= $nav->hasBadgeNumber() ? 'class="badge" data-badge-number="' . intval($nav->getBadgeNumber()) . '"' : '' ?>>
                                <?= $nav->getImage()->asImg(20, $nav->getLinkAttributes()) ?>
                            </a>
                        <? elseif (is_string($key)) : ?>
                            <?=
                            Assets::img('blank.gif', ['width'  => 20,
                                'height' => 20]); ?>
                        <? endif ?>
                        <? echo ' ' ?>
                    <? endforeach ?>
                <? endif ?>
            </td>
        <? endif ?>
        <? if (in_array('last_activity', $view_filter)) : ?>
            <td style="text-align: center;">
                <span title="<?=_('Datum der letzten Aktivität in dieser Veranstaltung')?>">
                    <?= htmlReady(date('d.m.Y', $values['last_activity'])); ?>
                </span>
            </td>
        <? endif ?>
        <? foreach (PluginManager::getInstance()->getPlugins("AdminCourseContents") as $plugin) : ?>
            <? foreach ($plugin->adminAvailableContents() as $index => $label) : ?>
                <? if (in_array($plugin->getPluginId()."_".$index, $view_filter)) : ?>
                    <td style="text-align: center;">
                        <? $content = $plugin->adminAreaGetCourseContent($course, $index) ?>
                        <?= is_a($content, "Flexi_Template") ? $content->render() : $content ?>
                    </td>
                <? endif ?>
            <? endforeach ?>
        <? endforeach ?>
        <td class="actions">
            <? if (isset($actions[$selected_action]['partial']) && is_numeric($selected_action) && $GLOBALS['perm']->have_studip_perm('tutor', $semid)) : ?>
                <?= $this->render_partial("admin/courses/{$actions[$selected_action]['partial']}", [
                    'course' => $course,
                    'values' => $values,
                    'action' => $actions[$selected_action],
                ]) ?>
            <? elseif (!is_numeric($selected_action)) : ?>
                <? $plugin = PluginManager::getInstance()->getPlugin($selected_action) ?>
                <? $template = $plugin->getAdminCourseActionTemplate($semid, $values) ?>
                <? if ($template) : ?>
                    <?= $template->render() ?>
                <? elseif ($GLOBALS['perm']->have_studip_perm('tutor', $semid)) : ?>
                    <?=
                    \Studip\LinkButton::create(
                        $actions[$selected_action]['title'],
                        URLHelper::getURL(sprintf($actions[$selected_action]['url'], $semid),
                            ($actions[$selected_action]['params'] ? $actions[$selected_action]['params'] : [])),
                        ($actions[$selected_action]['attributes'] ? $actions[$selected_action]['attributes'] : [])
                    ) ?>
                <? endif ?>
            <? elseif ($GLOBALS['perm']->have_studip_perm('tutor', $semid)) : ?>
                <? $lockrules = [
                    '2' => "sem_tree",
                    '3' => "room_time",
                    '11' => "seminar_copy",
                    '14' => "admission_type",
                    '16' => "seminar_archive",
                    '17' => "admission_type",
                    '18' => 'room_time'
                ] ?>
                <? if ($GLOBALS['perm']->have_studip_perm("admin", $semid) || !isset($lockrules[$selected_action]) || !LockRules::Check($semid, $lockrules[$selected_action])) : ?>
                    <?=
                    \Studip\LinkButton::create(
                        $actions[$selected_action]['title'],
                        URLHelper::getURL(sprintf($actions[$selected_action]['url'], $semid),
                            ($actions[$selected_action]['params'] ? $actions[$selected_action]['params'] : [])),
                        ($actions[$selected_action]['attributes'] ? $actions[$selected_action]['attributes'] : [])
                    ) ?>
                <? endif ?>
            <? endif ?>
        </td>
    </tr>
<?php endif ?>
