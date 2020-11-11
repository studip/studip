<?php
$sem = Seminar::getInstance($show_entry['id']);
?>
<form class="default"
      action="<?= $controller->link_for('calendar/schedule/editseminar/' . $show_entry['id'] . '/' . $show_entry['cycle_id']) ?>"
      method="post" name="edit_entry">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Stundenplaneintrag') ?>
        </legend>

        <?= $this->render_partial('calendar/schedule/_colorpicker.php', [
            'selected' => $show_entry['color'],
        ]) ?>

        <? if ($show_entry['type'] == 'virtual') : ?>
            <section>
                <span
                    style="color: red; font-weight: bold"><?= _('Dies ist lediglich eine vorgemerkte Veranstaltung') ?></span><br><br>
            </section>
        <? endif ?>

        <section>
            <strong><?= _('Veranstaltungsnummer') ?></strong><br>
            <?= htmlReady($sem->getNumber()) ?>
        </section>

        <section>
            <strong><?= _('Name') ?></strong><br>
            <?= htmlReady($sem->getName()) ?>
        </section>

        <section>
            <strong><?= _('Lehrende') ?></strong><br>
            <? $pos = 0;
            foreach ($sem->getMembers('dozent') as $dozent) :?>
                <?php if ($pos > 0) echo ', '; ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $dozent['username']]) ?>">
                    <?= htmlReady($dozent['fullname']) ?>
                </a>
                <? $pos++ ?>
            <? endforeach ?>
        </section>

        <section>
            <strong><?= _('Veranstaltungszeiten') ?></strong><br>
            <?= $sem->getDatesHTML(['show_room' => true]) ?><br>
        </section>

        <section>
            <?= Icon::create('link-intern') ?>
            <? if ($show_entry['type'] == 'virtual') : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $show_entry['id']]) ?>">
                    <?= _('Zur Veranstaltung') ?>
                </a>
                <br>
            <? else : ?>
                <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $show_entry['id']]) ?>">
                    <?= _('Zur Veranstaltung') ?>
                </a>
                <br>
            <? endif ?>
        </section>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), ['style' => 'margin-right: 20px']) ?>

        <? if (!$show_entry['visible']) : ?>
            <?= Studip\LinkButton::create(
                _('Einblenden'),
                $controller->url_for(
                    'calendar/schedule/bind/' . $show_entry['id'] . '/' . $show_entry['cycle_id'] . '/',
                    ['show_hidden' => '1']
                ),
                ['style' => 'margin-right: 20px']) ?>
        <? else : ?>
            <?= Studip\LinkButton::create(
                $show_entry['type'] == 'virtual' ? _('Löschen') : _('Ausblenden'),
                $controller->url_for('calendar/schedule/unbind/' . $show_entry['id'] . '/' . $show_entry['cycle_id']),
                ['style' => 'margin-right: 20px']) ?>
        <? endif ?>

        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for('calendar/schedule'),
            ['onclick' => "jQuery('#edit_sem_entry').fadeOut('fast'); STUDIP.Calendar.click_in_progress = false; return false"]) ?>
    </footer>
</form>
