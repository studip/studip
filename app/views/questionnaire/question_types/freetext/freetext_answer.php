<?php
    $etask = $vote->etask;

    $answer = $vote->getMyAnswer();
    $answerdata = $answer['answerdata'] ? $answer['answerdata']->getArrayCopy() : [];
?>

<label>
    <div>
        <?= Icon::create('guestbook', Icon::ROLE_INFO)->asImg(20, ['class' => 'text-bottom']) ?>
        <? if (isset($etask->options['mandatory']) && $etask->options['mandatory']) : ?>
            <?= Icon::create('star', Icon::ROLE_ATTENTION)->asImg(20, ['class' => 'text-bottom', 'title' => _("Pflichtantwort")]) ?>
        <? endif ?>
        <?= formatReady($etask->description) ?>
    </div>
    <textarea name="answers[<?= $vote->getId() ?>][answerdata][text]"
              <?= isset($etask->options['mandatory']) && $etask->options['mandatory'] ? "required" : "" ?>
              ><?= htmlReady($answerdata['text']) ?></textarea>
</label>
