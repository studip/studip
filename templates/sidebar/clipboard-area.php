<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <div>
        <select name="selected_clipboard_id" class="clipboard-selector"
                <?= $clipboards ? '' : 'disabled="disabled"' ?>>
            <? if ($clipboards): ?>
                <? foreach ($clipboards as $clipboard): ?>
                    <option value="<?= htmlReady($clipboard->id) ?>"
                            <?= $clipboard->id == $selected_clipboard_id
                              ? 'selected="selected"'
                              : '' ?>>
                        <?= htmlReady($clipboard->name) ?>
                    </option>
                <? endforeach ?>
            <? endif ?>
        </select>
        <? if (!$readonly): ?>

            <input class="clipboard-name invisible" type="text" name="clipboard_name" value="">

            <?= Icon::create('edit', 'clickable')->asImg(
                '20px',
                [
                    'class' => 'middle clipboard-edit-button'
                           . (
                               $clipboards ? '' : ' invisible'
                           ),
                    'onClick' => "STUDIP.Clipboard.toggleEditButtons('"
                             . htmlReady($clipboard_widget_id)
                             . "');"
                ]
            ) ?>

            <?= Icon::create('accept', 'clickable')->asImg(
                '20px',
                [
                    'class' => 'middle clipboard-edit-accept invisible',
                    'onClick' => "STUDIP.Clipboard.rename(
                        {
                            'clipboard_id': '" . htmlReady($selected_clipboard_id) ."',
                            'widget_id': '" . htmlReady($clipboard_widget_id) . "'
                        });"
                ]
            ) ?>

            <?= Icon::create('decline', 'clickable')->asImg(
                '20px',
                [
                    'class' => 'middle clipboard-edit-cancel invisible',
                    'onClick' => "STUDIP.Clipboard.toggleEditButtons('"
                             . htmlReady($clipboard_widget_id)
                             . "');"
                ]
            ) ?>

            <?= Icon::create('trash', 'clickable')->asImg(
                '20px',
                [
                    'class' => 'middle clipboard-remove-button'
                           . (
                               $clipboards ? '' : ' invisible'
                           )
                ]
            ) ?>
        <? endif ?>
    </div>
    <div class="clipboard-area-container">
        <? if ($clipboards): ?>
            <? foreach ($clipboards as $clipboard): ?>
                <table id="Clipboard_<?= htmlReady($clipboard->id) ?>"
                       class="clipboard-area <?= $clipboard->id != $selected_clipboard_id
                                               ? 'invisible'
                                               : '' ?>"
                       data-id="<?= htmlReady($clipboard->id) ?>">
                    <? $items = $clipboard->getContent(false) ?>
                    <? if ($items): ?>
                        <? foreach ($items as $item): ?>
                            <?
                            $checkbox_id = sprintf(
                                'item_%1$s_%2$s_%3$s',
                                $clipboard->id,
                                $item['range_type'],
                                $item['range_id']
                            )
                            ?>
                            <? if ($special_item_template): ?>
                                <?= $this->render_partial(
                                    $special_item_template,
                                    [
                                        'item' => $item,
                                        'draggable_items' => $draggable_items,
                                        'readonly' => $readonly,
                                        'checkbox_id' => $checkbox_id
                                    ]
                                ) ?>
                            <? else: ?>
                                <tr class="clipboard-item <?= $draggable_items
                                                            ? 'draggable'
                                                            : '' ?>"
                                    data-range_id="<?= htmlReady($item['range_id']) ?>">
                                    <td class="item-name">
                                        <input type="checkbox"
                                               name="selected_clipboard_items[]"
                                               title="<?= _('Diesen Eintrag auswählen.') ?>"
                                               value="<?= htmlReady($item['id']) ?>"
                                               id="<?= htmlReady($checkbox_id) ?>"
                                               <?= in_array($item['id'], $selected_clipboard_items)
                                                 ? 'checked="checked"'
                                                 : (!$selected_clipboard_items
                                                  ? 'checked="checked"'
                                                  : '') ?>
                                               class="studip-checkbox">
                                        <label for="<?= htmlReady($checkbox_id) ?>"><?= htmlReady($item['name']) ?></label>
                                    </td>
                                    <? if (!$readonly): ?>
                                        <td class="item-actions">
                                        <?= Icon::create('trash', 'clickable')->asImg(
                                            '16px',
                                            [
                                                'class' => 'text-bottom clipboard-item-remove-button'
                                            ]
                                        ) ?>
                                        </td>
                                    <? endif ?>
                                </tr>
                            <? endif ?>
                        <? endforeach ?>
                    <? endif ?>
                    <tr class="empty-clipboard-message <?= $items ? 'invisible' : '' ?>">
                        <td>
                        <?= htmlReady($empty_clipboard_string) ?>
                        </td>
                    </tr>
                    <? if ($special_item_template): ?>
                        <?= $this->render_partial(
                            $special_item_template,
                            [
                                'item' => null,
                                'draggable_items' => $draggable_items
                            ]
                        ) ?>
                    <? else: ?>
                        <tr class="clipboard-item <?= $draggable_items
                                                    ? 'draggable'
                                                    : ''
                                                  ?> clipboard-item-template invisible"
                            data-range_id="">
                            <td class="item-name">
                                <input type="checkbox"
                                       name="selected_clipboard_items[]"
                                       value=""
                                       class="studip-checkbox item-id">
                                <label></label>
                            </td>
                            <? if (!$readonly): ?>
                                <td class="item-actions">
                                    <?= Icon::create('trash', 'clickable')->asImg(
                                        '16px',
                                        [
                                            'class' => 'text-bottom clipboard-item-remove-button'
                                        ]
                                    ) ?>
                                </td>
                            <? endif ?>
                        </tr>
                    <? endif ?>
                </table>
            <? endforeach ?>
        <? endif ?>
        <table id="Clipboard_CLIPBOARD_ID"
               class="clipboard-area clipboard-template invisible"
               data-id="CLIPBOARD_ID">
            <tr class="empty-clipboard-message">
                <td>
                <?= htmlReady($empty_clipboard_string) ?>
                </td>
            </tr>
            <? if ($special_item_template): ?>
                <?= $this->render_partial(
                    $special_item_template,
                    [
                        'item' => null,
                        'draggable_items' => $draggable_items
                    ]
                ) ?>
            <? else: ?>
                <tr class="clipboard-item <?= $draggable_items
                                            ? 'draggable'
                                            : ''
                                          ?> clipboard-item-template invisible"
                    data-range_id="">
                    <td>
                        <input type="checkbox"
                               name="clipboard_selected_items[]"
                               value=""
                               class="studip-checkbox">
                        <label></label>
                    </td>
                    <td>
                        <?= Icon::create('trash', 'clickable')->asImg(
                            '16px',
                            [
                                'class' => 'text-bottom clipboard-item-remove-button'
                            ]
                        ) ?>
                    </td>
                </tr>
            <? endif ?>
        </table>
    </div>
    <? if ($readonly): ?>
        <?= \Studip\Button::create(
            $apply_button_title,
            'clipboard_update_session_special_action',
            [
                'class' => 'apply-button'
            ]
        ) ?>
    <? endif ?>
</form>
