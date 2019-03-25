<form action="<?= $controller->show() ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Anzeige der Log-Events') ?></legend>

        <label class="col-2">
            <?= _('Aktionen filtern') ?>
            <select name="action_id" class="nested-select" required>
                <option value="all"><?= _('Alle Aktionen') ?></option>
            <? foreach ($log_actions as $group => $actions): ?>
                <option value="" class="nested-item-header" disabled>
                    <?= _('Gruppe') ?> <?= htmlReady($group) ?>
                </option>
                <? foreach ($actions as $id => $description): ?>
                    <option value="<?= htmlReady($id) ?>" <? if ($id === $action_id) echo 'selected'; ?> class="nested-item">
                        <?= htmlReady($description) ?>
                    </option>
                <? endforeach; ?>
            <? endforeach; ?>
            </select>
        </label>

        <label class="col-2">
            <?= _('Darstellung') ?>
            <select name="format">
                <option value="compact" <? if ($format === 'compact') echo 'selected'; ?>>
                    <?= _('Kompakt') ?>
                </option>
                <option value="detail" <? if ($format === 'detail') echo 'selected'; ?>>
                    <?= _('Details') ?>
                </option>
            </select>
        </label>

        <label class="col-2">
            <?= _('Art der Einträge') ?><br>
            <select name="type" <? if (isset($objects)) echo 'disabled'; ?>>
            <? foreach ($types as $name => $title): ?>
                <option value="<?= htmlReady($name) ?>" <? if ($type === $name) echo 'selected'; ?>>
                    <?= htmlReady($title) ?>
                </option>
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
                    <option value="<?= htmlReady($object[0]) ?>" <? if ($object[0] === $object_id) echo 'selected'; ?>>
                        <?= htmlReady($object[1]) ?>
                    </option>
                <? endforeach ?>
                </select>

                <a href="<?= $controller->show(['action_id' => $action_id]) ?>">
                    <?= Icon::create('refresh')->asImg(['title' => _('neue Suche')]) ?>
                </a>
            </div>
        </label>
    <? else : ?>
        <label class="col-3">
            <?= _('Suchen') ?>
            <input type="text" name="search"
                   placeholder="<?= _('Veranstaltung / Einrichtung / ... ') ?>">
        </label>
    <? endif ?>

    </fieldset>

    <footer>
        <?= Studip\Button::create(_('Anzeigen')) ?>
    </footer>

  <? if (isset($log_events)): ?>
    <br>
    <table class="default">
        <colgroup>
            <col style="width: 20ex">
            <col>
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Zeit') ?></th>
                <th><?= _('Info') ?></th>
            </tr>
        </thead>
        <tbody>
        <? if (count($log_events) === 0): ?>
            <tr>
                <td colspan="2">
                    <?= _('keine Einträge gefunden') ?>
                </td>
            </tr>
        <? endif; ?>
        <? foreach ($log_events as $log_event): ?>
            <tr>
                <td>
                    <?= strftime('%x %X', $log_event['time']) ?>
                </td>
                <td>
                    <?= $log_event['info'] ?>
                <? if ($format === 'detail' && $log_event['detail']): ?>
                    <br>
                    <?= _('Info:') ?>
                    <?= htmlReady($log_event['detail']) ?>
                <? endif ?>
                <? if ($format === 'detail' && $log_event['debug']): ?>
                    <br>
                    <?= _('Debug:') ?>
                    <?= htmlReady($log_event['debug']) ?>
                <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    <? if ($num_entries > 50): ?>
        <tfoot>
            <tr>
                <td colspan="2" class="actions">
                    <?= Pagination::create($num_entries, $page, 50)->asButtons() ?>
                </td>
            </tr>
        </tfoot>
    <? endif; ?>
    </table>
  <? endif ?>

</form>
