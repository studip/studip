<article class="studip toggle <?= ContentBoxHelper::classes($termin['id']) ?>" id="<?= $termin['id'] ?>">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::href($termin['id']) ?>">
                <?= Icon::create('date', 'inactive')->asImg(['class' => 'text-bottom']) ?>
                <?= htmlReady($termin['title']) ?>
            </a>
        </h1>
        <nav>
            <span>
                <?= $termin['room'] ? _('Raum') . ': ' . formatReady($termin['room']) : '' ?>
            </span>
            <? if($admin && $isProfile && $termin['type'] === 'CalendarEvent'): ?>
            <a href="<?= URLHelper::getLink('dispatch.php/calendar/single/edit/' . $termin['range_id'] . '/' . $termin['event_id'], ['source_page' => 'dispatch.php/profile']) ?>">
                <?= Icon::create('edit', 'clickable')->asImg(['class' => 'text-bottom']) ?>
            </a>
            <? endif; ?>
        </nav>
    </header>
    <div>
        <? $themen = $termin['topics'] ? : [] ?>
        <? if ($termin['description'] || count($themen)) : ?>
        <p><?= formatReady($termin['description']) ?></p>
        <? if (count($themen)) : ?>
            <? foreach ($themen as $thema) : ?>
                <h3>
                    <?= Icon::create('topic', Icon::ROLE_INFO)->asImg(20, ['class' => "text-bottom"]) ?>
                    <?= htmlReady($thema['title']) ?>
                </h3>
                <div>
                    <?= formatReady($thema['description']) ?>
                </div>
            <? endforeach ?>
        <? endif ?>
        <? else : ?>
            <?= _('Keine Beschreibung vorhanden') ?>
        <? endif ?>
        <ul class="list-csv" style="text-align: center;">
        <? foreach($termin['info'] as $type => $info): ?>
            <? if (trim($info)) : ?>
                <li>
                    <small>
                    <? if (!is_numeric($type)): ?>
                        <em><?= htmlReady($type) ?>:</em>
                    <? endif; ?>
                        <?= htmlReady(trim($info)) ?>
                    </small>
                </li>
            <? endif ?>
        <? endforeach; ?>
        </ul>
    </div>
</article>
