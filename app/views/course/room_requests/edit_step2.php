<? if ($available_properties): ?>
    <fieldset>
        <legend><?= _('Wünschbare Eigenschaften') ?></legend>
        <? foreach ($available_properties as $property): ?>
            <?
            switch ($property->type) {
                case'bool':
                    $label_html_classe = 'col-2';
                    break;
                case 'num':
                    $label_html_classe = 'col-1';
                    break;
                default:
                    $label_html_classe = '';
                    break;
            }
            ?>
            <?= $property->toHtmlInput(
                $selected_properties[$property->name],
                'selected_properties[' . htmlReady($property->name) . ']',
                true,
                $label_html_classe,
                false
            ) ?>
        <? endforeach ?>
    </fieldset>
<? endif ?>

