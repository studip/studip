<? if (!empty($feedback_elements)) : ?>
<article class="studip">
    <header>
        <h1>
            <?= Icon::create('star','info') ?>
            <?= _('Feedback') ?>
        </h1>
        <? if($create_perm) : ?>
        <nav>
            <a href="<?= $controller->link_for('course/feedback/create_form/' . $range_id . '/' . $range_type) ?>"
                title="<?= _('Neues Feedback-Element') ?>" class="feedback-add" data-id="<?= $feedback->id ?>"
                data-dialog="">
                <?= Icon::create('add'); ?>
            </a>
        </nav>
        <? endif; ?>
    </header>
    <? foreach($feedback_elements as $feedback) : ?>
        <?= $this->render_partial('course/feedback/_feedback_stream.php' , ['feedback' => $feedback]) ?>
    <? endforeach; ?>
</article>
<? endif; ?>