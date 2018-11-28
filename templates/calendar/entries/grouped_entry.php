<?php
# Lifter010: TODO
$color_background = Color::brighten($entry[0]['color'], 20);

$title = $heading = $ids = array();

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
    style="top: <?= $top ?>px; height: <?= $height ?>px; width: <?= $width ?>%<?= ($col > 0) ? ';left:'. ($col * $width) .'%' : '' ?>"
    title="<?= htmlReady(implode(', ', $title)) ?>">

    <a <?= $entry['url'] ? ' href="'.$entry['url'].'"' : '' ?>
        <?= $entry[0]['onClick'] ? 'onClick="STUDIP.Calendar.clickEngine(' . $entry[0]['onClick'] . ', this, event); return false;"' : '' ?>>

    <!-- for safari5 we need to set the height for the dl as well -->
    <dl <?= $calendar_view->getReadOnly() ? '' : 'class="hover"' ?> style="height: <?= $height ?>px;
        border: 1px solid <?= $entry[0]['color'] ?>;
        background-color: <?= $color_background ?>">
        <dt style="background-color: <?= $entry[0]['color'] ?>">
            <?= $entry[0]['start_formatted'] ?> - <?= $entry[0]['end_formatted'] ?>
            <?= (count($heading) ? ', <b>' . htmlReady(implode(', ', $heading)) . '</b>' : '') ?>
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
