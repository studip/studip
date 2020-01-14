<?php
/**
 * Template documentation:
 *
 * @param Array $criteria: A search criteria with the following structure:
 *     [
 *         'name' => The criteria's internal name.
 *         'type' => The type of the criteria:
 *                   'bool', 'num', 'select', 'date' or 'text'
 *         'range_search' => Whether a range search shall be used or not.
 *             This is only evaluated for the types 'date' and 'num'
 *         'switch' => Whether a checkbox shall be added to the criteria
 *             that enables/disables the input fields of the criteria.
 *         'value' => The value of the search criteria.
 *             For range search criteria the values are split by ':'.
 *     ]
 *
 * @param bool removable Whether the criteria can be removed or not.
 *     If the criteria can be removed a trash icon is shown.
 */
?>
<li class="item">
    <? if ($removable): ?>
        <?= Icon::create('trash', 'clickable')->asImg(
            '16px',
            [
                'class' => 'text-bottom remove-icon'
            ]
        ) ?>
    <? endif ?>
    <? if ($criteria['switch']): ?>
        <input type="checkbox" class="special-item-switch studip-checkbox" value="1"
               title="<?= _('Kriterium ausgewählt'); ?>" id="cb_<?= htmlReady($criteria['name']); ?>"
               name="<?= htmlReady($criteria['name'] . '_enabled')?>"
               <?= $criteria['enabled'] ? 'checked="checked"' : ''?>>
    <? endif ?>
    <? if ($criteria['type'] == 'bool'): ?>
        <input type="hidden"
               name="<?= htmlReady($criteria['name'])?>"
               value="1">
        <label class="undecorated" for="cb_<?= htmlReady($criteria['name']); ?>">
            <span><?= htmlReady($criteria['title']) ?></span>
        </label>
    <? elseif ($criteria['type'] == 'num'): ?>
        <? if ($criteria['range_search']): ?>
            <label class="range-search-label undecorated" for="cb_<?= htmlReady($criteria['name']); ?>">
                <input type="hidden" name="<?= htmlReady($criteria['name']) ?>">
                <span><?= htmlReady($criteria['title']) ?></span>
                <div class="range-input-container">
                    <?= _('von') ?>
                    <input type="number"
                           name="<?= htmlReady($criteria['name'])?>_min"
                           value="<?= empty($criteria['value'][0])?'':intval($criteria['value'][0])?>">
                    <?= _('bis') ?>
                    <input type="number"
                           name="<?= htmlReady($criteria['name'])?>_max"
                           value="<?= empty($criteria['value'][1])?'':intval($criteria['value'][1])?>">
                </div>
            </label>
        <? else: ?>
            <label class="undecorated" for="cb_<?= htmlReady($criteria['name']); ?>">
                <span><?= htmlReady($criteria['title']) ?></span>
                <input type="number"
                       name="<?= htmlReady($criteria['name'])?>"
                       value="<?= intval($criteria['value'])?>">
            </label>
        <? endif ?>
    <? elseif ($criteria['type'] == 'select'): ?>
        <label class="undecorated" for="cb_<?= htmlReady($criteria['name']); ?>">
            <span><?= htmlReady($criteria['title']) ?></span>
            <select name="<?= htmlReady($criteria['name']) ?>">
                <? if (is_array($criteria['options'])): ?>
                    <? foreach ($criteria['options'] as $value => $title): ?>
                        <option value="<?= htmlReady($value) ?>"
                                <?= ($value == $criteria['value']
                                   ? 'selected="selected"'
                                   : '') ?>>
                            <?= htmlReady($title) ?>
                        </option>
                    <? endforeach ?>
                <? endif ?>
            </select>
        </label>
    <? elseif ($criteria['type'] == 'select2'): ?>
        <label class="undecorated" for="cb_<?= htmlReady($criteria['name']); ?>">
            <span><?= htmlReady($criteria['title']) ?></span>
            <div class="wrapper_wrapper">
                <select name="<?= htmlReady($criteria['name']) ?>"
                        class="nested-select">
                    <? if (is_array($criteria['options'])): ?>
                        <? foreach ($criteria['options'] as $option): ?>
                            <option value="<?= htmlReady($option['id']) ?>"
                                    <?= ($option['id'] == $criteria['value']
                                    ? 'selected="selected"'
                                    : '') ?>>
                                <?= htmlReady($option['name']) ?>
                            </option>
                            <? foreach ($option['sub_options'] as $sub_option): ?>
                                <option value="<?= htmlReady($sub_option['id']) ?>"
                                        class="nested-item nested-level-1"
                                        <?= ($sub_option['id'] == $criteria['value']
                                        ? 'selected="selected"'
                                        : '') ?>>
                                    <?= htmlReady($sub_option['name']) ?>
                                </option>
                            <? endforeach ?>
                        <? endforeach ?>
                    <? endif ?>
                </select>
            </div>
        </label>
    <? elseif ($criteria['type'] == 'date'): ?>
        <label class="undecorated" for="cb_<?= htmlReady($criteria['name']); ?>">
            <span><?= htmlReady($criteria['title']) ?></span>
            <? if ($criteria['range_search']): ?>
                <div class="range-input-container">
                    <input type="text"
                           name="<?= htmlReady($criteria['name']) ?>_begin_date"
                           value="<?= htmlReady($criteria['value']['begin']->format('d.m.Y')) ?>"
                           class="has-date-picker">
                    <input type="text" data-time="yes"
                           name="<?= htmlReady($criteria['name']) ?>_begin_time"
                           value="<?= htmlReady($criteria['value']['begin']->format('H:i')) ?>"
                           class="has-time-picker">
                    <?= _('Uhr') ?>
                    <input type="text"
                           name="<?= htmlReady($criteria['name']) ?>_end_date"
                           value="<?= htmlReady($criteria['value']['end']->format('d.m.Y')) ?>"
                           class="has-date-picker">
                    <input type="text" data-time="yes"
                           name="<?= htmlReady($criteria['name']) ?>_end_time"
                           value="<?= htmlReady($criteria['value']['end']->format('H:i')) ?>"
                           class="has-time-picker">
                    <?= _('Uhr') ?>
                </div>
            <? else: ?>
                <div class="range-input-container">
                    <input type="text"
                           name="<?= htmlReady($criteria['name']) ?>_date"
                           value="<?= htmlReady($criteria['value']->format('d.m.Y')) ?>"
                           class="has-date-picker">
                    <input type="text" data-time="yes"
                           name="<?= htmlReady($criteria['name']) ?>_time"
                           value="<?= htmlReady($criteria['value']->format('H:i')) ?>"
                           class="has-time-picker">
                    <?= _('Uhr') ?>
                </div>
            <? endif ?>
        </label>
    <? elseif ($criteria['type'] == 'hidden'): ?>
        <input type="hidden"
            name="<?= htmlReady($criteria['name'])?>"
            value="<?= htmlReady(strval($criteria['value']))?>">
    <? elseif ($criteria['type'] == 'disabled_text'): ?>
        <label class="undecorated" for="cb_<?= htmlReady($criteria['name']); ?>">
            <span><?= htmlReady($criteria['title']) ?></span>
            <input type="text"
                name="<?= htmlReady($criteria['name'])?>"
                value="<?= htmlReady(strval($criteria['value']))?>"
                disabled="disabled">
        </label>
    <? else: ?>
        <label class="undecorated" for="cb_<?= htmlReady($criteria['name']); ?>">
            <span><?= htmlReady($criteria['title']) ?></span>
            <input type="text"
                   name="<?= htmlReady($criteria['name'])?>"
                   value="<?= htmlReady(strval($criteria['value']))?>">
        </label>
    <? endif ?>
</li>
