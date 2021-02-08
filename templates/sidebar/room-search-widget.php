<form class="default room-search-form" action="<?= $action_link ?>"
      method="post">
    <input type="hidden" name="extended_search">

    <ul class="criteria-list">
        <?= $this->render_partial(
            'sidebar/room-search-criteria.php',
            [
                'criteria' => (
                    $selected_criteria['special__room_name']
                    ? $selected_criteria['special__room_name']
                    : $criteria['special__room_name']
                    ),
                'removable' => false
            ]
        ) ?>
        <?= $this->render_partial(
            'sidebar/room-search-criteria.php',
            [
                'criteria' => (
                    $selected_criteria['room_type']
                    ? $selected_criteria['room_type']
                    : $criteria['room_type']
                    ),
                'removable' => false
            ]
        ) ?>
        <?= $this->render_partial(
            'sidebar/room-search-criteria.php',
            [
                'criteria' => (
                $selected_criteria['room_category_id']
                    ? $selected_criteria['room_category_id']
                    : $criteria['room_category_id']
                ),
                'removable' => false
            ]
        ) ?>
        <?= $this->render_partial(
            'sidebar/room-search-criteria.php',
            [
                'criteria' => (
                    $selected_criteria['special__building_location']
                    ? $selected_criteria['special__building_location']
                    : $criteria['special__building_location']
                    ),
                'removable' => false
            ]
        ) ?>
        <? if ($selected_criteria['special__building_location_label'] || $criteria['special__building_location_label']): ?>
            <?= $this->render_partial(
                'sidebar/room-search-criteria.php',
                [
                    'criteria' => (
                        $selected_criteria['special__building_location_label']
                        ? $selected_criteria['special__building_location_label']
                        : $criteria['special__building_location_label']
                        ),
                    'removable' => false
                ]
            ) ?>
        <? endif; ?>
        <?= $this->render_partial(
            'sidebar/room-search-criteria.php',
            [
                'criteria' => (
                    $selected_criteria['special__seats']
                    ? $selected_criteria['special__seats']
                    : $criteria['special__seats']
                    ),
                'removable' => false
            ]
        ) ?>
        <?= $this->render_partial(
            'sidebar/room-search-criteria-available-range.php',
            [
                'criteria' => $selected_criteria['special__time_range']
                          ? $selected_criteria['special__time_range']
                          : $criteria['special__time_range']
            ]
        ) ?>

        <? if ($selected_criteria): ?>
            <? foreach ($selected_criteria as $s): ?>
                <? if (preg_match('/^special__/', $s['name']) || ($s['name'] == 'room_type') || ($s['name'] == 'room_category_id')) {
                    continue;
                } ?>
                <?= $this->render_partial(
                    'sidebar/room-search-criteria.php',
                    [
                        'criteria' => $s,
                        'removable' => true
                    ]
                ) ?>
            <? endforeach ?>
        <? endif ?>
        <?= $this->render_partial(
            'sidebar/room-search-criteria-templates.php'
        ) ?>
    </ul>
    <?= \Studip\Button::create(_('Suchen'), 'room_search') ?>
    <?= \Studip\Button::create(_('Zurücksetzen'), 'room_search_reset') ?>
    <label>
        <?= _('Filter hinzufügen') ?>:
        <select class="criteria-selector"
                title="<?= _('Bitte aus dieser Liste Kriterien für die Raumsuche auswählen.')?>">
            <option value=""></option>
            <? foreach ($criteria as $c): ?>
                <? if (!$c['optional']) { continue; } ?>
                <option data-title="<?= htmlReady($c['title'])?>"
                        value="<?= htmlReady($c['name'])?>"
                        data-type="<?= htmlReady($c['type']) ?>"
                        data-range-search="<?= htmlReady($c['range_search']) ?>"
                        data-select_options="<?= htmlReady(
                                             is_array($c['options'])
                                             ? implode(';;', $c['options'])
                                             : $c['options']
                                             ) ?>"
                        <?= in_array($c['name'], array_keys($selected_criteria))
                          ? 'class="invisible"'
                          : ''?>>
                    <?= htmlReady($c['title']) ?>
                </option>
            <? endforeach ?>
        </select>
    </label>
</form>
<script type="text/javascript">
/*  jQuery('.room-search-form .criteria-list input[type="date"]').datepicker(
     {
         dateFormat: 'yy-mm-dd'
     }
 );
 jQuery('.room-search-form .criteria-list input[type="time"]').timepicker(
     {
         timeFormat: 'G:i'
     }
 ); */
</script>
