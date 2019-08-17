<?php
# Lifter010: TODO

$nav_items = Navigation::getItem('/')->getIterator()->getArrayCopy();
$nav_items = array_filter($nav_items, function ($item) {
    return $item->isVisible(true);
});

$header_nav = ['visible' => $nav_items, 'hidden' => []];
if (isset($_COOKIE['navigation-length'])) {
    $header_nav['hidden'] = array_splice(
        $header_nav['visible'],
        $_COOKIE['navigation-length']
    );
}
?>

<!-- Leiste unten -->
<div id="barBottomContainer">
    <div id="barBottomLeft">
        <input type="checkbox" id="barTopMenu-toggle">
        <label for="barTopMenu-toggle">
            <?= _('Menü') ?>
        </label>
        <? // The main menu will be placed here when scrolled, see navigation.less ?>
    </div>
    <div id="barTopFont">
        <?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?>
    </div>

    <!-- Dynamische Links ohne Icons -->
    <div id="barBottomright">
        <ul>

        <? if (Navigation::hasItem('/links')): ?>
            <? foreach (Navigation::getItem('/links') as $nav): ?>
                <? if ($nav->isVisible()) : ?>
                    <li class="<? if ($nav->isActive()) echo 'active'; ?> <?= htmlReady($nav->getLinkAttributes()['class']) ?>">
                        <a
                            <? if (is_internal_url($url = $nav->getURL())) : ?>
                                href="<?= URLHelper::getLink($url) ?>"
                            <? else: ?>
                                href="<?= htmlReady($url) ?>" target="_blank" rel="noopener noreferrer"
                            <? endif; ?>
                            <? if ($nav->getDescription()): ?>
                                title="<?= htmlReady($nav->getDescription()) ?>"
                            <? endif; ?>
                            ><?= htmlReady($nav->getTitle()) ?></a>
                    </li>
                <? endif; ?>
            <? endforeach; ?>
        <? endif; ?>

        <? if (isset($search_semester_nr)) : ?>
            <? if (PageLayout::hasCustomQuicksearch()): ?>
                <?= PageLayout::getCustomQuicksearch() ?>
            <? else: ?>
                <li id="quicksearch_item">
                    <script>
                        var selectSem = function (seminar_id, name) {
                            document.location = "<?= URLHelper::getURL("dispatch.php/course/details/", ["send_from_search" => 1, "send_from_search_page" => URLHelper::getURL("dispatch.php/search/courses?keep_result_set=1")])  ?>&sem_id=" + seminar_id;
                        };
                    </script>
                    <?= $GLOBALS['template_factory']->render('globalsearch/searchbar') ?>
                </li>
            <? endif; ?>
        <? endif; ?>

        <? if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('user')): ?>
            <? $active = Navigation::hasItem('/profile')
                      && Navigation::getItem('/profile')->isActive();
            ?>

            <!-- User-Avatar -->
            <li class="header_avatar_container <? if ($active) echo 'active'; ?>" id="barTopAvatar">

            <? if (is_object($GLOBALS['perm']) && PersonalNotifications::isActivated() && $GLOBALS['perm']->have_perm('autor')) : ?>
                <? $notifications = PersonalNotifications::getMyNotifications() ?>
                <? $lastvisit = (int)UserConfig::get($GLOBALS['user']->id)->getValue('NOTIFICATIONS_SEEN_LAST_DATE') ?>
                <div id="notification_container"<?= count($notifications) > 0 ? ' class="hoverable"' : '' ?>>
                    <? foreach ($notifications as $notification) {
                        if ($notification['mkdate'] > $lastvisit) {
                            $alert = true;
                        }
                    } ?>
                    <div id="notification_marker"<?= $alert ? ' class="alert"' : "" ?> title="<?= _("Benachrichtigungen") ?>" data-lastvisit="<?= $lastvisit ?>">
                        <?= count($notifications) ?>
                    </div>
                    <div class="list below" id="notification_list">
                        <a class="mark-all-as-read <? if (count($notifications) < 2) echo 'hidden'; ?>" href="<?= URLHelper::getLink('dispatch.php/jsupdater/mark_notification_read/all', ['return_to' => $_SERVER['REQUEST_URI']]) ?>">
                            <?= _('Alle Benachrichtigungen als gelesen markieren') ?>
                        </a>
                        <ul>
                        <? foreach ($notifications as $notification) : ?>
                            <?= $notification->getLiElement() ?>
                        <? endforeach ?>
                        </ul>
                    </div>
                <? if (PersonalNotifications::isAudioActivated()): ?>
                    <audio id="audio_notification" preload="none">
                        <source src="<?= Assets::url('sounds/blubb.ogg') ?>" type="audio/ogg">
                        <source src="<?= Assets::url('sounds/blubb.mp3') ?>" type="audio/mpeg">
                    </audio>
                <? endif; ?>
                </div>
            <? else: ?>
                <div id="notification_container"></div>
            <? endif; ?>

            <? if (Navigation::hasItem('/avatar')): ?>
                <div id="header_avatar_menu">
                <?php
                $action_menu = ContentGroupMenu::get();
                $action_menu->setLabel(User::findCurrent()->getFullName());
                $action_menu->setAriaLabel(_("Profilmenü"));
                $action_menu->setIcon(Avatar::getAvatar(User::findCurrent()->id)->getImageTag(Avatar::MEDIUM));

                foreach (Navigation::getItem('/avatar') as $subnav) {
                    $action_menu->addLink(
                        URLHelper::getURL($subnav->getURL(), [], true),
                        $subnav->getTitle(),
                        $subnav->getImage()
                    );
                }
                SkipLinks::addIndex(_("Profilmenü"), "header_avatar_menu", 2);
                ?>
                <?= $action_menu->render(); ?>
                </div>
                <?= Icon::create('arr_1down', 'info_alt', ['id' => 'avatar-arrow']); ?>
            <? endif; ?>
            </li>
        <? endif; ?>

        </ul>
    </div>
