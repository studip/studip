<? if (count($dozenten) > 0) : ?>
    <?= $this->render_partial('course/members/dozent_list') ?>
<? endif ?>

<? if (count($tutoren) > 0) : ?>
    <br />
    <?= $this->render_partial('course/members/tutor_list') ?>
<? endif ?>

<? if ($is_tutor && $semAdmissionEnabled) : ?>
    <p style="float: right">
        <? //TODO?>
        <strong><?= _('Teilnahmebeschränkte Veranstaltung') ?></strong> -
        <?= _('max. Teilnehmendenanzahl') ?> <?= $course->admission_turnout ?>,
        <?= _('davon belegt') ?>: <?= (count($autoren) + count($users) + count($accepted)) ?>
    </p>
    <div class="clear"></div>
<? endif ?>

<? if(count($autoren) >0) : ?>
    <br />
    <?= $this->render_partial('course/members/autor_list') ?>
<? endif ?>

<? if (count($users) > 0) : ?>
    <br />
    <?= $this->render_partial('course/members/user_list') ?>
<? endif ?>

<? if ($is_tutor && count($accepted) > 0) : ?>
    <?= $this->render_partial('course/members/accepted_list') ?>
<? endif ?>

<? if ($is_tutor && count($awaiting) > 0) : ?>
    <?= $this->render_partial('course/members/awaiting_list') ?>
<? endif ?>
