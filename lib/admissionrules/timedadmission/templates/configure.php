<h3><?= $rule->getName() ?></h3>
<label for="message" class="caption">
    <?= _('Nachricht bei fehlgeschlagener Anmeldung') ?>:
    <textarea name="message" rows="4" cols="50"><?= $rule->getMessage() ?></textarea>
</label>

<label for="startdate" class="caption">
    <?= _('Start des Anmeldezeitraums') ?>:
</label>
<label class="col-3">
    <?= _('Datum') ?>
    <input type="text" maxlength="10" name="startdate"
        class="size-s no-hint" placeholder="tt.mm.jjjj"
        id="startdate" value="<?= $rule->getStartTime() ?
        date('d.m.Y', $rule->getStartTime()) : '' ?>" data-max-date=""/>
</label>
<label class="col-3">
    <?= _('Uhrzeit') ?>
    <input type="text" name="starttime" id="starttime"
        class="size-s no-hint" placeholder="ss:mm"
        value="<?= $rule->getStartTime() ? date('H:i', $rule->getStartTime()) : '' ?>"/>
</label>

<label for="enddate" class="caption">
    <?= _('Ende des Anmeldezeitraums') ?>:
</label>

<label class="col-3">
    <?= _('Datum') ?>
    <input type="text" maxlength="10" name="enddate"
        class="size-s no-hint" placeholder="tt.mm.jjjj"
        id="enddate" value="<?= $rule->getEndTime() ?
        date('d.m.Y', $rule->getEndTime()) : '' ?>" data-min-date=""/>
</label>
<label class="col-3">
    <?= _('Uhrzeit') ?>
    <input type="text" name="endtime" id="endtime"
        class="size-s no-hint" placeholder="ss:mm"
        value="<?= $rule->getEndTime() ? date('H:i', $rule->getEndTime()) : '' ?>"/>
</label>

<script>
    $('#startdate').datepicker();
    $('#starttime').timepicker();
    $('#enddate').datepicker();
    $('#endtime').timepicker();
</script>
