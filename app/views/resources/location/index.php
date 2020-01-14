<? if ($location): ?>
    <article class="resource-detail-page">
        <? if ($location->description) : ?>
            <h3><?= _('Beschreibung und Hinweise') ?></h3>
            <p class="resource-description-text"><?= htmlReady($location->description) ?></p>
        <? endif ?>
        <? if (Request::isDialog() && ($geo_coordinates_object instanceof ResourceProperty)) : ?>
            <?= \Studip\LinkButton::create(
                _('Zum Lageplan'),
                ResourceManager::getMapUrlForResourcePosition($geo_coordinates_object),
                ['target' => '_blank']
            ) ?>
        <? endif ?>
        <? $property_groups = $location->getGroupedProperties($other_properties) ?>
        <? if (count($property_groups)): ?>
            <h2><?= _('Eigenschaften') ?></h2>
            <?= $this->render_partial(
                'resources/resource/_standard_properties_display_part.php',
                [
                    'property_groups' => $property_groups
                ]
            ) ?>
        <? endif ?>
    </article>

    <? $resource_folder = $location->getFolder(); ?>
    <? if($resource_folder && $resource_folder->getFiles()): ?>
        <h2><?= _('Dateien') ?></h2>
        <table class="default sortable-table" data-sortlist="[[2, 0]]">
            <?= $this->render_partial('files/_files_thead') ?>
            <? foreach($resource_folder->getFiles() as $file_ref): ?>
                <?= $this->render_partial('files/_fileref_tr',
                    [
                        'file_ref' => $file_ref,
                        'current_folder' => $resource_folder,
                        'last_visitdate' => time()
                    ]) ?>
            <? endforeach ?>
        </table>
    <? endif ?>

    <? if ($location->children): ?>
        <h2><?= _('Gebäude') ?></h2>
        <ul class="list-unstyled">
        <? foreach ($location->findChildrenByClassName('Building') as $child): ?>
            <li>
                <a href="<?= $controller->url_for('resources/building/index/'. $child->id); ?>" <?= (Request::isDialog())?'data-dialog':''; ?> >
                    <?= $child->getIcon('clickable')->asImg(
                        '16px',
                        ['class' => 'text-bottom']
                    ) ?>
                    <?= htmlReady($child->name); ?>
                </a>
            </li>
        <? endforeach; ?>
        </ul>
    <? endif; ?>

<? endif ?>
