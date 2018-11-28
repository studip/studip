<?= $admission_error ?>
<? if ($courseset_message) : ?>
<p>
    <?= $courseset_message ?>
</p>
<? endif ?>
<? foreach(PageLayout::getMessages() as $pm) : ?>
    <?= $pm ?>
<? endforeach; ?>

<? if ($admission_form): ?>
    <form name="apply_admission" action="<?= $controller->link_for("/apply/{$course_id}") ?>" method="post">
        <?= $admission_form ?>
        <div data-dialog-button>
            <?= Studip\Button::createAccept(_('OK'), 'apply', ['data-dialog' => 'size=big']) ?>
            <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel') ?>
        </div>
        <?= CSRFProtection::tokenTag() ?>
    </form>
<? elseif ($priocourses): ?>
    <form name="claim_admission" action="<?= $controller->link_for("/claim/{$course_id}") ?>" method="post" class="default">
    <? if (is_array($priocourses)): ?>
        <?= $this->render_partial('course/enrolment/_priocourses.php') ?>

        <div data-dialog-button>
            <?= Studip\Button::createAccept(_('Speichern'), 'claim', ['data-dialog' => 'size=big']) ?>
            <?= Studip\Button::createCancel(_('Schließen'), 'cancel') ?>
        </div>
    <? else : ?>
        <input type="hidden" name="courseset_claimed" value="<?= $already_claimed ? 0 : 1 ?>" >
        <div>
            (<?= sprintf(_("max. Teilnehmendenanzahl: %s / Anzahl der Anmeldungen: %s"), $priocourses->admission_turnout, $num_claiming) ?>)
        </div>

        <div data-dialog-button>
        <? if (!$already_claimed) :?>
            <?= Studip\Button::createAccept(_('Zur Platzverteilung anmelden'), 'claim', ['data-dialog' => 'size=big']); ?>
        <? else : ?>
            <?= Studip\Button::create(_('Von der Platzverteilung abmelden'), 'claim', ['data-dialog' => 'size=big']); ?>
        <? endif ?>

            <?= Studip\Button::createCancel(_('Schließen'), 'cancel') ?>
        </div>
    <? endif ?>
    <?= CSRFProtection::tokenTag() ?>
    </form>
<? elseif (!$enrol_user): ?>
    <div data-dialog-button>
        <?= Studip\LinkButton::createAccept(
            _('OK'),
            URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $course_id])
        ) ?>
    </div>
<? elseif ($confimed): ?>
    <div data-dialog-button>
        <?=Studip\LinkButton::createAccept(
            _('Zur Veranstaltung'),
            URLHelper::getLink('seminar_main.php', ['auswahl' => $course_id])
        ) ?>
    </div>
<? endif ?>

<script>STUDIP.enrollment();</script>
