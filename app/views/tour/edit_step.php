<? use Studip\Button, Studip\LinkButton; ?>

<div id="edit_tour_step" class="edit_tour_step">
    <? if (Request::isXhr()) : ?>
        <? foreach (PageLayout::getMessages() as $msg) : ?>
            <?= $msg ?>
        <? endforeach ?>
    <? endif ?>
    <form id="edit_tour_form" class="default"
          action="<?= $controller->url_for('tour/edit_step/' . $tour_id . '/' . $step->step . '/save') ?>"
          method="post">
        <?= CSRFProtection::tokenTag(); ?>
        <fieldset>
            <input type="hidden" name="tour_step_nr" value="<?= $step->step ?>">
            <input type="hidden" name="tour_step_editmode" value="<?= $mode ?>">
            <legend><?= sprintf(_('Schritt %s'), $step->step) ?></legend>
            <label>
                <?= _('Titel') ?>
                <input type="text" size="60" maxlength="255" name="step_title"
                       value="<?= $step ? htmlReady($step->title) : '' ?>"
                       placeholder="<?= _('Bitte geben Sie einen Titel für den Schritt an') ?>">
            </label>

            <label>
                <?= _('Inhalt') ?>
                <textarea cols="60" rows="5" name="step_tip"
                          placeholder="<?= _('Bitte geben Sie den Text für diesen Schritt ein') ?>"><?= $step ? htmlReady($step->tip) : '' ?></textarea>
            </label>

            <label>
                <span class="required"><?= _('Art') ?></span>
                <select name="step_interactive">
                    <option value="0" <? if (!$step->interactive) echo 'selected'; ?>>
                        <?= _('Geführt') ?>
                    </option>
                    <option value="1" <? if ($step->interactive) echo ' selected'; ?>>
                        <?= _('Interaktiv') ?>
                    </option>
                </select>
            </label>

            <? if ($force_route) : ?>
                <input type="hidden" name="step_route" value="<?= $force_route ?>">
                <input type="hidden" name="step_css" value="<?= $step->css_selector ?>">
            <? else : ?>
                <label for="step_route" class="caption">
                    <span class="required"><?= _('Seite') ?></span>
                    <input type="text" size="60" maxlength="255" name="step_route"
                           value="<?= $step ? htmlReady($step->route) : '' ?>"
                           placeholder="<?= _('Route für den Schritt (z.B. "dispatch.php/profile")') ?>">
                </label>
                <label>
                    <?= _('CSS-Selektor') ?>
                    <input type="text" size="60" maxlength="255" name="step_css"
                           value="<?= $step ? htmlReady($step->css_selector) : '' ?>"
                           placeholder="<?= _('Selektor, an dem der Schritt angezeigt wird') ?>"/>
                </label>

            <? endif ?>
        </fieldset>
    <? if ($step->css_selector) : ?>
        <fieldset>
            <legend><?= _('Orientierung') ?></legend>
            <div class="tour_step_orientation">
                <table>
                    <tr>
                        <td></td>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="TL" <? if ($step->orientation === 'TL') echo 'checked'; ?>>
                                <?= _('oben (links)') ?>
                            </label>
                        </td>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="T" <? if ($step->orientation === 'T') echo 'checked'; ?>>
                                <?= _('oben') ?>
                            </label>
                        </td>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="TR" <? if ($step->orientation === 'TR') echo 'checked'; ?>>
                                <?= _('oben (rechts)') ?>
                            </label>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="LT" <? if ($step->orientation === 'LT') echo 'checked'; ?>>
                                <?= _('links (oben)') ?>
                            </label>
                        </td>
                        <td colspan="3"></td>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="RT" <? if ($step->orientation === 'RT') echo 'checked'; ?>>
                                <?= _('rechts (oben)') ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="L" <? if ($step->orientation === 'L') echo 'checked'; ?>>
                                <?= _('links') ?>
                            </label>
                        </td>
                        <td colspan="3" style="text-align: center"><?= _('Selektiertes Element') ?></td>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="R" <? if ($step->orientation === 'R') echo 'checked'; ?>>
                                <?= _('rechts') ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="RB" <? if ($step->orientation === 'RB') echo 'checked'; ?>>
                                <?= _('links (unten)') ?>
                            </label>
                        </td>
                        <td colspan="3"></td>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="LB" <? if ($step->orientation === 'LB') echo 'checked'; ?>>
                                <?= _('rechts (unten)') ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="BL" <? if ($step->orientation === 'BL') echo 'checked'; ?>>
                                <?= _('unten (links)') ?>
                            </label>
                        </td>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="B" <? if ($step->orientation === 'B') echo 'checked'; ?>>
                                <?= _('unten') ?>
                            </label>
                        </td>
                        <td>
                            <label>
                                <input type="radio" name="step_orientation"
                                       value="BR" <? if ($step->orientation === 'BR') echo 'checked'; ?>>
                                <?= _('unten (rechts)') ?>
                            </label>
                        </td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </fieldset>
    <? endif ?>

        <footer data-dialog-button>
        <? if ($via_ajax): ?>
            <?= Button::createAccept(_('Speichern'), 'confirm', ['data-dialog' => '']) ?>
        <? else: ?>
            <?= Button::createAccept(_('Speichern'), 'submit') ?>
        <? endif; ?>
            <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('tour/admin_overview'), []) ?>
        </footer>
    </form>
</div>

<script>
jQuery(function ($) {
    $('input[name=step_css]').change(function () {
        if ($('input[name=step_css]').val()) {
            $('.tour_step_orientation').show();
        } else {
            $('.tour_step_orientation').hide();
        }
    });
    if (STUDIP.Tour.started) {
        $('#tour_controls, #tour_tip, #selector_overlay').hide();
    }
});
</script>
