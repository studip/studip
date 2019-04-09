<? if (isset($flash['decline_inst'])) : ?>
    <?=
    createQuestion(sprintf(_('Wollen Sie sich aus dem/der %s wirklich austragen?'),
            htmlReady($flash['name'])), ['cmd' => 'kill', 'studipticket' => $flash['studipticket']],
        ['cmd'          => 'back',
              'studipticket' => $flash['studipticket']],
        $controller->url_for(sprintf('my_institutes/decline_inst/%s', $flash['inst_id']))); ?>
<? endif ?>

<? if (empty($institutes)) : ?>
    <? if (!Config::get()->ALLOW_SELFASSIGN_INSTITUTE || $GLOBALS['perm']->have_perm("dozent")) : ?>
        <?=
        MessageBox::info(sprintf(_('Sie wurden noch keinen Einrichtungen zugeordnet. Bitte wenden Sie sich an einen der zuständigen %sAdministratoren%s.'),
            '<a href="' . URLHelper::getLink('dispatch.php/siteinfo/show') . '">', '</a>'))?>
    <? else : ?>
        <?=
        MessageBox::info(sprintf(_('Sie haben sich noch keinen Einrichtungen zugeordnet.
           Um sich Einrichtungen zuzuordnen, nutzen Sie bitte die entsprechende %sOption%s unter "Persönliche Angaben - Studiendaten"
           auf Ihrer persönlichen Einstellungsseite.'), '<a href="' . URLHelper::getLink('dispatch.php/settings/studies#einrichtungen') . '">', '</a>'))?>
    <? endif ?>
<? else : ?>
    <? SkipLinks::addIndex(_('Meine Einrichtungen'), 'my_institutes') ?>
    <table class="default" id="my_institutes">
        <caption><?= _('Meine Einrichtungen') ?></caption>
        <colgroup>
            <col width="10px">
            <col width="25px">
            <col>
            <col width="<?= $nav_elements * 27 ?>px">
            <col width="45px">
        </colgroup>
        <thead>
        <tr>
            <th></th>
            <th></th>
            <th><?= _("Name") ?></th>
            <th style="text-align: center"><?= _("Inhalt") ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($institutes as $values) : ?>
            <? $lastVisit = $values['visitdate']; ?>
            <? $instid = $values['institut_id'] ?>
            <tr>
                <td style="width:1px"></td>
                <td>
                    <?=
                    (InstituteAvatar::getAvatar($instid)->getImageTag(Avatar::SMALL, tooltip2(htmlReady($values['name']))) != '' ? Icon::create('institute', 'clickable', ['title' => htmlReady($values['name'])])->asImg(20) :
                        InstituteAvatar::getAvatar($instid)->getImageTag(Avatar::SMALL, tooltip2(htmlReady($values['name'])))) ?>
                </td>

                <td style="text-align: left">
                    <a href="<?= URLHelper::getLink('dispatch.php/institute/overview', ['auswahl' => $instid]) ?>">
                        <?= htmlReady($GLOBALS['INST_TYPE'][$values["type"]]["name"] . ": " . $values["name"]) ?>
                    </a>
                </td>

                <td style="text-align: left; white-space: nowrap">
                    <? if (!empty($values['navigation'])) : ?>
                        <? foreach (MyRealmModel::array_rtrim($values['navigation']) as $key => $nav)  : ?>
                            <? if (isset($nav) && $nav->isVisible(true)) : ?>
                                <a href="<?=
                                UrlHelper::getLink('dispatch.php/institute/overview',
                                    ['auswahl'     => $instid,
                                          'redirect_to' => strtr($nav->getURL(), '?', '&')]) ?>" <?= $nav->hasBadgeNumber() ? 'class="badge" data-badge-number="' . intval($nav->getBadgeNumber()) . '"' : '' ?>>
                                    <?= $nav->getImage()->asImg(20, $nav->getLinkAttributes()) ?>
                                </a>
                            <? elseif (is_string($key)) : ?>
                                <?= Assets::img('blank.gif', ['widtd' => 20, 'height' => 20]); ?>
                            <? endif ?>
                        <? endforeach ?>
                    <? endif ?>
                </td>

                <td style="text-align: left; white-space: nowrap">
                    <? if (Config::get()->ALLOW_SELFASSIGN_INSTITUTE && $values['perms'] == 'user') : ?>
                        <a href="<?=$controller->url_for('my_institutes/decline_inst/'.$instid)?>">
                            <?= Icon::create('door-leave', 'inactive', ['title' => _("aus der Einrichtung austragen")])->asImg(20) ?>
                        </a>
                    <? else : ?>
                        <?= Assets::img('blank.gif', ['size' => '20']) ?>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif ?>


<?php
$sidebar = Sidebar::Get();
$sidebar->setImage('sidebar/institute-sidebar.png');
$sidebar->setTitle(_('Meine Einrichtungen'));

$links = new ActionsWidget();
if ($reset) {
    $links->addLink(_('Alles als gelesen markieren'),
                    $controller->url_for('my_institutes/tabularasa/' . time()), Icon::create('accept', 'clickable'));
}
if ($GLOBALS['perm']->have_perm('dozent') && !empty($institutes)) {
    $links->addLink(_('Einrichtungsdaten bearbeiten'),
                    URLHelper::getURL('dispatch.php/settings/statusgruppen'), Icon::create('institute+edit', 'clickable') );
}
if ($GLOBALS['perm']->have_perm('autor')) {
    $links->addLink(_('Einrichtungen suchen'),
                    URLHelper::getURL('dispatch.php/search/globalsearch#GlobalSearchInstitutes'), Icon::create('institute+add', 'clickable') );
    $links->addLink(_('Studiendaten bearbeiten'),
                    URLHelper::getURL('dispatch.php/settings/studies'), Icon::create('person', 'clickable'));
}
$sidebar->addWidget($links);
