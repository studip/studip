<?
use Studip\Button, Studip\LinkButton;
?>
<form action="<?= $controller->url_for('shared/log_event/show', $object_type, $object_id) ?>" method="post">
  <? if ($object2_type) : ?>
    <input type="hidden" name="object2_type" value="<?= htmlReady($object2_type) ?>">
    <input type="hidden" name="object2_id" value="<?= htmlReady($object2_id) ?>">
  <? endif; ?>
  <?= CSRFProtection::tokenTag() ?>
  <span style="float:left;"><h3><?= _('Anzeige der Log-Events') ?></h3></span>
  <span class="text-bottom" style="float:right; font-size: smaller;">
    <select name="format">
      <option value="compact"><?= _('Kompaktdarstellung') ?></option>
      <option value="detail" <?= ($format == 'detail') ? 'selected="selected"' : ''?>>
          <?= _('Detaildarstellung') ?>
      </option>
    </select>
    &nbsp;
    <?= Button::create(_('Anzeigen'),'Anzeigen',['data-dialog' => '']) ?>
  </span>
  <? if (isset($error_msg)): ?>
    <?= MessageBox::error($error_msg) ?>
  <? endif ?>
  <? if (isset($log_events)): ?>
    <table class="default">
      <tr>
        <th>
          <?= _('Zeit') ?>
        </th>
        <th>
          <?= _('Info') ?>
        </th>
      </tr>
      <? foreach ($log_events as $log_event): ?>
        <tr class="<?= TextHelper::cycle('table_row_even', 'table_row_odd') ?>">
          <td style="font-size: smaller; white-space: nowrap;">
            <?= date('d.m.Y H:i:s', $log_event['time']) ?>
          </td>
          <td style="font-size: smaller;">
            <?= $log_event['info'] ?>
            <? $feld = explode('.', $log_event['detail']); ?>
            <? $element = count($feld) > 1 ? $feld[1] : $feld[0]; ?>
            <? if ($format === 'detail' && $element): ?>
              <br><?= _('Element:').' '. htmlReady($element) ?>
            <? endif ?>
            <? if ($format === 'detail' && $log_event['debug']): ?>
              <br><?= _('Wert:').' '. htmlReady($log_event['debug']) ?>
            <? endif ?>
          </td>
        </tr>
      <? endforeach ?>
    </table>
    <p>
      <? if (count($log_events) > 0): ?>
        <?= sprintf(_('Eintrag %s - %s von %s'), $start + 1, $start + count($log_events), $num_entries) ?>
        <input type="hidden" name="start" value="<?= $start ?>">
        <? if ($start > 0): ?>
          <?= Button::create('<< '. _("Zurück"), 'back',['data-dialog' => '']) ?>
        <? endif ?>
        <? if ($start + count($log_events) < $num_entries): ?>
          <?= Button::create(_('Weiter') . " >>", 'forward',['data-dialog' => '']) ?>
        <? endif ?>
    <? else: ?>
      <?= _('keine Einträge gefunden') ?>
    <? endif ?>
    </p>
  <? endif ?>
</form>
