<? if ($resource instanceof Resource): ?>
    <? if ($resource->description): ?>
        <p><?= htmlReady($resource->description) ?></p>
    <? endif ?>
    <? if ($resource->properties): ?>
        <h2><?= _('Eigenschaften') ?></h2>
        <?= $this->render_partial(
            'resources/resource/_standard_properties_display_part.php',
            [
                'property_groups' => $resource->getGroupedProperties()
            ]
        ) ?>
    <? endif ?>

    <? $resource_folder = $resource->getFolder(); ?>
    <? if($resource_folder && $resource_folder->getFiles() && !$hide_files): ?>
    <h2><?= _('Dateien') ?></h2>
        <table class="default sortable-table" data-sortlist="[[2, 0]]">
            <?= $this->render_partial('files/_files_thead') ?>
            <? foreach($resource_folder->getFiles() as $file): ?>
                <? if ($file->isVisible($GLOBALS['user']->id)) : ?>
                    <?= $this->render_partial(
                        'files/_fileref_tr',
                        [
                            'file'           => $file,
                            'current_folder' => $resource_folder,
                            'last_visitdate' => time()
                        ]
                    ) ?>
                <? endif ?>
            <? endforeach ?>
        </table>
    <? endif ?>
<? endif ?>
