<?
# Lifter010: TODO
?>
<h3><?=_("Liste der Zugriffsregeln")?></h3>
<form action="<?=$controller->url_for('admin/webservice_access/update#edit')?>" method="post" class="default">
<?=CSRFProtection::tokenTag()?>
<table class="default">
<tr>
  <th style="width: 30%;">
    <?= _('API-Key') ?>
  </th>
  <th style="width: 30%;">
    <?= _('Methode') ?>
  </th>
  <th style="width: 30%;">
    <?= _('IP Bereich') ?>
  </th>
  <th style="width: 5%;">
    <?= _('Typ') ?>
  </th>
  <th style="width: 5%;">
    <?= _('Aktion') ?>
  </th>
</tr>
<? foreach ($ws_rules as $rule): ?>
  <tr class="<?= TextHelper::cycle('table_row_even', 'table_row_odd') ?>">
    <? if (isset($edit) && $edit == $rule->id) :?>
        <td>
            <a name="edit"></a>
            <input name="ws_rule_id" type="hidden" value="<?=$rule->id?>">
            <input name="ws_rule_api_key" style="width:90%" type="text" required value="<?= htmlReady($rule->api_key) ?>">
        </td>
        <td>
            <input name="ws_rule_method" style="width:90%" type="text" value="<?= htmlReady($rule->method) ?>">
        </td>
        <td>
            <input name="ws_rule_ip_range" style="width:90%" type="text" value="<?= htmlReady($rule->ip_range) ?>">
        </td>
        <td>
            <select name="ws_rule_type">
            <option <?=($rule->type == 'allow' ? 'selected' : '') ?>>allow</option>
            <option <?=($rule->type == 'deny' ? 'selected' : '') ?>>deny</option>
            </select>
        </td>
        <td>
        <?= Icon::create('accept', 'accept', ['title' => _('Änderungen speichern')])->asInput(["type" => "image", "class" => "middle", "name" => "ok"]) ?>
        <?= Icon::create('decline', 'attention', ['title' => _('Abbrechen')])->asInput(['type' => "image", 'class' => "middle", 'name' => "cancel"]) ?>
        </td>
    <? else : ?>
        <td>
            <?= htmlReady($rule->api_key) ?>
        </td>
        <td>
            <?= htmlReady($rule->method) ?>
        </td>
        <td>
            <?= htmlReady($rule->ip_range) ?>
        </td>
        <td>
            <?= htmlReady($rule->type) ?>
        </td>
        <td>
          <a href="<?= $controller->url_for('admin/webservice_access/edit/'.$rule->id.'#edit') ?>">
            <?= Icon::create('edit', 'clickable', ['title' => _('bearbeiten')])->asImg() ?>
          </a>
          <a href="<?= $controller->url_for('admin/webservice_access/delete/'.$rule->id) ?>">
              <?= Icon::create('trash', 'clickable', ['title' => _('löschen')])->asImg() ?>
          </a>
        </td>
    <? endif;?>
  </tr>
<? endforeach ?>
</table>
</form>
<?
$sidebar = Sidebar::Get();
$sidebar->setImage('sidebar/admin-sidebar.png');
$sidebar->setTitle(_('Webservices'));

$actions = new ActionsWidget();
$actions->addLink(_('Regeln testen'),$controller->url_for('admin/webservice_access/test'), Icon::create('add', 'clickable'));
$actions->addLink(_('Neue Zugriffsregel anlegen'),$controller->url_for('admin/webservice_access/new'), Icon::create('add', 'clickable'));

$sidebar->addWidget($actions);
