<? if ($available_properties): ?>
    <fieldset>
        <legend><?= _('Wünschbare Eigenschaften') ?></legend>
        <? foreach ($available_properties as $property): ?>
            <?= $property->toHtmlInput(
                $selected_properties[$property->name],
                'selected_properties[' . htmlReady($property->name) . ']',
                true,
                '',
                false
            ) ?>
        <? endforeach ?>
    </fieldset>
<? elseif ($category_id) : ?>
    <?= MessageBox::info(
        _('Es sind keine wünschbaren Eigenschaften vorhanden.')
    ) ?>
<? endif ?>

