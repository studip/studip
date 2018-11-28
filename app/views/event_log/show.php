<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;
?>

<? if (isset($error_msg)): ?>
    <?= MessageBox::error($error_msg) ?>
<? endif ?>

<form action="<?= $controller->url_for('event_log/show') ?>" method="POST" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Anzeige der Log-Events') ?>
        </legend>

        <label class="col-2">
            <?= _('Aktionen filtern') ?>
            <select name="action_id">
                <option value="all"><?= _('Alle Aktionen') ?></option>
                <? foreach ($log_actions as $log_action): ?>
                    <option value="<?= $log_action['action_id'] ?>"
                        <? if ($log_action['action_id'] === $action_id): ?>
                        selected
                        <? endif ?>

                        <? if ($log_action['log_group'] !== $lastgroup): ?>
                            <? $lastgroup = $log_action['log_group'] ?>
                            style="border-top: 1px solid #cccccc;"
                        <? endif ?>
                    >
                    <?= htmlReady($log_action['description']) ?>
                </option>
                <? endforeach ?>
            </select>
        </label>

        <label class="col-2">
            <?= _('Darstellung') ?>
            <select name="format">
                <option value="compact" <?= $format == 'compact' ? 'selected' : '' ?>><?= _('Kompakt') ?></option>
                <option value="detail" <?= $format == 'detail' ? 'selected' : '' ?>><?= _('Details') ?></option>
            </select>
        </label>

        <label class="col-1">
            <?= _('Art der Einträge') ?><br>
            <select name="type" <?= isset($objects) ? 'disabled="disabled"' : ''?>>
                <? foreach ($types as $name => $title): ?>
                    <option value="<?= $name ?>" <?= $type == $name ? 'selected' : ''?>><?= htmlReady($title) ?></option>
                <? endforeach ?>
            </select>
        </label>

        <? if (isset($objects)): ?>
            <input type="hidden" name="type" value="<?= htmlReady($type) ?>">
            <input type="hidden" name="search" value="<?= htmlReady($search) ?>">

            <label class="col-3">
                <?= _('Eintrag auswählen') ?>
                <div class="hgroup">
                    <select name="object_id">
                        <? foreach ($objects as $object): ?>
                            <? $selected = $object[0] === $object_id ? 'selected' : '' ?>
                            <option value="<?= $object[0] ?>" <?= $selected ?>><?= htmlReady($object[1]) ?></option>
                        <? endforeach ?>
                    </select>

                    <a href="<?= $controller->url_for('event_log/show?action_id='.urlencode($action_id)) ?>">
                        <?= Icon::create('refresh', 'clickable', ['title' => _('neue Suche')])->asImg() ?>
                    </a>
                </div>
            </label>
        <? else : ?>
            <label class="col-3">
                <?= _('Suchen') ?>
                <input type="text" size="20" name="search" placeholder="<?= _('Veranstaltung / Einrichtung / ... ') ?>">
            </label>

        <? endif ?>

    </fieldset>

    <footer>
        <?= Button::create(_('Anzeigen')) ?>
    </footer>

  <? if (isset($log_events)): ?>
    <br>
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
            <? if ($format === 'detail' && $log_event['detail']): ?>
              <br><?= _('Info:').' '.htmlReady($log_event['detail']) ?>
            <? endif ?>
            <? if ($format === 'detail' && $log_event['debug']): ?>
              <br><?= _('Debug:').' '.htmlReady($log_event['debug']) ?>
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
          <?= Button::create('<< '. _("Zurück"), 'back') ?>
        <? endif ?>
        <? if ($start + count($log_events) < $num_entries): ?>
          <?= Button::create(_('Weiter') . " >>", 'forward') ?>
        <? endif ?>
    <? else: ?>
      <?= _('keine Einträge gefunden') ?>
    <? endif ?>
    </p>
  <? endif ?>

</form>
