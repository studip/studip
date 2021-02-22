<div class="missing_course">
    <span class="content">
        <?= sprintf(_('Der Termin am %s findet nicht statt.'), $formatted_date); ?>
    </span>
    <br><?= _('Kommentar') ?>: <?= htmlReady($ex_termin->getComment()); ?>
</div>
