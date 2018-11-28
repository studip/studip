<? if ($admin || $evaluations): ?>
<article class="studip">
    <header>
        <h1>
            <?= Icon::create('vote', 'info')->asImg(); ?>
            <?= _('Evaluationen') ?>
        </h1>
        <nav>
        <? if ($admin): ?>
            <a href="<?= URLHelper::getLink('admin_evaluation.php', array('rangeID' => $range_id)) ?>">
                <?= Icon::create('edit', 'clickable')->asImg(); ?>
            </a>
        <? endif; ?>
        </nav>
    </header>

    <? if (!$evaluations): ?>
        <section>
            <?= _('Keine Evaluationen vorhanden. Um neue Umfragen zu erstellen, klicken Sie rechts auf die Zahnräder.') ?>
        </section>
    <? else: ?>
        <? foreach ($evaluations as $evaluation): ?>
            <?= $this->render_partial('evaluation/_evaluation.php', array('evaluation' => $evaluation)); ?>
        <? endforeach; ?>
    <? endif; ?>
</article>
<? endif; ?>