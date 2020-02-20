<? if ($feedback->mode != 0) : ?>
<?php
        $n = 5;
        if ($feedback->mode == 2) {
            $n = 10;
        }
    ?>
<div class="rating">
    <p><?= _('Bewertung') ?></p>
    <? for ($i = 1; $i < $n+1; $i++) : ?>
    <label class="star-rating undecorated <?= $i <= $entry->rating || $i === 1 ? ' checked' : '' ?>">
        <input class="star-rating-input" name="rating" value="<?= $i ?>" type="radio"
            <?= $i == $entry->rating ? ' selected' : '' ?> required/>
        <?= Icon::create('star', 'clickable') ?>
    </label>
    <? endfor; ?>
</div>
<? endif; ?>
<? if ($feedback->commentable == 1) : ?>
<label>
    <?= _('Kommentar') ?>
    <textarea name="comment"><?= htmlReady($entry->comment) ?></textarea>
</label>
<? endif; ?>
<div>
    <?= Studip\Button::createAccept(_('Absenden'), 'add', ['class' => 'feedback-entry-submit']) ?>
    <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['class' => 'feedback-entry-cancel']) ?>
</div>
