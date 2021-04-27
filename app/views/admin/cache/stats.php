<section class="contentbox">
    <header>
        <h1><?= _('Cache-Statistiken') ?></h1>
    </header>
    <br>
    <ul>
        <? foreach ($stats as $index => $data) : ?>
            <li>
                <strong><?= htmlReady($index) ?></strong>
                <?= $this->render_partial('admin/cache/_stats_entry', ['entry' => $data]) ?>
            </li>
        <? endforeach ?>
    </ul>
    <br>
</section>
