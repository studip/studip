<form name="single_termin" class="default" action="<?= $controller->url_for("course/dates/singledate/" . htmlReady($date->getId())) ?>" method="POST" data-dialog>
<?= CSRFProtection::tokenTag()?>
    <fieldset>
    	<legend><?= _("Einzeltermin") ?></legend>

    	<label for="startDate">
                <?= _('Datum des Termins') ?>
    	       <input id="startDate" class="has-date-picker no-hint" name="startDate" type="text" maxlength="10" value="<?= !$date->date ? _('Datum') : date("d.m.Y", $date->date ); ?>">
        </label>

        <label for="startStunde" class="col-3">
            <?= _('Startuhrzeit') ?>

            <section class="hgroup">
                <input id="startStunde" class="no-hint size-s" name="start_stunde" type="text" maxlength="2" size="2" value="<?= !$date->date ? "" : date("H", $date->date ); ?>"> :
                <input id="startMinute" class="no-hint size-s" name="start_minute" type="text" maxlength="2" size="2" value="<?= !$date->date ? "" : date("i", $date->date ); ?>">
            </section>

        </label>

        <label for="endStunde" class="col-3">
            <?= _('Enduhrzeit') ?>

            <section class="hgroup">
                <input id="endStunde" class="no-hint size-s" name="end_stunde" type="text" maxlength="2" size="2" value="<?= !$date->end_time ? "" : date("H", $date->end_time ); ?>"> :
                <input id="endMinute" class="no-hint size-s" name="end_minute" type="text" maxlength="2" size="2" value="<?= !$date->end_time ? "" : date("i", $date->end_time ); ?>">
            </section>
        </label>

    	<label>
            <?= _('Art des Termins') ?>
        	<select name="dateType" >
           	<? foreach ($GLOBALS['TERMIN_TYP'] as $key => $val) : ?>
            	<option value="<?= $key ?>" <?= $date['date_typ'] == $key ? ' selected' : '' ?> > <?= htmlReady($val['name']) ?> </option>
    		<? endforeach; ?>
            </select>
        </label>

    	<label>
            <?= _('Freie Ortsangabe (keine Raumbuchung)') ?>
	       <textarea name="freeRoomText_sd" style="width: 98%;" rows="3" ><?= htmlReady($date->raum) ?></textarea>
       </label>
    </fieldset>

    <input type="hidden" name="singleDateID" value="<?= htmlReady($date->getId()) ?>">

	<footer data-dialog-button>
        <div class="button-group">
            <? if (!$dates_locked) : ?>
        	    <?= \Studip\Button::create(_('Termin Speichern'), "editSingleDate_button" ); ?>
            <? endif ?>
            <? if (!$cancelled_dates_locked && !$date->isNew()) : ?>
                <?= \Studip\LinkButton::create(_("Ausfallen lassen"), $controller->url_for("course/cancel_dates", ['termin_id' => $date->getId()]), ['data-dialog' => '']) ?>
            <? endif ?>
        </div>
    </footer>
</form>
