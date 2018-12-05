<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_create') ?>"
      method="post" data-secure>
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Wie sollen Gruppen angelegt werden?') ?>
        </legend>
        <section>
            <label>
                <input type="radio" name="mode" value="numbering" checked
                       onclick="$('.numbering-data').show();$('.course-data').hide();">
                <?= _('Erzeuge beliebig viele Gruppen mit Namenspräfix') ?>
            </label>
            <label>
                <input type="radio" name="mode" value="coursedata"
                       onclick="$('.numbering-data').hide();$('.course-data').show();">
                <?= _('Lege Gruppen zu bestehenden Veranstaltungsdaten an') ?>
            </label>
        </section>
    </fieldset>

    <fieldset>
        <legend>
            <?= _('Lege folgende Gruppen an') ?>
        </legend>
        <section class="numbering-data">
            <label>
                <span class="required">
                    <?= _('Anzahl anzulegender Gruppen') ?>
                </span>
                <input type="number" name="number" value="5" min="1">
            </label>
        </section>
        <section class="numbering-data">
            <label>
                <input type="radio" name="numbering_type" value="1" checked>
                <?= _('Numerische Nummerierung')?>
            </label>
            <label>
                <input type="radio" name="numbering_type" value="2">
                <?= _('Alphabetische Nummerierung')?>
            </label>
        </section>
        <section class="numbering-data">
            <label>
                <?= _('Beginne Nummerierung bei') ?>
                <input type="number" name="startnumber" value="1" min="0">
            </label>
        </section>
        <section class="numbering-data">
            <label class="numbering-data">
                <span class="required">
                    <?= _('Namenspräfix') ?>
                </span>
                <input type="text" name="prefix" maxlength="200" value="<?= _('Gruppe') ?>">
            </label>
        </section>
        <? if ($has_paper_related_topics): ?>
            <section class="course-data">
                <label>
                    <input type="radio" name="createmode" value="paper_related">
                    <?= _('Lege eine Gruppe pro Thema vom Typ "Hausarbeit/Referat" an') ?>
                </label>
            </section>
        <? endif; ?>
        <? if ($has_topics) : ?>
            <section class="course-data">
                <label>
                    <input type="radio" name="createmode" value="topics">
                    <?= _('Lege eine Gruppe pro Thema an') ?>
                </label>
            </section>
        <? endif ?>
        <? if ($has_cycles || $has_singledates) : ?>
            <section class="course-data">
                <label>
                    <input type="radio" name="createmode" value="dates">
                    <?= _('Lege eine Gruppe pro regelmäßiger Zeit/Einzeltermin an') ?>
                </label>
            </section>
        <? endif ?>
        <section class="course-data">
            <label>
                <input type="radio" name="createmode" value="lecturers">
                <?= _('Lege eine Gruppe pro Lehrendem an') ?>
            </label>
        </section>
    </fieldset>

    <fieldset>
        <legend>
            <?= _('Voreinstellungen für alle anzulegenden Gruppen') ?>
        </legend>
        <section>
            <label>
                <?= _('Gruppengröße') ?>
                <input type="number" name="size" value="0" min="0">
            </label>
        </section>
        <section>
            <label>
                <input type="checkbox" name="makefolder" value="1">
                <?= _('Dateiordner anlegen') ?>
            </label>
        </section>
        <section>
            <label>
                <input type="checkbox" name="selfassign" value="1">
                <?= _('Selbsteintrag') ?>
            </label>
        </section>
        <section>
            <label>
                <input type="checkbox" name="exclusive" value="1">
                <?= _('Selbsteintrag in nur eine Gruppe') ?>
            </label>
        </section>
        <section class="col-3">
            <label class="col-3">
                <?= _('Selbsteintrag erlaubt ab') ?>
                <input type="text" data-datetime-picker id="selfassign_start"  size="20"
                       name="selfassign_start" value="<?= date('d.m.Y H:i') ?>">
            </label>
        </section>
        <section class="col-3">
            <label class="col-3">
                <?= _('Selbsteintrag erlaubt bis') ?>
                <input type="text" data-datetime-picker='{">":"#selfassign_start"}' size="20"
                       name="selfassign_end" value="">
            </label>
        </section>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Anlegen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups')) ?>
    </footer>
</form>
<script type="text/javascript" language="JavaScript">
    //<!--
    $('.course-data').hide();
    STUDIP.Statusgroups.initInputs();
    //-->
</script>
