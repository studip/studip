<? if ($defined_variables) : ?>
    <form class="default" method="post" data-dialog="size=auto;reload-on-close"
          action="<?= $form_action ?>">
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="document_type"
               value="<?= htmlReady($document_type['name']) ?>">
        <? foreach ($enriched_properties as $property) : ?>
            <label>
                <span <?= in_array($property['name'], $required_properties)
                        ? 'class="required"'
                        : '' ?>>
                    <?= htmlReady($property['display_name'][$user_language] ?: $property['name']) ?>
                </span>
                <? if ($property['type'] == 'number') : ?>
                    <input type="number"
                           value="<?= htmlReady($document_properties[$property['name']]) ?>"
                           name="document_properties[<?= htmlReady($property['name']) ?>]">
                <? else : ?>
                    <? if ($property['type'] == 'name') : ?>
                        <input type="text" placeholder="<?= _('Nachname') ?>"
                               value="<?= htmlReady($document_properties[$property['name']][0]['family']) ?>"
                               name="document_properties[<?= htmlReady($property['name']) ?>][0][family]">
                        <input type="text" placeholder="<?= _('Vorname') ?>"
                               value="<?= htmlReady($document_properties[$property['name']][0]['given']) ?>"
                               name="document_properties[<?= htmlReady($property['name']) ?>][0][given]">
                        <input type="text" placeholder="<?= _('Namenszusatz') ?>"
                               value="<?= htmlReady($document_properties[$property['name']][0]['suffix']) ?>"
                               name="document_properties[<?= htmlReady($property['name']) ?>][0][suffix]">
                        <? elseif ($property['type'] == 'date') : ?>
                        <input type="text" placeholder="<?= _('Jahr') ?>"
                               value="<?= htmlReady($document_properties[$property['name']]['date-parts'][0][0]) ?>"
                               name="document_properties[<?= htmlReady($property['name']) ?>][date-parts][0][0]">
                        <input type="text" placeholder="<?= _('Monat') ?>"
                               value="<?= htmlReady($document_properties[$property['name']]['date-parts'][0][1]) ?>"
                               name="document_properties[<?= htmlReady($property['name']) ?>][date-parts][0][1]">
                        <input type="text" placeholder="<?= _('Tag') ?>"
                               value="<?= htmlReady($document_properties[$property['name']]['date-parts'][0][2]) ?>"
                               name="document_properties[<?= htmlReady($property['name']) ?>][date-parts][0][2]">

                    <? else : ?>
                        <input type="text"
                               value="<?= htmlReady($document_properties[$property['name']]) ?>"
                               name="document_properties[<?= htmlReady($property['name']) ?>]">
                    <? endif ?>
                <? endif ?>
            </label>
        <? endforeach ?>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Speichern'), 'save') ?>
        </div>
    </form>
<? endif ?>
