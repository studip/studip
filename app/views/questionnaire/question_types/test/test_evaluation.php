<?
$etask = $vote->etask;
$taskAnswers = $etask->task['answers'];
$numTaskAnswers = count($taskAnswers);

$results = array_fill(0, $numTaskAnswers, 0);
$results_users = array_fill(0, $numTaskAnswers, []);
$users = [];

if ($numTaskAnswers > 0) {
    foreach ($answers as $answer) {
        if ($etask->task['type'] === 'multiple') {
            foreach ($answer['answerdata']['answers'] as $a) {
                $results[(int) $a]++;
                $results_users[(int) $a][] = $answer['user_id'];
                $users[] = $answer['user_id'];
            }
        } else {
            $results[(int) $answer['answerdata']['answers']]++;
            $results_users[(int) $answer['answerdata']['answers']][] = $answer['user_id'];
            $users[] = $answer['user_id'];
        }
    }
}

$users = array_unique($users);
$labels = array_map(function ($answer) { return strip_tags(formatReady($answer['text'])); }, $taskAnswers->getArrayCopy());
?>

<h3>
    <?= Icon::create('test', 'info')->asImg(20, ['class' => 'text-bottom']) ?>
    <?= formatReady($etask->description) ?>
</h3>

<? if (count($vote->answers) > 0 && $numTaskAnswers > 0) : ?>
    <div style="max-height: none; opacity: 1;"
         id="questionnaire_<?= $vote->getId() ?>_chart"
         class="ct-chart"></div>

    <script>
     <?= Request::isAjax()
       ? 'jQuery(document).one("dialog-open", function () {'
       : 'jQuery(function () {' ?>
        var data = {
            labels: <?= json_encode($labels) ?>,
            series: [<?= json_encode($results) ?>]
        };
        <? if ($etask->task['type'] === 'multiple') : ?>
            new Chartist.Bar(
                '#questionnaire_<?= $vote->getId() ?>_chart',
                data,
                { onlyInteger: true, axisY: { onlyInteger: true } }
            );
        <? else : ?>
            data.series = data.series[0];
            new Chartist.Pie(
                '#questionnaire_<?= $vote->getId() ?>_chart',
                data,
                { labelPosition: 'outside' }
            );
        <? endif ?>
    });
    </script>
<? endif ?>
<? if (in_array($GLOBALS['user']->id, $users) || is_array($anonAnswers)) : ?>
<? $correctAnswered = is_array($anonAnswers) ? $vote->correctAnswered('anonymous', $anonAnswers) : $vote->correctAnswered()?>
    <div style="max-height: none; opacity: 1; font-size: 1.4em; text-align: center;">
        <? if ($correctAnswered) : ?>
            <?= MessageBox::success(_("Richtig beantwortet!")) ?>
        <? else : ?>
            <?= MessageBox::error(_("Falsch beantwortet!")) ?>
        <? endif ?>
    </div>
<? endif ?>

<table class="default nohover">
    <tbody>
        <? $countAnswers = $vote->questionnaire->countAnswers() ?>
        <? $userAnswer = is_array($anonAnswers) ? @$anonAnswers[0]['answerdata'] : @$answers->findBy('user_id', $GLOBALS['user']->id)->first()->answerdata ?>
        <? if ($userAnswer instanceOf StudipArrayObject) $userAnswer = $userAnswer->getArrayCopy() ?>
        <? foreach ($taskAnswers as $key => $answer) : ?>
          <tr class="<?= $data['correctanswer'] ? 'correct' : 'incorrect' ?>">
            <? $percentage = $countAnswers ? round((int) $results[$key] / $countAnswers * 100) : 0 ?>

            <td style="text-align: right; background-size: <?= $percentage ?>% 100%; background-position: right center; background-image: url('<?= Assets::image_path("vote_lightgrey.png") ?>'); background-repeat: no-repeat;" width="50%">
                <strong><?= formatReady($answer['text']) ?></strong>
                <? if ($userAnswer) : ?>
                    <?= Icon::create(in_array($key, $userAnswer['answers']) ? 'checkbox-checked' : 'checkbox-unchecked', 'info')->asImg( ['class' => 'text-bottom']) ?>
                <? endif ?>
                <? if ($answer['score'] > 0) : ?>
                    <?= Icon::create('accept', 'status-green', ['title' => _('Diese Antwort ist richtig')])->asImg( ['class' => 'text-bottom']) ?>
                <? else : ?>
                    <?= Icon::create('decline', 'status-red', ['title' => _('Eine falsche Antwort')])->asImg( ['class' => 'text-bottom']) ?>
                <? endif ?>
            </td>

            <td style="white-space: nowrap;">
                (<?= $percentage ?>% | <?= (int) $results[$key] ?>/<?= $countAnswers ?>)
            </td>

            <td width="50%">
                <? if (!$vote->questionnaire['anonymous'] && $results[$key]) : ?>

                    <? $users = SimpleCollection::createFromArray(
                        User::findMany($results_users[$key])); ?>

                    <? foreach ($results_users[$key] as $index => $user_id) : ?>

                        <? $user = $users->findOneBy('user_id', $user_id); ?>

                        <? if ($user) : ?>
                            <a href="<?= URLHelper::getLink(
                                     'dispatch.php/profile',
                                     ['username' => $user->username]
                                     ) ?>">
                                <?= Avatar::getAvatar($user_id, $user->username)->getImageTag(
                                    Avatar::SMALL,
                                    ['title' => $user->getFullname('no_title')]
                                ) ?>
                                <? if (count($results_users[$key]) < 4) : ?>
                                    <?= htmlReady($user->getFullname('no_title')) ?>
                                <? endif ?>
                            </a>
                        <? endif ?>
                    <? endforeach ?>
                <? endif ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
