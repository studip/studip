<? use Studip\Button, Studip\LinkButton; ?>

<?
    //the avaiable object-icons for every category
    $availableIcons =  [1=>"cont_res1.gif",2=> "cont_res2.gif",3=> "cont_res3.gif", 4=>"cont_res4.gif",5=> "cont_res5.gif"];
?>

<form method="POST" action="<?= URLHelper::getLink() ?>#a" class="default">
<?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Neuen Typ anlegen') ?></legend>

        <label for="new_type">
            <?= _('Name') ?>

            <input type="text" id="new_type" name="add_type"
                   size="50" maxlength="255"
                   placeholder="&lt;<?= _('bitte geben Sie hier den Namen ein') ?>&gt;">
        </label>

        <label>
            <input type="checkbox" name="resource_is_room">
            <?= _('Ressourcen-Typ wird als Raum behandelt') ?>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Anlegen'), '_add_type') ?>
    </footer>
</form>


<form method="POST" action="<?=URLHelper::getLink('?change_categories=TRUE') ?>">
<?= CSRFProtection::tokenTag() ?>
<div style="text-align: center; margin-top: 1em;">
    <?= Button::createAccept(_('Übernehmen'), 'change_types') ?>
</div>

<table class="default zebra" style="margin: 0 1%; width: 98%;">
    <colgroup>
        <col width="4%">
        <col width="25%">
        <col width="65%">
        <col width="6%">
    </colgroup>
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th><?= _('Typ') ?></th>
            <th><?= _('zugeordnete Eigenschaften') ?></th>
            <th style="text-align: center;"><?= _('X') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($types as $type): ?>
        <tr>
            <td>
            <? if ($created_category_id == $type['category_id']): ?>
                <a name="a"></a>
            <? endif; ?>
                &nbsp;
            </td>
            <td style="vertical-align: top;">
                <input type="hidden" name="change_properties_id[]" value="<?= $type['category_id'] ?>">

                <input type="text" size="20" maxlength="255"
                       name="change_category_name[<?= $type['category_id'] ?>]"
                       value="<?= htmlReady($type['name']) ?>">
                <br>

            <? foreach ($availableIcons as $key => $val): ?>
                <label>
                    <input type="radio" value="<?= $key ?>"
                           name="change_category_iconnr[<?= $type['category_id'] ?>]"
                           <? if ($type['iconnr'] == $key) echo 'checked'; ?>>
                    <?= Assets::img($val) ?>
                </label>
            <? endforeach; ?>
            <? if ($type['is_room']): ?>
                <br><?= _('wird als <i>Raum</i> behandelt') ?>
            <? endif; ?>
                <br><?= sprintf(_('wird von <b>%s</b> Objekten verwendet'), $type['depRes']) ?>
            <? if ($type['system']): ?>
                <br><?= _('(systemobjekt)') ?>
            <? endif; ?>
            </td>
            <td style="vetical-align: top;">

                <table class="default zebra-hover" style="width: 90%">
                    <colgroup>
                        <col width="32%">
                        <col width="30%">
                        <col width="12%">
                        <col width="10%">
                        <col width="6%">
                    </colgroup>
                    <tbody>
                <? $tmp_resvis = [];
                   foreach ($type['properties'] as $property):
                        //schon zugewiesene Properties merken
                        $tmp_resvis[] = $property['property_id'];
                ?>
                        <tr>
                            <td><?= htmlReady($property['name']) ?></td>
                            <td style="white-space: nowrap;">
                            <? if ($property['type'] == 'bool'): ?>
                                <?= _('Zustand Ja/Nein') ?>
                            <? elseif ($property['type'] == 'text'): ?>
                                <?= _('mehrzeiliges Textfeld') ?>
                            <? elseif ($property['type'] == 'num'): ?>
                                <?= _('einzeiliges Textfeld') ?>
                            <? elseif ($property['type'] == 'select'): ?>
                                <?= _('Auswahlfeld') ?>
                            <? endif; ?>
                            </td>
                            <td>
                            <? if ($type['is_room']): ?>
                                <input type="hidden" name="requestable[]" value="_id1_<?= $type['category_id'] ?>">
                                <input type="hidden" name="requestable[]" value="_id2_<?= $property['property_id'] ?>">
                                <input type="checkbox" name="requestable[]" <? if ($property['requestable']) echo 'checked'; ?>>
                                <?= _('wünschbar') ?>
                            <? else: ?>
                                &nbsp;
                            <? endif; ?>
                            </td>
                            <td>
                                <input type="hidden" name="protected[]" value="_id1_<?= $type['category_id'] ?>">
                                <input type="hidden" name="protected[]" value="_id2_<?= $property['property_id'] ?>">
                                <input type="checkbox" name="protected[]" <? if ($property['protected']) echo 'checked'; ?>>
                                <?= _('geschützt') ?>
                            </td>
                            <td style="text-align: right;">
                            <? if (!$property['system']):  ?>
                                <a href="<?= URLHelper::getLink('?delete_type_property_id=' . $property['property_id']
                                                               .'&delete_type_category_id='.$property['category_id']) ?>">
                                    <?= Icon::create('trash', 'clickable', ['title' => _('Eigenschaft löschen')])->asImg(16, ["class" => 'text-top']) ?>
                                </a>
                            <? else: ?>
                                <?= Icon::create('trash+decline', 'inactive', ['title' => _('Löschen der Eigenschaft nicht möglich, Systemobjekt!')])->asImg(16, ["class" => 'text-top']) ?>
                            <? endif; ?>
                            </td>
                        </tr>
                    <? endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                        <? if (count($properties) != count($tmp_resvis)): ?>
                            <td colspan="5">
                                <select name="add_type_property_id[<?= $type['category_id'] ?>]">
                            <? //Noch nicht vergebene Properties zum Vergeben anbieten
                               foreach ($properties as $property): ?>
                                <? if (!in_array($property['property_id'], $tmp_resvis)): ?>
                                    <option value="<?= $property['property_id'] ?>">
                                        <?= htmlReady($property['name']) ?>
                                    </option>
                                <? endif; ?>
                            <? endforeach; ?>
                                </select>
                                <?= Button::create(_('Zuweisen'), 'change_category_add_property' . $type['category_id']) ?>
                            </td>
                        <? endif; ?>
                        </tr>
                    </tfoot>
                </table>

            </td>
            <td style="text-align: center; vertical-align: bottom;">
                <?= _('diesen Typ') ?><br>
            <? if ($type['depRes'] == 0 && !$type['system']): ?>
                <?= LinkButton::create(_('Löschen'), URLHelper::getURL('?delete_type=' .$type['category_id'])) ?>
            <? else: ?>
                <?= Button::create(_('Löschen'), [
                        'disabled' => 'disabled',
                        'title' => _('Dieser Typ kann nicht gelöscht werden, da er von Ressourcen verwendet wird!')]
                    ) ?>
            <? endif; ?>
                <br>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="table_footer" style="text-align: center">
                <?= Button::createAccept(_('Übernehmen'), 'change_types') ?>
            </td>
        </tr>
    </tfoot>
</table>

</form>
<br><br>
