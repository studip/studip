<form action="<?= $controller->url_for('course/timesrooms/saveComment/' . $termin->id) ?>"
      method="post" class="default" <?= Request::int('fromDialog') ? 'data-dialog="size=big"' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('course/timesrooms/_cancel_form.php', compact('termin')) ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Übernehmen'), 'editDeletedSingleDate') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), '?#' . $termin_id) ?>
    </footer>
</form>