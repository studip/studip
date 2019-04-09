<? $colspan = 2 ?>
<? if ($actions[$selected_action]['multimode']) : ?>
    <form action="<?= URLHelper::getLink($actions[$selected_action]['url']) ?>" method="post">
<? endif ?>
<?= CSRFProtection::tokenTag() ?>
<table class="default course-admin">
    <colgroup>
        <col width="2%">
    <? if (in_array('number', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="8%">
    <? endif ?>
    <? if (in_array('name', $view_filter)) : ?>
        <? $colspan++ ?>
        <col>
    <? endif ?>
    <? if (in_array('type', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="10%">
    <? endif ?>
    <? if (in_array('room_time', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="30%">
    <? endif ?>
    <? if (in_array('semester', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="10%">
    <? endif ?>
    <? if (in_array('requests', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="5%">
    <? endif ?>
    <? if (in_array('teachers', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="10%">
    <? endif ?>
    <? if (in_array('members', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="3%">
    <? endif ?>
    <? if (in_array('waiting', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="5%">
    <? endif ?>
    <? if (in_array('preliminary', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="5%">
    <? endif ?>
    <? if (in_array('contents', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="8%">
    <? endif ?>
    <? if (in_array('last_activity', $view_filter)) : ?>
        <? $colspan++ ?>
        <col width="8%">
    <? endif ?>
        <? foreach (PluginManager::getInstance()->getPlugins("AdminCourseContents") as $plugin) : ?>
            <? foreach ($plugin->adminAvailableContents() as $index => $label) : ?>
                <? if (in_array($plugin->getPluginId()."_".$index, $view_filter)) : ?>
                    <? $colspan++ ?>
                    <col width="8%">
                <? endif ?>
            <? endforeach ?>
        <? endforeach ?>
        <col width="15%">

    </colgroup>
    <caption>
        <? if (!$GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE || ($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE === "all")) : ?>
            <?= _('Veranstaltungen') ?>
        <? else : ?>
            <?= htmlReady(sprintf(_('Veranstaltungen im %s'), $semester->name)) ?>
        <? endif ?>
        <span class="actions">
                <?= sprintf('%u %s', $count_courses, $count_courses > 1 ? _('Veranstaltungen') : _('Veranstaltung')) ?>
            </span>
    </caption>
    <thead>
    <tr class="sortable">
    <? if (Config::get()->ADMIN_COURSES_SHOW_COMPLETE): ?>
        <th <? if ($sortby === 'completion') printf('class="sort%s"', mb_strtolower($sortFlag)) ?>>
            <a href="<?= URLHelper::getLink('', ['sortby' => 'completion', 'sortFlag' => mb_strtolower($sortFlag)]) ?>" class="course-completion" title="<?= _('Bearbeitungsstatus') ?>">
                <?= _('Bearbeitungsstatus') ?>
            </a>
        </th>
    <? else: ?>
        <th>
            &nbsp;
        </th>
    <? endif; ?>
        <? if (in_array('number', $view_filter)) : ?>
            <th <?= ($sortby == 'VeranstaltungsNummer') ? sprintf('class="sort%s"', mb_strtolower($sortFlag)) : '' ?>>
                <a href="<?=
                URLHelper::getLink('', ['sortby'   => 'VeranstaltungsNummer',
                                             'sortFlag' => mb_strtolower($sortFlag)]) ?>">
                    <?= _('Nr.') ?>
                </a>
            </th>
        <? endif ?>
        <? if (in_array('name', $view_filter)) : ?>
            <th <?= ($sortby == 'Name') ? sprintf('class="sort%s"', mb_strtolower($sortFlag)) : '' ?>>
                <a href="<?=
                URLHelper::getLink('', ['sortby'   => 'Name',
                                             'sortFlag' => mb_strtolower($sortFlag)]) ?>">
                    <?= _('Name') ?>
                </a>
            </th>
        <? endif ?>
        <? if (in_array('type', $view_filter)) : ?>
            <th <?= ($sortby == 'status') ? sprintf('class="sort%s"', mb_strtolower($sortFlag)) : '' ?>>
                <a href="<?=
                URLHelper::getLink('', ['sortby'   => 'status',
                                             'sortFlag' => mb_strtolower($sortFlag)]) ?>">
                    <?= _("VA-Typ") ?>
                </a>
            </th>
        <? endif ?>
        <? if (in_array('room_time', $view_filter)) : ?>
            <th><?= _('Raum/Zeit') ?></th>
        <? endif ?>
        <? if (in_array('semester', $view_filter)) : ?>
            <th <?= ($sortby == 'start_time') ? sprintf('class="sort%s"', mb_strtolower($sortFlag)) : '' ?>>
                <a href="<?= URLHelper::getLink('', ['sortby'   => 'start_time', 'sortFlag' => mb_strtolower($sortFlag)]) ?>">
                    <?= _('Semester') ?>
                </a>
            </th>
        <? endif ?>
        <? if (in_array('requests', $view_filter)) : ?>
            <th <?= ($sortby == 'requests') ? sprintf('class="sort%s"', mb_strtolower($sortFlag)) : '' ?>>
                <a href="<?=
                URLHelper::getLink('', ['sortby'   => 'requests',
                                             'sortFlag' => mb_strtolower($sortFlag)]) ?>">
                    <abbr title="<?= _('Raumanfragen') ?>">
                        <?= _('RA') ?>
                    </abbr>
                </a>
            </th>
        <? endif ?>
        <? if (in_array('teachers', $view_filter)) : ?>
            <th><?= _('Lehrende') ?></th>
        <? endif ?>
        <? if (in_array('members', $view_filter)) : ?>
            <th <?= ($sortby == 'teilnehmer') ? sprintf('class="sort%s"', mb_strtolower($sortFlag)) : '' ?>>
                <a href="<?=
                URLHelper::getLink('', ['sortby'   => 'teilnehmer',
                                             'sortFlag' => mb_strtolower($sortFlag)]) ?>">
                    <abbr title="<?= _('Teilnehmende') ?>">
                        <?= _('TN') ?>
                    </abbr>
                </a>
            </th>
        <? endif ?>
        <? if (in_array('waiting', $view_filter)) : ?>
            <th <? if ($sortby == 'waiting') printf('class="sort%s"', mb_strtolower($sortFlag)); ?>>
                <a href="<?= URLHelper::getLink('', ['sortby'   => 'waiting',
                                                    'sortFlag' => mb_strtolower($sortFlag)]) ?>">
                    <?= _('Warteliste') ?>
                </a>
            </th>
        <? endif ?>
        <? if (in_array('preliminary', $view_filter)) : ?>
            <th <?= ($sortby == 'prelim') ? sprintf('class="sort%s"', mb_strtolower($sortFlag)) : '' ?>>
                <a href="<?=
                URLHelper::getLink('', ['sortby'   => 'prelim',
                                             'sortFlag' => mb_strtolower($sortFlag)]) ?>"><?= _('Vorläufig') ?></a>
            </th>
        <? endif ?>
        <? if (in_array('contents', $view_filter)) : ?>
            <th style="width: <?= $nav_elements * 27 ?>px">
                <?= _('Inhalt') ?>
            </th>
        <? endif ?>
        <? if (in_array('last_activity', $view_filter)) : ?>
            <th style="width: <?= $nav_elements * 27 ?>px">
                <?= _('letzte Aktivität') ?>
            </th>
        <? endif ?>
        <? foreach (PluginManager::getInstance()->getPlugins("AdminCourseContents") as $plugin) : ?>
            <? foreach ($plugin->adminAvailableContents() as $index => $label) : ?>
                <? if (in_array($plugin->getPluginId()."_".$index, $view_filter)) : ?>
                    <th style="width: <?= $nav_elements * 27 ?>px"><?= htmlReady($label) ?></th>
                <? endif ?>
            <? endforeach ?>
        <? endforeach ?>
        <th style="text-align: center" class="actions">
            <?= _('Aktion') ?>
        </th>
    </tr>
    <? if ($actions[$selected_action]['multimode']) : ?>
        <?= $this->render_partial('admin/courses/additional_inputs.php', compact('colspan')) ?>
        <? if (count($courses) > 10): ?>
            <tr>
                <th colspan="<?= $colspan ?>" style="text-align: right">
                    <? if (is_a($actions[$selected_action]['multimode'], "\\Studip\\Button")) : ?>
                        <?= $actions[$selected_action]['multimode'] ?>
                    <? else : ?>
                        <?= Studip\Button::createAccept(
                                is_string($actions[$selected_action]['multimode'])
                                    ? $actions[$selected_action]['multimode']
                                    : $actions[$selected_action]['title'],
                                'save_action',
                                $selected_action == 16 ? ['data-dialog' => 1] : null) ?>
                    <? endif ?>
                </th>
            </tr>
        <? endif; ?>
    <? endif ?>
    </thead>
    <tbody>
    <? foreach ($courses as $semid => $values) : ?>
        <?= $this->render_partial('admin/courses/_course', compact('semid', 'values', 'view_filter', 'actions', 'selected_action', 'courses')) ?>
    <? endforeach ?>
    </tbody>
<? if ($actions[$selected_action]['multimode']) : ?>
    <tfoot>
        <tr>
            <td colspan="<?= $colspan ?>" style="text-align: right">
                <? if (is_a($actions[$selected_action]['multimode'], "\\Studip\\Button")) : ?>
                    <?= $actions[$selected_action]['multimode'] ?>
                <? else : ?>
                    <?= Studip\Button::createAccept(
                        is_string($actions[$selected_action]['multimode'])
                            ? $actions[$selected_action]['multimode']
                            : $actions[$selected_action]['title'],
                        $actions[$selected_action]['name'],
                        $selected_action == 16 ? ['data-dialog' => 1] : null) ?>
                <? endif ?>
            </td>
        </tr>
    </tfoot>
    <? endif ?>
</table>
<? if ($actions[$selected_action]['multimode']) : ?>
</form>
<? endif ?>
