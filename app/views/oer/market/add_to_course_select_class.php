
<h2><?= _('Bereich auswählen') ?></h2>

<div class="file_select_possibilities">
    <div>
        <? foreach ($classes as $class) : ?>
        <a href="<?= $controller->link_for("oer/market/add_to_course/".$material->getId(), ['seminar_id' => $course->getId(), 'class' => $class]) ?>">
            <? $object = PluginManager::getInstance()->getPlugin($class) ?: new $class() ?>
            <? $metadata = $object->getMetadata() ?>

            <? $icon = $object->oerGetIcon() ?>
            <? if ($icon) : ?>
                <?= $icon->asImg(50) ?>
            <? endif ?>
            <?= htmlReady($metadata['displayname']) ?>
        </a>
        <? endforeach ?>
    </div>
</div>
