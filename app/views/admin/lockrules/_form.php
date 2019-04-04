<?
# Lifter010:
use Studip\Button, Studip\LinkButton;

?>
<form action="<?= $action ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag(); ?>
    <fieldset>
        <legend>
            <? if ($lock_rule->name) : ?>
                <?= sprintf(_('Sperrebene "%s" ändern'), htmlready($lock_rule["name"])) ?>
            <? else : ?>
                <?= _('Neue Sperrebene eingeben für den Bereich:') ?> <?= $rule_type_names[$lock_rule_type]; ?>
            <? endif ?>
        </legend>

        <label>
            <?= _("Name") ?>

            <input type="text" style="width:90%" required name="lockdata_name"
                   value="<?= htmlReady($lock_rule['name']) ?>">
       </label>

       <label>
            <?= _('Beschreibung') ?>
            <?= tooltipIcon(_('Dieser Text wird auf allen Seiten mit gesperrtem Inhalt angezeigt')) ?>

            <textarea name="lockdata_description" rows="5"
                      style="width:90%"><?= htmlReady($lock_rule["description"]) ?></textarea>
      </label>

      <label>
            <?= _('Nutzerstatus') ?>
            <?= tooltipIcon(_('Die Einstellungen dieser Sperrebene gelten für Nutzer bis zu dieser Berechtigung')) ?>

            <select name="lockdata_permission">
                <? foreach ($lock_rule_permissions as $p) : ?>
                    <option <?= ($lock_rule['permission'] == $p ? 'selected' : '') ?>><?= $p ?></option>
                <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Übernehmen'), 'ok', array('title' => _('Einstellungen übernehmen'))) ?>
    </footer>


    <? foreach ($lock_config['groups'] as $group => $group_title) : ?>
        <? $attributes = array_filter(array_map(function ($a) use ($group) {
            return $a['group'] == $group ? $a['name'] : null;
        }, $lock_config['attributes'])); ?>
        <? if (count($attributes)) : ?>
            <br>
            <table class="default">
                <caption>
                    <?= htmlready($group_title) ?>
                </caption>
                <colgroup>
                    <col width="70%">
                    <col width="15%">
                    <col width="15%">
                </colgroup>
                <thead>
                <tr>
                    <th></th>
                    <th><?= _('gesperrt') ?></th>
                    <th><?= _('nicht gesperrt') ?></th>
                </tr>
                </thead>
                <tbody>
                <? foreach ($attributes as $attr => $attr_name) : ?>
                    <tr>
                        <td>
                            <?= htmlready($attr_name) ?>
                        </td>
                        <td>
                            <input type="radio"
                                   name="lockdata_attributes[<?= $attr ?>]" <?= ($lock_rule['attributes'][$attr] ? 'checked' : '') ?>
                                   value="1"/>
                        </td>
                        <td>
                            <input type="radio"
                                   name="lockdata_attributes[<?= $attr ?>]" <?= (!$lock_rule['attributes'][$attr] ? 'checked' : '') ?>
                                   value="0"/>
                        </td>
                    </tr>
                <? endforeach ?>
                </tbody>
            </table>

        <footer>
            <?= Button::create(_('Übernehmen'), 'ok', array('title' => _('Einstellungen übernehmen'))) ?>
        </footer>
        <? endif ?>

    <? endforeach ?>
</form>
