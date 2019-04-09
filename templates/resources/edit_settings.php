<? use Studip\Button, Studip\LinkButton; ?>

<form method="POST" action="<?=URLHelper::getLink('?change_global_settings=TRUE')?>" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Globale Einstellungen') ?></legend>

        <label>
            <input type="checkbox" name="allow_requests"
                   <? if (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) echo 'checked'; ?>>
            <?= _('Zulassen von Raumanfragen')?>
            <?= tooltipIcon(_('NutzerInnen können im Rahmen der Veranstaltungsverwaltung Raumeigenschaften und konkrete Räume wünschen.')) ?>
        </label>

        <label>
            <input type="checkbox" name="allow_requestable_requests"
                   <? if (Config::get()->RESOURCES_ALLOW_REQUESTABLE_ROOM_REQUESTS) echo 'checked'; ?>>
            <?= _('Nur wünschbare Räume')?>
            <?= tooltipIcon(_('NutzerInnen können im Rahmen der Veranstaltungsverwaltung nur wünschbare Räume wünschen.')) ?>
        </label>


        <label>
            <input type="checkbox" name="direct_requests_only"
                   <? if (Config::get()->RESOURCES_DIRECT_ROOM_REQUESTS_ONLY) echo 'checked'; ?>>
            <?= _('Nur konkrete Räume wünschbar')?>
            <?= tooltipIcon(_('NutzerInnen können im Rahmen der Veranstaltungsverwaltung nur konkrete Räume wünschen.')) ?>
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _('Sperrzeiten für die Bearbeitung von Raumbelegungen') ?></legend>

        <?= _('Die <b>Bearbeitung</b> von Belegungen soll für alle lokalen Ressourcen-Administratoren zu folgenden Bearbeitungszeiten geblockt werden:') ?><br>
        <label>
            <input type="checkbox" name="locking_active"
                   <? if (Config::get()->RESOURCES_LOCKING_ACTIVE) echo 'checked'; ?>>
            <?= _('Blockierung ist zu den angegebenen Sperrzeiten aktiv:') ?><br>
        </label>
        <br>

        <?= $this->render_partial('resources/display_locks', ['locks' => $locks['edit']]) ?>

        <?= LinkButton::create(_('Neue Sperrzeit anlegen'), URLHelper::getLink('?create_lock=edit')) ?>
    </fieldset>

    <fieldset>
        <legend><?=_('Sperrzeiten für Raumelegungen') ?></legend>

        <?= _('Die <b>Belegung</b> soll für alle lokalen Ressourcen-Administratoren zu folgenden Belegungszeitenzeiten geblockt werden:') ?><br>
        <label>
            <input type="checkbox" name="assign_locking_active"
                   <? if (Config::get()->RESOURCES_ASSIGN_LOCKING_ACTIVE) echo 'checked'; ?>>
            <?= _('Blockierung ist zu den angegebenen Sperrzeiten aktiv:') ?><br>
        </label>
        <br>

        <?= $this->render_partial('resources/display_locks', ['locks' => $locks['assign']]) ?>

        <?= LinkButton::create(_('Neue Sperrzeit anlegen'), URLHelper::getLink('?create_lock=assign')) ?>
    </fieldset>

    <fieldset>
        <legend><?= _('Optionen beim Bearbeiten von Anfragen') ?></legend>

        <label>
            <?= _('Anzahl der Belegungen, ab der Räume dennoch mit Einzelterminen passend belegt werden können in Prozent') ?>
            <input type="text" size="5" maxlength="10" class="size-s"
                   name="allow_single_assign_percentage"
                   value="<?= Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE ?>">
            <br>
        </label>

        <label>
            <?= _('Anzahl ab der Einzeltermine gruppiert bearbeitet werden sollen') ?>
            <input type="text" size="3" maxlength="5" class="size-s"
                   name="allow_single_date_grouping"
                   value="<?= Config::get()->RESOURCES_ALLOW_SINGLE_DATE_GROUPING ?>"><br>
        </label>
    </fieldset>

    <fieldset>
        <legend>Organisatorisches</legend>

        <h2><?= _('Einordnung von Räumen in Orga-Struktur') ?></h2>
        <label>
            <input type="checkbox" name="enable_orga_classify"
                   <? if (Config::get()->RESOURCES_ENABLE_ORGA_CLASSIFY) echo 'checked'; ?>>
            <?= _('Räume können Fakultäten und Einrichtungen unabhängig von Besitzerrechten zugeordnet werden.')?><br>
        </label>

        <h2><?= _('Anlegen von Räumen') ?></h2>
        <label>
            <?= _('Das Anlegen von Räumen kann nur durch folgende Personenkreise vorgenommen werden:') ?><br>
            <select name="allow_create_resources">
                <option value="1" <? if (Config::get()->RESOURCES_ALLOW_CREATE_ROOMS == 1) echo 'selected'; ?>>
                    <?= _('NutzerInnen ab globalem Status Tutor') ?>
                </option>
                <option value="2" <? if (Config::get()->RESOURCES_ALLOW_CREATE_ROOMS == 2) echo 'selected'; ?>>
                    <?= _('NutzerInnen ab globalem Status Admin') ?>
                </option>
                <option value="3" <? if (Config::get()->RESOURCES_ALLOW_CREATE_ROOMS == 3) echo 'selected'; ?>>
                    <?= _('nur globale Ressourcenadministratoren') ?>
                </option>
            </select>
        </label>


        <h2><?= _('Vererbte Berechtigungen von Veranstaltungen und Einrichtungen für Ressourcen')?></h2>
        <?= _('Mitglieder von Veranstaltungen oder Einrichtungen erhalten '
             .'folgende Rechte in Ressourcen, die diesen Veranstaltungen '
             .'oder Einrichtungen gehören:') ?><br>
        <label>
            <input type="radio" name="inheritance_rooms" value="1"
                   <? if (Config::get()->RESOURCES_INHERITANCE_PERMS_ROOMS == 1) echo 'checked'; ?>>
            <?= _('die lokalen Rechte der Einrichtung oder Veranstaltung werden übertragen') ?>
            <br>
        </label>
        <label>
            <input type="radio" name="inheritance_rooms" value="2"
                   <? if (Config::get()->RESOURCES_INHERITANCE_PERMS_ROOMS == 2) echo 'checked'; ?>>
            <?= _('nur Autorenrechte (eigene Belegungen anlegen und bearbeiten)') ?>
            <br>
        </label>
        <label>
            <input type="radio" name="inheritance_rooms" value="3"
                   <? if (Config::get()->RESOURCES_INHERITANCE_PERMS_ROOMS == 3) echo 'checked'; ?>>
            <?= _('keine Rechte') ?>
            <br>
        </label>


        <h2><?= _('Vererbte Berechtigungen von Veranstaltungen und Einrichtungen für <i>Räume</i>') ?></h2>
        <?= _('Mitglieder von Veranstaltungen oder Einrichtungen erhalten '
             .'folgende Rechte in <i>Räumen</i>, die diesen Veranstaltungen '
             .'oder Einrichtungen gehören:') ?><br>
        <label>
            <input type="radio" name="inheritance" value="1"
                   <? if (Config::get()->RESOURCES_INHERITANCE_PERMS == 1) echo 'checked'; ?>>
            <?= _('die lokalen Rechte der Einrichtung oder Veranstaltung werden übertragen') ?><br>
        </label>
        <label>
            <input type="radio" name="inheritance" value="2"
                   <? if (Config::get()->RESOURCES_INHERITANCE_PERMS == 2) echo 'checked'; ?>>
            <?= _('nur Autorenrechte (eigene Belegungen anlegen und bearbeiten)') ?><br>
        </label>
        <label>
            <input type="radio" name="inheritance" value="3"
                   <? if (Config::get()->RESOURCES_INHERITANCE_PERMS == 3) echo 'checked'; ?>>
            <?= _('keine Rechte') ?><br>
        </label>
        <br>
    </fieldset>

    <footer>
        <?= Button::create(_('Übernehmen'), '_send_settings') ?>
    </footer>
</form>
