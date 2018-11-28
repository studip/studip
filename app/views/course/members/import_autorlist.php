<? use Studip\Button, Studip\LinkButton;?>

<form action="<?= $controller->url_for('course/members/set_autor_csv')?>" method="post" name="user" class="default">
<?= CSRFProtection::tokenTag() ?>
    <h1>
        <?=sprintf(_('%s hinzufügen'), htmlReady(get_title_for_status('autor', 1)))?>
    </h1>
    <fieldset>
        <legend>
            <?=_('Teilnehmendenliste übernehmen')?>
        </legend>

        <label>
            <?=_('Eingabeformat')?>:

            <?= tooltipHtmlIcon(sprintf(_('In das Textfeld <strong>Teilnehmendenliste übernehmen</strong> können Sie eine Liste mit Namen von %s eingeben,
                die in die Veranstaltung aufgenommen werden sollen. Wählen Sie in der Auswahlbox das gewünschte Format, in dem Sie die Namen eingeben möchten.<br />
                <strong>Eingabeformat</strong><br/>
                <strong>Nachname, Vorname &crarr;</strong><br />Geben Sie dazu in jede Zeile den Nachnamen und (optional) den Vornamen getrennt durch ein Komma oder ein Tabulatorzeichen ein.<br />
                <strong>Nutzername &crarr;</strong><br />Geben Sie dazu in jede Zeile den Stud.IP Nutzernamen ein.'), htmlReady($status_groups['autor'])));?>

            <select name="csv_import_format">
                <option value="realname"><?=_("Nachname, Vorname")?> &crarr;</option>
                <option value="username"><?=_("Nutzername")?> &crarr;</option>
                <? if(!empty($accessible_df)) : ?>
                    <? foreach ($accessible_df as $df) : ?>
                        <option value="<?=$df->getId()?>" <?=(Request::get('csv_import_format') ==  $df->getId()? 'selected="selected"': '')?>><?= htmlReady($df->getName())?> &crarr;</option>
                    <? endforeach?>
                <? endif ?>
            </select>
        </label>

        <label>
            <?= sprintf(_('<strong>%s</strong> in die Veranstaltung eintragen'), htmlReady(get_title_for_status('autor', 1)))?></td>
            <textarea name="csv_import" rows="6" cols="50"></textarea>
        </label>
    </fieldset>

    <footer>
        <?= Button::createAccept(_('Eintragen'), 'add_member_list',
            array('title' => sprintf(_("als %s eintragen"), htmlReady(get_title_for_status('autor', 1))))) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/members/index')) ?>
    </footer>
</form>
