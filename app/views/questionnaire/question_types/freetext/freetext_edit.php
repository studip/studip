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
