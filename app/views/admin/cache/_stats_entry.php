<? if (is_array($entry)) : ?>
    <dl class="default">
        <? foreach ($entry as $index => $data) : ?>
            <dt><?= htmlReady($index) ?></dt>
            <dd><?= $this->render_partial('admin/cache/_stats_entry', ['entry' => $data]) ?></dd>
        <? endforeach ?>
    </dl>
<? else : ?>
    <?= htmlReady($entry) ?>
<? endif; ?>
