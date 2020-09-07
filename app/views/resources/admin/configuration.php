<form class="default" method="post"
      action="<?= URLHelper::getLink('dispatch.php/resources/admin/configuration')?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grundeinstellungen') ?></legend>
        <label>
            <input type="hidden" name="resources_enable" value="0">
            <input type="checkbox" name="resources_enable" value="1"
                   <?= $config->RESOURCES_ENABLE == '1'
                     ? 'checked="checked"'
                     : ''?>>
            <?= _('Raumverwaltung aktivieren') ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Anzeigeoptionen') ?></legend>
        <label>
            <input type="checkbox" name="resources_allow_view_resource_occupation"
                   value="1"
                   <?= $config->RESOURCES_ALLOW_VIEW_RESOURCE_OCCUPATION == '1'
                     ? 'checked="checked"'
                     : ''?>>
            <?= _('Belegungen sind sichtbar für alle Nutzer') ?>
        </label>
        <? if ($colours): ?>
            <? foreach ($colours as $colour): ?>
                <label>
                    <?= htmlReady(
                        $colour->description
                        ? $colour->description
                        : $colour->color_id
                    ) ?>
                    <input type="color" name="colours[<?= htmlReady($colour->colour_id) ?>]"
                           value="<?= htmlReady($colour) ?>">
                </label>
            <? endforeach ?>
        <? endif ?>
        <label>
            <?= _('URL für Kartendienst') ?>
            <input type="text" name="resources_map_service_url"
                   placeholder="https://www.openstreetmap.org/#map=17/LATITUDE/LONGITUDE"
                   value="<?= htmlReady($config->RESOURCES_MAP_SERVICE_URL) ?>">
            <?= _('Die URL muss zwei Platzhalter enhalten: LATITUDE für die Längenangabe und LONGITUDE für die Breitenangabe.') ?>
        </label>
        <label>
            <?= _('Ab welcher Uhrzeit sollen Belegungen in der Standardansicht des Belegungsplans angezeigt werden?')?>
            <input type="text" name="resources_booking_plan_start_hour"
                   placeholder="HH:MM"
                   value="<?= htmlReady($resources_booking_plan_start_hour) ?>">
        </label>
        <label>
            <?= _('Bis zu welcher Uhrzeit sollen Belegungen in der Standardansicht des Belegungsplans angezeigt werden?')?>
            <input type="text" name="resources_booking_plan_end_hour"
                   placeholder="HH:MM"
                   value="<?= htmlReady($resources_booking_plan_end_hour) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Raumanfragen') ?></legend>
        <label>
            <input type="checkbox" name="resources_allow_room_requests"
                   value="1"
                   <?= $config->RESOURCES_ALLOW_ROOM_REQUESTS == '1'
                     ? 'checked="checked"'
                     : ''?>>
            <?= _('Ressourcenanfragen sind eingeschaltet') ?>
        </label>
        <label>
            <input type="checkbox" name="resources_allow_room_property_requests"
                   value="1"
                   <?= $config->RESOURCES_ALLOW_ROOM_PROPERTY_REQUESTS == '1'
                     ? 'checked="checked"'
                     : ''?>>
            <?= _('Ressourceneigenschaften dürfen bei einer Anfrage gewünscht werden') ?>
        </label>
        <label>
            <input type="checkbox" name="resources_direct_room_requests_only"
                   value="1"
                   <?= $config->RESOURCES_DIRECT_ROOM_REQUESTS_ONLY == '1'
                     ? 'checked="checked"'
                     : ''?>>
            <?= _('Nur konkrete Raumanfragen erlauben') ?>
        </label>
        <label>
            <input type="checkbox" name="resources_display_current_requests_in_overview"
                   value="1"
                   <?= $config->RESOURCES_DISPLAY_CURRENT_REQUESTS_IN_OVERVIEW == '1'
                     ? 'checked="checked"'
                     : ''?>>
            <?= _('Aktuelle Raumanfragen auf der Übersichtsseite anzeigen') ?>
        </label>
        <label>
            <?= _('Ab welchem Prozentwert (für den Anteil an Belegungen) sollen Einzelbelegungen statt Serienbelegungen durchgeführt werden, wenn es zu Überschneidungen kommt?')?>
            <input type="number" name="resources_allow_single_assign_percentage"
                   min="0" max="100" step="1"
                   value="<?= htmlReady($config->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE) ?>">
        </label>
        <label>
            <?= _('Ab welcher Anzahl an Einzelterminen sollen diese zusammengefasst zu einer Gruppe bearbeitet werden?') ?>
            <input type="number" name="resources_allow_single_date_grouping"
                   value="<?= htmlReady($config->RESOURCES_ALLOW_SINGLE_DATE_GROUPING) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Buchen von Räumen und Ressourcen') ?></legend>
        <label>
            <?= _('Wie lange darf die Rüstzeit vor dem Beginn einer Buchung maximal dauern? (Angabe in Minuten)') ?>
            <input type="number" name="resources_max_preparation_time"
                   value="<?= htmlReady($config->RESOURCES_MAX_PREPARATION_TIME) ?>">
        </label>
        <label>
            <?= _('Was ist die kürzeste erlaubte Dauer einer Buchung in Minuten?') ?>
            <input type="number" name="resources_min_booking_time"
                   value="<?= htmlReady($config->RESOURCES_MIN_BOOKING_TIME) ?>">
        </label>
    </fieldset>
    <?= \Studip\Button::create(_('Speichern'), 'save') ?>
</form>
