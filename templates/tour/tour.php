<? use Studip\Button, Studip\LinkButton; ?>
<div id="tour_controls" style="display: none">
    <div id="tour_title"></div>
    <div id="tour_interactive_text" style="display:none; width:360px">
        <?= _('Die Tour wird fortgesetzt, wenn Sie die beschriebene Aktion ausgeführt haben.') ?>
    </div>
    <div id="tour_buttons">
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td style="text-align: center">
                    <div id="tour_editor" style="display: none">
                        <?= LinkButton::create(_('Neuer Schritt'), '', ['id' => 'tour_new_step', 'data-dialog' => 'size=auto;reload-on-close']) ?>
                        <?= LinkButton::create(_('Schritt bearbeiten'), '', ['id' => 'tour_edit', 'data-dialog' => 'size=auto;reload-on-close']) ?>
                        <?= Button::create(_('Schritt löschen'), 'tour_delete_step', ['id' => 'tour_delete_step']) ?>
                        <?= LinkButton::create(_('Seitenwechsel'), '', ['id' => 'tour_new_page', 'data-dialog' => 'size=auto']) ?>
                        <br>
                        <?= Button::create(_('Klick setzen (weiter)'), 'tour_select_action_next', ['id' => 'tour_select_action_next']) ?>
                        <?= Button::create(_('Klick setzen (zurück)'), 'tour_select_action_prev', ['id' => 'tour_select_action_prev']) ?>
                        <?= Button::create(_('Position wählen'), 'tour_select_css', ['id' => 'tour_select_css']) ?>
                        <?= Button::create(_('Keine Position'), 'tour_no_css', ['id' => 'tour_no_css']) ?>
                    </div>
                </td>
                <td width="120" style="text-align: center">
                    <?= Button::create(_('Zurück'), 'tour_prev', ['id' => 'tour_prev', 'style' => 'display:none']) ?>
                    <?= Button::create(_('Tour neu beginnen'), 'tour_reset', ['id' => 'tour_reset', 'style' => 'display:none']) ?>
                </td>
                <td width="120" style="text-align: center">
                    <?= Button::create(_('Weiter'), 'tour_next', ['id' => 'tour_next']) ?>
                    <?= Button::create(_('Tour fortsetzen'), 'tour_proceed', ['id' => 'tour_proceed', 'style' => 'display:none']) ?>
                </td>
                <td width="120" style="text-align: center">
                    <?= Button::createCancel(_('Abbrechen'), 'tour_cancel', ['id' => 'tour_cancel', 'style' => 'display:none']) ?>
                    <?= Button::createCancel(_('Beenden'), 'tour_end', ['id' => 'tour_end']) ?>
                </td>
            </tr>
        </table>
    </div>
</div>
<div id="tour_tip" class="tour_tip" style="display: none">
    <div id="tour_tip_title"></div>
    <div id="tour_tip_content"></div>
</div>
<div id="tour_tip_interactive" class="tour_tip" style="display: none">
    <div id="tour_tip_title"></div>
    <div id="tour_tip_content"></div>
</div>