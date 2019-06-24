<h3><?= $rule->getName() ?></h3>
<label for="start" class="caption">
    <?= _('Zeitpunkt der automatischen Platzverteilung') ?>:
</label>

<label class="col-3">
    <?= _('Datum') ?>
    <input type="text" name="distributiondate" id="distributiondate"
        class="size-s no-hint" placeholder="tt.mm.jjjj"
        value="<?= $rule->getDistributionTime() ? date('d.m.Y', $rule->getDistributionTime()) : '' ?>"/>
</label>

<label class="col-3">
    <?= _('Uhrzeit') ?>
    <input type="text" name="distributiontime" id="distributiontime"
        class="size-s no-hint" placeholder="ss:mm"
        value="<?= $rule->getDistributionTime() ? date('H:i', $rule->getDistributionTime()) : '23:59' ?>"/>
</label>

<? if ($rule->isFCFSallowed()) : ?>
    <label for="enable_FCFS">
    <input <?=($rule->prio_exists ? 'disabled' : '')?> type="checkbox" id="enable_FCFS"  name="enable_FCFS" value="1" <?= (!is_null($rule->getDistributionTime()) && !$rule->getDistributionTime() ? "checked" : ""); ?>>
    <?=_("<u>Keine</u> automatische Platzverteilung (Windhund-Verfahren)")?>
    <?=($rule->prio_exists ? tooltipicon(_("Es existieren bereits Anmeldungen für die automatische Platzverteilung.")) : '')?>
    </label>
<? endif ?>
<script>
    $('#distributiondate').datepicker();
    $('#distributiontime').timepicker();
</script>
