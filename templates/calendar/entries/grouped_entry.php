<?php
$title   = [];
$heading = [];
$ids     = [];

// check, if at least one entry is visible
$show = false;
foreach ($entry as $element) :
    $title[] = $element['content'];
    if ($element['title']) :
        $heading[] = $element['title'];
    endif;
    $ids[] = $element['id'];
    if ($element['visible']) $show = true;
endforeach;
$element_id = md5(uniqid());
?>

<? if ($show || $show_hidden) : ?>
<div id="schedule_entry_<?= $element_id ?>_<?= $entry[0]['start'] .'/'. $entry[0]['end'] .'/'. implode(',', $ids) .'/'. $day ?>" class="schedule_entry <?= !$show ? 'invisible_entry' : '' ?>"
    style="top: <?= $top ?>px; height: <?= $height ?>px; width: <?= str_replace(',', '.', $width) ?>%<?= ($col > 0) ? ';left:'. str_replace(',', '.', $col * $width) .'%' : '' ?>"
    title="<?= htmlReady(implode(', ', $title)) ?>">

    <a <?= $entry['url'] ? ' href="'.$entry['url'].'"' : '' ?>
        <?= $entry[0]['onClick'] ? 'onClick="STUDIP.Calendar.clickEngine(' . $entry[0]['onClick'] . ', this, event); return false;"' : '' ?>>

    <!-- for safari5 we need to set the height for the dl as well -->
    <dl class="schedule-category<?= $entry[0]['color']?> <?= $calendar_view->getReadOnly() ? '' : 'hover' ?>" style="height: <?= $height ?>px;">
        <dt>
            <?= $entry[0]['start_formatted'] ?> - <?= $entry[0]['end_formatted'] ?>
            <?= (count($heading) ? ', <strong>' . htmlReady(implode(', ', $heading)) . '</strong>' : '') ?>
        </dt>
        <dd>
            <? foreach ($entry as $element) :
                if (!isset($element['visible']) || $element['visible']) : ?>
                <?= htmlReady($element['content']) ?><br>
                <? elseif ($show_hidden) : ?>
                <span class="invisible_entry"><?= htmlReady($element['content']) ?></span><br>
                <? endif ?>
            <? endforeach; /* the elements for this grouped entry */ ?>
        </dd>
    </dl>

    </a>

    <div class="snatch" style="display: none"><div> </div></div>
    <?= $this->render_partial('calendar/entries/icons', compact('element_id')) ?>

</div>
<? endif ?>
