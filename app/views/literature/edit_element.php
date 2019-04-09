<section id="lit_edit_element">
    <?= $form->getFormStart(URLHelper::getLink('dispatch.php/literature/edit_element?_catalog_id=' . $catalog_id),
        ['class' => 'default', 'data-dialog' => '']) ?>
    <fieldset>
        <legend><?= ($element->isNewEntry()) ? _("Neuer Eintrag") : _('Eintrag') ?></legend>


    <? if (!$element->isNewEntry()) : ?>
        <p>
            <?= sprintf(_('Anzahl an Referenzen für diesen Eintrag: %s'), (int)$element->reference_count) ?><br>
            <b><?= ($element->getValue('user_id') == 'studip') ? _('Systemeintrag:') : _('Eingetragen von:') ?></b><br>
            <?= ($element->getValue('user_id') == 'studip') ? _('Dies ist ein vom System generierter Eintrag.') : get_fullname($element->getValue("user_id"), 'full', true) ?>
            <br>
            <b><?= _('Letzte Änderung') ?>:</b><br>
            <?= strftime('%d.%m.%Y', $element->getValue('chdate')) ?>
        </p>
    <? endif ?>
    <p>
        <small>
            <?= sprintf(_('Alle mit einem Sternchen %s markierten Felder müssen ausgefüllt werden.'),
                '<span style="font-size:1.5em;color:red;font-weigth:bold;">*</span>') ?>
        </small>
    </p>

    <? foreach ($element->fields as $field_name => $field_detail) : ?>
        <? if ($field_detail['caption']) : ?>
            <label>
                <span <?= ($field_detail['mandatory']) ? 'class="required"' : '' ?>>
                <?= $field_detail['caption'] ?></span>
                <?= $form->getFormFieldInfo($field_name) ?>


            <?
            $element_attributes = $attributes[$form->form_fields[$field_name]['type']];
            if (!$element->isChangeable()) {
                $attributes['readonly'] = 'readonly';
                $attributes['disabled'] = 'disabled';
            }
            ?>
            <?= $form->getFormField($field_name, $element_attributes) ?>
            </label>

            <? if ($field_name == 'lit_plugin') : ?>
                <p>
                    <?= (($link = $element->getValue('external_link'))) ? formatReady('=) [Link zum Katalog]' . $link) : _('(Kein Link zum Katalog vorhanden.)') ?>
                </p>
            <? endif ?>
        <? endif ?>
    <? endforeach ?>
    </fieldset>


    <footer class="submit_wrapper" data-dialog-button="1">
        <?= CSRFProtection::tokenTag() ?>
        <? if ($element->isChangeable()) : ?>
            <?= $form->getFormButton('send') . ($element->isNewEntry() ? '' : $form->getFormButton('delete')) ?>
        <? elseif ($catalog_id != 'new_entry') : ?>
            <?= Studip\LinkButton::create(_('Kopie erstellen'), URLHelper::getURL('dispatch.php/literature/edit_element?cmd=clone_entry&_catalog_id=' . $catalog_id),
                ['title' => _('Eine Kopie dieses Eintrages anlegen'), 'data-dialog' => '']) ?>
        <? endif ?>
        <? if ($catalog_id != "new_entry") : ?>
            <?= Assets::img('blank.gif', ['size' => '15@28']) ?>
            <?= Studip\LinkButton::create(_('Verfügbarkeit'), URLHelper::getURL('dispatch.php/literature/edit_element?cmd=check_entry&_catalog_id=' . $catalog_id),
                ['title' => _('Verfügbarkeit überprüfen'), 'data-dialog' => '']) ?>
        <? endif ?>
        <? if ($catalog_id != "new_entry" && !$clipboard->isInClipboard($catalog_id)) : ?>
            <?= Assets::img('blank.gif', ['size' => '15@28']) ?>
            <?= Studip\LinkButton::create(_('Merkliste'), URLHelper::getURL('dispatch.php/literature/edit_element?cmd=in_clipboard&_catalog_id=' . $catalog_id),
                ['title' => _('Eintrag in Merkliste aufnehmen'), 'data-dialog' => '']) ?>
        <? endif ?>
    </footer>

<?= $form->getFormEnd(); ?>

<? if ($reload && $return_range) : ?>
    <script>
        jQuery('#lit_edit_element').parent().dialog({
            beforeClose: function () {
                window.location.href = "<?= URLHelper::getURL('dispatch.php/literature/edit_list', ['_range_id' => $return_range, 'return_range' => null])?>";
            }
        });
    </script>
<? endif;