</div>
<!-- Ende Header -->

<!-- Start Header -->
<div id="flex-header">
    <!--<div id='barTopLogo'>
        <?= Assets::img('logos/logoneu.jpg', ['alt' => 'Logo Uni Göttingen']) ?>
    </div>
     -->

    <? SkipLinks::addIndex(_('Hauptnavigation'), 'barTopMenu', 1); ?>
    <ul id="barTopMenu" role="navigation" <? if (count($header_nav['hidden']) > 0) echo 'class="overflown"'; ?>>
    <? foreach ($header_nav['visible'] as $path => $nav): ?>
        <?= $this->render_partial(
            'header-navigation-item.php',
            compact('path', 'nav', 'accesskey_enabled')
        ) ?>
    <? endforeach; ?>
        <li class="overflow">
            <input type="checkbox" id="header-sink">
            <label for="header-sink">
                <a class="canvasready" href="#">
                    <?= Icon::create('action', 'navigation')->asImg(28, [
                        'class'  => 'headericon original',
                        'title'  => '',
                        'alt'    => '',
                    ]) ?>
                    <div class="navtitle">
                        <?= _('Mehr') ?>&hellip;
                    </div>
                </a>
            </label>

            <ul>
            <? foreach ($header_nav['hidden'] as $path => $nav) : ?>
                <?= $this->render_partial(
                    'header-navigation-item.php',
                    compact('path', 'nav', 'accesskey_enabled')
                ) ?>
            <? endforeach; ?>
            </ul>
        </li>
    </ul>

    <!-- Stud.IP Logo -->
    <a class="studip-logo" id="barTopStudip" href="http://www.studip.de/" title="Stud.IP Homepage" target="_blank" rel="noopener noreferrer">
        Stud.IP Homepage
    </a>
</div>
