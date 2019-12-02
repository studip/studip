<form class="default" action="<?= $controller->link_for('/change_course_set') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Anmelderegeln') ?></legend>
        <section>
            <?= _('Bitte geben Sie hier an, welche speziellen Anmelderegeln gelten sollen.'); ?>
        </section>
        <? if ($current_courseset) : ?>
            <div>
                <?= sprintf(_('Diese Veranstaltung gehört zum Anmeldeset "%s".'), htmlReady($current_courseset->getName())) ?>
                <div id="courseset_<?= $current_courseset->getId() ?>">
                    <?= $current_courseset->toString(true) ?>
                </div>
                <div>
                    <? if (!$is_locked['admission_type'] || $current_courseset->isUserAllowedToEdit($user_id)) : ?>
                        <? if ($current_courseset->isUserAllowedToAssignCourse($user_id, $course_id)) : ?>
                            <?= Studip\Button::create(_("Zuordnung aufheben"), 'change_course_set_unassign', ['data-dialog' => '']) ?>
                        <? endif ?>
                        <? if ($current_courseset->isUserAllowedToEdit($user_id)) : ?>
                            <?= Studip\LinkButton::create(_("Anmeldeset bearbeiten"), $controller->url_for('/edit_courseset/' . $current_courseset->getId()), ['data-dialog' => '']); ?>
                        <? endif ?>
                    <? endif ?>
                </div>
            </div>
        <? else : ?>
            <div>
                <? if (!$is_locked['passwort'] && isset($activated_admission_rules['PasswordAdmission'])) : ?>
                    <?= Studip\LinkButton::create(_("Anmeldung mit Passwort"), $controller->url_for('/instant_course_set', ['type' => 'PasswordAdmission']), ['data-dialog' => '']) ?>
                <? endif ?>
                <? if (!$is_locked['admission_type']) : ?>
                    <? if (isset($activated_admission_rules['LockedAdmission'])) : ?>
                        <?= Studip\LinkButton::create(_("Anmeldung gesperrt"), $controller->url_for('/instant_course_set', ['type' => 'LockedAdmission']), ['data-dialog' => '']) ?>
                    <? endif ?>
                    <? if (isset($activated_admission_rules['TimedAdmission'])) : ?>
                        <?= Studip\LinkButton::create(_("Zeitgesteuerte Anmeldung"), $controller->url_for('/instant_course_set', ['type' => 'TimedAdmission']), ['data-dialog' => '']) ?>
                    <? endif ?>
                    <br>
                    <? if (isset($activated_admission_rules['ParticipantRestrictedAdmission'])) : ?>
                        <?= Studip\LinkButton::create(_("Teilnahmebeschränkte Anmeldung"), $controller->url_for('/instant_course_set', ['type' => 'ParticipantRestrictedAdmission']), ['data-dialog' => '']) ?>
                        <? if (isset($activated_admission_rules['TimedAdmission'])) : ?>
                            <?= Studip\LinkButton::create(_("Zeitgesteuerte und Teilnahmebeschränkte Anmeldung"), $controller->url_for('/instant_course_set', ['type' => 'ParticipantRestrictedAdmission_TimedAdmission']), ['data-dialog' => '']) ?>
                        <? endif ?>
                    <? endif ?>
                <? endif ?>
            </div>
            <? if (!$is_locked['admission_type'] && count($available_coursesets)) : ?>
                <details class="studip">
                    <summary title="<?= _("Klicken um Zuordnungsmöglichkeiten zu öffnen") ?>">
                        <?= _("Zuordnung zu einem bestehenden Anmeldeset"); ?>
                        <?= tooltipIcon(_("Wenn die Veranstaltung die Anmelderegeln eines Anmeldesets übernehmen soll, klicken Sie hier und wählen das entsprechende Anmeldeset aus.")); ?>
                    </summary>

                    <select name="course_set_assign" style="display: inline-block;"
                            onChange="$('#course_set_assign_explain').load('<?= $controller->link_for('/explain_course_set') ?>&set_id=' + $(this).val());">
                        <option></option>
                        <? $my_own_sets = $available_coursesets->findBy('my_own', true); ?>
                        <? $other_sets = $available_coursesets->findBy('my_own', false); ?>
                        <? if ($my_own_sets->count()) : ?>
                            <optgroup label="<?= _("Meine Anmeldesets") ?>">
                                <? foreach ($my_own_sets as $cs) : ?>
                                    <option
                                            value="<?= $cs['id'] ?>"><?= htmlReady(my_substr($cs['name'], 0, 100)) ?></option>
                                <? endforeach ?>
                            </optgroup>
                        <? endif ?>
                        <? if ($other_sets->count()) : ?>
                            <optgroup label="<?= _("Verfügbare Anmeldesets meiner Einrichtungen") ?>">
                                <? foreach ($available_coursesets->findBy('my_own', false) as $cs) : ?>
                                    <option
                                            value="<?= $cs['id'] ?>"><?= htmlReady(my_substr($cs['name'], 0, 100)) ?></option>

                                <? endforeach ?>
                            </optgroup>
                        <? endif ?>
                    </select>

                    <div id="course_set_assign_explain" style="display: inline-block;padding:1ex;">
                    </div>
                    <div style="display: inline-block;padding:1ex;">
                        <?= Studip\Button::create(_("Zuordnen"), 'change_course_set_assign', ['data-dialog' => '']) ?>
                    </div>
                </details>
            <? endif ?>
        <? endif ?>
    </fieldset>
