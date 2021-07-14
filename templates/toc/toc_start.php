<ul class="toc">
    <li class="chapter <?= htmlReady($active) ?>">
        <div id="chapter0">
            <a class="navigate" href="<?= URLHelper::getLink('wiki.php',
                                        ['keyword' => htmlReady($wikiwikiweb)]) ?>  ">
                <?= ($active)
                    ? Icon::create('wiki', 'info')->asImg(20, ['style' => 'padding-right: 5px'])
                    : Icon::create('wiki', 'clickable')->asImg(20, ['style' => 'padding-right: 5px'])
                ?>
                <?= ($wikiwikiweb == 'WikiWikiWeb')
                    ? _('Wiki-Startseite')
                    : htmlReady($wikiwikiweb)
                ?>
                </a>
        </div>
        <?= $children?>
    </li>
</ul>
