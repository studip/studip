<form method="post" class="default" action="<?= $controller->link_for('course/feedback/create' , $feedback->range_id, $feedback->range_type) ?>">
    <?= CSRFProtection::tokenTag() ?>    
    <?= $this->render_partial('course/feedback/_new_edit_feedback_form.php') ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Erstellen')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen')) ?>
    </footer>
</form>