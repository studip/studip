<?
# Lifter010: TEST
use Studip\Button, Studip\LinkButton;
?>
<form method="post" action="<?= URLHelper::getLink('?change_object_properties='. $resObject->getId()) ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="view" value="edit_object_properties">

    <fieldset>
        <legend><?= htmlReady($resObject->getName()) ?></legend>

        <label>
            <?= _('Name:') ?><br>
            <input type="text" name="change_name" value="<?= htmlReady($resObject->getName()) ?>" maxlength="255">
        </label>

        <label>
            <?= _('Typ des Objektes:') ?><br>
        <? if (!$resObject->isAssigned()): ?>
            <select name="change_category_id">
            <? if (!$resObject->getCategoryId()) : ?>
                <option value=""><?= _('nicht zugeordnet') ?></option>
            <? endif; ?>
            <? foreach ($EditResourceData->selectCategories(allowCreateRooms()) as $category_id => $name): ?>
                <option value="<?= $category_id ?>"
                        <? if ($category_id == $resObject->getCategoryId()) echo 'selected'; ?>>
                    <?= htmlReady($name) ?>
                </option>
            <? endforeach; ?>
            </select>
            <?= Button::create(_('Zuweisen'), 'assign')?>
        <? else : ?>
            <b><?=  htmlReady($resObject->getCategoryName()) ?></b>
            <input type="hidden" name="change_category_id" value="<?= $resObject->getCategoryId() ?>">
        <? endif; ?>
        </label>

        <?= _('verantwortlich:') ?><br>
        <a href="<?= $resObject->getOwnerLink()?>"><?= htmlReady($resObject->getOwnerName(true)) ?></a>

        <label>
            <?= _('Beschreibung:') ?><br>
            <textarea name="change_description" rows="3" cols="60"><?= htmlReady($resObject->getDescription()) ?></textarea>
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _('Eigenschaften') ?></legend>

    <? if ($resObject->isRoom() && get_config('RESOURCES_ENABLE_ORGA_CLASSIFY')): ?>
        <label>
            <?= _('organisatorische Einordnung:') ?>
            <? if ($resObject->getInstitutId()) : ?>
                <a href="<?= $resObject->getOrgaLink() ?>">
                    <?= htmlReady($resObject->getOrgaName(TRUE)) ?>
                </a>
            <? else : ?>
                <?= _('keine Zuordnung') ?>
            <? endif ?>

            <? if ($ObjectPerms->havePerm('admin')) : ?>
                <select name="change_institut_id" class="nested-select">
                    <option value="" class="is-placeholder">
                        &lt;<?= _('keine Zuordnung') ?>&gt;
                    </option>
                <? foreach ($EditResourceData->selectFaculties() as $institute_id => $faculty): ?>
                    <option class="nested-item-header" value="<?= $institute_id ?>"
                            <? if ($institute_id == $resObject->getInstitutId()) echo 'selected'; ?>>
                        <?= htmlReady(my_substr($faculty['Name'], 0, 50)) ?>
                    </option>
                    <? foreach ($faculty['institutes'] as $institute_id => $name): ?>
                        <option class="nested-item" value="<?= $institute_id ?>"
                                <? if ($institute_id == $resObject->getInstitutId()) echo 'selected'; ?>>
                            <?= htmlReady(my_substr($name, 0, 50)) ?>
                        </option>
                    <? endforeach; ?>
                <? endforeach; ?>
                </select>
            <? else : ?>
                <?= MessageBox::info(_('Sie können die Einordnung in die Orga-Struktur nicht ändern.')) ?>
            <? endif; ?>
        </label>
    <? endif; ?>

