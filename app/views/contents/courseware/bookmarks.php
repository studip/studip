<div class="cw-bookmarks">
    <? if(!empty($bookmarks)): ?>
    <ul class="cw-tiles">
        <? foreach($bookmarks as $bookmark) :?>
            <li class="tile <?= htmlReady($bookmark['element']['payload']['color'])?>">
                <a href="<?= htmlReady($bookmark['url'])?>">
                    <div class="preview-image" style="background-image: url(<?= htmlReady($bookmark['element']->getImageUrl()) ?>)" ></div>
                    <div class="description">
                        <header><?= htmlReady($bookmark['element']['title']) ?></header>
                        <div class="description-text-wrapper">
                            <p><?= htmlReady($bookmark['element']['payload']['description']) ?></p>
                        </div>
                        <footer>
                        <? if($bookmark['course']): ?>
                            <?= Icon::create('seminar', Icon::ROLE_INFO_ALT)?> <?= htmlReady($bookmark['course']['name'])?>
                        <? endif; ?>
                        <? if($bookmark['user']): ?>
                            <?= Icon::create('headache', Icon::ROLE_INFO_ALT)?> <?= htmlReady($bookmark['user']->getFullName())?>
                        <? endif; ?>
                        </footer>
                    </div>
                </a>
            </li>
        <? endforeach; ?>
    </ul>
    <? else: ?>
        <?= MessageBox::info(_('Sie haben noch keine Lesezeichen angelegt.')); ?>
    <? endif; ?>
</div>