<?php
    $lastSemester = null;
    $allSemesters = [];
    foreach ($dates as $key => $date) {
        $currentSemester = Semester::findByTimestamp($date['date']);
        if ($currentSemester && (
            !$lastSemester ||
            $currentSemester->getId() !== $lastSemester->getId()
        )) {
            $allSemesters[] = $currentSemester;
            $lastSemester = $currentSemester;
        }
    }
    $lostDateKeys = [];

    if (!count($dates)) {
        PageLayout::postMessage(
            MessageBox::info(_('Keine Termine vorhanden'))
        );
    }
?>

<? foreach ($allSemesters as $semester): ?>
<table class="dates default sortable-table <?= $has_access ? 'has-access' : '' ?>" data-sortlist="[[0, 0]]" data-table-id="<?= htmlReady($semester->id) ?>">
    <caption><?= htmlReady($semester['name']) ?></caption>
        <colgroup class="hidden-small-down">
            <col width="25%">
            <col width="40%">
            <col width="10%">
            <? if (count($course->statusgruppen) > 0): ?>
                <col width="10%">
            <? endif; ?>
            <col width="10%">
            <col width="5%">
        </colgroup>
    <thead>
        <tr>
            <th data-sort="htmldata"><?= _('Zeit') ?></th>
            <th data-sort="text" class="hidden-small-down"><?= _('Thema') ?></th>
            <th data-sort="text" class="hidden-small-down"><?= _('Typ') ?></th>
            <? if (count($course->statusgruppen)): ?>
                <th data-sort="text" class="hidden-small-down"><?= _('Gruppen') ?></th>
            <? endif; ?>
            <th data-sort="text"><?= _('Raum') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
        // print dates
        foreach ($dates as $key => $date) {
            $dateSemester = Semester::findByTimestamp($date['date']);
            if ($dateSemester && $semester->getId() === $dateSemester->getId()) {
                if (is_null($is_next_date) && $date['end_time'] >= time() && !is_a($date, 'CourseExDate')) {
                    $is_next_date = $key;
                }
                $partial = $date instanceof CourseExDate ? '_date_row-exdate' : '_date_row';
                echo $this->render_partial("course/dates/{$partial}.php", [
                    'date'         => $date,
                    'is_next_date' => $is_next_date === $key,
                    'course'       => $course,
                    'has_acces'    => $has_access,
                ]);
            } elseif (!$dateSemester && !in_array($key, $lostDateKeys)) {
                $lostDateKeys[] = $key;
            }
        }
    ?>
    </tbody>
</table>
<? endforeach; ?>

<? if (count($lostDateKeys) > 0): ?>
<table class="dates default sortable-table" data-sortlist="[[0, 0]]" data-table-id="none">
    <caption><?= _('Ohne Semester') ?></caption>
    <thead>
    <tr>
        <th data-sort="htmldata"><?= _('Zeit') ?></th>
        <th data-sort="text" class="hidden-small-down"><?= _('Thema') ?></th>
        <th data-sort="text" class="hidden-small-down"><?= _('Typ') ?></th>
        <? if (count($course->statusgruppen)): ?>
            <th data-sort="text" class="hidden-small-down"><?= _('Gruppen') ?></th>
        <? endif; ?>
        <th data-sort="text"><?= _('Raum') ?></th>
        <th class="actions"><?= _('Aktionen') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
        foreach ($lostDateKeys as $key) {
            $date = $dates[$key];
            $partial = $date instanceof CourseExDate ? '_date_row-exdate' : '_date_row';
            echo $this->render_partial(
                "course/dates/{$partial}.php",
                compact('date', 'dates', 'key')
            );
        }
    ?>
    </tbody>
</table>
<? endif; ?>
