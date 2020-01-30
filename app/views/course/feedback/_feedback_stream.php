<article class="studip toggle feedback-stream" id="feedback-stream-<?= $feedback->id ?>" data-id="<?= $feedback->id ?>">
    <header>
        <h1>
            <a href="<?= $controller->link_for('course/feedback/view/' . $feedback->id) ?>">
                <? if ($feedback->mode != 0 && ((!$feedback->isFeedbackable() && $feedback->results_visible == 1) || $admin_perm) && count($feedback->entries) > 0) : ?>
                <?= Icon::create('star') ?>
                <span class="mean"><?= $feedback->getMeanOfRating() ?></span>
                <? endif; ?>
                <?= htmlReady($feedback->question) ?>
            </a>
    
            <? if ($feedback->isOwner()) : ?>
                <?= Icon::create('decline', 'info', ['title' => _('Das Feedback-Element wurde von Ihnen erstellt, daher können Sie kein Feedback geben'),]); ?>
            <? elseif (!$feedback->isFeedbackable()) : ?>
                <?= Icon::create('accept', 'status-green', ['title' => _('Bereits Feedback gegeben'),]); ?>
            <? endif; ?>
        </h1>
        <? if ($admin_perm) : ?>
        <nav>
            <a href="<?= $controller->link_for('course/feedback/edit_form/' . $feedback->id) ?>"
                title="<?= _('Bearbeiten') ?>" class="feedback-edit" data-id="<?= $feedback->id ?>"
                data-dialog="">
                <?= Icon::create('edit') ?>
            </a>
            <a href="<?= $controller->link_for('course/feedback/delete/' . $feedback->id) ?>" title="<?= _('Löschen') ?>"
                class="feedback-delete" data-id="<?= $feedback->id ?>"
                data-confirm="<?= _('Feedback-Element und dazugehörige Einträge löschen?') ?>">
                <?= Icon::create('trash') ?>
            </a>
        </nav>
        <? endif; ?>
    </header>
    <div class="feedback-view">
        <?= $this->render_partial('course/feedback/_feedback.php' , ['feedback' => $feedback]) ?>
    </div>
</article>