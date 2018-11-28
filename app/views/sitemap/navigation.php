<?
# Lifter010: TODO
?>
<ul>
    <? foreach ($navigation as $nav) : ?>
        <? if ($nav->isVisible($needs_image) && $nav->isEnabled()) : ?>
            <li>
                <a href="<?= URLHelper::getLink($nav->getURL()) ?>" style="font-weight: <?= $style ?>;">
                    <?= htmlReady($nav->getTitle()) ?>
                </a>
                <? if (count($nav->getSubNavigation())) : ?>
                    <?= $this->render_partial('sitemap/navigation',
                            array('navigation' => $nav, 'needs_image' => false, 'style' => 'normal')) ?>
                <? endif ?>
            </li>
        <? endif ?>
    <? endforeach ?>
</ul>
