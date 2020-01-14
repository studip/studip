<form class="default" action="<?= URLHelper::getLink($form_action_url) ?>"
      method="<?= htmlReady($form_method) ?>">
    <? if ($form_method == 'post'): ?>
        <?= CSRFProtection::tokenTag() ?>
    <? endif ?>
    <? foreach ($option_elements as $element): ?>
        <? if ($element['type'] == 'checkbox'): ?>
            <input type="checkbox" name="<?= htmlReady($element['name']) ?>"
                   value="<?= htmlReady($element['value']) ?>"
                   class="<?= '' /*studip-checkbox*/ ?>"
                   <?= $element['checked'] ? 'checked="checked"' : '' ?>>
            <label for="<?= htmlReady($element['name']) ?>">
                <?= htmlReady($element['label']) ?>
            </label>
        <? elseif ($element['type'] == 'radio'): ?>
                <input type="checkbox" name="<?= htmlReady($element['name']) ?>"
                       value="<?= htmlReady($element['value']) ?>"
                       <?= $element['checked'] ? 'checked="checked"' : '' ?>>
            <label for="<?= htmlReady($element['name']) ?>">
                    <?= htmlReady($element['label']) ?>
            </label>
        <? elseif ($element['type'] == 'select'): ?>
            <label>
                <?= htmlReady($element['label']) ?>
                <select name="<?= htmlReady($element['name']) ?>">
                    <? foreach ($element['options'] as $key => $name): ?>
                        <option value="<?= htmlReady($key) ?>">
                            <?= htmlReady($name) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
        <? endif ?>
    <? endforeach ?>
    <? if (!$submit_form_directly): ?>
        <?= \Studip\Button::create(_('Setzen'), 'set') ?>
    <? endif ?>
</form>
