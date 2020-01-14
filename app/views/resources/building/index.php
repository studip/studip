<? if ($building): ?>
    <nav class="resource-hierarchy">
        <? if ($building->location): ?>
            <a href="<?= $building->location->getLink('show') ?>" <?= (Request::isDialog())?'data-dialog':''; ?> >
                <?= htmlReady($building->location->name)?>
            </a>
            &gt;
        <? endif ?>
        <?= htmlReady($building->name) ?>
    </nav>
    <article class="resource-detail-page">
        <? if ($building->description): ?>
            <h3><?= _('Beschreibung und Hinweise') ?></h3>
            <p class="resource-description-text"><?= htmlReady($building->description) ?></p>
        <? endif ?>
        <? if ($building->number) : ?>
            <h3><?= _('Gebäudenummer') ?></h3>
            <p><?= htmlReady($building->number) ?></p>
        <? endif ?>
        <? if ($building->address) : ?>
            <h3><?= _('Adresse') ?></h3>
            <p><?= htmlReady($building->address) ?></p>
        <? endif ?>
        <? if (Request::isDialog() && ($geo_coordinates_object instanceof ResourceProperty)): ?>
            <?= \Studip\LinkButton::create(
                _('Zum Lageplan'),
                ResourceManager::getMapUrlForResourcePosition($geo_coordinates_object),
                ['target' => '_blank']
            ) ?>
        <? endif ?>
        <?
        $property_groups = $building->getGroupedProperties(
            ['geo_coordinates', 'number', 'address']
        );
        ?>
        <? if (count($property_groups)): ?>
            <h2><?= _('Weitere Eigenschaften') ?></h2>
            <?= $this->render_partial(
                'resources/resource/_standard_properties_display_part.php',
                [
                    'property_groups' => $property_groups
                ]
            ) ?>
        <? endif ?>
    </article>
<? endif ?>

<? $resource_folder = $building->getFolder(); ?>
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

<? if ($building->children): ?>
    <h2><?= _('Räume') ?></h2>
    <ul class="list-unstyled">
    <? foreach ($building->findChildrenByClassName('Room') as $child): ?>
        <li>
            <a href="<?= $controller->url_for('resources/room/index/'. $child->id); ?>" <?= (Request::isDialog())?'data-dialog':''; ?> >
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
