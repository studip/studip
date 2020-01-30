<form method="post" class="default feedback-entry-add"
    action="<?= $controller->link_for('course/feedback/entry_edit', $entry->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <h2><?= _('Feedback bearbeiten') ?></h2>
    <?= $this->render_partial('course/feedback/_add_edit_entry_form.php' , ['entry' => $entry, 'feedback' => $feedback]) ?>
</form>