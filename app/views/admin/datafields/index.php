<?
# Lifter010: TODO
?>
<? if (isset($flash['delete'])): ?>
    <?= createQuestion(sprintf(_('Wollen Sie das Datenfeld "%s" wirklich löschen? Bedenken Sie bitte, dass noch Einträge dazu existieren können'), $flash['delete']['name']),
                       ['delete' => 1],
                       ['back' => 1],
                       $controller->url_for('admin/datafields/delete'.'/' . $flash['delete']['datafield_id'])); ?>
<? endif; ?>

<!-- Alle Datenfelder  -->
<table class="collapsable default">
    <colgroup>
        <col>
        <col width="20%">
        <col width="10%">
        <col width="10%">
        <col width="10%">
        <col width="10%">
        <col width="10%">
        <col width="6%">
        <col width="6%">
        <col width="2%">
        <col width="48px">
    </colgroup>
    <caption>
        <?= _('Verwaltung von generischen Datenfeldern') ?>
    </caption>
    <thead style="vertical-align: bottom">
        <tr>
            <th rowspan="2"><?= _('Name') ?></th>
            <th rowspan="2"><?= _('Feldtyp') ?></th>
            <th rowspan="2" style="word-wrap: nowrap">
                <?= _('Typ') ?>
                <?= tooltipIcon(_('Veranstaltungskategorie, Einrichtungstyp, Sprache bzw. Nutzerstatus')) ?>
            </th>
            <th rowspan="2">
                <?= _('Institution') ?>
            </th>
            <th colspan="2" style="text-align: center">
                <?= _('benötigter Status') ?>
            </th>
            <th colspan="2" rowspan="2"></th>
            <th rowspan="2"><?= _('Position') ?></th>
            <th rowspan="2">
                <abbr title="<?= _('Einträge') ?>">#</abbr>
            </th>
            <th rowspan="2" class="actions"></th>
        </tr>
        <tr>
            <th style="white-space: nowrap">
                <?= _('Änderbar') ?>
                <?= tooltipIcon(_('Gibt den Status an, ab dem das Datenfeld änderbar ist')) ?>
            </th>
            <th style="white-space: nowrap">
                <?= _('Öffentlich') ?>
                <?= tooltipIcon(_('Gibt den Status an, ab dem das Datenfeld für andere sichtbar ist')) ?>
            </th>
        </tr>
    </thead>
