<?

$element_id = md5(uniqid());
?>

<div id="schedule_entry_<?= $element_id ?>_<?= $entry['id'] ?>" class="schedule_entry <?= ((isset($entry['visible']) && !$entry['visible']) ? 'invisible_entry' : '') . ($entry['onClick'] ? " clickable" : "") ?>" style="top: <?= $top ?>px; height: <?= $height ?>px; width: <?= str_replace(',', '.', $width) ?>%<?= ($col > 0) ? ';left:'. str_replace(',', '.', $col * $width) .'%' : '' ?>" title="<?= htmlReady($entry['title']) ?>">

    <a <?= $entry['url'] ? ' href="'.$entry['url'].'"' : '' ?>
        <?= $entry['onClick'] ? 'onClick="STUDIP.Calendar.clickEngine('. $entry['onClick'].', this, event); return false;"' : '' ?>>

    <!-- for safari5 we need to set the height for the dl as well -->
    <dl class="hidden-medium-up schedule-category<?= $entry['color']?> <?= $calendar_view->getReadOnly() ? '' : 'hover' ?>" style="height: <?= $height ?>px;">
        <dt>
            <?= nl2br(htmlReady($entry['content'])) ?><br>
        </dt>
        <dd>
            <?= floor($entry['start']/100).":".(($entry['start']%100) < 10 ? "0" : "").($entry['start']%100) ?> - <?= floor($entry['end']/100).":".(($entry['end']%100) < 10 ? "0" : "").($entry['end']%100) ?><?= $entry['title'] ? ', <b>'. htmlReady($entry['title']) .'</b>' : '' ?>
        </dd>
    </dl>
    <dl class="hidden-small-down schedule-category<?= $entry['color']?> <?= $calendar_view->getReadOnly() ? '' : 'hover' ?>" style="height: <?= $height ?>px;">
        <dt>
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
