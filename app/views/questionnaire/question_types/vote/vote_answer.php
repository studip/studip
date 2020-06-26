<?
$etask = $vote->etask;

$taskAnswers = $etask->task['answers'];
$indexMap = count($taskAnswers) ? range(0, count($taskAnswers) - 1) : [];
if ($etask->options['randomize']) {
    shuffle($indexMap);
}

$response = $vote->getMyAnswer();
$responseData = $response['answerdata'] ? $response['answerdata']->getArrayCopy() : [];
?>
<div <?= isset($etask->options['mandatory']) && $etask->options['mandatory'] ? ' class="mandatory"' : "" ?>>
    <h3>
        <?= Icon::create(is_a($vote, 'Test') ? 'test' : 'vote', 'info')->asImg(20, ['class' => 'text-bottom']) ?>
        <? if (isset($etask->options['mandatory']) && $etask->options['mandatory']) : ?>
            <?= Icon::create('star', Icon::ROLE_ATTENTION)->asImg(20, ['class' => 'text-bottom', 'title' => _("Pflichtantwort")]) ?>
        <? endif ?>
        <?= formatReady($etask->description) ?>
    </h3>

    <div class="hidden invalidation_notice">
        <?= _("Diese Frage muss beantwortet werden.") ?>
    </div>

    <ul class="clean">
        <? foreach ($indexMap as $index) : ?>
            <li>
                <label>

                    <? if ($etask->task['type'] === 'multiple') : ?>

                        <input type="checkbox"
                               name="answers[<?= $vote->getId() ?>][answerdata][answers][<?= $index ?>]"
                               value="<?= $index ?>"
                               <?= isset($responseData['answers']) && in_array($index, (array) $responseData['answers']) ? 'checked' : '' ?>>

                    <? else : ?>

                        <input type="radio"
                               name="answers[<?= $vote->getId() ?>][answerdata][answers]"
                               value="<?= $index ?>"
                               <?= isset($responseData['answers']) && $index == $responseData['answers'] ? 'checked' : '' ?>>
                    <? endif ?>

                    <?= formatReady($taskAnswers[$index]['text']) ?>

                </label>
            </li>
        <? endforeach ?>
    </ul>
</div>
