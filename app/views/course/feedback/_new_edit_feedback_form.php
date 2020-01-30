<fieldset>
    <legend>
        <?= _('Grundeigenschaften') ?>
    </legend>
    <label>
        <?= _('Fragestellung') ?>
        <input required type="text" name="question" placeholder="<?= _('Frage') ?>"
            value="<?= htmlReady($feedback->question); ?>">
    </label>
    <label>
        <?= _('Beschreibung') ?>
        <textarea name="description" class="add_toolbar wysiwyg"
            placeholder="<?= _('Optionale Beschreibung') ?>"><?= wysiwygReady($feedback->description); ?></textarea>
    </label>
    <label>
        <input type="checkbox" name="results_visible" value="1" <?= $feedback->results_visible == 1 ? 'checked' : '' ?>>
        <?= _('Feedback Ergebnisse nach Antwort sichtbar') ?>
    </label>
    <label>
        <input id="comment-activated" type="checkbox" name="commentable" value="1" <? if ($this->current_action ==
        'edit_form') {echo ('disabled');} else { echo('data-activates="#comment-only"');}?>
        <?= $feedback->commentable == 1 ? 'checked' : '' ?>>
        <?= _('Abgegebenes Feedback kann einen Kommentar beinhalten') ?>
    </label>
    <label>
        <input id="comment-only" type="checkbox" name="comment_only" value="1"
            <?= $feedback->mode == 0 ? 'checked' : '' ?> <? if ($this->current_action ==
        'edit_form') {echo ('disabled');} else { echo('data-deactivates="#comment-activated, .feedback-mode"');}?>>
        <?= _('Nur Kommentare (keine numerische Bewertung)') ?>
    </label>
</fieldset>
<fieldset>
    <legend>
        <?= _('Bewertungsmodus') ?>
    </legend>
    <label>
        <input class="feedback-mode" type="radio" name="mode" value="1" <?= $feedback->mode == 1 ? 'checked' : '' ?>
            required <? if ($this->current_action ==
        'edit_form') {echo ('disabled');}?>><?= _('Sternbewertung von 1 bis 5') ?>
    </label>
    <label>
        <input class="feedback-mode" type="radio" name="mode" value="2" <?= $feedback->mode == 2 ? 'checked' : '' ?> <?
            if ($this->current_action ==
        'edit_form') {echo ('disabled');}?>><?= _('Sternbewertung von 1 bis 10') ?>
    </label>
</fieldset>