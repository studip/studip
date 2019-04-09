<? if (isset($flash['decline_course'])) : ?>
    <?=
    createQuestion($flash['message'], ['cmd' => $flash['cmd'], 'studipticket' => $flash['studipticket']],
        ['cmd'          => 'back',
              'studipticket' => $flash['studipticket']],
        $controller->url_for(sprintf('my_courses/decline/%s', $flash['course_id']))); ?>
<? endif ?>

<? if (sizeof($waiting_list)) : ?>
    <?= $this->render_partial('my_courses/waiting_list.php', compact('waiting_list')) ?>
<? endif ?>


<? if (!empty($sem_courses)) : ?>
    <? $_order = (!$order_by || $order == 'desc') ? 'asc' : 'desc' ?>
    <? SkipLinks::addIndex(_("Meine Veranstaltungen"), 'my_seminars') ?>
    <div id="my_seminars">
        <? foreach ($sem_courses as $sem_key => $course_group) : ?>
            <table class="default collapsable mycourses">
                <caption>
                    <?= htmlReady($sem_data[$sem_key]['name']) ?>
                </caption>
                <colgroup>
                    <col width="7px">
                    <col width="25px">
                    <? if ($config_sem_number) : ?>
                        <col width="70px">
                    <? endif ?>
                    <col>
                    <col class="hidden-small-down" width="45px">
                    <col class="hidden-small-down" width="<?= $nav_elements * 27 ?>px">
                    <col class="hidden-small-down" width=45px>
                </colgroup>
                <thead>
                <tr class="sortable">
                    <th></th>
                    <th></th>
                    <? if ($config_sem_number) : ?>
                        <th class=<?= ($order_by == "veranstaltungsnummer") ? ($order == 'desc') ? 'sortdesc' : 'sortasc' : '' ?>>
                            <a href="<?= $controller->url_for(sprintf('my_courses/index/veranstaltungsnummer/%s', $_order)) ?>">
                                <?= _("Nr.") ?>
                            </a>
                        </th>
                    <? endif ?>
                    <th
                        class=<?= ($order_by == "name") ? ($order == 'desc') ? 'sortdesc' : 'sortasc' : '' ?>>
                        <a href="<?= $controller->url_for(sprintf('my_courses/index/name/%s', $_order)) ?>">
                            <?= _("Name") ?>
                        </a>
                    </th>
                    <th class="hidden-small-down"></th>
                    <th class="hidden-small-down"><?= _("Inhalt") ?></th>
                    <th class="hidden-small-down"></th>
                </tr>
                </thead>
                <? if (strcmp($group_field, 'sem_number') !== 0) : ?>
                    <?= $this->render_partial("my_courses/_group", compact('sem_key','course_group')) ?>
                <? else : ?>
                    <? $course_collection = $course_group ?>
                    <?= $this->render_partial("my_courses/_course", compact('course_collection')) ?>
                <? endif ?>
            </table>
        <? endforeach ?>
    </div>
<? else : ?>
    <?= PageLayout::postMessage(MessageBox::info(_('Es wurden keine Veranstaltungen gefunden. Mögliche Ursachen:'), [
        sprintf(_('Sie haben zur Zeit keine Veranstaltungen belegt, an denen Sie teilnehmen können.<br>Bitte nutzen Sie %s<b>Veranstaltung suchen / hinzufügen</b>%s um sich für Veranstaltungen anzumelden.'),'<a href="' . URLHelper::getLink('dispatch.php/search/courses') . '">', '</a>'),
        _('In dem ausgewählten <b>Semester</b> wurden keine Veranstaltungen belegt.').'<br>'._('Wählen Sie links im <b>Semesterfilter</b> ein anderes Semester aus')
    ]))?>
<? endif ?>
<? if (count($my_bosses)) : ?>
    <?= $this->render_partial('my_courses/_deputy_bosses'); ?>
<? endif ?>
