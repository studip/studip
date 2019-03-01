<table class="default">
    <colgroup>
        <col width="4%">
        <col width="36%">
        <col width="60%">
    </colgroup>
    <tbody style="vertical-align: top;">
        <tr>
            <td>&nbsp;</td>
            <td>
                <b><?= _('Name:') ?></b><br>
                <?= htmlReady($this->resObject->getName()) ?>
                (<?= htmlReady($this->resObject->getCategoryName()) ?: _('Hierachieebene') ?>)
            </td>
            <td>
                <b><?= _('verantwortlich:') ?></b><br>
            <?  if (Request::option('view_mode') == 'no_nav'): ?>
                <?= htmlReady($this->resObject->getOwnerName(TRUE)) ?>
            <? else: ?>
                <a href="<?= $this->resObject->getOwnerLink() ?>">
                    <?= htmlReady($this->resObject->getOwnerName(TRUE)) ?>
                </a>
            <? endif; ?>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="2">
                <b><?= _('Beschreibung:') ?></b><br>
                <?= formatReady($this->resObject->getDescription()) ?>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        <? if (!$children): ?>
            <td colspan="2">
        <? else: ?>
            <td>
        <? endif; ?>
                <b><?= _('Einordnung:') ?></b><br>
                <?= ResourcesBrowse::getHistory($resObject->getId()) ?>
            </td>
        <? if ($children): ?>
            <td>
                <b><?= _('Untergeordnete Objekte:') ?></b><br>
                <? $list->showListObjects($this->resObject->getId()) ?>
            </td>
        <? endif; ?>
        </tr>
<? if ($this->resObject->getCategoryId()): ?>
        <tr>
            <td>&nbsp;</td>
            <td colspan="2">
                <b><?= _('Eigenschaften:') ?></b>
            </td>
        </tr>
    <? foreach ($properties as $property): ?>
        <tr>
            <td>&nbsp;</td>
            <td>
                &nbsp; &nbsp;
                &bull;
                <?= htmlReady($property['name']); ?>
            </td>
            <td>
            <? if ($property['type'] == 'bool'): ?>
                <?= $property['state'] ? htmlReady($property['options']) : '-' ?>
            <? elseif (in_array($property['type'], words('num text'))): ?>
                <?= htmlReady($property['state']) ?: '-' ?>
            <? elseif ($property['type'] == 'select'): ?>
                <?= in_array($property['state'], explode(';', $property['options'])) ? htmlReady($property['state']) : '-' ?>
            <? endif; ?>
            </td>
        </tr>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
</table>


<?
$sidebar = Sidebar::Get();
$sidebar->setTitle(htmlReady($resObject->getName()));
$action = new ActionsWidget();
$action->addLink(_('Ressourcensuche'), URLHelper::getURL('resources.php?view=search&quick_view_mode=' . $view_mode));

$sidebar->addWidget($action);
?>
