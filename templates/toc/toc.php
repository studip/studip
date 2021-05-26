<input type="checkbox" id="cb-toc-close"/>
<article class="studip toc_overview toc_transform" id="toc">
<header id="toc_header">
        <h1 id="toc_h1"><?= sprintf(_('Inhalt (%s Seiten)'), htmlReady($toc_counter)) ?></h1>
        <label for="cb-toc" class="check-box"><?=Icon::create('decline')->asImg(20) ?></label>
</header>
    <section style="width: 650px">
        <?= $toc_new ?>
    </section>
</article>
