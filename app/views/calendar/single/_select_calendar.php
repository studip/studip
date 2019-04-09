<form name="select_calendars" class="default" method="post" action="<?= htmlReady($action_url) ?>">

    <section class="hgroup">
        <?= _('Kalender') ?>
        <select class="sidebar-selectlist submit-upon-select" style="width: 16em;" name="range_id">
            <option value="user.<?= get_username() ?>"<?= (get_userid() == $calendar_id ? ' selected' : '') ?>>
                    <?= _("Eigener Kalender") ?>
            </option>
            <? $groups = Calendar::getGroups($GLOBALS['user']->id); ?>
            <? if (count($groups)) : ?>
                <optgroup style="font-weight:bold;" label="<?= _('Gruppenkalender:') ?>">
                <? foreach ($groups as $group) : ?>
                    <option value="<?= $group->getId() ?>"<?= ($range_id == $group->getId() ? ' selected' : '') ?>>
                         <?= htmlReady($group->name) ?>
                    </option>
                <? endforeach ?>
                </optgroup>
            <? endif; ?>
            <? $calendar_users = CalendarUser::getOwners($GLOBALS['user']->id)->getArrayCopy(); ?>
            <? usort($calendar_users, function ($a, $b) {
                return strnatcmp($a->owner->Nachname, $b->owner->Nachname);
            }); ?>
            <? if (count($calendar_users)) : ?>
                <optgroup style="font-weight:bold;" label="<?= _('Einzelkalender:') ?>">
                <? foreach ($calendar_users as $calendar_user) : ?>
                    <? if (!$calendar_user->owner) {
                        continue;
                    } ?>
                    <option value="<?= $calendar_user->owner_id ?>"<?= ($range_id == $calendar_user->owner_id ? ' selected' : '') ?>>
                        <?= htmlReady($calendar_user->owner->getFullname('full_rev')) ?>
                    </option>
                <? endforeach ?>
                </optgroup>
            <? endif ?>
            <?/*
                if ($GLOBALS['perm']->have_perm('dozent')) {
                    $lecturers = Calendar::GetLecturers();
                } else {
                    $lecturers = array();
                }
                */
                $lecturers = [];
            ?>
            <? if (count($lecturers)) : ?>
                <optgroup style="font-weight:bold;" label="<?= _('Dozentenkalender:') ?>">
                <? foreach ($lecturers as $lecturer) : ?>
                    <option value="<?= $lecturer['id'] ?>"<?= ($range_id == $lecturer['id'] ? ' selected' : '') ?>>
                        <?= htmlReady(my_substr($lecturer['name'] . " ({$lecturer['username']})", 0, 30)) ?>
                    </option>
                <? endforeach ?>
                </optgroup>
            <? endif ?>
            <? if (get_config('COURSE_CALENDAR_ENABLE')) : ?>
                <? $courses = Calendar::GetCoursesActivatedCalendar($GLOBALS['user']->id); ?>
                <? if (count($courses)) : ?>
                    <optgroup style="font-weight:bold;" label="<?= _('Veranstaltungskalender:') ?>">
                    <? foreach ($courses as $course) : ?>
                        <option value="<?= $course->id ?>"<?= ($range_id == $course->id ? ' selected' : '') ?>>
                            <?= htmlReady($course->getFullname()) ?>
                        </option>
                    <? endforeach ?>
                    </optgroup>
                <? endif ?>
                <? $insts = Calendar::GetInstituteActivatedCalendar($GLOBALS['user']->id); ?>
                <? if (count($insts)) : ?>
                    <optgroup style="font-weight:bold;" label="<?= _('Einrichtungskalender:') ?>">
                    <? foreach ($insts as $inst_id => $inst_name) : ?>
                        <option value="<?= $inst_id ?>"<?= ($range_id == $inst_id ? ' selected' : '') ?>>
                            <?= htmlReady(my_substr($inst_name, 0, 30)); ?>
                        </option>
                    <? endforeach ?>
                    </optgroup>
                <? endif ?>
            <? endif ?>
        </select>

        <input type="hidden" name="view" value="<?= $view ?>">
        <?= Icon::create('accept', 'clickable')->asInput(['class' => "text-top"]) ?>
    </section>
</form>
