<?
$etask = $vote->etask;
?>
<h3>
    <?= formatReady($etask->description) ?>
</h3>


<ul class="clean">
    <? foreach ($vote->answers as $answer) : ?>
        <? if (trim($answer['answerdata']['text'])) : ?>
        <li style="border: #d0d7e3 thin solid; margin: 10px; padding: 10px;">
            <? if (!$vote->questionnaire['anonymous']) : ?>
                <div style="margin-bottom: 7px;">
                    <? if ($answer['user_id'] && $answer['user_id'] !== "nobody") : ?>
                        <?= Avatar::getAvatar($answer['user_id'])->getImageTag(Avatar::SMALL) ?>
                        <span style="color: #888888; font-weight: bold; font-size: 0.8em;"><?= get_fullname($answer['user_id']) ?></span>
                    <? else : ?>
                        <?= Avatar::getAvatar($answer['user_id'])->getImageTag(Avatar::SMALL) ?>
                        <span style="color: #888888; font-weight: bold; font-size: 0.8em;"><?= get_fullname($answer['user_id']) ?></span>
                    <? endif ?>
                </div>
            <? endif ?>
            <?= formatReady($answer['answerdata']['text']) ?>
        </li>
        <? endif ?>
    <? endforeach ?>
</ul>


