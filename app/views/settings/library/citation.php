<? if ($available_citation_styles) : ?>
    <form class="default" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="course_id" value="<?= htmlReady($course->id) ?>">
        <input type="hidden" name="user_id" value="<?= htmlReady($user->id) ?>">
        <fieldset>
            <label>
                <?= _('Zitationsstil') ?>
            </label>
            <select name="citation_style">
                <option value="" <?= !$selected_citation_style ? 'selected="selected"' : '' ?>>
                    <?= _('Bitte wählen') ?>
                </option>
                <? foreach ($available_citation_styles as $style) : ?>
                    <option value="<?= htmlReady($style) ?>"
                            <?= $style == $selected_citation_style ? 'selected="selected"' : '' ?>>
                        <?= htmlReady($style) ?>
                    </option>
                <? endforeach ?>
            </select>
        </fieldset>
        <?= \Studip\Button::create(_('Speichern'), 'save') ?>
    </form>
<? endif ?>
