<? use Studip\Button, Studip\LinkButton; ?>
<?= $delete_question ?>

<form class="default" action="<?= $controller->url_for('tour/save/' . $tour->tour_id) ?>" method="post">
    <?= CSRFProtection::tokenTag(); ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>

    <? if (!count($tour->steps)) : ?>
        <label>
           <span class="required">
                <?= _('Sprache der Tour:') ?>
           </span>
            <select name="tour_language">
            <? foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $key => $language) : ?>
                <option value="<?= mb_substr($key, 0, 2) ?>" <? if ($tour->language === mb_substr($key, 0, 2)) echo 'selected'; ?>>
                    <?= htmlReady($language['name']) ?>
                </option>
            <? endforeach ?>
            </select>
        </label>
    <? endif ?>

        <label>
            <span class="required"><?= _('Name der Tour:') ?></span>
            <input type="text" size="60" maxlength="255" name="tour_name"
                   value="<?= $tour ? htmlReady($tour->name) : '' ?>"
                   required="required" aria-required="true"
                   placeholder="<?= _('Bitte geben Sie einen Namen für die Tour an') ?>">
        </label>

        <label>
            <span class="required"> <?= _('Beschreibung:') ?></span>
            <textarea cols="60" rows="5" name="tour_description"
                      required="required" aria-required="true"
                      placeholder="<?= _('Bitte geben an, welchen Inhalt die Tour hat') ?>"><?= $tour ? htmlReady($tour->description) : '' ?></textarea>
        </label>

        <label>
            <?= _('Art der Tour:') ?>
            <select name="tour_type">
                <option value="tour" <? if ($tour->type === 'tour') echo 'selected'; ?>>
                    <?= _('Tour (passiv)') ?>
                </option>
                <option value="wizard" <? if ($tour->type === 'wizard') echo 'selected'; ?>>
                    <?= _('Wizard (interaktiv)') ?>
                </option>
            </select>
        </label>

        <label>
            <?= _('Zugang zur Tour:') ?>
            <select name="tour_access">
                <option value="link" <? if ($tour->settings->access === 'link') echo 'selected'; ?>>
                    <?= _('unsichtbar') ?>
                </option>
                <option value="standard" <? if ($tour->settings->access === 'standard') echo 'selected'; ?>>
                    <?= _('Anzeige im Hilfecenter') ?>
                </option>
                <option value="autostart" <? if ($tour->settings->access === 'autostart') echo 'selected'; ?>>
                    <?= _('Startet bei jedem Aufruf der Seite, bis die Tour abgeschlossen wurde') ?>
                </option>
                <option value="autostart_once" <? if ($tour->settings->access === 'autostart_once') echo 'selected'; ?>>
                    <?= _('Startet nur beim ersten Aufruf der Seite') ?>
                </option>
            </select>
        </label>

    <? if (!count($tour->steps)) : ?>
        <label>
            <span class="required"><?= _('Startseite der Tour:') ?></span>
            <input type="text" size="60" maxlength="255" name="tour_startpage"
                   value="<?= $tour_startpage ? htmlReady($tour_startpage) : '' ?>"
                   required="required" aria-required="true"
                   placeholder="<?= _('Bitte geben Sie eine Startseite für die Tour an') ?>"/>
        </label>

    <? endif ?>

    <section>
        <?= _('Geltungsbereich (Nutzendenstatus):') ?>
        <? foreach (['autor', 'tutor', 'dozent', 'admin', 'root'] as $role) : ?>
        <label>
            <input type="checkbox" name="tour_roles[]" value="<?= $role ?>"
                   <? if (mb_strpos($tour->roles, $role) !== false) echo 'checked'; ?>>
            <?=$role ?>
        </label>
        <? endforeach ?>
    </section>

        <!--label for="tour_audience" class="caption">
        <?= _('Bedingung') ?>
    </label>
    <select name="tour_audience_type">
    <option value=""></option>
    <option value="sem"<?= ($audience->type == 'sem') ? ' selected' : '' ?>><?= _('Teilnehmende der Veranstaltung') ?></option>
    <option value="inst"<?= ($audience->type == 'inst') ? ' selected' : '' ?>><?= _('Mitglied der Einrichtung') ?></option>
    <option value="studiengang"<?= ($audience->type == 'studiengang') ? ' selected' : '' ?>><?= _('Eingeschrieben in Studiengang') ?></option>
    <option value="abschluss"<?= ($audience->type == 'abschluss') ? ' selected' : '' ?>><?= _('Angestrebter Abschluss') ?></option>
    <option value="userdomain"<?= ($audience->type == 'userdomain') ? ' selected' : '' ?>><?= _('Zugeordnet zur Nutzerdomäne') ?></option>
    </select>
    <input type="text" size="60" maxlength="255" name="tour_audience_range_id"
        value="<?= $audience ? htmlReady($audience->range_id) : '' ?>"
        placeholder="<?= _('interne ID des Objekts') ?>"/-->

    </fieldset>
    <footer>
        <?= CSRFProtection::tokenTag() ?>
        <?= Button::createAccept(_('Speichern'), 'save_tour_details') ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('tour/admin_overview')) ?>
    </footer>
