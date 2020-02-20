<? if ($feedback->description != '') : ?>
<section class="feedback-description">
    <h2><?= _('Beschreibung') ?></h2>
    <p><?= formatReady($feedback->description) ?></p>
</section>
<? endif; ?>
<? if (($feedback->results_visible == 1 && !$feedback->isFeedbackable()) || $admin_perm) : ?>
    <?= $this->render_partial('course/feedback/_results.php' , ['feedback' => $feedback]) ?>
<? endif; ?>
<? if ($feedback->getOwnEntry()) : ?>
<section class="feedback-entries">
    <h2><?= _('Mein Feedback') ?></h2>
    <?= $this->render_partial('course/feedback/_entry.php' , ['entry' => $feedback->getOwnEntry()]) ?>
</section>
<? endif; ?>
<? if (
        count($feedback->entries) > 0
        && (($feedback->results_visible == 1
        && !$feedback->isFeedbackable())
        || $admin_perm)
    ) : ?>
<section class="feedback-entries">
    <h2>
    <? if (
        !$feedback->isOwner()
        && !$feedback->isFeedbackable()
    ) : ?>
    <?= _('Andere Einträge') ?>
    <? else : ?>
    <?= _('Einträge') ?>
    <? endif; ?>
    </h2>
    <? foreach($feedback->entries as $entry) : ?>
    <? if ($entry->user_id !== $GLOBALS['user']->id) : ?>
    <?= $this->render_partial('course/feedback/_entry.php' , ['entry' => $entry]) ?>
    <? endif; ?>
    <? endforeach; ?>
</section>
<? endif; ?>
<? if ($feedback->isFeedbackable()) : ?>
<form method="post" class="default feedback-entry-add"
    action="<?= $controller->link_for('course/feedback/entry_add', $feedback->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <h2><?= _('Feedback geben') ?></h2>
    <?= $this->render_partial('course/feedback/_add_edit_entry_form.php' , ['feedback' => $feedback]) ?>
</form>
<? endif; ?>
