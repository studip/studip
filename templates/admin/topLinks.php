<? if ($adminList) : ?>
    <? foreach ($adminList as $index => $seminar) {
        if ($next_one === "next_round") {
            $next_one = $index;
            break;
        }
        if ($seminar['Seminar_id'] === $course_id) {
            $next_one = "next_round";
        } else {
            $last_one = $index;
        }
    }
    if ($next_one === "next_round") {
        unset($next_one);
    }
    ?>
<div style="width: 70%; margin: 10px; margin-left: auto; margin-right: auto;text-align: center;" id="admin_top_links">
    <? if (isset($last_one)) : ?>
    <div style="float: left;">
        <a href="<?= URLHelper::getLink("?#admin_top_links", ['cid' => $adminList[$last_one]['Seminar_id']]) ?>" title="<?= htmlReady($adminList[$last_one]['Name']) ?>">
            <?= Icon::create('arr_1left', 'clickable')->asImg(['class' => "text-bottom"]) ?>
            <?= _("zurück") ?>
        </a>
    </div>
    <? endif ?>
    <? if (isset($next_one)) : ?>
    <div style="float: right;">
        <a href="<?= URLHelper::getLink("?#admin_top_links", ['cid' => $adminList[$next_one]['Seminar_id']]) ?>" title="<?= htmlReady($adminList[$next_one]['Name']) ?>">
            <?= _("vor") ?>
            <?= Icon::create('arr_1right', 'clickable')->asImg(['class' => "text-bottom"]) ?>
        </a>
    </div>
    <? endif ?>
    <div>
        <a href="<?= URLHelper::getLink("adminarea_start.php", ['list' => "TRUE"]) ?>">
            <?= Icon::create('arr_1up', 'clickable')->asImg(['class' => "text-bottom"]) ?>
            <?= _("Liste") ?>
        </a>
    </div>
</div>
<? endif ?>