</form>
<br>

<? if ($current_courseset && $current_courseset->isSeatDistributionEnabled()) : ?>
    <form class="default" action="<?= $controller->link_for('/change_admission_turnout') ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= _('Beschränkte Teilnehmendenanzahl') ?></legend>
            <div>
                <?= _('Bitte geben Sie hier an, wieviele Personen maximal für die Veranstaltung vorgesehen sind,
                und ob eine Warteliste erstellt werden soll, falls die Zahl der Anmeldungen die maximale Personenzahl überschreitet.'); ?>
            </div>

            <label for="admission_turnout">
                <?= _('max. Anzahl:') ?>
                <input type="number" name="admission_turnout" id="admission_turnout"
                       value="<?= $course->admission_turnout ?>">
                <small><?= sprintf(_("(%s freie Plätze)"), $course->getFreeSeats()) ?></small>
            </label>
            <br>
            <?= _('Einstellungen für die Warteliste:') ?>
            <label for="admission_disable_waitlist">
                <input <?= $is_locked['admission_disable_waitlist'] ?>
                        type="checkbox" id="admission_disable_waitlist"
                        name="admission_disable_waitlist"
                        value="1" <?= $course->admission_disable_waitlist == 0 ? "checked" : "" ?>>
                <?= _('Warteliste aktivieren') ?>
                <? if ($num_waitlist = $course->admission_applicants->findBy('status', 'awaiting')->count()) : ?>
                    &nbsp;<?= sprintf(_("(%s Wartende)"), $num_waitlist) ?>
                <? endif ?>
            </label>
            <label for="admission_disable_waitlist_move">
                <input <?= $is_locked['admission_disable_waitlist_move'] ?>
                        type="checkbox"
                        id="admission_disable_waitlist_move"
                        name="admission_disable_waitlist_move"
                        value="1" <?= $course->admission_disable_waitlist_move == 0 ? "checked" : "" ?>>
                <?= _('automatisches Nachrücken aus der Warteliste aktivieren') ?></label>
            <label for="admission_waitlist_max">
                <?= _('max. Anzahl an Wartenden (optional)') ?>
                <input <?= $is_locked['admission_waitlist_max'] ?>
                        type="text"
                        id="admission_waitlist_max"
                        name="admission_waitlist_max"
                        value="<?= $course->admission_waitlist_max ?: '' ?>">
            </label>
        </fieldset>
        <footer>
            <?= Studip\Button::create(_('Teilnehmendenanzahl und Warteliste ändern'), 'change_admission_turnout', ['data-dialog' => '']) ?>
        </footer>
    </form>
    <br>
<? endif ?>

<form class="default" action="<?= $controller->link_for('/change_admission_prelim') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Anmeldemodus') ?></legend>
        <div>
            <?= _('Bitte wählen Sie hier einen Anmeldemodus aus:'); ?>
        </div>
        <label for="admission_prelim_0">
            <input <?= $is_locked['admission_prelim'] ?>
                    type="radio" id="admission_prelim_0"
                    name="admission_prelim"
                    value="0" <?= $course->admission_prelim == 0 ? "checked" : "" ?>>
            <?= _('Direkter Eintrag') ?></label>
        <label for="admission_prelim_1">
            <input <?= $is_locked['admission_prelim'] ?>
                    type="radio" id="admission_prelim_1"
                    name="admission_prelim"
                    value="1" <?= $course->admission_prelim == 1 ? "checked" : "" ?>>
            <?= _('Vorläufiger Eintrag') ?></label>
        <? if ($course->admission_prelim == 1) : ?>
            <label for="admission_prelim_txt"><?= _("Hinweistext bei vorläufigen Eintragungen:"); ?></label>
            <textarea <?= $is_locked['admission_prelim_txt'] ?> id="admission_prelim_txt" name="admission_prelim_txt"
                                                                rows="4"><?
                echo htmlReady($course->admission_prelim_txt);
                ?></textarea>
        <? endif ?>
        <label for="admission_binding">
            <input <?= $is_locked['admission_binding'] ?> id="admission_binding"
                                                          type="checkbox" <?= $course->admission_binding == 1 ? "checked" : "" ?>
                                                          name="admission_binding" value="1">
            <?= _("Anmeldung ist <u>verbindlich</u>. (Teilnehmenden können sich nicht selbst wieder abmelden.)") ?>
        </label>
    </fieldset>
    <footer>
        <?= Studip\Button::create(_("Anmeldemodus ändern"), 'change_admission_prelim', ['data-dialog' => '']) ?>
    </footer>
</form>
<br>

<? if (Config::get()->ENABLE_FREE_ACCESS && !$current_courseset) : ?>
    <form class="default" action="<?= $controller->link_for('/change_free_access') ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= _('Zugriff für externe Nutzer') ?></legend>
            <div>
                <?= _('Über diese Einstellung können Sie externen Nutzern, die keinen Zugang zum Stud.IP haben, Zugriff auf die Veranstaltung gewähren. Bitte beachten Sie, dass von Kursteilnehmern z.B. im Forum, Dateibereich oder Wiki erstellte Inhalte damit weltweit ohne Anmeldung einsehbar sind. Die Teilnehmerliste ist für externe Nutzer nicht sichtbar.'); ?>
            </div>

            <label for="lesezugriff">
                <input <?= $is_locked['read_level'] ?>
                        id="lesezugriff"
                        type="checkbox" <?= $course->lesezugriff == 0 ? "checked" : "" ?>
                        name="read_level" value="1">
                <?= _('Lesezugriff für nicht in Stud.IP angemeldete Personen erlauben') ?>
            </label>

            <? if (!$is_locked['write_level'] || $course->schreibzugriff == 0): ?>
                <label for="schreibzugriff">
                    <input <?= $is_locked['write_level'] ?>
                            id="schreibzugriff"
                            type="checkbox" <?= $course->schreibzugriff == 0 ? "checked" : "" ?>
                            name="write_level" value="1">
                    <?= _('Schreibzugriff für nicht in Stud.IP angemeldete Personen erlauben') ?>
                </label>
            <? endif ?>
        </fieldset>
        <footer>
            <?= Studip\Button::create(_('Zugriffseinstellung ändern'), 'change_free_access', ['data-dialog' => '']) ?>
        </footer>
    </form>
    <br>
<? endif ?>

<? if (count($all_domains)) : ?>
    <form class="default" action="<?= $controller->link_for('/change_domains') ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= _('Zugelassenene Nutzerdomänen') ?></legend>
            <div>
                <?= _('Bitte geben Sie hier an, welche Nutzerdomänen zugelassen sind.'); ?>
            </div>
            <? foreach ($all_domains as $domain) : ?>
                <label for="user_domain_<?= htmlReady($domain->id) ?>">
                    <input <?= $is_locked['user_domain'] ?>
                            id="user_domain_<?= htmlReady($domain->id) ?>"
                            type="checkbox" <? if (in_array($domain->id, $seminar_domains)) echo 'checked'; ?>
                            name="user_domain[]" value="<?= htmlReady($domain->id) ?>">
                    <?= htmlReady($domain->name) ?>
                </label>
            <? endforeach ?>
        </fieldset>
        <footer>
            <?= Studip\Button::create(_("Nutzerdomänen ändern"), 'change_domains', ['data-dialog' => '']) ?>
        </footer>
    </form>
<? endif ?>
