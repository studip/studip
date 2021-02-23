<? if ($category->description) : ?>
    <section class="contentbox">
        <header>
            <h1><?= _('Beschreibung') ?></h1>
        </header>
        <section>
            <?= htmlReady($category->description) ?>
        </section>
    </section>
<? endif ?>

<? if ($category->property_definitions): ?>
    <section class="contentbox">
        <header>
            <h1><?= _('Eigenschaften') ?></h1>
        </header>
        <section>
            <ul class="list-unstyled">
                <? foreach ($category->property_definitions as $definition): ?>
                    <li>
                        <?= Icon::create('checkbox-checked')->asImg(['class' => 'text-bottom']) ?>
                        <? if ($definition->system): ?>
                            <strong><?= htmlReady($definition) ?></strong>
                        <? else: ?>
                            <?= htmlReady($definition) ?>
                        <? endif ?>
                        [<?= htmlReady($definition->type) ?>]
                    </li>
                <? endforeach ?>
            </ul>
        </section>
    </section>
<? endif ?>
