<ul class="boxed-grid">
<? foreach (Navigation::getItem('/course/admin') as $name => $nav): ?>
    <? if ($nav->isVisible() && $name != 'main'): ?>
        <li>
            <a href="<?= URLHelper::getLink($nav->getURL()) ?>">
                <h3>
                    <? if ($nav->getImage()): ?>
                        <?= $nav->getImage()->asImg(false, $nav->getLinkAttributes()) ?>
                    <? endif; ?>
                    <?= htmlReady($nav->getTitle()) ?>
                </h3>
                <p>
                    <?= htmlReady($nav->getDescription()) ?>
                </p>
            </a>
        </li>
    <? endif; ?>
<? endforeach; ?>
<!--
    this is pretty ugly but we need to spawn some empty elements so that the
    last row of the flex grid won't be messed up if the boxes don't line up
-->
    <li></li><li></li><li></li>
    <li></li><li></li><li></li>
</ul>
