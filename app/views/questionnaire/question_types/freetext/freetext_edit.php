<?php
    $etask = $vote->etask;
?>
<label>
    <?= _('Frage') ?>
    <textarea name="questions[<?= $vote->getId() ?>][description]"
              class="size-l wysiwyg"
              placeholder="<?= _('Erzählen Sie uns ...') ?>"
    ><?= wysiwygReady($etask->description) ?></textarea>
</label>

<input type="hidden" name="questions[<?= $vote->getId() ?>][options][mandatory]" value="0">
<label>
    <input type="checkbox"
           name="questions[<?= $vote->getId() ?>][options][mandatory]"
           value="1"<?= isset($etask->options['mandatory']) && $etask->options['mandatory'] ? 'checked' : '' ?>>
    <?= _("Pflichtfrage") ?>
</label>
