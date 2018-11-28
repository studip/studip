<? $index = 1 ?>
<? foreach ($course_group as $title => $course_collection) : ?>
    <? $last_modified = 0;
    foreach (array_values($course_collection) as $course) {
        $last_modified = max($last_modified, $course['last_modified']);
    }
    $id = md5($sem_key.$title);
    ?>
    <tbody class="<?=!$_my_sem_open[$id] ? 'collapsed' : ''?>">
    <tr class="table_header header-row">
        <th style="white-space: nowrap; text-align: left"></th>
        <th class="toggle-indicator" style="text-align: left" colspan="<?= !$config_sem_number ? '2' : '3' ?>">
            <a href="<?= URLHelper::getLink(sprintf('dispatch.php/my_courses/set_open_group/%s', $id)) ?>">
                <? if (strcmp($group_field, 'sem_tree_id') === 0 && strcmp($title, '') === 0) : ?>
                    <? $title = "keine Zuordnung"; ?>
                <? endif ?>

                <? if (strcmp($this->group_field, 'gruppe') === 0) : ?>
                    <? $title = _('Gruppe') . " " . $index ?>
                <? endif ?>

                <?= htmlReady($title) ?></a>
        </th>
        <th class="dont-hide hidden-small-down"></th>
        <th class="dont-hide hidden-small-down" colspan="2">
            <? if ($last_modified) : ?>
                <?= tooltipIcon(_('Letzte Änderung') . ': ' . strftime('%x, %H:%M', $last_modified), true) ?>
            <? endif ?>
        </th>
    </tr>
    <? if ($_my_sem_open[$id]) : ?>
        <?= $this->render_partial("my_courses/_course", compact('course_collection')) ?>
    <? endif ?>
    </tbody>
    <? $index++ ?>
<? endforeach ?>