</form>

<? if (!$tour->isNew()) : ?>
    <form method="post">
        <?= CSRFProtection::tokenTag() ?>
        <table class="default">
            <caption>
                <div class="step_list_title"><?= _('Schritte') ?></div>
            </caption>
            <colgroup>
                <col width="2%">
                <col width="25%">
                <col>
                <col width="15%">
                <col width="80">
            </colgroup>
            <thead>
                <tr>
                    <th><?= _('Nr.') ?></th>
                    <th><?= _('Überschrift') ?></th>
                    <th><?= _('Inhalt') ?></th>
                    <th><?= _('Seite') ?></th>
                    <th><?= _('Aktion') ?></th>
                </tr>
            </thead>
            <tbody>
            <? if (count($tour->steps)) : ?>
                <? foreach ($tour->steps as $step) : ?>
                    <tr id="<?= $tour_id . '_' . $step->step ?>">
                        <td><?= $step->step ?></td>
                        <td><?= htmlReady($step->title) ?></td>
                        <td><?= htmlReady($step->tip) ?></td>
                        <td><?= htmlReady($step->route) ?></td>
                        <td class="actions">
                        <? $actionMenu = ActionMenu::get() ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('tour/edit_step/' . $tour->tour_id . '/' . $step->step),
                            _('Schritt bearbeiten'),
                            Icon::create('edit'),
                            ['data-dialog' => 'size=auto;reload-on-close']
                        ) ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('tour/admin_details/' . $tour->tour_id, ['delete_tour_step' => $step->step]),
                            _('Schritt löschen'),
                            Icon::create('trash')
                        ) ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('tour/edit_step/' . $tour->tour_id . '/' . ($step->step + 1) . '/new'),
                            _('Neuen Schritt hinzufügen'),
                            Icon::create('add'),
                            ['data-dialog' => 'size=auto;reload-on-close']
                        ) ?>
                            <?= $actionMenu->render() ?>
                        </td>
                    </tr>
                <? endforeach ?>
            <? else : ?>
                <tr>
                    <td colspan="6">
                        <?= _('In dieser Tour sind bisher keine Schritte vorhanden.') ?>
                    </td>
                </tr>
            <? endif ?>
            </tbody>
        </table>
    </form>
<? endif ?>

<?
if (count($tour->steps)) {
    $sidebar = Sidebar::get();

    $widget = new ActionsWidget();
    $widget->addLink(
        _('Schritt hinzufügen'),
        $controller->url_for('tour/edit_step/' . $tour->tour_id . '/' . (count($tour->steps) + 1) . '/new'),
        Icon::create('add', 'clickable'),
        ['data-dialog' => 'size=auto;reload-on-close']
    );
    $sidebar->addWidget($widget);
}
