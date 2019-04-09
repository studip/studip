<?
# Lifter010: TODO

use Studip\Button, Studip\LinkButton;

$sem = Seminar::getInstance($show_entry['id']);

?>
<form class="default" action="<?= $controller->url_for('calendar/schedule/editseminar/'. $show_entry['id'] .'/'. $show_entry['cycle_id'] ) ?>" method="post" name="edit_entry">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Stundenplaneintrag') ?>
        </legend>

        <section>
            <b><?= _("Farbe des Termins") ?></b><br>
            <? foreach ($GLOBALS['PERS_TERMIN_KAT'] as $data) : ?>
            <span style="background-color: <?= $data['color'] ?>; vertical-align: middle; padding: 3px">
                <input type="radio" name="entry_color" value="<?= $data['color'] ?>" <?= ($data['color'] == $show_entry['color']) ? 'checked="checked"' : '' ?>>
            </span>
            <? endforeach ?>
        </section>

        <? if ($show_entry['type'] == 'virtual') : ?>
            <section>
                <span style="color: red; font-weight: bold"><?= _("Dies ist lediglich eine vorgemerkte Veranstaltung") ?></span><br><br>
            </section>
        <? endif ?>

        <section>
            <b><?= _("Veranstaltungsnummer") ?></b><br>
            <?= htmlReady($sem->getNumber()) ?>
        </section>

        <section>
            <b><?= _("Name") ?></b><br>
            <?= htmlReady($sem->getName()) ?>
        </section>


        <section>
            <b><?= _("Dozenten") ?></b><br>
            <? $pos = 0;foreach ($sem->getMembers('dozent') as $dozent) :
                if ($pos > 0) echo ', ';
                ?><a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $dozent['username']) ?>"><?= htmlReady($dozent['fullname']) ?></a><?
                $pos++;
            endforeach ?>
        </section>

        <section>
            <b><?= _("Veranstaltungszeiten") ?></b><br>
            <?= $sem->getDatesHTML(['show_room' => true]) ?><br>
        </section>

        <section>
            <?= Icon::create('link-intern', 'clickable')->asImg() ?>
            <? if ($show_entry['type'] == 'virtual') : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/course/details/?sem_id='. $show_entry['id']) ?>"><?=_("Zur Veranstaltung") ?></a><br>
            <? else : ?>
                <a href="<?= URLHelper::getLink('seminar_main.php?auswahl='. $show_entry['id']) ?>"><?=_("Zur Veranstaltung") ?></a><br>
            <? endif ?>
        </section>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('Speichern'), ['style' => 'margin-right: 20px']) ?>

        <? if (!$show_entry['visible']) : ?>
            <?= LinkButton::create(_('Einblenden'),
                                   $controller->url_for('calendar/schedule/bind/'. $show_entry['id'] .'/'. $show_entry['cycle_id'] .'/'. '?show_hidden=1'),
                                   ['style' => 'margin-right: 20px']) ?>
        <? else : ?>
            <?= LinkButton::create($show_entry['type'] == 'virtual' ? _('Löschen') : _('Ausblenden'),
                                   $controller->url_for('calendar/schedule/unbind/'. $show_entry['id'] .'/'. $show_entry['cycle_id']),
                                   ['style' => 'margin-right: 20px']) ?>
        <? endif ?>

        <?= LinkButton::createCancel(_('Abbrechen'),
                                     $controller->url_for('calendar/schedule'),
                                     ['onclick' => "jQuery('#edit_sem_entry').fadeOut('fast'); STUDIP.Calendar.click_in_progress = false; return false"]) ?>
    </div>
</form>
