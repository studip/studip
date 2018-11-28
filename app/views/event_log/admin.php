<?
# Lifter010: TODO
?>
<? if (isset($error_msg)): ?>
  <?= MessageBox::error($error_msg) ?>
<? endif ?>

<h3><?= _('Konfiguration der Logging-Funktionen') ?></h3>

<p><?= _('Sie können hier einen Teil der Logging-Funktionen direkt verändern.') ?></p>

<form action="<?= $controller->url_for('event_log/save/'.urlencode($edit_id)) ?>" method="POST" class="default">
<?= CSRFProtection::tokenTag() ?>

  <table class="default">
    <tr>
      <th>
        <?= _('Name') ?>
      </th>
      <th>
        <?= _('Beschreibung') ?>
      </th>
      <th>
        <?= _('Template') ?>
      </th>
      <th>
        <?= _('Anzahl') ?>
      </th>
      <th>
        <?= _('Aktiv?') ?>
      </th>
      <th>
        <?= _('Ablaufzeit') ?>
      </th>
      <th>
      </th>
    </tr>

    <? foreach ($log_actions as $log_action): ?>
      <tr class="<?= TextHelper::cycle('table_row_even', 'table_row_odd') ?>">
        <td style="font-size: smaller;">
          <?= htmlReady($log_action['name']) ?>
        </td>
        <? if ($edit_id === $log_action['action_id']): ?>
          <td style="font-size: smaller;">
            <a name="edit"></a>
            <input type="text" style="width: 100%;" name="description" value="<?= htmlReady($log_action['description']) ?>">
          </td>
          <td style="font-size: smaller;">
            <input type="text" style="width: 100%;" name="info_template" value="<?= htmlReady($log_action['info_template']) ?>">
          </td>
          <td style="font-size: smaller;">
            <?= $log_action['log_count'] ?>
          </td>
          <td style="font-size: smaller;">
            <input type="checkbox" name="active" value="1" <?= $log_action['active'] ? 'checked' : '' ?>>
          </td>
          <td style="font-size: smaller; white-space: nowrap;">
            <input type="text" style="width: 4ex;" name="expires"
                   value="<?= $log_action['expires'] / 86400 ?>"
                   title="<?= _('0 = keine Ablaufzeit') ?>"> <?= _('Tage') ?>
          </td>
          <td style="font-size: smaller;">
            <?= Icon::create('accept', 'clickable', ['title' => _('Speichern')])->asInput(["type" => "image", "class" => "middle", "name" => "save"]) ?>
          </td>
        <? else: ?>
          <td style="font-size: smaller;">
            <?= htmlReady($log_action['description']) ?>
          </td>
          <td style="font-size: smaller;">
            <?= htmlReady($log_action['info_template']) ?>
          </td>
          <td style="font-size: smaller;">
            <?= $log_action['log_count'] ?>
          </td>
          <td style="font-size: smaller;">
            <? if ($log_action['active']): ?>
              <?= Icon::create('accept', 'accept')->asImg() ?>
            <? else: ?>
              <?= Icon::create('decline', 'attention')->asImg() ?>
            <? endif ?>
          </td>
          <td style="font-size: smaller; white-space: nowrap;">
            <? if ($log_action['expires'] > 0): ?>
              <?= $log_action['expires'] / 86400 ?> <?= _('Tage') ?>
            <? else: ?>
              <?= Icon::create('decline', 'attention')->asImg() ?>
            <? endif ?>
          </td>
          <td style="font-size: smaller;">
            <a href="<?= $controller->url_for('event_log/edit/'.$log_action['action_id']) ?>#edit">
              <?= Icon::create('edit', 'clickable')->asImg() ?>
            </a>
          </td>
        <? endif ?>
      </tr>
    <? endforeach ?>
  </table>

</form>
