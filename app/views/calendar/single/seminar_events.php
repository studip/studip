<? if (!empty($sem_courses)) : ?>
    <?= $this->render_partial('calendar/single/_semester_filter') ?>
    <? $_order = (!$order_by || $order == 'desc') ? 'asc' : 'desc' ?>
    <form action="<?= $controller->url_for('calendar/single/store_selected_sem') ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <div id="my_seminars">
            <? foreach ($sem_courses as $sem_key => $course_group) : ?>
                <table class="default collapsable">
                    <caption>
                        <?= htmlReady($sem_data[$sem_key]['name']) ?>
                    </caption>
                    <colgroup>
                        <col width="7px">
                        <col width="25px">
                        <? if ($config_sem_number) : ?>
                            <col width="10%">
                        <? endif ?>
                        <col>
                        <col width="45px">
                        <col width="10%">
                    </colgroup>
                    <thead>
                    <tr class="sortable">
                        <th></th>
                        <th></th>
                        <? if ($config_sem_number) : ?>
                            <th class=<?= ($order_by == 'veranstaltungsnummer') ? ($order == 'desc') ? 'sortdesc' : 'sortasc' : '' ?>>
                                <a href="<?= $controller->url_for(sprintf('my_courses/index/veranstaltungsnummer/%s', $_order)) ?>">
                                    <?= _('Nr.') ?>
                                </a>
                            </th>
                        <? endif ?>
                        <th
                            class=<?= ($order_by == 'name') ? ($order == 'desc') ? 'sortdesc' : 'sortasc' : '' ?>>
                            <a href="<?= $controller->url_for(sprintf('calendar/single/seminar_events/name/%s', $_order)) ?>" data-dialog="size=auto">
                                <?= _('Name') ?>
                            </a>
                        </th>
                        <th></th>
                        <th><?= _('Auswahl') ?></th>
                    </tr>
                    </thead>
                <? foreach ($course_group as $course)  : ?>
                    <? $sem_class = $course['sem_class']; ?>
                    <tr>
                        <td class="gruppe<?= $course['gruppe'] ?>"></td>
                        <td>
                            <? if ($sem_class['studygroup_mode']) : ?>
                                <?= StudygroupAvatar::getAvatar($course['seminar_id'])->getImageTag(Avatar::SMALL, ['title' => $course['name']])
                                ?>
                            <? else : ?>
                                <?= CourseAvatar::getAvatar($course['seminar_id'])->getImageTag(Avatar::SMALL, ['title' => $course['name']])
                                ?>
                            <? endif ?>
                        </td>
                        <? if($config_sem_number) :?>
                            <td><?= $course['veranstaltungsnummer']?></td>
                        <? endif?>
                        <td style="text-align: left">
                            <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $course['seminar_id']]) ?>"
                                <?= $course['visitdate'] <= $course['chdate'] ? 'style="color: red;"' : '' ?>>
                                <?= htmlReady($course['name']) ?>
                                <?= ($course['is_deputy'] ? ' ' . _('[Vertretung]') : '');?>
                            </a>
                            <? if ($course['visible'] == 0) : ?>
                                <?= _('[versteckt]') ?>
                            <? endif ?>
                        </td>
                        <td>
                            <? if (!$sem_class['studygroup_mode']) : ?>
                                <a data-dialog href="<?= $controller->url_for(sprintf('course/details/index/%s', $course['seminar_id'])) ?>">
                                    <? $params = tooltip2(_('Veranstaltungsdetails')); ?>
                                    <? $params['style'] = 'cursor: pointer'; ?>
                                    <?= Icon::create('info-circle', 'inactive')->asImg(20, $params) ?>
                                </a>
                            <? else : ?>
                                <?= Assets::img('blank.gif', ['width'  => 20, 'height' => 20]); ?>
                            <? endif ?>
                        </td>
                        <td style="text-align: center;">
                            <input type="hidden" name="selected_sem[<?= $course['seminar_id'] ?>]" value="0">
                            <input type="checkbox" name="selected_sem[<?= $course['seminar_id'] ?>]" value="1"<?= in_array($course['seminar_id'], $bind_calendar) ? ' checked' : '' ?>>
                        </td>
                    </tr>
                <? endforeach ?>
                </table>
            <? endforeach ?>
        </div>
        <div style="text-align: center;" data-dialog-button>
            <?= Studip\Button::create(_('Speichern'), 'store') ?>
            <? if (!Request::isXhr()) : ?>
            <?= Studip\LinkButton::create(_('Abbrechen'), $controller->url_for('calendar/single/' . $last_view)) ?>
            <? endif; ?>
        </div>
    </form>
<? else : ?>
    <?= PageLayout::postMessage(MessageBox::info(_('Es wurden keine Veranstaltungen gefunden. Mögliche Ursachen:'), [
        sprintf(_('Sie haben zur Zeit keine Veranstaltungen belegt, an denen Sie teilnehmen können.<br>Bitte nutzen Sie %s<b>Veranstaltung suchen / hinzufügen</b>%s um sich für Veranstaltungen anzumelden.'),'<a href="' . URLHelper::getLink('dispatch.php/search/courses') . '">', '</a>'),
        _('In dem ausgewählten <b>Semester</b> wurden keine Veranstaltungen belegt.').'<br>'._('Wählen Sie links im <b>Semesterfilter</b> ein anderes Semester aus')
    ]))?>
<? endif ?>
<? if (is_array($my_bosses) && count($my_bosses)) : ?>
    <?= $this->render_partial('my_courses/_deputy_bosses'); ?>
<? endif ?>
