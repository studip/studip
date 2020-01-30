<form method="post" class="default" action="<?= $controller->link_for('course/feedback/edit', $feedback->id) ?>">
    <?= CSRFProtection::tokenTag() ?>    
    <?= $this->render_partial('course/feedback/_new_edit_feedback_form.php', ['feedback' => $feedback]) ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\Button::createCancel() ?>
    </footer>
</form>