<? foreach ($datafields_list as $key => $data): ?>
    <tbody class="<? if ($current_class !== $key && !$class_filter) echo 'collapsed'; ?> <? if (empty($datafields_list[$key])) echo 'empty'; ?>">
        <tr class="table_header header-row">
        <? if (in_array($key, words('sem user'))): ?>
            <th class="toggle-indicator" colspan="6">
        <? else: ?>
            <th class="toggle-indicator" colspan="11">
        <? endif; ?>
            <? if (empty($datafields_list[$key])): ?>
                <?= sprintf(_('Datenfelder für %s'), $allclasses[$key]) ?>
            <? else: ?>
                <a name="<?= $key ?>" class="toggler" href="<?= $controller->url_for('admin/datafields/index/' . $key) ?>">
                    <?= sprintf(_('Datenfelder für %s'), $allclasses[$key]) ?>
                </a>
            <? endif; ?>
            </th>
        <? if ($key === 'sem'): ?>
            <th><?= _('Pflichtfeld') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <th colspan="3"></th>
        <? elseif ($key === 'user'): ?>
            <th style="white-space: nowrap;">
                <?= _('System') ?>
                <?= tooltipIcon(_('Für die Person nur sichtbar, wenn der Status zum Bearbeiten '
                                . ' oder für die Sichtbarkeit ausreichend ist')) ?>
            </th>
            <th><?= _('Anmelderegel') ?></th>
            <th colspan="3"></th>
        <? endif; ?>
        </tr>
    <? foreach ($data as $input => $val): ?>
        <tr>
            <td>
                <a name="item_<?= $val->id ?>"></a>
                <?= htmlReady($val->name) ?>
            </td>
            <td>
            <? if (in_array($val->type, words('selectbox selectboxmultiple radio combo'))): ?>
                <a data-dialog="size=auto" href="<?= $controller->url_for('admin/datafields/config/'. $val->id) ?>">
                    <?= Icon::create('edit', 'clickable')->asImg(['class'=> 'text-top', 'title' => 'Einträge bearbeiten']) ?>
                </a>
            <? endif; ?>
                 <span><?= htmlReady($val->type) ?></span>
            </td>
            <td>
            <? if ($key === 'sem'): ?>
                <?= $val->object_class !== null ? htmlReady($GLOBALS['SEM_CLASS'][$val->object_class]['name']) : _('alle')?>
            <? elseif ($key == 'inst'): ?>
                <?=  $val->object_class !== null ? htmlReady($GLOBALS['INST_TYPE'][$val->object_class]['name']) : _('alle')?>
            <? elseif ($key === 'moduldeskriptor' || $key === 'modulteildeskriptor') : ?>
                <?=  $val->object_class !== null ? htmlReady(implode(', ', array_map(function ($class) {
                    return $GLOBALS['MVV_MODULTEIL_DESKRIPTOR']['SPRACHE']['values'][$class]['name'];
                }, explode(',', $val->object_class)))) : _('alle')?>
            <? else: ?>
                <?= $val->object_class !== null ? DataField::getReadableUserClass($val->object_class) : _('alle')?>
            <? endif; ?>
            </td>
            <td>
                <? if ($val->institution) : ?>
                    <?= htmlReady($val->institution->name)?>
                <? endif; ?>
            </td>
            <td><?= $val->edit_perms ?></td>
            <td><?= $val->view_perms ?></td>
        <? if ($key === 'user'): ?>
            <td>
            <? if ($val->system): ?>
                <?= Icon::create('checkbox-checked', 'inactive', ['title' => _('Ja')])->asImg() ?>
            <? else: ?>
                <?= Icon::create('checkbox-unchecked', 'inactive', ['title' => _('Nein')])->asImg() ?>
            <? endif; ?>
            </td>
            <td>
            <? if ($val->is_userfilter): ?>
                <?= Icon::create('checkbox-checked', 'inactive', ['title' => _('Ja')])->asImg() ?>
            <? else: ?>
                <?= Icon::create('checkbox-unchecked', 'inactive', ['title' => _('Nein')])->asImg() ?>
            <? endif; ?>
            </td>
        <? elseif ($key === 'sem'): ?>
            <td>
            <? if ($val->is_required): ?>
                <?= Icon::create('checkbox-checked', 'inactive', ['title' => _('Ja')])->asImg() ?>
            <? else: ?>
                <?= Icon::create('checkbox-unchecked', 'inactive', ['title' => _('Nein')])->asImg() ?>
            <? endif; ?>
            </td>
            <td>
            <? if (trim($val->description)): ?>
                <?= Icon::create('checkbox-checked', 'inactive', ['title' => _('Ja')])->asImg() ?>
            <? else: ?>
                <?= Icon::create('checkbox-unchecked', 'inactive', ['title' => _('Nein')])->asImg() ?>
            <? endif; ?>
            </td>
        <? else: ?>
            <td colspan="2"></td>
        <? endif; ?>
            <td><?= $val->priority ?></td>
            <td><?= count($val) ?></td>
            <td class="actions">
                <a href="<?=$controller->url_for('admin/datafields/edit/' . $val->id)?>" data-dialog>
                    <?= Icon::create('edit', 'clickable', ['title' => 'Datenfeld ändern'])->asImg() ?>
                </a>
                <a href="<?=$controller->url_for('admin/datafields/delete/' . $val->id)?>">
                    <?= Icon::create('trash', 'clickable', ['title' => 'Datenfeld löschen'])->asImg() ?>
                </a>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
</table>
