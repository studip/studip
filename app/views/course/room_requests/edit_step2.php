<? if ($available_properties): ?>
    <h2><?= _('Wünschbare Eigenschaften') ?></h2>
    <ul class="default">
        <? foreach($available_properties as $property): ?>
            <li>
                <?= $property->toHtmlInput(
                    $selected_properties[$property->name],
                    'selected_properties[' . htmlReady($property->name) . ']',
                    true,
                    'undecorated',
                    false
                ) ?>
            </li>
        <? endforeach ?>
    </ul>
<? elseif ($category_id) :  ?>
    <?= MessageBox::info(
        _('Es sind keine wünschbaren Eigenschaften vorhanden.')
    ) ?>
<? endif ?>

