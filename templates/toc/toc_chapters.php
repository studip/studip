<ul class="toc <?= $numbering ?>">
    <? $i++ ?>
    <? foreach ($descendants as $descendant) : ?>
        <? if (Request::get('keyword') == $descendant->keyword) : ?>
            <? $active = ' active'; ?>
        <? else : ?>
            <? $active = ''; ?>
        <? endif ?>
        <? $children = $descendant->children; ?>

        <li class="chapter <?= htmlReady($active) ?>" id="chap<?= htmlReady($i) ?>">
        <div>
            <a class="navigate" href="<?= URLHelper::getLink('wiki.php',
                ['keyword' => $descendant->keyword]) ?>">
                <?= htmlReady($descendant->keyword) ?>
            </a>
        </div>

        <? if ($children) : ?>
            <?= $this->render_partial('toc/toc_chapters.php', ['descendants' => $children, 'i' => $i]); ?>
        <? else : ?>
            </li>
        <? endif ?>

    <? endforeach; ?>
</ul>
