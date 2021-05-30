<section class="contentbox course-statusgroups" data-sortable="<?=$controller->url_for('/sorttools', ['order' => 1]) ?>">
<? if ($sem->tools): ?>
    <? foreach ($sem->tools as $tool): ?>
    <?php if (!$tool->getStudipModule()) continue; ?>
        <article class="draggable" id="<?= $tool->plugin_id ?>">
            <header>
                <span class="sg-sortable-handle"></span>
                <h1><?= htmlready($tool->getDisplayName()) ?></h1>
            </header>
        </article>
    <? endforeach ?>
<? endif ?>
</section>


