<?php
$container_data = [
    'id' => (int)$container->id,
];

// TODO: Remove debug
$class = Request::option('css-class');
?>
<noscript>
    <?= MessageBox::info(_('Bitte aktivieren Sie JavaScript in Ihrem Browser')) ?>
</noscript>
<section class="grid-stack <?= $class ?> <? if ($mode === 'admin') echo 'admin-mode'; ?>" data-widgetsystem='<?= json_encode($container_data) ?>'>
<? foreach ($container->elements as $element): ?>
    <div class="grid-stack-item"
        data-gs-x="<?= $element->x ?>" data-gs-y="<?= $element->y ?>"
        data-gs-width="<?= $element->width ?>" data-gs-height="<?= $element->height ?>"
        data-element-id="<?= $element->id ?>"
    <? if ($element->locked): ?>
        data-gs-locked="yes" data-gs-no-resize="yes" data-gs-no-move="yes"
    <? endif; ?>
    <? if ($element->removable): ?>
        data-gs-removable
    <? endif; ?>
        >
        <?= $element->render(compact('mode')) ?>
    </div>
<? endforeach; ?>
</section>