<? if ($resObject->getCategoryId()) : ?>
    <? foreach ($EditResourceData->selectProperties() as $property): ?>
    	<? $protected_property = getGlobalPerms($user->id) != 'admin' && $property['protected']; ?>

        <label for="property_<?= $property['property_id'] ?>">
            <input type="hidden" name="change_property_val[]" value="_id_<?= $property['property_id'] ?>">

            <? if ($property['type'] == 'bool'): ?>
                <input id="property_<?= $property['property_id'] ?>" type="checkbox"
                       name="change_property_val[]" <? if ($property['state']) echo 'checked'; ?><? if ($protected_property) echo ' disabled '; ?>>
                <?= htmlReady($property['name']); ?> <?= htmlReady($property['options']) ?>
            <? elseif ($property['type'] == 'num' && $property['system'] == 2): ?>
                <?= htmlReady($property['name']); ?>
                <input id="property_<?= $property['property_id'] ?>" type="text"
                       name="change_property_val[]" value="<?= htmlReady($property['state']) ?>"
                       size="5" maxlength="10" <? if ($protected_property) echo ' disabled '; ?>>
            <? elseif ($property['type'] == 'num'): ?>
                <?= htmlReady($property['name']); ?>
                <input id="property_<?= $property['property_id'] ?>" type="text"
                       name="change_property_val[]" value="<?= htmlReady($property['state']) ?>"
                       size="30" maxlength="255" <? if ($protected_property) echo ' disabled '; ?>>
            <? elseif ($property['type'] == 'text'): ?>
                <?= htmlReady($property['name']); ?>
                <textarea id="property_<?= $property['property_id'] ?>" name="change_property_val[]"
                          cols="30" rows="2" <? if ($protected_property) echo ' disabled '; ?>><?= htmlReady($property['state']) ?></textarea>
            <? elseif ($property['type'] == 'select'): ?>
                <?= htmlReady($property['name']); ?>
                <select id="property_<?= $property['property_id'] ?>" name="change_property_val[]" <? if ($protected_property) echo ' disabled '; ?>>
                <? foreach (explode(';', $property['options']) as $option): ?>
                    <option value="<?= $option ?>" <? if ($property['state'] == $option) echo 'selected'; ?>>
                        <?= htmlReady($option) ?>
                    </option>
                <? endforeach; ?>
                </select>
            <? endif; ?>

        </label>
    <? endforeach; ?>
<? else : ?>
    <span style="color: red">
        <?= _('Das Objekt wurde noch keinem Typ zugewiesen. Um Eigenschaften bearbeiten zu können, müssen Sie vorher einen Typ festlegen!') ?>
    </span>
<? endif; ?>
    <? if ($resObject->getCategoryId() && getGlobalPerms($user->id) == 'admin') : ?>

            <label for="change_multiple_assign">
                <input type="checkbox" id="change_multiple_assign" name="change_multiple_assign" value="1"
                   <? if ($resObject->getMultipleAssign()) echo 'checked'; ?>>
               <b><?= _('gleichzeitige Belegung') ?></b>
               <?= tooltipIcon(_('Die Ressource darf mehrfach zur gleichen Zeit belegt werden - Überschneidungschecks finden nicht statt!')) ?>
           </label>

        <? if ($resObject->isRoom()): ?>
            <label for="change_requestable">
                <input type="checkbox" id="change_requestable" name="change_requestable" value="1"
                    <? if ($resObject->getRequestable()) echo 'checked'; ?>>

                <b><?= _('wünschbar') ?></b>
                <?= tooltipIcon(_('Legt fest ob ein Raum wünschbar ist')) ?>
           </label>
        <? endif; ?>
    <? endif; ?>
    </fieldset>
    <footer>
        <?= Button::create(_('Übernehmen'))?>
        <? if ($resObject->isUnchanged()) : ?>
            <?= LinkButton::createCancel(_('Abbrechen'), URLHelper::getLink('?cancel_edit='. $resObject->id))?>
        <? endif; ?>
    </footer>
</form>

<?
$sidebar = Sidebar::Get();
$sidebar->setTitle(htmlReady($resObject->getName()));
$action = new ActionsWidget();
$action->addLink(_('Ressourcensuche'), URLHelper::getURL('resources.php?view=search&quick_view_mode=' . $view_mode));

$sidebar->addWidget($action);
?>
