<?
# Lifter010: TODO
foreach (Navigation::getItem("/")->getSubNavigation() as $path => $nav) {
    if ($nav->isActive()) {
        $path1 = $path;
    }
}
$ebene3 = [];
?>
<div class="tabs_wrapper">
    <? SkipLinks::addIndex(_('Erste Reiternavigation'), 'tabs', 10); ?>
    <ul id="tabs" role="navigation">
        <? if (!empty($navigation)): ?>
        <? foreach ($navigation as $path => $nav) : ?>
            <? if ($nav->isVisible()) : ?>
                <li id="nav_<?= $path1 ?>_<?= $path ?>"<?= $nav->isActive() ? ' class="current"' : '' ?>>
                    <? if ($nav->isActive()) $path2 = $path; ?>
                    <? if ($nav->isEnabled()): ?>
                        <?
                        $attr = array_merge(['class' => ''], $nav->getLinkAttributes());
                        if ($nav->hasBadgeNumber()) {
                            $attr['class'] = trim("{$attr['class']} badge");
                            $attr['data-badge-number'] = (int) $nav->getBadgeNumber();
                        }
                        ?>
                        <a href="<?= URLHelper::getLink($nav->getURL()) ?>" <?= arrayToHtmlAttributes($attr) ?>>
                            <? if ($image = $nav->getImage()) : ?>
                                <?= $image->asImg(['class' => "tab-icon", 'title' => $nav->getTitle() ? htmlReady($nav->getTitle()) : htmlReady($nav->getDescription())]) ?>
                            <? endif ?>
                            <span title="<?= $nav->getDescription() ? htmlReady($nav->getDescription()) :  htmlReady($nav->getTitle())?>" class="tab-title"><?= $nav->getTitle() ? htmlReady($nav->getTitle()) : '&nbsp;' ?></span>
                        </a>
                        <? if (count($nav->getSubNavigation()) > 1): ?>

                        <?
                        if ($nav->isActive()) {
                            foreach ($nav->getSubNavigation() as $subnav) {
                                $ebene3[$subnav->getURL()]  = $subnav;
                            }
                        }
                        /*$content_group = ContentGroupMenu::get();
                            $content_group->setLabel("");
                            $content_group->setIcon(Icon::create('arr_1down', 'clickable', array()));
                            foreach ($nav->getSubNavigation() as $subnav) {
                                $content_group->addLink(URLHelper::getLink($subnav->getURL()), _($subnav->getTitle()), $subnav->getImage());
                            }*/
                        ?>
                        <?//= $content_group->render(); ?>

                        <? endif; ?>
                    <? else: ?>
                        <span class="quiet tab-title">
                            <? if ($image = $nav->getImage()) : ?>
                                <?= $image->asImg(['class' => "tab-icon", 'title' => htmlReady($nav->getTitle())]) ?>
                            <? endif ?>
                            <?= htmlReady($nav->getTitle()) ?>
                        </span>
                    <? endif ?>
                </li>
            <? endif ?>
        <? endforeach ?>
       <? endif; ?>
    </ul>
    <? if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('autor')) : ?>
        <?= Helpbar::get()->render() ?>
    <? endif; ?>
</div>
