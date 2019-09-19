<?
$etask = $vote->etask;
$taskAnswers = $etask->task['answers'];
$numTaskAnswers = count($taskAnswers);

$results = array_fill(0, $numTaskAnswers, 0);
$results_users = array_fill(0, $numTaskAnswers, []);

if ($numTaskAnswers > 0) {
    foreach ($answers as $answer) {
        if ($etask->task['type'] === 'multiple') {
            if (is_array($answer['answerdata']['answers']) || $answer['answerdata']['answers'] instanceof Traversable) {
                foreach ($answer['answerdata']['answers'] as $a) {
                    $results[(int)$a]++;
                    $results_users[(int)$a][] = $answer['user_id'];
                }
            }
        } else {
            $results[(int) $answer['answerdata']['answers']]++;
            $results_users[(int) $answer['answerdata']['answers']][] = $answer['user_id'];
        }
    }
}

$ordered_results = $results;
arsort($ordered_results);
$ordered_answer_options = [];
$ordered_users = [];
foreach ($ordered_results as $index => $value) {
    if ($value > 0) {
        $ordered_answer_options[] = strip_tags(formatReady($taskAnswers[$index]['text']));
    } else {
        unset($ordered_results[$index]);
    }
}
rsort($ordered_results);
?>

<h3>
    <?= Icon::create('vote', 'info')->asImg(20, ['class' => 'text-bottom']) ?>
    <?= formatReady($etask->description) ?>
</h3>

<? if (count($vote->answers) > 0 && $numTaskAnswers > 0) : ?>
    <div style="max-height: none; opacity: 1;"
         id="questionnaire_<?= $vote->getId() ?>_chart"
         class="ct-chart"></div>

    <script>
     <?= Request::isAjax()
       ? 'jQuery(document).add(".questionnaire_results").one("dialog-open", function () {'
       : 'jQuery(function () {' ?>
        var data = {
            labels: <?= json_encode($ordered_answer_options) ?>,
            series: [<?= json_encode($ordered_results) ?>]
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

<table class="default nohover">
    <tbody>
        <? $countAnswers = $vote->questionnaire->countAnswers() ?>
        <? foreach ($taskAnswers as $key => $answer) : ?>
        <tr>
            <? $percentage = $countAnswers ? round((int) $results[$key] / $countAnswers * 100) : 0 ?>

            <td style="text-align: right; background-size: <?= $percentage ?>% 100%; background-position: right center; background-image: url('<?= Assets::image_path("vote_lightgrey.png") ?>'); background-repeat: no-repeat;" width="50%">
                <strong><?= formatReady($answer['text']) ?></strong>
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
