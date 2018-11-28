<div id="schedule_icons_<?= $element_id ?>" class="schedule_icons">
    <? if (is_array($entry['icons'])) foreach ($entry['icons'] as $icon) : ?>
        <? if($icon['url']) : ?>
        <a href="<?= $icon['url'] ?>" <?= $icon['onClick'] ? 'onClick="STUDIP.Calendar.clickEngine('. $icon['onClick'].', this, event); return false;"' : '' ?>>
            <?= Assets::img($icon['image'], array('title' => htmlReady($icon['title']), 'alt' => htmlReady($icon['title']))) ?>
        </a>
        <? else : ?>
        <?= Assets::img($icon['image'], array('title' => htmlReady($icon['title']))) ?>
        <? endif; ?>
    <? endforeach ?>
</div>
