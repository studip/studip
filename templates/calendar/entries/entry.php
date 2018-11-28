<?

$color_background = Color::brighten($entry['color'], 20);

$element_id = md5(uniqid());
?>

<div id="schedule_entry_<?= $element_id ?>_<?= $entry['id'] ?>" class="schedule_entry <?= ((isset($entry['visible']) && !$entry['visible']) ? 'invisible_entry' : '') . ($entry['onClick'] ? " clickable" : "") ?>" style="top: <?= $top ?>px; height: <?= $height ?>px; width: <?= $width ?>%<?= ($col > 0) ? ';left:'. ($col * $width) .'%' : '' ?>" title="<?= htmlReady($entry['title']) ?>">

    <a <?= $entry['url'] ? ' href="'.$entry['url'].'"' : '' ?>
        <?= $entry['onClick'] ? 'onClick="STUDIP.Calendar.clickEngine('. $entry['onClick'].', this, event); return false;"' : '' ?>>

    <!-- for safari5 we need to set the height for the dl as well -->
    <dl <?= $calendar_view->getReadOnly() ? '' : 'class="hover"' ?> style="height: <?= $height - 2 ?>px;
        border: 1px solid <?= $entry['color'] ?>;
        background-color: <?= $color_background ?>;">
        <dt style="background-color: <?= $entry['color'] ?>;">
            <?= floor($entry['start']/100).":".(($entry['start']%100) < 10 ? "0" : "").($entry['start']%100) ?> - <?= floor($entry['end']/100).":".(($entry['end']%100) < 10 ? "0" : "").($entry['end']%100) ?><?= $entry['title'] ? ', <b>'. htmlReady($entry['title']) .'</b>' : '' ?>
        </dt>
        <dd>
            <?= nl2br(htmlReady($entry['content'])) ?><br>
        </dd>
    </dl>

    </a>

    <div class="snatch" style="display: none"><div> </div></div>

    <?= $this->render_partial('calendar/entries/icons', compact('element_id')) ?>
</div>
